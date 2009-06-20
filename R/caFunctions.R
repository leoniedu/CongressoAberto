##FIX: dbWriteTable , append=TRUE does NOT update the table
##Possible solutions: write a function that deletes the data beforehand
##                    write a function that creates a new temp table and updates from there with a join 
##                    delete relevant rows explicitly

clean.name<-function(x){
    #CLEANS ACCENTS AND OTHER MARKS FROM FIELD CALLED "NAME" 
    y <- clean(x$name)
    return(y)
}

clean<-function(x){
    #CLEANS ACCENTS AND OTHER MARKS FROM FIELD
    y<-toupper(x)
    y<-gsub("Â","A", y) 
    y<-gsub("Á","A", y)
    y<-gsub("Ã","A", y)
    y<-gsub("É","E", y)
    y<-gsub("Ê","E", y)
    y<-gsub("Í","I", y)
    y<-gsub("Ó","O", y)
    y<-gsub("Ô","O", y)
    y<-gsub("Õ","O", y)
    y<-gsub("Ú","U", y)
    y<-gsub("Ü","U", y)
    y<-gsub("Ç","C", y)
    y<-gsub("'"," ", y)
    y<-gsub("."," ", y, fixed=TRUE)  
    y<-gsub("-"," ", y, fixed=TRUE)    
    return(y)
}

## get first and last name
firstlast <- function(x) {
  x <- as.character(x)
  s1 <- strsplit(x," ")
  fl <- function(z) paste(z[1],z[length(z)])
  sapply(s1,fl)
}



##convert character vectors from a df from latin1
iconv.df <- function(df,encoding="windows-1252") {
  cv <- which(sapply(df,is.character))
  for (i in cv) df[,i] <- iconv(df[,i],from=encoding)
  fv <- which(sapply(df,is.factor))
  for (i in fv) {
    levels(df[,i]) <- iconv(levels(df[,i]),from=encoding)    
  }
  df
}


## MySQL utils - These save factors and characters
## that are utf8, convert the_codes_ into latin1 (three bytes per non ascii char)
## and writes the  table
dbWriteTableU <- function(conn,name,value,convert=FALSE,...) {
  if (is.data.frame(value)) {
    if (convert) {
      value <- iconv.df(value)
    }
  } else {
    stop("must be a data frame")
  }
  dbWriteTable(conn, name, value,...,row.names = FALSE, eol = "\r\n")
}

dbReadTableU <- function(conn,name,...,convert=TRUE) {
  df <- dbReadTable(conn, name,...)
  if (convert) {
    df <- iconv.df(df)
  }
  df
}

dbWriteTableSeq <- function(conn,name,value,n=NULL,...) {
  nr <- nrow(value)
  if (is.null(n)) {
    splits <- min(100,round(nr/2))
  } else {
    splits=n
  }
  st <- rep(1:splits,length.out=nr)
  for ( i in 1:max(st)) {
    cat(i,".")
    dbWriteTableU(connect,name,value[st==i,],append=TRUE)
  }
}

  

## EXAMPLE FOR WORKING AROUND RMYSQL ENCODING LIMITATIONS
## library(RMySQL)
## driver <- dbDriver("MySQL")
## connect <-dbConnect(driver, group="yourdb")
## dbRemoveTable(connect,"t1")
## df1 <- data.frame(a=c("Apple","Passion Fruit"),b=c("Maçã","Maracujá"),fix="")
## dbWriteTable(connect,"t1",df1,row.names=FALSE)
## dbReadTable(connect,'t1')
## df1$fix <- iconv(as.character(df1$b),from='latin1')
## dbWriteTable(connect,"t1",df1,append=TRUE,row.names=FALSE)
## dbReadTable(connect,'t1')





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
  names(LV) <- c("session","rcvoteid","namelegis",paste("vote",voteid,sep="."),"party","state","id") #rename fields
  ##FIX: ENABLE CLEAN NAME OR NOT?
  ##LV$name<-clean.name(LV) #apply cleaning function for accents and other characters
  LV$state <- toupper(state.l2a(LV$state))
  LV$state <- factor(LV$state,levels=toupper(states))
  LV <- LV[,c("id","namelegis","party","state",paste("vote",voteid,sep="."))] #rearrange fields
  vt.date<-as.Date(as.character(read.table(HEfile, header = FALSE, nrows = 1,skip = 2, strip.white = TRUE, as.is = TRUE)[1,1]), "%d/%m/%Y")
  vt.descrip<-read.table(HEfile, header = FALSE, nrows = 1,skip = 12, strip.white = TRUE, as.is = TRUE, sep=";",quote="")
  vt.session<-read.table(HEfile, header = FALSE, nrows = 1,skip = 0, strip.white = TRUE, as.is = TRUE)[1,1]
  vt.descrip<-gsub("\"","",vt.descrip)    #get rid of quotes in the description of the bill
  HE <- data.frame(rcvoteid=voteid,rcdate=vt.date,session=vt.session,billtext=vt.descrip)  
  data.votacoes <- get.votacoes(HE)
  data.votacoes$legis <- get.legis(data.votacoes$legisyear)
  data.votacoes$rcfile <- LVfile
  data.votos <- LV
  data.votos$rcfile <- LVfile
  data.votos$rcvoteid <- voteid
  names(data.votos)[5] <- "rc"
  data.votos$rc <- gsub("^<.*","Ausente",as.character(data.votos$rc))
  data.votos$rc <- gsub("^Art.*","Abstenção",as.character(data.votos$rc))
  data.votos$legis <- data.votacoes$legis[1]
  if (!post) {
    list(data.votos=data.votos,data.votacoes=data.votacoes)    
  } else {
    connect.db()
    session.now <- as.character(data.votacoes$legis[1])
    ##bioids
    idname <- dbGetQuery(connect,paste("select * from br_bioidname where legis='",session.now,"'",sep=''))
    idname <- iconv.df(idname)
    idname$id <- NULL
    ## merge using the br_ids db (a mapping of all ids)
    createtab <- !dbExistsTable(connect,"br_idbioid")
    ids <- dbReadTable(connect,"br_idbioid")
    if (nrow(ids)>0) {
      ##merge with camara ids first
      tomatch <- merge(data.votos,ids,by=c("id","legis"),all.x=TRUE)
      tomatch <- subset(tomatch,is.na(bioid),select=-bioid)
    } else {
      tomatch <- data.votos
    }
    ##try to find the bioid for new deps
    if (nrow(tomatch)>0) {
      idname$namelegis <- idname$name
      res <- merge.approx(states,idname,
                          tomatch,"state","namelegis")
      ##might have multiple matches. We discard if the 
      ##tripple (id,bioid, session) is still unique
      res <- unique(with(res,data.frame(bioid,id,legis)))
      ##fix: check explicitly for multiple ids.
      if(min(tomatch$id%in%res$id)==0) {
        print(tomatch[!tomatch$id%in%res$id,])
        stop("Some legislators not yet in db")
      } else if (sum(duplicated(res$bioid))) {
        print(res[with(res,bioid%in%bioid[which(duplicated(bioid))]),])
        stop("Some ids are duplicated ")
      }
      ##write new matches to db
      dbWriteTableU(connect, "br_idbioid",res,append=TRUE)
    }
    ## read table again
    ids <- dbReadTableU(connect,"br_idbioid")
    data.votos <- merge(data.votos,ids,by=c("id","legis"),all.x=TRUE)
    if (sum(is.na(data.votos$bioid))>0) stop("there are missing ids")
    dbWriteTableU(connect, "br_votos",data.votos, append=TRUE)
    dbWriteTableU(connect, "br_votacoes",data.votacoes, append=TRUE)
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
  if (exists("connect")) {
    testconnect <- class(try(dbListTables(connect)))
    if ("try-error"%in%testconnect) {
      try(dbDisconnect(connect))
      connect<<-dbConnect(driver, group="congressoaberto")
    }
  } else {
    driver<<-dbDriver("MySQL")
    connect<<-dbConnect(driver, group="congressoaberto")
  }
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
  vec <- cut(x+1,seq(1947,max(x)+4,4),include.est=FALSE)
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
    billproc <- billtext
    billproc <- gsub(" +"," ",billproc)
    billproc <- gsub(" / ","/",billproc)
    billproc <- gsub("^PL P ","PLP ",billproc)
    billproc <- gsub("N º","Nº",billproc)
    billproc <- gsub("N \\.","N.",billproc)
    billproc <- gsub("/;","/",billproc)
    billproc <- gsub("(^[A-Z]+) ([0-9])","\\1 Nº \\2",billproc)
    ##     wpdate <- as.character(paste(data,"T12:00:00"))
  })
  ## parse billproc (bill)
  ss <- strsplit(gsub(" +"," ",as.character(data.votacoes$billproc)),c(" |/"))
  data.votacoes <- within(data.votacoes,{  
    billtype <- factor(sapply(ss,function(x) x[1]))
    billtype <- car::recode(billtype,"'MENSAGEM'='MSG';'PROCESSO'='PRC';'PROPOSICAO'='PRP';'RECURSO'='REC';'REQUERIMENTO'='REQ';'L'='PL'")
    billtype <- gsub("\\.","",billtype)
    billno <- (sapply(ss,function(x) x[3]))
    billyear <- as.numeric(sapply(ss,function(x) x[4]))
    billyear[billyear==203] <- 2003
    billyear <- ifelse(billyear<1000 & billyear>50, billyear+1900,billyear)
    billyear <- ifelse(billyear<1000 & billyear<50, billyear+2000,billyear)
    rcyear <- format.Date(rcdate,"%Y")
    m <- as.numeric(as.numeric(format.Date(rcdate,"%m"))<2)
    legisyear <- as.numeric(rcyear)-m
    rm(m)
    ##if ano is missing use ano_votacao
    billyear <- ifelse(is.na(billyear), rcyear,billyear)
    bill <- with(data.votacoes,paste(billtype," ",billno,"/",billyear,sep=""))
    billdescription <- sapply(ss,function(x) paste(x[6:length(x)], collapse=" "))
    billdescription <- gsub("^ *- *", "", billdescription)
  })
  data.votacoes
}


##sanity check, exclude duplicates
dedup.db <- function(tab) {
  for (x in tab) {
    ## FIX make it drop rows and insert instead of creating tables anew
    init <- dbGetQuery(connect,paste("select count(*) as row_ct from ",x))[1,1]
    try(dbSendQuery(connect,"drop  table tmp"))
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


dia <- paste(c("Â","Á","Ã","É","Ê","Í","Ó","Ô","Õ","Ú","Ü","Ç"),collapse=" ")
## "Í" and "Á" create problems in MySQL latin1 conversions.
