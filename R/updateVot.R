## dbRemoveTable(connect,"br_votos")
## dbRemoveTable(connect,"br_votacoes")

##need var "update.all" set to something
##need var "download.now" set to something
source("~/reps/CongressoAberto/R/caFunctions.R",encoding="UTF8")
run.from <- "~/reps/CongressoAberto/data/camara/rollcalls"
##Get current year
current.year <- format(Sys.time(), "%Y")
##list of files to download
zip.files <- apply(expand.grid(c("Janeiro","Fevereiro","Mar%C3%A7o","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro"),substr(1999:current.year,3,4)),1,paste,collapse="")
zip.files <- c(zip.files,c("Janeiro1999","1slo51l","2sle51l","2slo51l","4sle51l","3slo51l"))
try(dir.create(paste(run.from,"/extracted",sep=""),recursive=TRUE)  )
setwd(paste(run.from,sep=""))
SFfiles <- grep("SF",dir(),value=TRUE)
if(length(SFfiles)>0){file.remove(SFfiles)} #Get rid of SENADO files, if they exist
### See current files, download this months`s data, flag new votes
##we could change this so that it uses the csv votes (already tabulated) as the reference
old.LVfiles <- grep("LV",dir("extracted"),value=TRUE)  #get already coded votes

##Download current months's zip
##FIXED Marco should have cedilha!!
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
new.LVfiles<- dir(pattern="LV.*txt$",ignore.case=TRUE)
votes <- setdiff(new.LVfiles,old.LVfiles) #compare new files with old to flag recently downloaded
if (update.all) votes <- new.LVfiles
nvotes <- length(votes)
file.table <- cbind(votes,gsub("^LV","HE",votes))
if (nvotes>0) {

  for(LVfile in votes[992:nvotes]) {  #for each new vote, create two new files
    print(LVfile)
    ##Read data from VOTE LIST file for the vote
    readOne(LVfile,post=TRUE)
    ##dedup.db('br_votos')
  }  

}
