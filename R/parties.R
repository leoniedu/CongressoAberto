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
setwd(rf("data"))
system("wget -x -N http://www.tse.gov.br/internet/partidos/index.htm")
old.parties0 <- read.csv("http://spreadsheets.google.com/pub?key=t5NG3eCVSoS02TtuAcjKaGQ&single=true&gid=0&output=csv")[,1:5]


dnow <- readLines(rf("data/www.tse.gov.br/internet/partidos/index.htm"))

dnow <- readLines(rf("data/www.tse.gov.br/internet/partidos/index.htm"),encoding="latin1")
loc <- grep("partidos_politicos",dnow)
## do a substitution to fix problems in the file
abbr.raw <- dnow[loc]
abbr <- gsub(".*classe1\">([A-Za-z]*).*","\\1",gsub("<strong>","",abbr.raw))
name.raw <- dnow[loc+1]
name <- decode.html(gsub(".*tabelas\">([^<]*).*","\\1",gsub("<strong>|</strong>|&nbsp;","",name.raw)))
numb.raw <- dnow[loc+4]
numb <- gsub(".*center\">([0-9]*).*","\\1",gsub("<strong>","",numb.raw))
parties <- data.frame(party=abbr,name=trimm(name),number=as.numeric(numb))


old.parties <- old.parties0
names(old.parties) <- c("name","party","number","year_extinct","notes")
old.parties <- subset(old.parties,(!number%in%c("--","")))
## include only parties in action after 1990
## since our roll calls begin in 1991
old.parties <- subset(old.parties,(year_extinct>1990))
old.parties$year_extinct <- as.numeric(as.character(old.parties$year_extinct))
old.parties$party <- gsub(" ","",toupper(old.parties$party))
all.parties <- merge(parties,old.parties,all=TRUE)
all.parties$name <- toupper(all.parties$name)
all.parties$date=Sys.Date()
## ambiguous abbreviations
tx <- table(all.parties$party)
amb <- names(tx[tx>1])
print(paste("Ambiguous abbreviations: ",paste(amb,collapse=",")))



## write tables
connect.db()
## all parties
dbWriteTableU(connect,"br_parties",all.parties,append=TRUE)
## current parties
dbGetQuery(connect, "truncate br_parties_current")
dbWriteTableU(connect,"br_parties_current",subset(all.parties,is.na(year_extinct),select=c(party,name,number,date)),append=TRUE)
