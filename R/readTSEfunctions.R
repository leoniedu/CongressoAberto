
read.all <- function(state,dir="/Users/eduardo/doNotBackup/doNotBackup/projects/BrazilianPolitics/trunk/electoral/section/Eleições\ 1998/votosecao1998/",year=1998,round=1,write.sections=FALSE,...) {
  require(reshape)
  cat(state,"\n")
  dnow <- read.section(file=paste("sections/voto_secao_",state,year,"T",
                         round,".txt",sep=""),
                       dir=dir,...)
  dnow$year <- year
  dnow$elec.round <- round
  dnow$state <- state
  dnow$office <- recode.office(dnow$office)
  dnow.rc <- dnow
  dnow.rc$id <- with(dnow.rc,paste(municipality,office,type,candidate.code,sep=";"))
  dnow.rc$votes <- with(dnow.rc,ave(votes,id,FUN=sum))
  dnow.rc$section <- dnow.rc$zone <- dnow.rc$id <- NULL
  dnow.rc <- data.frame(unique(dnow.rc))
  ##data.frame(recast(dnow,year+elec.round+state+municipality+office+type+candidate.code~variable,fun.aggregate=sum,measure.var="votes"))
  if (write.sections) dbWriteTableU(connect, "br_vote_section", dnow,append=TRUE)
  dbWriteTableU(connect, "br_vote_mun", dnow.rc,append=TRUE)
}

recode.office <- function(office) {
  factor(office,levels=office.codes$code,labels=office.codes$office)
}



get.candidates <- function(year) {
    dirnow <- rf(paste("data/electoral/tse/sections/", year, "sections/candidates", sep=''))
    files <- dir(dirnow,full.names=TRUE)
    f <- function(file,...) {
        ## as char  because do.call is giving a seg fault
        data.frame(read.csv2(file,...,stringsAsFactors=FALSE),state=gsub(".*_([A-Z]{2}).*","\\1",file))
    }
    candidates <- do.call(rbind,lapply(files,f,encoding="latin1",header=FALSE))
    print(head(candidates))        
    if (year%in%c(1998)) {
        candidates <- candidates[,c(1:9)]
        candidates$status <- NA
    } else if (year%in%c(2002)) {
        candidates <- candidates[, c(1,2,3,4,8,5,6,10,11)]
        candidates$status <- NA
    } else {
        ## FIX: WILL NOT WORK FOR OTHER YEARS (NEED TO SPECIFY status COLUMN)
        ## works for 2006
        candidates <- candidates[,c(10,4,1,2,3,5,6,7,9,8)]
    }
    ##print(head(candidates))    
    names(candidates) <- c("state","office","candidate_code","name","name.short","sex","party","colig","sit","status")
    candidates$colig <- as.numeric(as.character(candidates$colig))
    print(head(candidates))
    candidates$year <- year
    candidates
}

read.section <- function(file="voto_secao_AC1998T1.txt",dir="/Users/eduardo/doNotBackup/projects/BrazilianPolitics/trunk/electoral/section/1998sections/") {
  ##voto_secao_AC1998T1.txt
  state <- substr(file,12,13)
  year <- substr(file,14,17)
  elec.round <- substr(file,19,19)
  data <- read.table(paste(dir,file,sep=""),header=FALSE,sep=",",
                     comment.char="",
                     col.names=c("municipality","zone","section","office","type","candidate.code","votes")
                     ,colClasses="integer"
                     ##,nrows=100
                     )
  data <- data.frame(year,elec.round,data)
  data
}


get.party <- function(year) {
    if (year==2006) {
        partido <- read.csv2(rf(paste("data/electoral/tse/sections/2006sections/commontables/partido_",year,".txt",sep="")), encoding="latin1",header=FALSE)
    } else if (year==2002) {
        partido <- read.csv2(rf(paste("data/electoral/tse/sections/2002sections/parties/partido_2002.txt", sep='')),encoding="latin1",header=FALSE)
    } else if (year==1998) {
        partido <- read.csv2(rf(paste("data/electoral/tse/sections/1998sections/parties/partido_1998.txt", sep='')),encoding="latin1",header=FALSE)
    } else {
        stop()
    }
    names(partido) <- c("party","partyl","partyname")
    partido$partyl <- toupper(gsub(" ","",partido$partyl))
    partido$partyname <- toupper(partido$partyname)
    partido$year <- year
    partido
}















###################
## get.tse <- function(tse,office.now="deputado federal") {
##   tse <- subset(tse,office==office.now)
##   tse$pvote.state <- with(tse,votes/ave(votes,state,FUN=sum))
##   tse$pvote.nation <- with(tse,votes/sum(votes))
##   tse <- with(tse,data.frame(name.bio=tolower(name),
##                              state=state,
##                              votes=votes,
##                              party.elected=tolower(partyl),
##                              code.tse=candidate_code,
##                              cargo.tse=office.now,
##                              pvote.state,pvote.nation,
##                              sit=sit))
##   tse$partyC.elected <- recode.parties(tse$party.elected)
##   tse$state <- tolower(tse$state)
##   ##tse$sit <- ifelse(tse$sit%in%c(1,5),"titular","suplente/nao eleito")
##   tse$sit <- recode.sit(tse$sit)
##   tse
## }




## ### labels



## ## 2002 section not available for now. get municipal level results
## read.mun <- function(dir="/Users/eduardo/doNotBackup/projects/BrazilianPolitics/trunk/electoral/section/2002sections/municipalities/") {
##   zz <- pipe(paste("ls ",dir))  
##   files <- readLines(zz)
##   close(zz)
##   mun <- NULL
##   for (i in files) {
##     ##state <- state.a2ln(substr(i,13,14))
##     year <- substr(i,8,11)
##     zz <- pipe(paste("unaccent ",dir,i,sep=""))
##     mun <- rbind(mun,cbind(year,read.csv2(zz,encoding="latin1",header=FALSE)))
##     ##close(zz)
##   }
##   mun <- mun[,-2]
##   names(mun) <- c("year","state","municipality","office","candidate.code","name","name.short","party","partyname","votes","sit")
##   mun
## }


## ## by state, office, candidate and year
## get.state <- function(year) {
##   ## candidates
##   candidates <- get.candidates(year)
##   ## parties
##   partido <- get.partido(year)
##   dstate <- dbGetQuery(connect, statement=paste("SELECT year, office, state, type, candidate_code, sum(votes) as votes FROM br_vote_state WHERE ((type=1 OR type=4) AND year=",year,") GROUP BY type, year, office, state, candidate_code"))
##   dstate <- merge(dstate,candidates,by=c("candidate_code","state","office","year"),all.x=TRUE)
##   dstate$office <- factor(dstate$office,levels=cargo$office,labels=cargo$officel)
##   dstate$state <- factor(dstate$state,levels=1:27,labels=states)
##   obs <- is.na(dstate$name)
##   dstate$party[obs] <- dstate$candidate_code[obs]
##   dstate <- merge(dstate,partido,all.x=TRUE)
##   dstate$name <- as.character(dstate$name)
##   ##dstate$name[obs] <- (dstate$partyl)[obs]
##   dstate
## }

