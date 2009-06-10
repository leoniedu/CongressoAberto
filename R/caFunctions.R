##connect to external db
connect.db <- function() {
  library(RMySQL)
  driver<<-dbDriver("MySQL")
  if (exists("connect")) dbDisconnect(connect)
  connect<<-dbConnect(driver, group="congressoaberto")
}

##convert character vectors from a df from latin1
iconv.df <- function(df,encoding="latin1") {
  cv <- which(sapply(df,is.character))
  for (i in cv) df[,i] <- iconv(df[,i],from=encoding)
  df
}

## reshape votos
gv <- function(filename) {
  require(reshape)
  dnow <- read.csv(filename)
  dnow <- melt(dnow,id.var=c("id","name","party","state"))
  dnow
}
## reshape descriptions
gd <- function(filename,encoding=TRUE) {
  if (encoding) {
    data.votacoes <- read.csv(filename,encoding="latin1")
  } else {
    data.votacoes <- read.csv(filename)
  }
  data.votacoes <- get.votacoes(data.votacoes)
  names(data.votacoes)[1] <- "origvote"
  data.votacoes$legislatura <- get.legis(data.votacoes$anolegislativo)
  data.votacoes
}

##given a _legislative_ year (ends Feb 1st) give the legislative
## session (e.g. 1991-1994)
get.legis <- function(x) {
  vec <- cut(x,seq(1947,max(x)+4,4),include.lowest=FALSE)
  labs <- levels(vec)
  labs <- cbind(lower = as.numeric( sub("\\((.+),.*", "\\1", labs) ),
                upper = as.numeric( sub("[^,]*,([^]]*)\\]", "\\1", labs) ))
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


##sanity check, exclude duplicates
dedup.db <- function(tab) {
  for (x in tab) {
    init <- dbGetQuery(connect,paste("select count(*) as row_ct from ",x))[1,1]
    dbSendQuery(connect,"drop  table tmp")
    dbSendQuery(connect,paste("create table tmp as select distinct * from ",x))
    dbSendQuery(connect,paste("drop  table ",x))
    dbSendQuery(connect,paste("create table ",x," as select * from tmp"))
    end <- dbGetQuery(connect,paste("select count(*) as row_ct from ",x))[1,1]
    cat(paste(init-end), "rows deleted in table ",x,"\n")
  }
}
