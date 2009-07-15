source("~/reps/CongressoAberto/R/caFunctions.R")

## load SQL script to create tables

## R scripts
update.all <- TRUE
##download.now <- TRUE
download.now <- TRUE

## bio tables
source("bioprocess.R", echo=TRUE)

##  roll call tables
source("updateVot.R", echo=TRUE)

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
