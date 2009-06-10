library(RMySQL)

## TODO: What should we do about the last day of the month? Perhaps download the current month and the last? CUrrent fix: do the entire year
## TODO: Look for the deputados in the deputados db and add the ones missing
options(encoding="ISO8859-1")

##SHould work for current downloads
#Older downloads, prior to 2009, have subdirectories in the zip files, use four digit years, etc...
##run.from<-"C:/DATA/NECON/"
##source(paste(run.from,"/DATA/Functions/_cleanname.fnct.R",sep=""))  
##source(paste(run.from,"~/R/_cleanname.fnct.R",sep=""))
run.from<-"~/reps/CongressoAberto/data/NECON/"
source("~/reps/CongressoAberto/R/_cleanname.fnct.R")
source("~/reps/CongressoAberto/R/caFunctions.R")
## set as iso


#Get current date, and move to appropriate directory
current.year <- format(Sys.time(), "%Y")
setwd(paste(run.from,current.year,sep=""))  
SFfiles <- grep("SF",dir(),value=TRUE)
if(length(SFfiles)>0){file.remove(SFfiles)} #Get rid of SENADO files, if they exist
### See current files, download this months`s data, flag new votes
##we could change this so that it uses the csv votes (already tabulated) as the reference
##Download current months's zip
monthspt<-c("Janeiro","Fevereiro","Marco","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro")
year <- substr(current.year,3,4)



old.LVfiles <- grep("LV",dir(),value=TRUE)  #get already coded votes
for (current.month in 1:12) {
  month <- monthspt[current.month]
  the.url <- paste("http://www.camara.gov.br/internet/plenario/result/votacao/",month,year,".zip",sep="")
  ##tmp <- try(download.file(the.url,dest=paste(month,current.year,".zip",sep=""),quiet = FALSE, mode="wb"),silent=TRUE) #continue even if some file doesn't exist
  ##if ("try-error"%in%class(tmp)) next
  cat(current.month)
  ##Unzip this months's zip file
  ##zip.unpack(paste(month,current.year,".zip",sep=""), dest=getwd())
  ## the following code extracts to the current dir junking paths (i.e.)
  ##    it does not create directories, putting everything in the same place
  unzip(paste(month,current.year,".zip",sep=""),junkpaths=TRUE)
  SFfiles <- grep("SF",dir(),value=TRUE)
  if(length(SFfiles)>0){file.remove(SFfiles)} #Get rid of SENADO files, if they were downloaded
}

new.LVfiles<- dir(pattern="LV.*txt$",ignore.case=TRUE)
votes <- setdiff(new.LVfiles,old.LVfiles) #compare new files with old to flag recently downloaded
nvotes <- length(votes)
if (nvotes>0) {
  file.table <- data.frame(matrix("",ncol=2),stringsAsFactors=FALSE)
  for(i in 1:nvotes) {  #for each new vote, create two new files
    file.table[i,1] <- LVfile <- votes[i]  
    ##Read data from VOTE LIST file for the vote
    ##if(nchar(vote)==24){ #formato antigo: titulo tinha 24 characters, no novo so 21
    ##Fixed the following line (I think)
    if(nchar(LVfile)==24)  { #formato antigo: titulo tinha 24 characters, no novo so 21
      LV <- read.fwf(LVfile, widths=c(9,-1,9,40,10,10,25,4),strip.white=TRUE)
    }  else {
      LV <- read.fwf(LVfile, widths=c(9,-1,6,40,10,10,25,4),strip.white=TRUE)
    }
    voteid <- LV$V2[1]  #store number of vote for future use
    names(LV) <- c("sessionid","voteid","name",paste("vote",voteid,sep="."),"party","state","id") #rename fields
    LV$name<-clean.name(LV) #apply cleaning function for accents and other characters
    states <- c("AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG",       #change state names to acronyms
                "PA","PB","PR","PE","PI","RJ","RN","RS","RO","RR","SC","SP","SE","TO") #Think whether this is kosher    
    levels(LV$state)<- states
    LV <- LV[,c("id","name","party","state",paste("vote",voteid,sep="."))] #rearrange fields
    write.csv(LV,file=paste(gsub("txt","csv",LVfile)),row.names = FALSE) #save fil 
    ##Read data from HEADER file for the vote
    headerfiles<- dir(pattern="HE.*[txTX]+$")
    file.table[i,2] <- HEfile <- grep(as.character(voteid),headerfiles,value=TRUE) #find appropriate HE file, and read line by line...    
    vt.date<-as.Date(as.character(read.table(HEfile, header = FALSE, nrows = 1,skip = 2, strip.white = TRUE, as.is = TRUE)[1,1]), "%d/%m/%Y")
    vt.descrip<-read.table(HEfile, header = FALSE, nrows = 1,skip = 12, strip.white = TRUE, as.is = TRUE, sep=";",quote="")
    vt.session<-read.table(HEfile, header = FALSE, nrows = 1,skip = 0, strip.white = TRUE, as.is = TRUE)[1,1]
    vt.descrip<-gsub("\"","",vt.descrip)    #get rid of quotes in the description of the bill
    HE <- data.frame(voteid,dates=vt.date,session=vt.session,bill=vt.descrip)
    write.csv(HE,file=paste(gsub("txt","csv",HEfile)),row.names = FALSE)
  }
  file.table <- as.matrix(file.table)
  file.table <- gsub("txt","csv",file.table)
  ## do something (load db, post, graphs, whatever)
  data.votacoes <- do.call(rbind,lapply(file.table[,2],gd,encoding=FALSE))
  data.votos <- get.votos(do.call(rbind,lapply(file.table[,1],gv)))  
  data.deputados <- unique(merge(data.votacoes,data.votos,by="origvote")[,c("name","state","id","legislatura")])
  ## get ids for deputados
  legis <- as.character(unique(data.deputados$legislatura))
  idname <- dbReadTable(connect,"br_idname")
  idname <- subset(idname,sessions%in%legis)
  idname <- iconv.df(idname)
}

stop()

connect.db()

##put in db

dbWriteTable(connect, "br_votos", data.votos, overwrite=FALSE,
             row.names = F, eol = "\r\n" ,append=TRUE)    
dbWriteTable(connect, "br_votacoes", data.votacoes, overwrite=FALSE,
             row.names = F, eol = "\r\n" ,append=TRUE)
##sanity check, exclude duplicates
dbGetQuery(connect,"select count(*) as row_ct from br_votos")
dbSendQuery(connect,"drop  table tmp")
dbSendQuery(connect,"create table tmp as select distinct * from br_votos")
dbSendQuery(connect,"drop  table br_votos")
dbSendQuery(connect,"create table br_votos as select * from tmp")
dbGetQuery(connect,"select count(*) as row_ct from br_votos")
dbGetQuery(connect,"select count(*) as row_ct from br_votacoes")
dbSendQuery(connect,"drop  table tmp")
dbSendQuery(connect,"create table tmp as select distinct * from br_votacoes")
dbSendQuery(connect,"drop  table br_votacoes")
  dbSendQuery(connect,"create table br_votacoes as select * from tmp")
dbGetQuery(connect,"select count(*) as row_ct from br_votacoes")
##FIX check that deputados are in db
##dbWriteTable(connect, "br_deputados", data.deputados, overwrite=TRUE,
  ##row.names = F, eol = "\r\n" )    



  

