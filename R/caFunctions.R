## duplicates are being dropped but they are nor necessarily duplicates. check.
## FIX: need to add file name as a column.
readOne <- function(LVfile,post=FALSE) {
  options(encoding="ISO8859-1")
  HEfile <- gsub("^LV","HE",LVfile)
  cat(".")
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
  ##FIX: ENABLE CLEAN NAME OR NOT?
  ##LV$name<-clean.name(LV) #apply cleaning function for accents and other characters
  LV$state <- toupper(state.l2a(LV$state))
  LV$state <- factor(LV$state,levels=toupper(states))
  LV <- LV[,c("id","name","party","state",paste("vote",voteid,sep="."))] #rearrange fields
  vt.date<-as.Date(as.character(read.table(HEfile, header = FALSE, nrows = 1,skip = 2, strip.white = TRUE, as.is = TRUE)[1,1]), "%d/%m/%Y")
  vt.descrip<-read.table(HEfile, header = FALSE, nrows = 1,skip = 12, strip.white = TRUE, as.is = TRUE, sep=";",quote="")
  vt.session<-read.table(HEfile, header = FALSE, nrows = 1,skip = 0, strip.white = TRUE, as.is = TRUE)[1,1]
  vt.descrip<-gsub("\"","",vt.descrip)    #get rid of quotes in the description of the bill
  HE <- data.frame(voteid,dates=vt.date,session=vt.session,bill=vt.descrip)  
  data.votacoes <- get.votacoes(HE)
  data.votacoes$sessions <- get.legis(data.votacoes$anolegislativo)
  data.votacoes$filename <- LVfile
  data.votos <- LV
  data.votos$filename <- LVfile
  data.votos$voteid <- voteid
  names(data.votos)[5] <- "voto"
  data.votos$voto <- gsub("^<.*","Ausente",as.character(data.votos$voto))
  data.votos$voto <- gsub("^Art.*","Abstenção",as.character(data.votos$voto))
  data.votos$sessions <- data.votacoes$sessions[1]
  if (!post) {
    list(data.votos=data.votos,data.votacoes=data.votacoes)    
  } else {
    connect.db()
    session.now <- as.character(data.votacoes$sessions[1])
    ##bioids
    idname <- dbGetQuery(connect,paste("select * from br_bioidname where sessions='",session.now,"'",sep=''))
    idname <- iconv.df(idname)
    idname$id <- NULL
    ## merge using the br_ids db (a mapping of all ids)
    createtab <- !dbExistsTable(connect,"br_idbioid")
    if (createtab) {
      ids <- data.frame(bioid="",id="",sessions="")
    } else {
      ids <- dbReadTable(connect,"br_idbioid")
    }
    ##merge with camara ids first
    tomatch <- merge(data.votos,ids,by=c("id","sessions"),all.x=TRUE)
    tomatch <- subset(tomatch,is.na(bioid),select=-bioid)
    ##try to find the bioid for new deps
    if (nrow(tomatch)>0) {
      res <- merge.approx(states,idname,
                          tomatch,"state","name")
      ##might have multiple matches. We discard if the 
      ##tripple (id,bioid, session) is still unique
      res <- unique(with(res,data.frame(bioid,id,sessions)))
      ##fix: check explicitly for multiple ids.
      if(min(tomatch$id%in%res$id)==0) {
        print(tomatch[!tomatch$id%in%res$id,])
        stop("Some legislators not yet in db")
      } else if (sum(duplicated(res$bioid))) {
        print(res[with(res,bioid%in%bioid[which(duplicated(bioid))]),])
        stop("Some ids are duplicated ")
      }
      ##write new matches to db
      dbWriteTable(connect, "br_idbioid",res, append=TRUE,
                   row.names = F, eol = "\r\n" )
      ## exclude dups
      ##dedup.db("br_idbioid")      
    }
    ## read table again
    ids <- dbReadTable(connect,"br_idbioid")
    data.votos <- merge(data.votos,ids,by=c("id","sessions"),all.x=TRUE)
    if (sum(is.na(data.votos$bioid))>0) stop("there are missing ids")
    dbWriteTable(connect, "br_votos",data.votos, append=TRUE,
                 row.names = F, eol = "\r\n" )    
    dbWriteTable(connect, "br_votacoes",data.votacoes, append=TRUE,
                 row.names = F, eol = "\r\n" )
    ## exclude dups
    ##dedup.db(c("br_votos",'br_votacoes'))      
  }
}

  
## write.csv(LV,file=paste(gsub("txt$","csv",LVfile,ignore.case=TRUE)),row.names = FALSE) #save fil 
## write.csv(HE,file=paste(gsub("txt$","csv",HEfile,ignore.case=TRUE)),row.names = FALSE)
## data.votacoes <- do.call(rbind,lapply(file.table[,2],gd,encoding=FALSE))
## data.votos <- get.votos(do.call(rbind,lapply(file.table[,1],gv)))  
## data.deputados <- unique(merge(data.votacoes,data.votos,by="origvote")[,c("name","state","id","legislatura")])



## function to recode long state names to short
state.l2a <- function(object) {
  ##require(car)
  object <- tolower(as.character(object))
  car:::recode(object,"
                       'acre'                  = 'ac';
                       'alagoas'               = 'al';
                       'amazonas'              = 'am';
                       c('amapa','amapá')      = 'ap';
                       'bahia'                 = 'ba';
                       c('ceara','ceará')       = 'ce';
                       'distrito federal'      = 'df';
                       'espírito santo'        = 'es';
                       'espirito santo'        = 'es';
                       'espitito santo'        = 'es';
                       c('goias','goiás')       = 'go';
                       c('maranhao','maranhão')= 'ma';
                       'minas gerais'          = 'mg';
                       'mato grosso do sul'    = 'ms';
                       'mato g sul'            = 'ms';
                       'm. g. do sul'          = 'ms';
                       'mato grosso'           = 'mt';
                       'para'                  = 'pa';
                       'pará'                  = 'pa';
                       'paraiba'               = 'pb';
                       'paraíba'               = 'pb';
                       'pernambuco'            = 'pe';
                       'piaui'                 = 'pi';
                       'piauí'                 = 'pi';
                       'parana'                = 'pr';
                       'paraná'                = 'pr';
                       'rio de janeiro'        = 'rj';
                       'r. g. do norte'        = 'rn';
                       'rio grande do norte'   = 'rn';
                       'rondonia'              = 'ro';
                       'rondônia'              = 'ro';
                       'roraima'               = 'rr';
                       'rio grande do sul'     = 'rs';
                       'r. g. do sul'          = 'rs';
                       'santa catarina'        = 'sc';
                       'sergipe'               = 'se';
                       'sao paulo'             = 'sp';
                       'são paulo'             = 'sp';
                       'tocantins'              = 'to'")
}



##connect to external db
connect.db <- function() {
  library(RMySQL)
  driver<<-dbDriver("MySQL")
  if (exists("connect")) {
    testconnect <- try(dbExistsTable(connect,"iakjsdh")[1])
    if (("try-error"%in%class(testconnect))) {
      try(dbDisconnect(connect))
      try(rm(connect))
    }
  } else {
    connect<<-dbConnect(driver, group="congressoaberto")
  }
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
  dnow$filename <- filename
  dnow <- melt(dnow,id.var=c("id","name","party","state","filename"))
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
  data.votacoes$filename <- filename
  data.votacoes
}

##given a _legislative_ year (ends Feb 1st) give the legislative
## session (e.g. 1991-1994)
get.legis <- function(x) {
  ## note the +1 here to make calc right
  vec <- cut(x+1,seq(1947,max(x)+4,4),include.lowest=FALSE)
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
  data.votos$voto <- gsub("^<.*","Ausente",as.character(data.votos$value))
  data.votos$voto <- gsub("^Art.*","Abstenção",as.character(data.votos$value))
  ##data.votos$voto <- car::recode(data.votos$value,"1='Sim';2='Não';3='Abstenção';5='Obstrução';6='Ausente'; else=NA")
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
    texordia <- gsub("N \\.","N.",texordia)
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
    ## FIX make it drop rows and insert instead of creating tables anew
    init <- dbGetQuery(connect,paste("select count(*) as row_ct from ",x))[1,1]
    dbSendQuery(connect,"drop  table tmp")
    dbSendQuery(connect,paste("create table tmp as select distinct * from ",x))
    mid <- dbGetQuery(connect,paste("select count(*) as row_ct from tmp"))[1,1]
    if (mid<init) {
      dbSendQuery(connect,paste("drop  table ",x))
      dbSendQuery(connect,paste("create table ",x," as select * from tmp"))
      end <- dbGetQuery(connect,paste("select count(*) as row_ct from ",x))[1,1]
      cat(paste(init-end), "rows deleted in table ",x,"\n")
    }
  }
}


##manual fixes
manfix <- function(id,newstate) {
  dbSendQuery(connect,paste("update br_idname set state='",newstate,"' where bioid='",id,"'",sep=''))
  dbSendQuery(connect,paste("update br_bio set state='",newstate,"' where bioid='",id,"'",sep=''))
}
## These are no longer needed (fixed using index file)
## manfix("96883","AP")
## manfix("97304","RO")
## manfix("100597","MT")
## manfix("98460","MG")
## bio.all[bio.all$bioid=="96883","state"] <- "AP"
## bio.all[bio.all$bioid=="97304","state"] <- "RO" ## Expedito Junior (born in SP)
## bio.all[bio.all$bioid=="100597","state"] <- "MT" ## Osvaldo Sobrinho
