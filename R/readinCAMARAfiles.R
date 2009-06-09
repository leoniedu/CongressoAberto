#First RUN get.files.R, to download the roll call data and place it in folers by year
#
#This code reads in the files on individual roll calls, obtained from the brasilian legislature's website, 
#provided they're all in a single folder. 
#
# Note: different file format in 1998 and yearlier years
#       only does it for CD votes. For CC votes see other file
#
#
#Change in party label, (PRB) was not accounted for in this file. It should be accounted for when file is used      
#Secret votes are eliminiated automatically by converting "presente" (which indicates secret votes) to 9's (not votings)
#Votes coded the NECON way: 1=Yes, 2=No, 3=Abstention, 5=Obstruction, 6=Not voting                                  
#Can be followed by:                                                                                                
#       get.faltas.R: for some years, can later be devided into justified and non justified absenses                
##      get.leaders.R: DOWNLOAD GOVERNMENT'S POSITION DIRECTLY FROM THE WEB, FOLLOWING THE VOTE NUMBER ID           
#
#PRODUCES A FILE WITH ROLL CALL VOTES
#PRODUCES A FILE WITH VOTE NUMBER, DATES AND DESCRIPTION
#
# June 9 2009: Changed the col widths in the fwf command to accomodate errors in 1999.
#run.from<-"//files/zucco"
run.from<-"C:"
source(paste(run.from,"/DATA/Functions/_cleanname.fnct.R",sep=""))   
years <- c(1999)

for(yy in 1:length(years)){
    year<-years[yy]
    cat("Assembling Roll Call Votes for",year,"\n")
    setwd(paste(run.from,"/DATA/NECON/",year,sep=""))
    filenames<-grep("LVCD",dir(),value=TRUE) #only the vote files from CAMARA DOS DEDPUTADOS
    #read first vote separately to get structure
    if(nchar(filenames[1])==24){ #formato antigo: titulo tinha 24 characters, no novo so 21
            rc <- read.fwf(filenames[1], widths=c(9,-1,9,40,10,10,25,3),strip.white=TRUE) }else{
            rc <- read.fwf(filenames[1], widths=c(9,-1,6,40,10,10,25,3),strip.white=TRUE)}   #originally had a -1 between 10 and 25
    names(rc) <- c("sessionid","voteid","name",paste("vote",rc$V2[1],sep="."),"party","state","id")
    rc$name<-clean.name(rc)  
    rc<-rc[,-c(1,2)]
    rc$party<-as.character(rc$party)
    cat("Merging data on individual votes...\n")
    for(i in 2:length(filenames)){
        if(nchar(filenames[i])==24){
            each.file <- try(read.fwf(filenames[i], widths=c(9,-1,9,40,10,10,25,3),strip.white=TRUE),silent=TRUE) }else{
            each.file <- try(read.fwf(filenames[i], widths=c(9,-1,6,40,10,10,25,3),strip.white=TRUE),silent=TRUE)} 
            if(class(each.file)=="try-error"){
                                    rc <- cbind(rc,rep(NA,nrow(rc)))
                                    names(rc)[ncol(rc)] <- paste("vote.",substr(filenames[i],12,(regexpr(".",filenames[i],fixed=TRUE)-1)),sep="")
                                    cat("Erro em",i,"\n")
                                    next} #this is basically to handle a corrupted txt file in 1991. 
        names(each.file) <- c("sessionid","voteid","name",paste("vote",each.file$V2[1],sep="."),"party","state","id")
        each.file<-each.file[,-c(1,2)]
        each.file$name <- clean.name(each.file)
        rc<-merge(rc,each.file,by=c("id","name","party","state"),all=TRUE)
        cat(i,"\n")
        flush.console()
    }
    vote.originals <- sort(as.numeric(gsub("vote.","",grep("vote.",names(rc),value=TRUE))))
    vote.info <- data.frame(orig.vote=vote.originals,dates=NA,session=NA,bill=NA) #,new.vote=vote.ordering[-c(1:4)])  #to store future vote info
    rc$name <- as.character(rc$name)
    rc<-rc[sort(rc$name,index.return=TRUE)$ix, #sort names of legislators
           c(1:4,sort(names(rc)[5:length(names(rc))],index.return=TRUE)$ix+4) ]  #sort bills
    for(v in 5:ncol(rc)){rc[,v]<-ifelse(as.character(rc[,v])=="Sim",1,
                                 ifelse(as.character(rc[,v])=="Não",2,
                                 ifelse(as.character(rc[,v])=="Obstrução",5,
                                 ifelse(as.character(rc[,v])=="Abstenção",3,
                                 ifelse(as.character(rc[,v])=="Branco",3,4)))))#4 é falta ou voto secreto
                                 }
    levels(rc$state)<-c("AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG",       #change state names to acronyms
                         "PA","PB","PR","PE","PI","RJ","RN","RS","RO","RR","SC","SP","SE","TO")
    rc$party[which(rc$party=="S.Part.")]<-NA     #change S.Part. to NA
    write.csv(rc,file=paste(run.from,"/DATA/NECON/brvotes",year,".csv",sep=""),row.names = FALSE)

#Generate VOTE INFORMATION file ##########################################################
    cat("Generating vote information file...\n")
    headerfiles<-grep("HECD",dir(),value=TRUE) #get summary files with dates
    vote.info$dates <- as.Date(vote.info$dates)
    for(i in 1:length(vote.originals)){
        vote.he <- grep(as.character(vote.info[i,1]),headerfiles,value=TRUE)
        vt.date <- read.table(vote.he, header = FALSE, nrows = 1,skip = 2, strip.white = TRUE, as.is = TRUE)[1,1]
        vt.bill <-read.table(vote.he, header = FALSE, nrows = 1,skip = 12, strip.white = TRUE, as.is = TRUE, sep=";",quote="")
        vote.info$session[i] <- read.table(vote.he, header = FALSE, nrows = 1,skip = 0, strip.white = TRUE, as.is = TRUE)[1,1]
        vote.info$dates[i]<- as.Date(as.character(vt.date), "%d/%m/%Y")
        vote.info$bill[i]<- vt.bill
    }
    vote.info$bill<-gsub("\"","",vote.info$bill)
    write.csv(vote.info,file=paste(run.from,"/DATA/NECON/data.voteDescription",year,".csv",sep=""),row.names=FALSE)
    cat("Done!\n\n")
}
