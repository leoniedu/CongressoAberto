## FIX: title is not unique!!!!
## possible solutions.
## 1. enforce unique title.


rf <- function(x=NULL) {
  if (.Platform$OS.type!="unix") {
    run.from <- "C:/reps/CongressoAberto"
  } else {
    run.from <- "~/reps/CongressoAberto"
  }
  ## side effect: load functions
  source(paste(run.from,"/R/caFunctions.R",sep=""),encoding="utf8")
  if (is.null(x)) {
    run.from
  } else {
    paste(run.from,"/",x,sep='')
  }
}
source(rf("R/spatial.R"))
source(rf("R/wordpress.R"))

connect.db()
connect.wp()


pmedians <- dbGetQueryU(connect, "select * from br_partymedians where finaldate=(select max(finaldate) from br_partymedians) ")
pmedians <- pmedians[order(pmedians$coord1D),]


getThresh <- function(rc, billtype, billdescription) {
  Nao <-  sum(rc=="Não")
  Sim <-  sum(rc=="Sim")
  Abs <-  sum(rc=="Abstenção")
  ## simple majority
  smaj <- round((Nao+Sim)/2)
  if (any(grepl("requerimento", billdescription, ignore.case=TRUE))) {
    ## camara rules says that requerimento only needs a simple majority
    ## with quorum
    thresh <- smaj
  }
  if (billtype=="PEC") {
    ## constitutional amendments need 3/5
    thresh <- 308
  } else if (billtype=="PLP") {
    ## lei complementar need 1/2
    thresh <- 257
  } else {
    ## else need simple majority
    thresh <- smaj
  }
  ## But note, you still need a quorum.
  ## So if e. g.
  ## Sim=100
  ## Nao=100
  ## Abst, Obs, = 0
  ## then
  ## needed to complete the quorum: 257-(Sim+Nao+Abs)
  ## thresh = Sim + max(0, 257-(Sim+Nao+Abs))
  ## this is the "effective threshold" for _approving_ legislation
  ## FIX: double check this
  quorum <- Sim+Nao+Abs
  c(thresh, if (quorum>256) thresh else Sim + 257-quorum)
}


govwins <- function(rcnow, rcgov, thresh) {
  if (nrow(rcgov)==0) return(NA)
  pro <- sum(rcnow$rc=="Sim")-thresh
  if (rcgov$rc=="Sim") {
    progov <- pro
  } else  { ## FIX: is this doing it right for abstentions and the like?
    progov <- -pro
  } 
  progov
}



govpos <- function(rcgov) {
  if (nrow(rcgov)==0) return(NA)
  res <- NA
  ifelse(rcgov$rc=="Sim", "A Favor", "Contra")
}

sumroll <- function(rcnow, margin, rcgov) {
  res <- NULL
  ## FIX: what happens when there is less than 513 deputies?
  quorum <- sum(rcnow$rc%in%c("Ausente", "Obstrução"))<257
  if (!quorum) {
    res <- c(res, "Não houve quorum.")
  }
  tx <- table(rcnow$rc)
  ntx <- c("Sim", "Não", "Obstrução", "Abstenção",  "Ausente")
  tx <- tx[ntx]
  tx[is.na(tx)] <- 0
  ntx <- c("Sim", "Não", "Obstrução", "Abstenção",  "Ausentes")
  names(tx) <- ntx
  res <- c(res,paste(names(tx)%+%": ",tx,collapse="; "))  
  if (!is.na(margin)) {
    res <- c(res, paste("Posição do governo:",rcgov$rc, ". ", sep=''))
    if (quorum) {
      if (margin>0) {
        res <- c(res, "O governo venceu a votação.")
      } else {
        res <- c(res, "O governo foi derrotado.")
      }
    }
  } else {
    res <- c(res, "Não houve indicação do governo.")
  }
  paste(paste("<p>", res, collapse="</p>"), "</p>", collapse=" ")
}
  
postroll <- function(rcid=2797, saveplot=TRUE, post=TRUE) {
  print(rcid)
  rcs <- dbGetQueryU(connect, "select * from br_votacoes where rcvoteid="%+%rcid)
  rcnow <- dbGetQueryU(connect, "select * from br_votos where rcvoteid="%+%rcid)
  ## fix pfl
  rcnow$party <- recode.party(rcnow$party)
  ## FIX: what to do with abstentions, etc
  rcgov <- dbGetQueryU(connect, "select * from br_leaders where block='GOV' and rc!='Liberado' and rcvoteid="%+%rcid)
  fulltext <- paste(rcs,collapse="\n")
  ## create post data
  title <- rcs$billproc
  name <- with(rcs,encode(paste(bill,rcvoteid,sep="-")))
  content <- paste('<script language="php">$rcvoteid = ',rcs$rcvoteid,';include("php/rc.php");</script>')
  date <- wptime(rcs$rcdate)
  tagsname <- with(rcs,sapply(c(billtype,billyear),
                              encode))
  tagslug <- gsub("[-,.]+","_",tagsname)
  tags <- data.frame(slug=tagslug,name=tagsname)
  billtype <- toupper(rcs$billtype)
  threshold <- getThresh(billtype=rcs$billtype,
                         billdescription=rcs$billdescription,
                         rc=rcnow$rc)
  margin <- govwins(rcnow, rcgov, threshold[1])
  post_excerpt <- sumroll(rcnow, margin, rcgov)
  post_category <- data.frame(slug="votacoes",name="Votações")
  img <- paste("images/rollcalls/bar",rcid, sep='')
  if (!is.na(margin))  {
    if (margin>0) {
      post_category <- rbind(post_category, data.frame(slug="governo_venceu",name="Governo venceu"))
    } else {
      post_category <- rbind(post_category, data.frame(slug="governo_perdeu",name="Governo foi derrotado"))
    }
    if (margin<10) {
      img <- paste("images/rollcalls/mosaic",rcid, sep='')
      post_category <- rbind(post_category, data.frame(slug="Featured",name="Featured"))
    }
  } else {
  }
  barplots <- barplot.rc(rcnow, govpos(rcgov), threshold=threshold[2])  
  mosaicplots <- mosaic.rc(rcnow, pmedians)
  ## write plots to disk
  print.png <- function(plots, fn, crop=TRUE, small=5, large=6) {
    fns <- rf(fn%+%"small.pdf")
    fnl <- rf(fn%+%"large.pdf")
    pdf(file=fns, bg="white", width=small, height=small)
    print(plots[["small"]])
    dev.off()
    convert.png(fns, crop=crop)
    pdf(file=fnl, bg="white", width=large, height=large)
    print(plots[["large"]])
    dev.off()
    convert.png(fnl, crop=crop)
  }
  if (saveplot) {
    print.png(barplots, paste("images/rollcalls/bar",rcid, sep=''), crop=FALSE, small=2.5)
    print.png(mosaicplots, paste("images/rollcalls/mosaic",rcid, sep=''))
    ## maps
    ## small
    ## large
    fn <- paste("images/rollcalls/map",rcid, sep='')
    fns <- rf(fn%+%"small.pdf")
    fnl <- rf(fn%+%"large.pdf")
    pdf(file=fns, width=4, height=4, bg="white")
    map.rc(rcnow, large=FALSE, percent=TRUE)
    dev.off()
    pdf(file=fnl,  width=6, height=6, bg="white")
    map.rc(rcnow, large=TRUE, percent=TRUE)
    dev.off()
    convert.png(fns, crop=TRUE)
    convert.png(fnl, crop=TRUE)
  }
  if (post) {
    postid <- wpAddByName(conwp,post_title=title,post_type="post",post_content=content,post_date=date$brasilia,post_date_gmt=date$gmt,fulltext=fulltext,post_excerpt=post_excerpt,post_category=post_category,
                          custom_fields=data.frame(meta_key="Image",meta_value=img%+%"small.png"),
                          post_name=name,tags=tags)
    ##FIX: create table in mysql
    dbWriteTableU(connect,"br_rcvoteidpostid",data.frame(postid,rcvoteid=rcs$rcvoteid),append=TRUE)
    res <- c(rcid,postid)
    print(res)
    res
  }
}




m1 <- readShape.cent(rf("data/maps/BRASIL.shp"), "UF")

rcsnow <- dbGetQuery(connect,"select rcvoteid, rcdate from br_votacoes where legis="%+%"53")
rcsnow <- rcsnow[order(rcsnow$rcdate, decreasing=TRUE),]


rcsnow <- rcsnow$rcvoteid

##billsnow <- bills$billid[sample(1:nrow(bills),2)]
##billsnow <- bills$billid[1:nrow(bills)]
##t(sapply(rcsnow[-c(1:10)],postroll, saveplot=TRUE, post=FALSE))
##t(sapply(rcsnow[-c(1:10)],postroll, saveplot=FALSE, post=FALSE))
##t(sapply(tail(rcsnow,10),postroll, saveplot=TRUE, post=TRUE))
##t(sapply(tail(rcsnow,10),postroll, saveplot=TRUE, post=FALSE))
res <- t(sapply(rcsnow, function(x) {
  print(x)
  try(postroll(x, saveplot=TRUE, post=TRUE), silent=TRUE)
}))
##try(system("syncCA images"))
