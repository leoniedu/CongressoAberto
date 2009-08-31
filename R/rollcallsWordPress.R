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
source(rf("R/wordpress.R"))

connect.db()
connect.wp()


pmedians <- dbGetQueryU(connect, "select * from br_partymedians where finaldate=(select max(finaldate) from br_partymedians) ")
pmedians <- pmedians[order(pmedians$coord1D),]




## roll calls are posts, not pages! (since they follow  a chronology.)
govwins <- function(rcnow, rcs, rcgov) {
  if (nrow(rcgov)==0) return(NA)
  res <- NA
  billtype <- rcs$billtype
  if (billtype=="PEC") {
    thresh <- 308
  } else if (billtype=="PLP") {
    thresh <- 257
  } else {
    thresh <- sum(rcnow$rc=="Não")
  }
  pro <- sum(rcnow$rc=="Sim")-thresh
  if (rcgov$rc=="Sim") {
    progov <- pro
    if (pro>=0) {
      res <- TRUE
    } else {
      res <- FALSE
    }
  } else if (rcgov$rc=="Não") {
    progov <- -pro
    if (pro<0) {
      res <- TRUE
    } else {
      res <- FALSE
    }      
  }
  list(win=res, margin=progov)       
}



govpos <- function(rcgov) {
  if (nrow(rcgov)==0) return(NA)
  res <- NA
  ifelse(rcgov$rc=="Sim", "A Favor", "Contra")
}

sumroll <- function(rcnow, govres, rcgov) {
  res <- NULL
  quorum <- 257 ## FIX: what happens when there is less than 513 deputies?
  if (sum(rcnow$rc%in%c("Ausente", "Obstrução"))>256) {
    res <- c(res, "Não houve quorum.")
  }
  tx <- table(rcnow$rc)
  if (!tx["Ausente"]%in%1) {
    names(tx) <- car::recode(names(tx),"'Ausente'='Ausentes'")
  }
  if (!is.na(govres)) {    
    if (govres) {
      res <- c(res, "O governo venceu a votação.")
    } else {
      res <- c(res, "O governo foi derrotado.")
    }
    res <- c(res, paste("Posição do governo:",rcgov$rc, ". "))
  }
  res <- c(res,paste(names(tx)%+%": ",tx,collapse="; "))
  paste(res, collapse="\n")
}

postroll <- function(rcid=2797) {
  rcs <- dbGetQueryU(connect, "select * from br_votacoes where rcvoteid="%+%rcid)
  rcnow <- dbGetQueryU(connect, "select * from br_votos where rcvoteid="%+%rcid)
  rcgov <- dbGetQueryU(connect, "select * from br_leaders where block='GOV' and rcvoteid="%+%rcid)
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
  govres <- govwins(rcnow, rcs, rcgov)
  closevote <- govres[["margin"]]<10
  govres <- govres[["win"]]
  post_excerpt <- sumroll(rcnow, govres, rcgov)
  post_category <- data.frame(slug="votacoes",name="Votações")
  if (!is.na(govres))  {
    barplots <- barplot.rc(rcnow, govpos(rcgov))
    if (govres) {
      post_category <- rbind(post_category, data.frame(slug="governo_venceu",name="Governo venceu"))
    } else {
      post_category <- rbind(post_category, data.frame(slug="governo_perdeu",name="Governo foi derrotado"))
    }
  }
  ## plots!
  barplots <- barplot.rc(rcnow, govpos(rcgov))
  mosaicplots <- mosaic.rc(rcnow, pmedians)
  ## write plots to disk
  print.png <- function(plots, fn) {
    fns <- rf(fn%+%"small.pdf")
    fnl <- rf(fn%+%"large.pdf")
    pdf(file=fns, bg="white")
    print(plots[["small"]])
    dev.off()
    convert.png(fns, crop=TRUE)
    pdf(file=fnl, bg="white")
    print(plots[["large"]])
    dev.off()
    convert.png(fnl, crop=TRUE)
  }
  img <- paste("images/rollcalls/bar",rcid, sep='')
  print.png(barplots, paste("images/rollcalls/bar",rcid, sep=''))
  print.png(mosaicplots, paste("images/rollcalls/mosaic",rcid, sep=''))
  postid <- wpAddByTitle(conwp,post_title=title,post_type="post",post_content=content,post_date=date$brasilia,post_date_gmt=date$gmt,fulltext=fulltext,post_excerpt=post_excerpt,post_category=post_category,
                         custom_fields=data.frame(meta_key="Image",meta_value=img%+%"small.png"),
                         post_name=name,tags=tags)
  ##FIX: create table in mysql
  dbWriteTableU(connect,"br_rcvoteidpostid",data.frame(postid,rcvoteid=rcs$rcvoteid),append=TRUE)
  res <- c(rcid,postid)
  print(res)
  res  
}

rcsnow <- sort(unlist(dbGetQuery(connect,"select rcvoteid from br_votacoes where legis="%+%"53")))

##billsnow <- bills$billid[sample(1:nrow(bills),2)]
##billsnow <- bills$billid[1:nrow(bills)]
t(sapply(tail(sort(rcsnow),2),postroll))
