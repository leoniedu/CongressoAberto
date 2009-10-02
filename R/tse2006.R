##http://www.tse.gov.br/sadEleicao2006DivCand/procCandidatoListar.jsp?tribunal=SP&cargo=3&docsPerPage=10000
source("~/reps/CongressoAberto/R/caFunctions.R")
source("~/reps/CongressoAberto/R/matchFunctions.R")

connect.db()

##run.from <- "~/reps/CongressoAberto/data/camara/rollcalls"


## Downloading from tse
## candidaturas
##http://www.tse.gov.br/sadEleicao2006DivCand/listaBens.jsp?sg_ue=SP&sq_cand=10297


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
##dcis <- subset(dcis,situation!="Não Concorreu")
dcis$firstlast <- clean(firstlast(dcis$name))
## recode cargo to match tse database
dcis$cargo <- as.character(car::recode(dcis$office,c("'Deputados Federais'='deputado federal';'Deputados Estaduais'='deputado estadual';'Senadores'='senador'")))
dcis$cargo[(dcis$cargo=="deputado estadual") & (dcis$state=="DF")] <- "deputado distrital"
##FIXED: Apparently there is a wrong numero in dcis data (checked with tse)
dcis[with(dcis,cargo=="deputado federal" & number==6556 & state=="RO"),"number"] <- 5665
##tse actually is not an unique id. (see the   situation variable)
dcis$tseid <- with(dcis,paste(cargo,state,number,sep=";"))
dcis$cisid <- with(dcis,paste(cargo,id,sep=";"))


##load TSE data
tmp0 <- dbGetQuery(connect,"select distinct cargo, tseid, nome, uf, situacao, sum(votos) as votos   from br_tse2006mun where (cargo!='senador') AND (cargo!='presidente') AND (cargo!='governador') group by tseid" )
## merge it with CIS

##get data with unique tseid
dcis.m <- dcis
dcis.m$cargo <- NULL
tmp1 <- tmp0
tmp1$nome2 <- clean(tmp1$nome)
dcis.m$nome2 <- clean(dcis.m$name)
dcis.m1 <- subset(dcis.m,tseid%in%tseid[duplicated(tseid)])
## not dups (merge by tseid)
dcis.m2 <- subset(dcis.m,!tseid%in%tseid[duplicated(tseid)])
dcis.m2$nome2 <- NULL
dcis.m2 <- merge(dcis.m2,tmp1,by=c("tseid"))
## dups (merge by name)
dcis.m1 <- merge(dcis.m1,tmp1,by=c("tseid","nome2"))
dcis.m <- rbind(dcis.m1,dcis.m2)
dcis.m <- merge(dcis.m,dcis,all=TRUE)
## FIX: We can't trust the "outcome" variable from CIS
## RENATO COZZOLINO SOBRINHO (Suplente) [coded as eleito in CIS db]
## EDGARD MONTENOR FERNANDES (Suplente) [coded as eleito in CIS db]
## MARCELO RICARDO MARIANO (Suplente) [coded as eleito in CIS db]
## MS0048 did not run
## So we use the tse
## tmp <- merge(matches,dcis.m) ## some obs of matches do notcome through (not in tse data, did not run in election)
dcis.m$outcome <- "Não Concorreu"
dcis.m$outcome[dcis.m$situacao%in%c("media","eleito")] <- "Eleito"
dcis.m$outcome[dcis.m$situacao%in%c("nao eleito")] <- "Não Eleito"
dcis.m$outcome[dcis.m$situacao%in%c("suplente")] <- "Suplente"
## check for dupes
table(duplicated(dcis.m$tseid[dcis.m$outcome!="Não Concorreu"]))

## get data from CA (bio) (note, we will try to find everyone, not just the current dfs)
dca <- dbGetQuery(connect,"select * from br_bio")
dca <- iconv.df(dca)
dca$firstlast <- clean(firstlast(dca$name))
dca$birthyear <- as.numeric(format.Date(dca$birth,"%Y"))
dca$scurrent <- FALSE
dca$scurrent[grep("2007-2011",dca$sessions)] <- TRUE

## This is the function use for matching
match.2006 <- function(d1,d2,name1="name",name2="name",maxd=.2,mfl=TRUE) {
  d2 <- match.state(d1,d2)
  d2 <- match.birthyear(d1,d2,10)
  d2n <- match.name(d1,d2,maxd,name1,name2)
  if ((nrow(d2n)==0)) {
    if (mfl) d2n <- match.name(d1,d2,maxd,"firstlast","firstlast")
    if (nrow(d2n)>0) d2n$dist <- 1
  } else {
    d2n$dist <- 0
  }
  d2 <- d2n
  d2n <- match.birth(d1,d2,2)
  if (nrow(d2n)==0) {
    ## try flipping month and day
    try(d1$birth <- as.character(format.Date(d1$birth,"%Y-%d-%m")))
    d2n <- match.birth(d1,d2,2)
  }
  d2 <- d2n
  if (nrow(d2)>0) {
    cat(as.character(d2$name),";",as.character(d1$name),"\n")
    cat(as.character(d2$birth),";",d1$birth,"\n")
  }
  d2
}



## manual fixes
## Manoel Salviano Sobrinho
dca[dca$bioid=="99672","birth"] <- "1939-09-24"
## The plan is to match each row of bio to someone in TSE
## First match current deps
matchcdf <- match(subset(dca,scurrent),subset(dcis,office=="Deputados Federais"),match.all=TRUE,id="cisid",fun.match=match.2006)

## Then match everyone else
matchncdf <- match(subset(dca,!scurrent),subset(dcis,office!="Deputados Federais"),match.all=FALSE,id="cisid",fun.match=match.2006,maxd=.1,mfl=FALSE)


matches <- rbind(matchcdf,matchncdf)

save(matches,file="../data/electoral/biocis2006.RData")
##load(file="../data/electoral/biocis2006.RData")

## write to db
dbRemoveTable(connect,"br_cisidbioid")
dbWriteTable(connect,"br_cisidbioid",matches)


## write to db
dbRemoveTable(connect,"br_cis")
dbWriteTable(connect,"br_cis",dcis.m)



