## produces the kml file for google maps for each candidate
## to start we get those with bioids
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

## FIX: 1st load SQL script to create tables

## R scripts
update.all <- TRUE
##update.all <- FALSE
##download.now <- FALSE
download.now <- TRUE

## bio tables (weekly?)
session.now <- "QQ" ## download all files for all sessions
##session.now <- 53
usource(rf("R/bioprocess.R"), echo=TRUE)

## get bioid for TSE file (when new deps assume office) 
usource(rf("R/biotse.R"),echo=TRUE)

##  roll call tables (daily)
usource(rf("R/updateVot.R"), echo=TRUE)

## update ausencias for pre 1999 data (once)
usource(rf("R/abstentions.R"), echo=TRUE)

## bill info from Camara download  (daily)
usource(rf("R/downloadbills.R"), echo=TRUE)

## bill info from Camara process (daily)
usource("processbills.R", echo=TRUE)


## wnominate (weekly? monthly?)
usource("wnomLastYear.R", echo=TRUE)



## election tables
## electoral finance tables (by election)
##usource("/Users/eduardo/reps/CongressoAberto/R/br_contribAssemble.R",echo=TRUE,encoding="latin1")


## post legislators main page
usource(rf("R/legisListWordPress.R"), echo=TRUE)
##post legislators
usource(rf("R/legisWordPress.R"), echo=TRUE)
##post bills
usource("billsWordPress.R", echo=TRUE)
## post about us (quem somos) page
usource("aboutusWordPress.R")
## post parties
usource("partiesWordPress.R")
## post plio
usource("plioWordPress.R")


## indices camara (also posts main page "desempenho")
usource(rf("R/indicesCamara.R"))

usource("abstentionsWordpress.R")


## post roll calls to wp db

## post
