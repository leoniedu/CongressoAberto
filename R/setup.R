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

## FIX: 1st load SQL script to create tables

## R scripts
update.all <- TRUE
##download.now <- TRUE
download.now <- TRUE

## bio tables
source(rf("R/bioprocess.R"), echo=TRUE)

##  roll call tables
source("updateVot.R", echo=TRUE)

## update ausencias for pre 1999 data
source(rf("R/abstentions.R"), echo=TRUE)

## bill info from Camara download 
source("downloadbills.R", echo=TRUE)

## bill info from Camara process
source("processbills.R", echo=TRUE)

## election tables

## electoral finance tables
source("/Users/eduardo/reps/CongressoAberto/R/br_contribAssemble.R",echo=TRUE,encoding="latin1")

## post legislators to wp db (finance,  rcs,  bio, bills)

## post roll calls to wp db

## post
