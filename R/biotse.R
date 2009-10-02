## Gets bioid for TSE data (current deputados federais only!)
rf <- function(x=NULL) {
  if (.Platform$OS.type!="unix") {
    run.from <- "C:/reps/CongressoAberto"
  } else {
    run.from <- "~/reps/CongressoAberto"
  }
  ## side effect: load functions
  source(paste(run.from,"/R/caFunctions.R",sep=""))
  if (is.null(x)) {
    run.from
  } else {
    paste(run.from,"/",x,sep='')
  }
}
setwd(rf())
usource(rf("R/mergeApprox.R"))

connect.db()

##FIX: get year and legis from arguments
d1 <- dbGetQuery(connect,"select * from br_vote_candidates where year=2006 and office='Deputado Federal' and sit in ('SUPLENTE','ELEITO','MÉDIA')")
d2 <- dbGetQueryU(connect,"select * from br_bioidname where legis=53")
d1$name <- clean(d1$name)
d2$name <- clean(d2$name)
d1.sub <- subset(d1,sit!="SUPLENTE")

res1 <- merge.approx(x=states,
                     data1=d1.sub,
                     data2=d2,by1="state",by2="name")
if(length(unique(res1$bioid))!=513) stop("all not matched!")
d1.sub <- subset(d1,sit=="SUPLENTE")
## take out the bioids already found
d2.sub <- subset(d2,!bioid%in%res1$bioid)
## merge exactly
d1.sub$x.ind <- 1:nrow(d1.sub)
res2 <- merge(d2.sub,d1.sub)
## take out matched from d1 abd d2
d2.sub <- subset(d2.sub,!bioid%in%res2$bioid)
d1.sub <- d1.sub[-res2$x.ind,]
library(plyr)
## have to get unique names.
## so we select the longest name
d2.sub <- ddply(d2.sub,"bioid",function(x) subset(x,
                                                  nchar(as.character(name))==
                                                  max(nchar(as.character(name)))))
## and keep only one name if there is more than one
d2.sub <- d2.sub[!duplicated(d2.sub$bioid),]
## approx merge it
res3 <- merge.approx(x=states,
                     data1=d1.sub,
                     data2=d2.sub,by1="state",by2="name")
if(length(unique(d2.sub$bioid))!=length(unique(res3$bioid))) stop("all not matched!")

## merge matched subsets
c1 <- c("state","candidate_code","bioid","year","office")
res <- rbind(res1[,c1],
             res2[,c1],
             res3[,c1])
if(length(unique(d2$bioid))==length(unique(res$bioid))) print("all matched!")
res$state <- toupper(res$state)
if (nrow(res)!=nrow(unique(res[,c("state","candidate_code")]))) stop("something wrong!")

## write table FIX: put index and create table in Mysql
dbWriteTableU(connect,name="br_bioidtse",value=res,append=TRUE)

