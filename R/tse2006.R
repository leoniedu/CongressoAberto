source("~/reps/CongressoAberto/R/caFunctions.R",encoding="UTF8")
##run.from <- "~/reps/CongressoAberto/data/camara/rollcalls"

connect.db()

## Downloading from tse
## candidaturas
##http://www.tse.gov.br/sadEleicao2006DivCand/listaBens.jsp?sg_ue=SP&sq_cand=10297

## electoral
load("../data/electoral/2006.RData")
##votos.x situacao.x turno.x votos.y situacao.y turno.y

library(gtools)
tmp <- smartbind(depfed.nom,depest.nom,senador)
tmp$office <- car::recode(tmp$cargo, "c('deputado estadual','deputado distrital')='Deputados Estaduais';'deputado federal'='Deputados Federais';'senador'='Senadores'")
tmp$tseid <- with(tmp,paste(office,toupper(uf),numero,sep=";"))

dbWriteTable(connect,"br_tse2006legis",tmp)





##load data from CIS
dcis <- read.spss("../data/electoral/cis_2006.sav",to.data.frame = TRUE,reencode="latin1",trim.factor.names=TRUE,trim_values=TRUE)
names(dcis) <- c("id","office","state","region","name","number","party","coalition","coalitcomp","birth","birthyear","age","ageag","sex","married","occupation","occupationag","education","nationality","citybith","statebirth","regionbirth","situation","spendmax","spendmaxag","wealth","wealthag","obs","votes","percovote","outcome")
dcis <- data.frame(lapply(dcis,function(x) {
  if (is.factor(x)) {
    levels(x) <- trim(levels(x))
  }
  x
}))

## convert date
dcis$birth <- Hmisc::importConvertDateTime(dcis$birth,type="date",input="spss")
dcis$state <- toupper(state.l2a(dcis$state))
##tse actually is not an unique id. (see the   situation variable)
dcis$tseid <- with(dcis,paste(office,state,number,sep=";"))
##dcis <- subset(dcis,situation!="Não Concorreu")
dcis$firstlast <- clean(firstlast(dcis$name))


###############
tmp0 <- with(tmp,unique(data.frame(tseid,nome)))

###############
tmp1 <- merge(tmp,dcis,by="tseid")




## subset deputados federais
##dcis.df <- subset(dcis,office=="Deputados Federais")


## get data from CA (note, we will try to find everyone, not just the current dfs)
dca <- dbGetQuery(connect,"select * from br_bio")
dca <- iconv.df(dca)
dca$firstlast <- clean(firstlast(dca$name))
dca$birthyear <- as.numeric(format.Date(dca$birth,"%Y"))
dca$s20072011 <- FALSE
dca$s20072011[grep("2007-2011",dca$sessions)] <- TRUE

match.state <- function(d1,d2) subset(d2,state==d1$state)
match.office <- function(d1,d2,office.now="Deputados Federais") subset(d2,office==office.now)
match.birthyear <- function(d1,d2,maxd=3) {
  if (!is.na(d1$birthyear)) {
    y1 <- d1$birthyear
    y2 <- d2$birthyear
    d2[y2%in%seq(y1-maxd,y1+maxd),]
  } else {
    d2
  }
}
match.name <-  function(d1,d2,maxd=.2,name1="name",name2="name") {
  d2 <- d2[agrep(d1[,name1],d2[,name2],max.distance=maxd),]
  ## if multiple matches, decrease max dist
  gseq <- seq(maxd,0,length=10)
  for (i in gseq) {
    ##while ((maxd>0.001)&(nrow(d2)>1)) {
    d2 <- d2[agrep(d1[,name1],d2[,name2],max.distance=i),]
    if (nrow(d2)==1) {
      break
    }
  }
  ##if (nrow(d2)>1) stop("still multiple matches")
  d2
}  
match.birth <-  function(d1,d2,maxd=.2) {
  if (!is.na(d1$birth)) {
    d2 <- d2[agrep(as.character(d1$birth),as.character(d2$birth),max.distance=maxd),]
    ## if multiple matches, decrease max dist
    gseq <- seq(maxd,0,length=10)
    for (i in gseq) {
      d2 <- d2[agrep(d1$birth,d2$birth,max.distance=i),]
      if (nrow(d2)==1) {
        break
      }
    }
  }
  d2
}  


match <- function(d1.all,d2.all,match.all=FALSE) {
  bioidtse <- matrix(ncol=3,nrow=0)
  for (i in 1:nrow(d1.all)) {
    if (i>1) {
      now <- nrow(bioidtse)/(i-1)
      if (match.all & (now<1)) stop(i)
    }
    if (i%%20 == 0) cat(round(i/nrow(d1.all),2),",")
    d1 <- d1.all[i,]
    d2 <- d2.all
    ## * Match exactly by office if the deputy is in the 2007-2011 bio db
    if  (d1$s20072011) {
      d2 <- subset(d2,office=="Deputados Federais")
    }
    d2 <- match.state(d1,d2)
    d2 <- match.birthyear(d1,d2,3)
    d2n <- match.name(d1,d2,10,"name","name")
    if (nrow(d2n)==0) {
      d2n <- match.name(d1,d2,.2,"firstlast","firstlast")
  }
    d2 <- d2n
    if (nrow(d2)>1) {
      d2 <- match.birth(d1,d2,.2)
    }
    if (nrow(d2)>1) {
      stop("multiple matches")
    }  
    if (nrow(d2)==1) {
      id <- c(as.character(d1$bioid),as.character(d2$tseid),as.character(d2$id))
      ##cat("matched",id[1],";")
      bioidtse <- rbind(bioidtse,id)
      d2.all <- subset(d2.all,tseid!=d2$tseid)
      ##print(dim(d2.all))
    }
  }
  rownames(bioidtse) <- NULL
  bioidtse <- data.frame(bioidtse)
  names(bioidtse) <- c("bioid","tseid","id")
  res <- list(bioidtse=bioidtse,d2.all=d2.all)
  save(res,file="tmp.RData")
}

## The plan is to match each row of bio to someone in TSE
## First match current deps
matchcdf <- match(subset(dca,s20072011),subset(dcis,office=="Deputados Federais"),match.all=TRUE)
## Then match everyone else
matchncdf <- match(subset(dca,!s20072011),subset(dcis,office!="Deputados Federais"),match.all=FALSE)
matches <- rbind(matchcdf[[1]],matchncdf[[1]])
tmp <- merge(matches,dcis)

names(matches)[3] <- "csiid"
save(matches,file="../data/electoral/biocis2006.RData")

## write to db
dbRemoveTable(connect,"br_csiidbioid")
dbWriteTable(connect,"br_csiidbioid",matches)

## FIX: We can't trust the "outcome" variable
## RENATO COZZOLINO SOBRINHO (Suplente) [coded as eleito in CIS db]
## EDGARD MONTENOR FERNANDES (Suplente) [coded as eleito in CIS db]
## MARCELO RICARDO MARIANO (Suplente) [coded as eleito in CIS db]



## matching to depfed

