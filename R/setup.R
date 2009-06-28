source("~/reps/CongressoAberto/R/caFunctions.R")

## load SQL script to create tables

## R scripts
update.all <- TRUE
download.now <- TRUE

## bio tables
source("bioprocess.R", echo=TRUE)

##  roll call tables
source("updateVot.R", echo=TRUE)
