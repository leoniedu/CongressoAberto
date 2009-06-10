#SHould work for current downloads
#Older downloads, prior to 2009, have subdirectories in the zip files, use four digit years, etc...
run.from<-"C:/DATA/NECON/"
source(paste(run.from,"/DATA/Functions/_cleanname.fnct.R",sep=""))  


#Get current date, and move to appropriate directory
current.month <- as.numeric(format(Sys.time(), "%m"))
current.year <- format(Sys.time(), "%Y")
setwd(paste(run.from,current.year,sep=""))  
SFfiles <- grep("SF",dir(),value=TRUE)
if(length(SFfiles)>0){file.remove(SFfiles)} #Get rid of SENADO files, if they exist


### See current files, download this months`s data, flag new votes
old.LVfiles <- grep("LV",dir(),value=TRUE)  #get already coded votes
                                            #we could change this so that it uses the csv votes (already tabulated) as the reference
 
        #Download current months's zip
        monthspt<-c("Janeiro","Fevereiro","Marco","Abril","Maio","Junho","Julho","Agosto","Setembro","Outubro","Novembro","Dezembro")
        month <- monthspt[current.month]
        year <- substr(current.year,3,4)
        the.url <- paste("http://www.camara.gov.br/internet/plenario/result/votacao/",month,year,".zip",sep="")
        try(download.file(the.url,dest=paste(month,current.year,".zip",sep=""),quiet = FALSE, mode="wb"),silent=TRUE) #continue even if some file doesn't exist
        #Unzip this months's zip file
         zip.unpack(paste(month,current.year,".zip",sep=""), dest=getwd())
         SFfiles <- grep("SF",dir(),value=TRUE)
         if(length(SFfiles)>0){file.remove(SFfiles)} #Get rid of SENADO files, if they were downloaded

new.LVfiles<-grep("LV",dir(),value=TRUE) 
votes <- setdiff(new.LVfiles,old.LVfiles) #compare new files with old to flag recently downloaded

for(i in 1:length(votes)){  #for each new vote, create two new files
    LVfile <- votes[i]  
    #Read data from VOTE LIST file for the vote
    if(nchar(vote)==24){ #formato antigo: titulo tinha 24 characters, no novo so 21
            LV <- read.fwf(LVfile, widths=c(9,-1,9,40,10,10,25,4),strip.white=TRUE) }else{
            LV <- read.fwf(LVfile, widths=c(9,-1,6,40,10,10,25,4),strip.white=TRUE) }
    voteid <- LV$V2[1]  #store number of vote for future use
    names(LV) <- c("sessionid","voteid","name",paste("vote",voteid,sep="."),"party","state","id") #rename fields
    rc$name<-clean.name(LV) #apply cleaning function for accents and other characters
    levels(LV$state)<-c("AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG",       #change state names to acronyms
                         "PA","PB","PR","PE","PI","RJ","RN","RS","RO","RR","SC","SP","SE","TO") #Think whether this is kosher
    LV <- LV[,c("id","name","party","state",paste("vote",voteid,sep="."))] #rearrange fields
    write.csv(LV,file=paste(gsub("txt","csv",LVfile)),row.names = FALSE) #save file 
    #Read data from HEADER file for the vote
    headerfiles<-grep("HE",dir(),value=TRUE) #get summary files with dates
    HEfile<-grep(as.character(voteid),headerfiles,value=TRUE) #find appropriate HE file, and read line by line...
    vt.date<-as.Date(as.character(read.table(HEfile, header = FALSE, nrows = 1,skip = 2, strip.white = TRUE, as.is = TRUE)[1,1]), "%d/%m/%Y")
    vt.descrip<-read.table(HEfile, header = FALSE, nrows = 1,skip = 12, strip.white = TRUE, as.is = TRUE, sep=";",quote="")
    vt.session<-read.table(HEfile, header = FALSE, nrows = 1,skip = 0, strip.white = TRUE, as.is = TRUE)[1,1]
    vt.descrip<-gsub("\"","",vt.descrip)    #get rid of quotes in the description of the bill
    HE <- data.frame(voteid,date=vt.date,session=vt.session,description=vt.descrip)
    write.csv(HE,file=paste(gsub("txt","csv",HEfile)),row.names = FALSE)
}
