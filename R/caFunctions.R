##FIX: dbWriteTable , append=TRUE does NOT update the table
##Possible solutions: write a function that deletes the data beforehand
##                    write a function that creates a new temp table and updates from there with a join 
##                    delete relevant rows explicitly
##UPDATE summary AS t, (query) AS q SET t.C=q.E, t.D=q.F WHERE t.X=q.X
tmptable <- function() paste("t",paste(sample(c(letters,0:9),10,replace=TRUE), collapse=""),sep='')

usource <- function(...) source(...,encoding="utf8")

options(stringsAsFactors=FALSE)

run <- FALSE
if (run) {
  ## paths (put on the beg of R scripts)
  rf <- function(x=NULL) {
    if (.Platform$OS.type!="unix") {
      run.from <- "C:/reps/CongressoAberto"
    } else {
      run.from <- "~/reps/CongressoAberto"
    }
    ## side effect: load functions
    source(paste(run.from,"/R/caFunctions.R",sep=""),encoding="utf8")
    if (is.null(x)) {
      run.from
    } else {
      paste(run.from,"/",x,sep='')
    }
  }
}




##cpf/cnpj validation
validate.cpfcnpj <- function(x) {
  ifelse(nchar(x)==11,sapply(x,valid.cpf),sapply(x,valid.cnpj))
}
valid.cnpj <- function(x) {
  valid.cnpj1 <- c(5,4,3,2,9,8,7,6,5,4,3,2)
  valid.cnpj2 <- c(6,valid.cnpj1)
  x <- as.character(x)
  if (nchar(x)!=14) return(FALSE)
  x <- strsplit(x,"")[[1]]
  ## validate 1st
  sx <- sum(as.numeric(x[1:12])*valid.cnpj1)%%11
  if (sx<2) {
    d1 <- 0
  } else {
    d1 <- 11-sx
  }
  if (d1!=x[13]) {
    return(FALSE)
  }
  ## validate 2nd
  sx <- sum(as.numeric(x[1:13])*valid.cnpj2)%%11
  if (sx<2) {
    d1 <- 0
  } else {
    d1 <- 11-sx
  }
  if (d1!=x[14]) {
    return(FALSE)
  }
  TRUE
}
valid.cpf <- function(x) {
  x <- as.character(x)
  if (nchar(x)!=11) return(FALSE)
  valid.cpf1 <- c(10,9,8,7,6,5,4,3,2)
  valid.cpf2 <- c(11,valid.cpf1)
  x <- strsplit(x,"")[[1]]
  ## validate 1st
  d1 <- 11-sum(as.numeric(x[1:9])*valid.cpf1)%%11
  if (d1>=10) {
    d1 <- 0
  }
  if (d1!=x[10]) {
    return(FALSE)
  }
  ## validate 2nd
  d2 <- 11-sum(as.numeric(x[1:10])*valid.cpf2)%%11
  if (d2>=10) {
    d2 <- 0
  } 
  if (d2!=x[11]) {
    return(FALSE)
  }
  TRUE
}


## recode bill type
## FIX: check this types against the ones used in camara
##FIX: Parecer da Câmara (p.c) vs. parecer de comissão (PAR)
##FIX: PLC (Lei complementar) or PLP?
recode.billtype <- function(x) {
  car::recode(x,"'PLN'='PL';c('MP','MEDIDA')='MPV';c('MENSAGEM', 'MENS','MSG')='MSC';c('PARECER')='PAR';'PDL'='PDC';'PLC'='PLP';'PROCESSO'='PRC';'PROPOSICAO'='PRP';'RECURSO'='REC';'REQUERIMENTO'='REQ';'L'='PL'")
}

##get bill no as numeric
get.billno <- function(x) {
  x <- gsub("\\.","",x)
  x <- gsub("[A-Z]*|-","",x)
  x <- as.numeric(x)
  x
}


recode.party <- function(x) x <- car::recode(x,'"PFL"="DEM"')

pad0 <- function(x,mx=NULL,fill=0) {
  lx <- nchar(as.character(x))
  mx.calc <- max(lx,na.rm=TRUE)
  if (!is.null(mx)) {
    if (mx<mx.calc) {
      stop("number of maxchar is too small")
    }
  } else {
    mx <- mx.calc
  }
  px <- mx-lx
  paste(sapply(px,function(x) paste(rep(fill,x),collapse="")),x,sep="")
}

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


## check to see if charset conversion is needed
dbConvert <- function(connect) {
  a <- data.frame(word="MaçãMAÇÓES")
  tmp <- tmptable()
  dbGetQuery(connect, paste("drop table if exists ", tmp))
  dbWriteTable(connect, tmp, a)
  if ((dbReadTable(connect, tmp)==a)[1]) {
    res <- FALSE
  } else {
    res <- TRUE
  }
  dbGetQuery(connect, paste("drop table if exists ", tmp))
  res
}

dbReadTableU <- function(conn,name,...,
                         ##convert=FALSE
                         convert=dbConvert(conn)
                         ) {
  df <- dbReadTable(conn, name,...)
  if (convert) {
    df <- iconv.df(df)
  }
  df
}


dbGetQueryU <- function(conn,statement,...,
                        ##convert=FALSE
                        convert=dbConvert(conn)
                        ) {
  df <- dbGetQuery(conn, statement,...)
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
    return(list(data.votos=data.votos,data.votacoes=data.votacoes))
  } else {
    connect.db()
    session.now <- as.character(data.votacoes$legis[1])
    ##bioids
    idname <- dbGetQueryU(connect,paste("select * from br_bioidname where legis='",session.now,"'",sep=''))
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
    ## Remove deputies without state
    tomatch.nostate <- subset(tomatch,is.na(state))
    tomatch <- subset(tomatch,!is.na(state))
    ##try to find the bioid for new deps
    idname$namelegis <- idname$name
    res <- res.nostate <- NULL
    check <- function(res,tomatch) {
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
      res
    }
    if (nrow(tomatch)>0) {
      ##browser()
      res <- merge.approx(states,idname,
                          tomatch,"state","namelegis")
      res <- check(res,tomatch)
    }
    ## does this actually happen?
    if (nrow(tomatch.nostate)>0) {
      res.nostate <- merge.one(idname,tomatch.nostate,"namelegis",maxd=0.2)
      res.nostate <- check(res.nostate,tomatch.nostate)
    }
    res <- unique(rbind(res,res.nostate))
    ##write new matches to db
    if (!is.null(nrow(res))) dbWriteTableU(connect, "br_idbioid",res,append=TRUE)
  }
  ## read table again
  ids <- dbReadTableU(connect,"br_idbioid")
  data.votos <- merge(data.votos,ids,by=c("id","legis"),all.x=TRUE)
  if (sum(is.na(data.votos$bioid))>0) stop("there are missing ids")
  dbWriteTableU(connect, "br_votos",data.votos, append=TRUE)
  dbWriteTableU(connect, "br_votacoes",data.votacoes, append=TRUE)
  ## update leaders table
  getLeaders(voteid)  
}
##readOne(LVfile,post=TRUE)
  
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
  if (.Platform$OS.type!="unix") {
    defaultfile <- "C:/my.cnf"
  } else {
    defaultfile <- "~/.my.cnf"
  }
  library(RMySQL)
  if (exists("connect")) {
    testconnect <- class(try(dbListTables(connect)))
    if ("try-error"%in%testconnect) {
      try(dbDisconnect(connect))
      connect<<-dbConnect(driver, group="congressoaberto",default.file=defaultfile)
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
## session  (e.g. 1991-1994)
get.legis.text <- function(x) {
  ## note the +1 here to make calc right
  vec <- cut(x+1,seq(1947,max(x)+4,4),include.est=FALSE)
  labs <- levels(vec)
  labs <- cbind(lower = as.numeric( sub("\\((.+),.*", "\\1", labs) ),
                upper = as.numeric( sub("[^,]*,([^]]*)\\]", "\\1", labs) ))
  labs <- apply(labs,1,paste,collapse="-")
  levels(vec) <- labs
  as.character(vec)
}

##given a _legislative_ year (ends Feb 1st) give the legislative
## session number (e.g. 49 for 1991-1994)
get.legis <- function(x) {
  ## note the +1 here to make calc right
  vec <- as.numeric(cut(x+1,seq(1947,max(x)+4,4),include.est=FALSE))+37
  vec
}

##given a legislatura (e.g. 1991-1995) returns the legislatura number (49)
get.legis.n <- function(x) get.legis(as.numeric(substr(strsplit(as.character(x), ",")[[1]], 1, 4)))

##given a legislatura number (49) returns the first legis year
get.legis.year <- function(x=49) {
  1795+x*4
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
    billtype <- as.character(sapply(ss,function(x) x[1]))
    billtype <- recode.billtype(gsub("\\.","",billtype))
    billno <- get.billno(sapply(ss,function(x) x[3]))
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


trim <-  function (s)
{
  s <- sub("^\t+","", s)
  s <- sub("^ +", "", s)
  s <- sub(" +$", "", s)
  s
}


trimm <- function(x) gsub(" +"," ",trim(x))

getbill <- function(sigla="MPV",numero=447,ano=2008,overwrite=TRUE, deletefirst=TRUE) {
  ##FIX use RCurl?
  if (overwrite) {
    opts <- "-N"
  } else {
    opts <- "-nc"
  }  
  ## FIX: download the file to disk (allows faster recovery)
  ## FIX: code as NA if value is missing
  ## -N for overwriting, -nc for not overwriting
  ##tmp <- system(paste("wget -r -l1 -t 15  ",opts," 'http://www.camara.gov.br/sileg/Prop_Lista.asp?Sigla=",sigla,"&Numero=",numero,"&Ano=",ano,"' -P ~/reps/CongressoAberto/data/www.camara.gov.br/sileg  2>&1",sep=''),intern=TRUE)
  ##if (deletefirst)   unlink(paste("~/reps/data/", billurl, sep=''))
  tmp <- system(paste("wget -r -l1 -t 15 --force-html --base=url  ",opts," 'http://www.camara.gov.br/sileg/Prop_Lista.asp?Sigla=",sigla,"&Numero=",numero,"&Ano=",ano,"' -P ~/reps/CongressoAberto/data/  2>&1",sep=''),intern=TRUE)
  tmp <- iconv(tmp,from="latin1")
  urlloc <- grep(".*www.camara.gov.br/sileg/.*id=.*",tmp)[1]
  ##url <- Prop_Detalhe.asp?id=
  ##id <- gsub(".*id=(.*)", "\\1", url)
  url <- tmp[urlloc]
  id <- gsub(".*id=([0-9]*).*", "\\1", url)
  if (length(grep("id=", url))==0) {
    id <- NA
  }
  ##c(url, id)
  id
}

##http://www.camara.gov.br/sileg/MostrarIntegra.asp?CodTeor=83624


remove.tags <- function(x) gsub("<[^<]*>|\t",  "",  x)

#FIX add to db, first checking that the results were updated.
readbill <- function(file) {  
  if (length(grep("Prop_Erro|Prop_Lista",file))>0)  return(NULL)
  ##FIX: figure out if encoding is necessary
  tmp <- readLines(file,encoding="latin1"
                   )
  ## write file out for debugging
  writeLines(tmp, con="~/reps/CongressoAberto/tmp/tmp.html")
  if(length(grep("Nenhuma proposição encontrada",tmp))>0) return(NULL)
  tmp <-  gsub("\r|&nbsp","",tmp)
  tmp <-  gsub(";+"," ",tmp)
  t0 <- tmp[grep("Proposição",tmp)[1]]
  propno <- as.numeric(trimm(gsub(".*CodTeor=([0-9]+).*","\\1",t0)))
  ##FIX: parse result when a deputado is the author
  t0 <- tmp[grep("Autor",tmp)[1]]
  author <- trimm(gsub(".*Autor: </b></td><td>(.*)</td>.*","\\1",t0))
  ##FIX: what to do with "Poder Executivo" and other non-legislators?
  if (length(grep("Detalhe.asp", t0))>0) {
    authorid <- gsub(".*Detalhe.asp\\?id=([0-9]*).*", "\\1", t0)
  } else {
    authorid <- NA
  }
  t0 <- tmp[grep("Data de Apresentação",tmp)]
  date <- trimm(gsub(".*</b>(.*)","\\1",t0))
  date <- as.character(as.Date(date,"%d/%m/%Y"))
  ##FIX: find what this is
  t0 <- tmp[grep("Apreciação:",tmp)][1]
  aprec <- trimm(gsub(".*</b>(.*)","\\1",t0))
  ##FIX: need english name
  t0 <- tmp[grep("Regime de tramitação:",tmp)+1][1]
  ##tramit <- trimm(gsub(".*</b>(.*)","\\1",t0))
  ## note use of the not operator!
  tramit <- trimm(gsub("([^<]*)<br>?.*","\\1",t0, perl=TRUE))
  tramit[tramit=="."] <- NA
  ##FIX: Categorize response
  t0 <- tmp[grep("Situação:",tmp)][1]
  status <- trimm(gsub(".*</b>(.*)<br>","\\1",t0))
  ##FIX: name
  t0 <- tmp[grep("Ementa:",tmp)][1]
  ementa <- trimm(gsub(".*</b>(.*)","\\1",t0))
  ##FIX: name
  t0 <- tmp[grep("Explicação da Ementa:",tmp)][1]
  ementashort <- trimm(gsub(".*</b>(.*)","\\1",t0))
  ##FIX: name
  t0 <- tmp[grep("Indexação:",tmp)][1]
  indexa <- trimm(gsub(".*Indexação: </b>(.*)","\\1",t0))
  ##FIX: name
  iua <- grep("Última Ação:",tmp)[1]+7
  if (!is.na(iua)) {
    t0 <- tmp[iua]
    uadate <- trimm(gsub("<b>([^<]*).*","\\1",  tmp[iua]))
    iua.e <- grep("</table>",tmp[-c(1:iua)])[1]+iua
    t0 <- paste(tmp[(iua+1):iua.e],  collapse=" ")
    t0 <- trimm(gsub("<[^<]*>|\t",  "",  t0))
    uadesc <- trimm(gsub("<b>([^<]*).*","\\1",  tmp[iua+7]))
  } else {
    uadate <- NA
    uadesc <- NA
  }
  ## FIX: what else? despacho? table with tramitação?
  f <- function(x) ifelse (length(x)==0,NA,remove.tags(x))
  res <- try(data.frame(## billtype=f(sigla), ##FIX GET FROM FILE
                        ## billno=f(numero),
                        ## billyear=f(ano),
                        propno=f(propno),
                        billauthor=f(author),
                        billauthorid=f(authorid),
                        billdate=f(date),
                        aprec=f(aprec),
                        tramit=f(tramit),
                        status=f(status),
                        ementa=f(ementa),
                        ementashort=f(ementashort),
                        indexa=f(indexa),
                        lastactiondate=f(uadate),
                        lastaction=f(uadesc),
                        stringsAsFactors=FALSE))
  if (("try-error"%in%class(res))) {   
    res <- NULL
  } 
  res
}

factors2strings <- function(x) data.frame(lapply(x,function(z) {
  if (is.factor(z)) z <- as.character(z)
  z
}),stringsAsFactors=FALSE)


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


## VARIABLES

states <- c("AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG", "PA","PB","PR","PE","PI","RJ","RN","RS","RO","RR","SC","SP","SE","TO")



map.rc <- function(filenow='',title='') {  
  rc$rc2 <- rc$rc=="Sim"
  tmp <- recast(rc,state~variable,measure.var="rc2",fun.aggregate=function(x) c(n=length(x),p=sum(x)/length(x)))
  tmp$UF <- tmp$state
  m2 <- merge.sp(m1,tmp,by="UF")
  par(bg="grey")
  n1 <- 4
  seqx <- c(0,.15,.3,.45,.55,.70,.85,1)
  col.vec <- c(rev(brewer.pal(n1,"Blues")[-1]),"grey95",brewer.pal(n1,"Reds")[-1])
  ##pdf(file=paste(fname,"small.pdf",sep=""),height=6,width=6)
  ##par(mai=c(0,0,0,0))
  ##plot.heat(m2,NULL,"concpt_p",title="Proporção votando\njunto com o PT",breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,plot.legend=FALSE)
  ##dev.off()
  ##   pdf(file=paste(fname,".pdf",sep=""),height=6,width=6)
  par(mai=c(0,0,0.6,0))
  plot.heat(m2,NULL,"rc2_p",title="Proporção votando a favor da proposição",breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,main=filenow)
  with(m2@data,text(x,y,UF,cex=0.8),col="grey30")
  mtext(title,3, cex=.9)
  ##   dev.off()
}


barplot.rc <- function(pty="PT",title="") {
  ## FIX: add reference lines for the required number of Yes votes.
  ## Look for quorum.
  votop <- names(which.max(table(rc[rc$party==pty,"rc"])))
  rc$votop <- rc$rc==votop
  rc$rc2 <- factor(with(rc,car::recode(rc,"'Sim'='A Favor';else='Contra'")),levels=c("Contra","A Favor"))
  colvec <- c("darkblue","red")[order(table(rc$votop))]
  colvec <- alpha(colvec,"1")
  ## Stacked barchart
  wd <- .97
  theme_set(theme_grey(base_size = 10))
  p <- ggplot(rc, aes(x = rc2))+geom_bar(width = wd,aes(fill = rc))+geom_bar(data=rc,colour=colvec,width=wd,size=2,fill="transparent")+scale_y_continuous(name="",limits=c(0,513),expand=c(0,0))
  p <- p+theme_bw()+opts(axis.title.x = theme_blank(),
                         axis.title.y = theme_blank(),
                         panel.grid.minor = theme_blank(),
                         panel.grid.major=theme_blank(),
                         panel.background=theme_rect(fill = NA, colour = NA),
                         plot.background = theme_rect(colour = NA,fill=NA)
                         ,plot.title = theme_text(size = 10))
  psmall <- p+opts(legend.position="none",
                   axis.text.y = theme_blank(),
                   axis.text.x = theme_blank()
                   ,axis.ticks = theme_blank()
                   ,panel.border = theme_blank()
                   )
  p <- p+opts(title=title)
  ## pdf(file=paste(fname,"bar.pdf",sep=""),height=6,width=6)
  print(p)
  ##   dev.off()
  ##   pdf(file=paste(fname,"barsmall.pdf",sep=""),height=6,width=4)
  ##   print(psmall)
  ##   dev.off()  
}


plot.heat <- function(tmp,state.map,z,title=NULL,breaks=NULL,reverse=FALSE,cex.legend=1,bw=.2,col.vec=NULL,main=NULL,plot.legend=TRUE) {
  ##Break down the vote proportions
  if (is.null(breaks)) {
    breaks=
      seq(
          floor(min(tmp@data[,z],na.rm=TRUE)*10)/10
          ,
          ceiling(max(tmp@data[,z],na.rm=TRUE)*10)/10
          ,.1)
  }
  tmp@data$zCat <- cut(tmp@data[,z],breaks,include.lowest=TRUE)
  cutpoints <- levels(tmp@data$zCat)
  if (is.null(col.vec)) col.vec <- heat.colors(length(levels(tmp@data$zCat)))
  if (reverse) {
    cutpointsColors <- rev(col.vec)
  } else {
    cutpointsColors <- col.vec
  }
  levels(tmp@data$zCat) <- cutpointsColors
  plot(tmp,border=gray(.8), lwd=bw,axes = FALSE, las = 1,col=as.character(tmp@data$zCat),main="A")
  if (!is.null(state.map)) {
      plot(state.map,add=TRUE,lwd=1)
  }
  if (plot.legend) legend("bottomleft", cutpoints, fill = cutpointsColors,bty="n",title=title,cex=cex.legend)
}


##read file and get centroids
readShape.cent <- function(shape.file="~/test.shp",IDvar="NOMEMESO") {
  require(maptools)
  ##  read shape and get centroids
  tmp <- read.shape(shape.file)
  tmp.c <- as.data.frame(get.Pcent(tmp))
  names(tmp.c) <- c("x","y")
  tmp.c[,IDvar] <- tmp$att.data[,IDvar]
  tmp <-  readShapePoly(shape.file,IDvar=IDvar)
  tm <- match(tmp@data[,IDvar],tmp.c[,IDvar])
  tmp@data$x <- tmp.c[tm,1]
  tmp@data$y <- tmp.c[tm,2]
  tmp
}


##merge sp objects with data
merge.sp <- function(tmp,data,by="uf") {
  by.loc <- match(by,names(data))
  by.data <- data[,by.loc]
  data <- data[,-by.loc]
  tmp@data <- data.frame(tmp@data,
                         data[match(tmp@data[,by],by.data),]
                         )
  tmp
}



##write a kml file for the vote map
tokml <- function(sw,file.now,dir.now="",name="NOME_MUNIC",white=FALSE, compress=3,order=FALSE) {
  if (white) sw@data$zCat <- "white"
  swd <- sw@data
  xa <- slot(sw, "polygons")
  out2 <- vector(length=length(xa)*2,mode="list")
  dim(out2) <- c(2,length(xa))
  dimnames(out2) <- list(c("style","content"))
  ##Ordering does not work for display in google maps (but does for g earth)
  ##FIX: Alphabetical order is screwed up by accents in google maps.
  j <- 1
  if (order) {
    ov <- order(-sw@data$votes_total)
    } else {
      ov <- 1:nrow(swd)
    }
  for (i in ov) {
    x <- xa[[i]]
    ## FIX: compress?
    x@Polygons[[1]]@coords <- round(x@Polygons[[1]]@coords,compress)
    res <- kmlPolygon(x,
                      name=swd[slot(x, "ID"), name], 
                      col=as.character(swd[slot(x, "ID"), "zCat"]), 
                      lwd=0, border=as.character(swd[slot(x, "ID"), "zCat"])
                      ,description=with(swd[slot(x, "ID"),],
                         ##paste("Total: ",votes_total, "; Candidato: ",round(votes/votes_total*100),"%",sep='')
                         "HERe"
                         ))
    out2[[1,j]] <- res[[1]]
    out2[[2,j]] <- res[[2]]
    j <- j +1
  }
  out <- out2
  tf <- paste(dir.now,file.now,".kml",sep='')
  kmlFile <- file(tf, "w")
  cat(kmlPolygon(kmlname="Eleições 2006", kmldescription="<i>Votos para Presidente, 1o. Turno: Lula</i>")$header, 
      file=kmlFile, sep="\n")
  cat(unlist(out["style",]), file=kmlFile, sep="\n")
  cat(unlist(out["content",]), file=kmlFile, sep="\n")
  cat(kmlPolygon()$footer, file=kmlFile, sep="\n")
  close(kmlFile)
  system(paste("scp ",dir.now,file.now,".kml leoniedu@cluelessresearch.com:files.eduardoleoni.com/",file.now,".kml",sep=''))
  system(paste("zip -r ",dir.now,file.now,".kmz ",dir.now,file.now,".kml",sep=''))
  system(paste("scp ",dir.now,file.now,".kmz leoniedu@cluelessresearch.com:files.eduardoleoni.com/",file.now,".kmz",sep=''))
}


## color scales for map
color.heat <- function(tmp,z,breaks=NULL,reverse=FALSE,col.vec=NULL) {
  ##Break down the vote proportions
  if (is.null(breaks)) {
    breaks=
      seq(
          floor(min(tmp@data[,z],na.rm=TRUE)*10)/10
          ,
          ceiling(max(tmp@data[,z],na.rm=TRUE)*10)/10
          ,.1)
  }
  tmp@data$zCat <- cut(tmp@data[,z],breaks,include.lowest=TRUE)
  cutpoints <- levels(tmp@data$zCat)
  if (is.null(col.vec)) col.vec <- heat.colors(length(levels(tmp@data$zCat)))
  if (reverse) {
    cutpointsColors <- rev(col.vec)
  } else {
    cutpointsColors <- col.vec
  }
  levels(tmp@data$zCat) <- cutpointsColors
  tmp@data$zCat <- as.character(tmp@data$zCat)
  tmp
}



# Gets the leadership votes for a specific voteid or vote.file
# Returns matrix with voteid, name of leadership, and vote
getLeaders <- function(x) {    #x is a string with the name of the vote.file (.txt), or the voteid field from
  print(x)
  st <- paste("select * from br_leaders where rcvoteid=",x,sep='')
  res <- dbGetQuery(connect,st)
  if (nrow(res)>0) return(NULL)
  if((nchar(x)>8))   {
    ## there is no data for these rcvoteid's
    return(NULL)
  }
  vote.name<-as.numeric(x)
  the.url <- paste("http://www.camara.gov.br/internet/votacao/mostraVotacao.asp?ideVotacao=",vote.name,sep="") 
  raw.data <-try(readLines(the.url,500,encoding="latin1"),silent=TRUE)
  if(class(raw.data)=="try-error") {
    print(the.url)
    cat("Connection problems",vote.name,"Will try again soon\n")
    marker <- proc.time() 
    while(((proc.time()-marker)[3])/60 < 0.1) {
      flush.console()
    }
    cat("\t Attempting to connect...\n")
    flush.console()
    raw.data<-try(readLines(the.url,500,encoding="latin1"),silent=TRUE)
    if(class(raw.data)=="try-error") {
      cat("\t No data for",vote.name,"\n")
      return(NULL)
    }
  }
  orientation.line <- grep("Orientação",raw.data)                 #Check for encoding problems here
  if(length(orientation.line)==0){
    cat("No data for",vote.name,"\n")
    flush.console()
    return(NULL)
  } #No leadership votes
  raw.orientation <- raw.data[grep("Orientação",raw.data):(grep("Parlamentar",raw.data)-10)]
  raw.leadership <- raw.orientation[grep(":",raw.orientation)]#make sure all parties are caps, for later matches
  raw.position <- raw.orientation[grep(":",raw.orientation)+1]
  leadership <- gsub(".*\"right\" >(.*):.*$","\\1",raw.leadership)
  ##leadership <- gsub("^.*>(\\w*)\\W{1,2}<.*$","\\1",raw.leadership)
  position <- gsub("^.*>(\\w*)\\s*<.*$","\\1",raw.position)
  leadership <- trimm(gsub("\\."," ",leadership))
  output <- data.frame(rcvoteid=vote.name,block=leadership,rc=position)
  output <- splitBlocks(output)
  dbWriteTableU(connect,"br_leaders",output,append=TRUE)
  return(output)
}

##split the coalition string into individual components
splitBlock <- function(block="PmdbPtPsdb") {
  if (length(grep("/",block))>0) {
    strsplit(block,"/")[[1]]
  } else {
    split1 <- strsplit(block,"")[[1]]
    split1 <- which(split1==toupper(split1))
    init <- split1
    end <- c(split1[-1]-1,nchar(block))
    substring(block,init,end)
  }
}

##convert the coalition dataframe into a parties dataframe
splitBlocks <- function(data) {
  data$block <- gsub("Repr ","",data$block)
  ## single parties are all caps and no slashes
  singlep <- data$block==toupper(data$block)
  singlep[grep("/",data$block)] <- FALSE  
  ## put all parties initials (and initials only) as caps
  data$block <- gsub("PTdoB","Ptdob",data$block)
  data$block <- gsub("PCdoB","Pcdob",data$block)
  data$block <- gsub("DEM","Dem",data$block)
  datas <- data[singlep,]
  data <- data[!singlep,]
  ## now the capital letter splits coalitions
  res <- lapply(1:nrow(data),function(x) {
    dx <- data[x,]
    party <- splitBlock(dx$block)
    np <- length(party)
    data.frame(data[rep(x,np),],party)
  })
  res <- do.call(rbind,res)
  datas$party <- datas$block
  res <- rbind(datas,res)  
  res$party <- car::recode(toupper(res$party),"'PTDOB'='PTdoB';'PCDOB'='PCdoB'")
  res$block <- toupper(res$block)
  res
}




## ### subplotting
## i <- sample(1:nrow(pred),1)
## ##i <- 373
## rel <- prop.table(table(pred$abs.score<pred$abs.score[i]))["TRUE"]
## if (is.na(rel)) rel <- 0
## p1 <- phist(pred,i)
## p1 <- p1+geom_text(data=data.frame(x=.75,y=max(table(cut(p1$data$pres.score,seq(0,1,.05))))*.9,label="",pres.score=.5),mapping=aes(x=x,y=y,label=label),size=20,colour="darkblue")
## p1

## ds <- subset(pred,party==pred$party[i])
## p2 <- phist(ds,i)
## p2 <- p2+geom_text(data=data.frame(x=.75,y=max(table(cut(p2$data$pres.score,seq(0,1,.05))))*.9,label=pred$party[i],pres.score=.5),mapping=aes(x=x,y=y,label=label),size=20,colour="darkblue")+
##   theme_bw()

## Layout <- grid.layout( nrow = 2, ncol = 1
##                       ,widths = unit(c(2,2), c("null","null") ),
##                       heights = unit (c(1,1), c("null", "null") )
##                       )
## vplayout <- function (...) {
##   grid.newpage()
##   pushViewport(viewport(
##                         layout= Layout
##                         ))
## }
## subplot <- function(x, y) viewport(layout.pos.row=x, layout.pos.col=y)
## vplayout()
## print(p1, vp=subplot(1,1))
## print(p2, vp=subplot(2,1))




