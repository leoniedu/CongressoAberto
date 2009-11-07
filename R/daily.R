## daily cron job for R
download.now <- TRUE
update.all <- FALSE
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
rf()

connect.db()
connect.wp()


## download csv with popular names of propositions
pop <- read.csv("http://www.factual.com/tables/666549.csaml")

names(pop) <- c("billtype", "billno", "billyear", "billname", "tags")
## load to db and get the ids

dbRemoveTable(connect, "tmp")
dbRemoveTable(connect, "br_proposition_names")
dbWriteTableU(connect, "tmp", pop)
tmp <- dbGetQuery(connect, "create table br_proposition_names select a.billid, b.tags, b.billname, c.postid, b.billno, b.billtype, b.billyear  from  br_billid as a, tmp as b, br_billidpostid as c where a.billyear=b.billyear and a.billno=b.billno and a.billtype=b.billtype and a.billid=c.billid")

tmp <- dbGetQuery(connect, "select * from  br_proposition_names")


source(rf("R/wordpress.R"))

## give the new titles to the bills
lapply(dbGetQuery(connect, "select billid from br_proposition_names")[,1], postbill, post_category=data.frame(slug="Featured", name="Featured"))

## put featured in the related roll calls

rcs <- dbGetQuery(connect, "select b.* from  br_proposition_names as a, br_votacoes as b where a.billyear=b.billyear and a.billno=b.billno and a.billtype=b.billtype")


lapply(rcs$rcvoteid, function(x) postroll(rcid=x, saveplot=FALSE, post=TRUE))








## roll calls
unlink(path.expand(rf("data/camara/rollcalls/*.zip")))
unlink(path.expand(rf("data/camara/rollcalls/extracted/*")))


##usource(rf("R/deputados.R"),echo=TRUE)
update.all <- FALSE
usource(rf("R/updateVot.R"),echo=TRUE)
usource(rf("R/downloadbills.R"),echo=TRUE)
usource(rf("R/processbills.R"),echo=TRUE)
usource(rf("R/parties.R"),echo=TRUE)

## update wordpress
## update this first, roll calls are needed for the legislator php
usource(rf("R/abstentionsWordpress.R"),echo=TRUE)
usource(rf("R/billsWordPress.R"),echo=TRUE)
usource(rf("R/legisWordPress.R"),echo=TRUE)
usource(rf("R/rollcallsWordPress.R"),echo=TRUE)


## get bioid for TSE file (when new deps assume office) 
usource(rf("R/biotse.R"),echo=TRUE)


## less than daily
## usource(rf("R/indicesCamara.R"),echo=TRUE)
## usource(rf("R/partiesWordpress.R"),echo=TRUE)

## indices
##usource(rf("R/indicesParties.R"),echo=TRUE)

print(Sys.time())


