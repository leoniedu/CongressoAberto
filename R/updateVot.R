##FIX The process order is changing everytime this is run, which creates the possibility that results will differ due to approx merges.
##FIX USe Rcurl instead of system wget?
##need var "update.all" set to something
##need var "download.now" set to something
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
run.from <- rf("data/camara/rollcalls")
usource(rf("R/mergeApprox.R"))

##Get current year
current.year <- format(Sys.time(), "%Y")

## get file names for current and past month
years <- 1995:current.year
meses <- as.list(c("Janeiro","Fevereiro","Marco","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"))
meses[[3]] <- c("Marco","Mar%C3%A7o")

current.date <- as.Date(Sys.time())
last.date <- as.Date(Sys.time())-30
current.file <- paste(meses[as.numeric(format(current.date, "%m"))],
                      format(current.date, "%y"),sep='')
last.file <- paste(meses[as.numeric(format(last.date, "%m"))],
                   format(last.date, "%y"),sep='')

##list of files to download
years.f <- ifelse(years>1998,substr(years,3,4),years)

if (update.all) {
  zip.files <- c("Janeiro1999","1slo51l","2sle51l","2slo51l","4sle51l","3slo51l")
  zip.files <- unique(c(zip.files,apply(expand.grid(unlist(meses),years.f),1,paste,collapse="")))
}  else {
  zip.files <- c(current.file,last.file)
}

try(dir.create(paste(run.from,"/extracted",sep=""),recursive=TRUE)  )
setwd(run.from)

SFfiles <- grep("SF",dir(),value=TRUE)
if(length(SFfiles)>0){file.remove(SFfiles)} #Get rid of SENADO files, if they exist
### See current files, download this months`s data, flag new votes
##we could change this so that it uses the csv votes (already tabulated) as the reference
old.LVfiles <- grep("LV",dir("extracted"),value=TRUE)  #get already coded votes
##Download current months's zip
for (i in zip.files) {
  the.url <- paste("http://www.camara.gov.br/internet/plenario/result/votacao/",i,".zip",sep="")
  ## this only downloads if file was updated
  if(download.now) {
    tmp <- system(paste("wget -Nc -P . ",the.url))
  }
}

##Unzip zip files
## the following code extracts to the current dir junking paths (i.e.)
##    it does not create directories, putting everything in the same place
## FIX: make overwrite false?
if (download.now) tmp <- lapply(dir(pattern=".*\\.zip$"),function(x) unzip(x,junkpaths=TRUE,exdir="extracted"))

  

setwd('extracted')
## unzip zip files that were inside zipfiles
tmp <- lapply(dir(pattern=".*\\.zip$"),function(x) unzip(x,junkpaths=TRUE))
SFfiles <- grep("SF",dir(),value=TRUE)
if(length(SFfiles)>0){file.remove(SFfiles)} #Get rid of SENADO files, if they were downloaded
unlink('*CD01E028O001905.TXT') ## Duplicated vote file LVCD01E028O001905 and LVCD01E028E001905
new.LVfiles<- dir(pattern="LV.*txt$",ignore.case=TRUE)
votes <- setdiff(new.LVfiles,old.LVfiles) #compare new files with old to flag recently downloaded
## FIX: use file information to get what files recently modified.


if (update.all) votes <- new.LVfiles
nvotes <- length(votes)
file.table <- cbind(votes,gsub("^LV","HE",votes))
connect.db()
votes.old <- dbGetQuery(connect,"select count(*) from br_votacoes")[[1]][1]
if (length(votes)>0) {
  for(LVfile in votes[1:nvotes]) {  #for each new vote, create two new files
    print(paste("uploading file ",LVfile))
    ##Read data from VOTE LIST file for the vote
    readOne(LVfile,post=TRUE)
    ##dedup.db('br_votos')
  }
  votes.new <- dbGetQuery(connect,"select count(*) from br_votacoes")[[1]][1]
  nvotes <- votes.new-votes.old
  ## FIX: perhaps better to do a diff on the database itself?
  ##print(user)
  ##print(password)
  if (nvotes>0) {
    ## load twitter user passwd
    load(rf("R/up.RData"))
    tw <- paste(nvotes,"new rollcalls uploaded!")
    ns <- tweet(tw, userpwd=usrpwd, wait=0)
  }
  print(paste(nvotes, "effectively updated"))
}

##print(ns)
warnings()
