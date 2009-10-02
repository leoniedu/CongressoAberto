library(RMySQL)
update.all <- TRUE

dbRemoveTable(connect,"br_votos")
dbRemoveTable(connect,"br_votacoes")

source("bioprocess.R",echo=TRUE)


## TODO: What should we do about the last day of the month? Perhaps download the current month and the last? CUrrent fix: do the entire year
## TODO: Look for the deputados in the deputados db and add the ones missing
##FIX include ID (from camara)  in the mapping
options(encoding="ISO8859-1")

##SHould work for current downloads
#Older downloads, prior to 2009, have subdirectories in the zip files, use four digit years, etc...
##run.from<-"C:/DATA/NECON/"
##source(paste(run.from,"/DATA/Functions/_cleanname.fnct.R",sep=""))  
##source(paste(run.from,"~/R/_cleanname.fnct.R",sep=""))
run.from<-"~/reps/CongressoAberto/data/NECON/"
source("~/reps/CongressoAberto/R/_cleanname.fnct.R",encoding="latin1")
source("~/reps/CongressoAberto/R/caFunctions.R")
## set as iso


#Get current date, and move to appropriate directory
current.year <- format(Sys.time(), "%Y")


for (current.year in 1999:2009) {
  source("~/reps/CongressoAberto/R/readCurrentYear.R",echo=TRUE)  
}
