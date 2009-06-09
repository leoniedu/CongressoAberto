library(reshape)
library(RMySQL)
years <- 1999:2000

##given a _legislative_ year (ends Feb 1st) give the legislative
## session (e.g. 1991-1994)
get.legis <- function(x) {
  vec <- cut(x,seq(1946,max(x)+4,4),include.lowest=FALSE)
  labs <- levels(vec)
  labs <- cbind(lower = as.numeric( sub("\\((.+),.*", "\\1", labs) ),
                upper = as.numeric( sub("[^,]*,([^]]*)\\]", "\\1", labs) ))
  labs[,"lower"] <- labs[,"lower"]+1
  labs <- apply(labs,1,paste,collapse="-")
  levels(vec) <- labs
  as.character(vec)
}

## recast votos
get.votos <- function(data.votos) {
  data.votos$origvote <- gsub("vote\\.","",data.votos$variable)
  data.votos$variable <- NULL
  data.votos$voto <- car::recode(data.votos$value,"1='Sim';2='Não';3='Abstenção';5='Obstrução';6='Ausente'; else=NA")
  ## drop NAs (not in congress)
  data.votos <- subset(data.votos,!is.na(value))
}

## recode descriptions
get.votacoes <- function(data.votacoes) {
  data.votacoes <- within(data.votacoes,{
    ##modify bill so that it is parseable
    texordia <- bill
    texordia <- gsub(" +"," ",texordia)
    texordia <- gsub(" / ","/",texordia)
    texordia <- gsub("^PL P ","PLP ",texordia)
    texordia <- gsub("N º","Nº",texordia)
    texordia <- gsub("N .","N.",texordia)
    texordia <- gsub("/;","/",texordia)
    texordia <- gsub("(^[A-Z]+) ([0-9])","\\1 Nº \\2",texordia)
    data <- dates
    dates <- NULL
    ##     wpdate <- as.character(paste(data,"T12:00:00"))
  })
  ## parse texordia (bill)
  ss <- strsplit(gsub(" +"," ",as.character(data.votacoes$texordia)),c(" |/"))
  data.votacoes <- within(data.votacoes,{  
    tipo <- factor(sapply(ss,function(x) x[1]))
    tipo <- car::recode(tipo,"'MENSAGEM'='MSG';'PROCESSO'='PRC';'PROPOSICAO'='PRP';'RECURSO'='REC';'REQUERIMENTO'='REQ';'L'='PL'")
    tipo <- gsub("\\.","",tipo)
    numero <- (sapply(ss,function(x) x[3]))
    ano <- as.numeric(sapply(ss,function(x) x[4]))
    ano[ano==203] <- 2003
    ano <- ifelse(ano<1000 & ano>50, ano+1900,ano)
    ano <- ifelse(ano<1000 & ano<50, ano+2000,ano)
    anovotacao <- format.Date(data,"%Y")
    m <- as.numeric(as.numeric(format.Date(data,"%m"))<2)
    anolegislativo <- as.numeric(anovotacao)-m
    rm(m)
    ##if ano is missing use ano_votacao
    ano <- ifelse(is.na(ano), anovotacao,ano)
    proposicao <- with(data.votacoes,paste(tipo," ",numero,"/",ano,sep=""))
    descricao <- sapply(ss,function(x) paste(x[6:length(x)], collapse=" "))
    descricao <- gsub("^ *- *", "", descricao)
  })
  data.votacoes
}

## before 1999 the files do not show who didn't vote
## (we have to somehow get the list of deputies current at each vote)
gv <- function(i) {
  dnow <- read.csv(paste("../data/NECON/brvotes/brvotes",i,".csv",sep=''))
  dnow <- melt(dnow,id.var=c("id","name","party","state"))
  dnow
}
gd <- function(i) {
  data.votacoes <- read.csv(paste("../data/NECON/data.voteDescription/data.voteDescription",i,".csv",sep=''),encoding="latin1")
  data.votacoes <- get.votacoes(data.votacoes)
  names(data.votacoes)[1] <- "origvote"
  data.votacoes
}


data.votos <- lapply(years,gv)
data.votos <- do.call(rbind,data.votos)
data.votos <- get.votos(data.votos)

data.votacoes <- lapply(years,gd)
data.votacoes <- do.call(rbind,data.votacoes)
data.votacoes$legislatura <- get.legis(data.votacoes$anolegislativo)

data.deputados <- unique(merge(data.votacoes,data.votos,by="origvote")[,c("name","state","id","legislatura")])

## Code to write to DB goes here
if (exists("connect")) dbDisconnect(connect)
connect<-dbConnect(driver, group="congressoaberto")
##should have a .my.cnf in home directory with the access data to the DB
##since the source is public putting it here causes a (serious) security concern
dbRemoveTable(connect,"br_votos")
dbRemoveTable(connect,"br_deputados")
dbRemoveTable(connect,"br_votacoes")
##put in db
dbWriteTable(connect, "br_votos", dnow, overwrite=TRUE,
             row.names = F, eol = "\r\n" )    
dbWriteTable(connect, "br_votacoes", data.votacoes, overwrite=TRUE,
             row.names = F, eol = "\r\n" )    
dbWriteTable(connect, "br_deputados", data.deputados, overwrite=TRUE,
             row.names = F, eol = "\r\n" )    

