options(stringsAsFactors=FALSE)
##options(encoding="utf8")
##FIX: dbWriteTable , append=TRUE does NOT update the table
## possible solutions
## delete table before hand
##UPDATE summary AS t, (query) AS q SET t.C=q.E, t.D=q.F WHERE t.X=q.X
## use dbInsert (in wordpress.R)

is.windows <- function() {
  (.Platform$OS.type!="unix")
}

theme_mini <- function() {
  structure(list(axis.ticks.margin = unit(c(-1), "lines"), plot.margin = unit(c(0, 0, 0, 0), "lines"), panel.margin = unit(0, "lines"), axis.title.y = theme_blank(), axis.text.x=theme_blank(), axis.text.y=theme_blank(), axis.ticks=theme_blank()), class="options") 
}


"%+%" <-  function(x,y) paste(x,y,sep='')

capwords <- function(s, strict = TRUE) {
  cap <- function(s) paste(toupper(substring(s,1,1)),
                           {s <- substring(s,2); if(strict) tolower(s) else s},
                           sep = "", collapse = " " )
  res <- sapply(strsplit(s, split = " "), cap, USE.NAMES = !is.null(names(s)))
  res <- gsub("De\\b", "de", res)
  res
}

tmptable <- function() paste("t",paste(sample(c(letters,0:9),10,replace=TRUE), collapse=""),sep='')

usource <- function(...) {
    if (is.windows()) {
        source(..., encoding="utf8")
    } else {
        source(...)
    }
}


## save the files directly to web path on server
webdir <- function(x=NULL) {
    paste("/var/www/",x,sep='')
}


## sync local repository images with server images
imagesync <- function() {
    system("rsync  --stats --recursive -u --chmod=o+r  ~/reps/CongressoAberto/images/  /var/www/images/.")
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


## recode parties
recode.party <- function(x,n=NULL,label.other="Outro",reorder=TRUE) {
  x <- car::recode(x,'"PFL"="DEM"')  
  if (!is.null(n)) {
    tx <- sort(table(x),decreasing=TRUE)
    tx <- tx[(n+1):length(tx)]
    smallp <- names(tx)
    recodes <- paste(shQuote(smallp),shQuote(label.other),sep="=",collapse="; ")
    print(recodes)
    x <- car::recode(x,recodes)
  } 
  if (reorder) {
    tx <- sort(table(x),decreasing=TRUE)
    np <- names(tx)
    if (!is.null(n)) {
      ## small parties at the end
      np <- c(np[np!=label.other],np[np==label.other])
    }
    print(np)
    x <- factor(x,levels=np,ordered=TRUE)
  }
  x
}

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

clean <- function(x,cleanmore=TRUE){
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
    if (cleanmore) {
      y<-gsub("."," ", y, fixed=TRUE)  
      y<-gsub("-"," ", y, fixed=TRUE)
    }
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
iconv.df <- function(df,from="windows-1252", to= getOption("encoding")) {
  cv <- try(which(sapply(df,is.character)),silent=TRUE)
  if (!"try-error" %in% class(cv)) {
    for (i in cv) df[,i] <- iconv(df[,i],from=from, to=to)
    fv <- which(sapply(df,is.factor))
    for (i in fv) {
      levels(df[,i]) <- iconv(levels(df[,i]),from=from, to=to)    
    }
  }
  df
}


convert.png <- function(file="tmp.pdf", crop=FALSE) { #Convert pdf figures to, temporarily hear, but erase later., SHould be in caFunctions.R
  if (crop) {
    nf <- file%+%'crop.pdf'
    try(system(paste("pdfcrop ",file,nf)))
    try(system(paste("mv ", nf, file)))
  }
  file <- path.expand(file)
  opts <-    paste(" -q -dNOPAUSE -dBATCH -sDEVICE=pngalpha -r300 -dEPSCrop -sOutputFile=",gsub(".pdf",".png",file)," ",file,sep='')
  if (.Platform$OS.type!="unix") {
    gs <- '"c:/Program Files/gs/gs8.63/bin/gswin32.exe"'
  } else {
    gs <- "gs"
  }
  command <- paste(gs, opts)
  print(command)  
  system(command,wait=TRUE)
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
    return(FALSE)
    if (FALSE) {
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

dbWriteTableSeq <- function(conn,name,value,n=NULL, wait=0, ...) {
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
    Sys.sleep(wait)
  }
}


diffyear <- function(x,y) {
    x.year <- as.POSIXlt(x)$year
    y.year <- as.POSIXlt(y)$year
    x.date <- as.Date(paste("2008-",substr(as.Date(x),6,10)))
    y.date <- as.Date(paste("2008-",substr(as.Date(y),6,10)))
    (y.year-x.year)-((x.date>y.date))    
}

read.fix <- function(file, encoding="latin1", ...) {
    ff <- file(file)
    tmp <- readLines(ff, encoding=encoding)
    writeLines(tmp, "tmp")
    LV <- read.fwf("tmp" , ...)
    unlink("tmp")
    closeAllConnections()
    LV
}

readOne <- function(LVfile,post=FALSE) {
    ## options(encoding="ISO8859-1")
    HEfile <- gsub("^LV","HE",LVfile)
    ##Read data from VOTE LIST file for the vote
    ##if(nchar(vote)==24){ #formato antigo: titulo tinha 24 characters, no novo so 21
    ##Fixed the following line (I think)
    if(nchar(LVfile)==24)  { #formato antigo: titulo tinha 24 characters, no novo so 21
        LV <- read.fix(LVfile, widths=c(9,-1,9,40,10,10,25,4),strip.white=TRUE)
    }  else {
        LV <- read.fix(LVfile, widths=c(9,-1,6,40,10,10,25,4),strip.white=TRUE,encoding="latin1")
    }
    voteid <- LV$V2[1]  #store number of vote for future use
    names(LV) <- c("session","rcvoteid","namelegis",paste("vote",voteid,sep="."),"party","state","id") #rename fields
    ##FIX: ENABLE CLEAN NAME OR NOT?
    ##LV$name<-clean.name(LV) #apply cleaning function for accents and other characters
    LV$state <- toupper(state.l2a(LV$state))
    LV$state <- factor(LV$state,levels=toupper(states))
    LV <- LV[,c("id","namelegis","party","state",paste("vote",voteid,sep="."))] #rearrange fields
    vt.date<-as.Date(as.character(read.table(HEfile, header = FALSE, nrows = 1,skip = 2, strip.white = TRUE, as.is = TRUE, encoding="latin1")[1,1]), "%d/%m/%Y")
    vt.descrip<-read.table(HEfile, header = FALSE, nrows = 1,skip = 12, strip.white = TRUE, as.is = TRUE, sep=";",quote="",encoding="latin1")
    vt.session<-read.table(HEfile, header = FALSE, nrows = 1,skip = 0, strip.white = TRUE, as.is = TRUE, encoding="latin1")[1,1]
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


connect.mysql <- function(connection,group) {
  if (.Platform$OS.type!="unix") {
    defaultfile <- "C:/my.cnf"
} else {
    defaultfile <- path.expand("~/.my.cnf")
  }
  new <- TRUE
  library(RMySQL)  
  if (exists(connection)) {
      testconnect <- class(try(dbListTables(get(connection)),silent=TRUE))
      if ("try-error"%in%testconnect) {
          try(dbDisconnect(get(connection)))
      } else {
          new <- FALSE
      }
  }
  if (new) {
      driver <-dbDriver("MySQL")
      assign(connection,dbConnect(driver,
                                  group=group,
                                default.file=defaultfile)
             ,envir = .GlobalEnv)
  }
}

## connect to wordpress db
connect.wp <- function() {
    connect.mysql(connection="conwp",group="congressoaberto_br")
    table.names <-   dbListTables(conwp)
    pattern <- "^wp_(.*)_posts$"
    ## the name in the wordpress databases include a random string. So we redefine
    ## the tname function to match the current wordpress installation
    uid <- gsub(pattern,"\\1",table.names[grep(pattern,table.names)])
    tname <<- function(name) {
        tstring <- paste("wp_",uid,"_",name,sep='')
        tstring <- gsub("_+", "_", tstring)
        tstring
    }
}


## connect to "data" db
connect.db <- function() {
    connect.mysql(connection="connect",group="data_br")
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




getweek <- function(x) {
  origin <- as.Date(x)-as.numeric(as.Date(x))
  as.Date(round(as.numeric(as.Date(x))/7)*7,origin=origin)
}


getmonth <- function(x) paste(as.character(format.Date(as.Date(x),"%Y-%m")),"-15",sep="")


##FIX: make just _one_ legis convert function to take care of the date translations.

## given the date, returns the number of days since the first legislative session (feb 1st)
getlegisdays <- function(x) {
  session <- get.legis(get.legis.year.date(x))
  year1 <- get.legis.year(session)
  as.numeric(as.Date(x)-as.Date(paste(year1,02,01,sep="-")))
}

## given a date, return the legislative year
get.legis.year.date <- function(x) {
  x <- as.Date(x)
  ## year in x
  year <- as.numeric(format(x,"%Y"))
  ## if date less than feb 1st year is year-1
  ifelse(x<as.Date(paste(year,"-02-01",sep='')),year-1,year)
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
  ## FIX: code as NA if value is missing
  ## -N for overwriting, -nc for not overwriting
  ##tmp <- system(paste("wget -r -l1 -t 15  ",opts," 'http://www.camara.gov.br/sileg/Prop_Lista.asp?Sigla=",sigla,"&Numero=",numero,"&Ano=",ano,"' -P ~/reps/CongressoAberto/data/www.camara.gov.br/sileg  2>&1",sep=''),intern=TRUE)
  ##if (deletefirst)   unlink(paste("~/reps/data/", billurl, sep=''))
  cmd <- paste("wget -t 15 -x --accept Prop_Deta* --force-html --base=url  ",opts," 'http://www.camara.gov.br/sileg/Prop_Lista.asp?Sigla=",sigla,"&Numero=",numero,"&Ano=",ano,"' -P ~/reps/CongressoAberto/data/  2>&1",sep='')
  print(cmd)  
  tmp <- system(cmd,intern=TRUE)
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

read.tramit <- function(file, encoding="latin1") {
    require(XML)
    zz <- pipe(paste("tidy -q -raw ",file, "2>&1"), encoding="latin1")
    tidy <- readLines(zz)
    if (any(grepl("Out of memory", zz))) stop("server error")
    closeAllConnections() 
    html <- htmlTreeParse(tidy, asText=TRUE, error=function(...){},
                          useInternalNodes=TRUE,
                          encoding="utf8")
    ##html <- htmlTreeParse(readLines(fnow), asText=TRUE, useInternalNodes=TRUE)
    tmp <- xpathSApply(html,"//table[1]/tr[1]",xmlValue)
    tmp <- tmp[grep("Andamento:",tmp):length(tmp)]
    tmp <- strsplit(tmp[-1],"\n")
    df <- data.frame(do.call(rbind,lapply(tmp,function(x) c(x[2],trimm(paste(x[3:length(x)],collapse=" "))))))
    names(df) <- c("date","event")
    df$date <- as.Date(as.character(df$date), "%d/%m/%Y")
    df$id <- 1:nrow(df)
    df
}

##FIX add to db, first checking that the results were updated.
read.bill <- function(file) {  
    if (length(grep("Prop_Erro|Prop_Lista",file))>0)  return(NULL)
    tmp <- readLines(file)
    if (any(grepl("Out of memory", tmp))) stop("server error")
    if (!any(grepl("Módulo", tmp))) tmp <- readLines(file,encoding="latin1")  
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
        uadate <- as.Date(trimm(gsub("<b>([^<]*).*","\\1",  tmp[iua])), format="%d/%m/%Y")
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



map.rc <- function(rc, filenow='',title='', large=TRUE, percent=FALSE) {
    m1 <- readShape(rf("data/maps/BRASIL.shp"))
    if (percent) {
        pct <- 100
        legend.title <- "Votando a favor da proposição (%)"    
    } else {
        pct <- 1
        legend.title <- "Proporção votando a favor da proposição"    
    }
    rc$rc <- car::recode(rc$rc, "'Obstrução'='Não'")
  rc <- subset(rc, rc%in%c("Sim", "Não"))
    rc$rc2 <- rc$rc=="Sim"
    tmp <- recast(rc,state~variable,measure.var="rc2",fun.aggregate=function(x) c(n=length(x),p=pct*sum(x)/length(x)))
    tmp$UF <- tmp$state
    m2 <- merge.sp(m1,tmp,by="UF")
    par(bg="grey90")
    n1 <- 4
    seqx <- c(0,.15,.3,.45,.55,.70,.85,1)*pct
    col.vec <- c(rev(brewer.pal(n1,"Reds")[-1]),"grey95",brewer.pal(n1,"Blues")[-1])
    ##pdf(file=paste(fname,"small.pdf",sep=""),height=6,width=6)
    ##par(mai=c(0,0,0,0))
    ##plot.heat(m2,NULL,"concpt_p",title="Proporção votando\njunto com o PT",breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,plot.legend=FALSE)
    ##dev.off()
    ##   pdf(file=paste(fname,".pdf",sep=""),height=6,width=6)
    if (large) {
        par(mai=c(0,0,0.6,0))
        plot.heat(m2,NULL,"rc2_p",title=legend.title,breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,main=filenow)
        with(m2@data,text(x,y,UF,cex=0.8),col="grey30")
        mtext(title,3, cex=.9)
    } else {
        par(mai=c(0,0,0,0))
        plot.heat(m2,NULL,"rc2_p",breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,main=filenow,plot.legend=FALSE)
        ##with(m2@data,text(x,y,UF,cex=0.8),col="grey30")
        mtext(title,3, cex=.9)
    }
    ##   dev.off()
}


barplot.rc.simple <- function(rc, gov=NA, title="", threshold=NULL) {
    require(RColorBrewer)
    require(ggplot2)
    rc <- subset(rc, rc%in%c("Sim", "Não"))
    rc$rc <- factor(rc$rc, levels=c("Não","Sim"))
    if (is.na(gov)) {
        colvec <- rep("transparent",2)
    } else {
        colvec <- c("grey20","transparent")
        if (gov=="A Favor") {
            colvec <- rev(colvec)
        }
        colvec <- alpha(colvec,"1")
    }
    ## Stacked barchart
    wd <- .95
    theme_set(theme_grey(base_size = 10))
    p <- ggplot(rc, aes(x = rc))+geom_bar(width = wd,aes(fill = rc))+geom_bar(data=rc,colour=colvec,width=wd,size=2,fill="transparent")+scale_y_continuous(name="",limits=c(0,513),expand=c(0,0))
    p <- p+theme_bw()+opts(axis.title.x = theme_blank(),
                           ##axis.title.y = theme_blank(),
                           panel.grid.minor = theme_blank(),
                           panel.grid.major=theme_blank(),
                           panel.background=theme_rect(fill = NA, colour = NA),
                           plot.background = theme_rect(colour = NA,fill=NA)
                           ,plot.title = theme_text(size = 10))
    col.rc <- alpha(rev(c(brewer.pal(3,"Blues")[3], brewer.pal(3,"Reds")[3]) ), .7)
    tx <- table(rc$rc)
    p <- p + scale_fill_manual(values=col.rc)
    if (!is.null(threshold)) {
        p <- p + geom_hline(data=data.frame(y=threshold), aes(yintercept=y), size=1.75, colour=alpha("orange", .85))
    }
    ##lc1 <- c("Não", "Obs", "Abs", "Aus")
    lc1 <- c("Não", "Obstrução", "Abstenção", "Ausente")
    rc1 <- factor(rc$rc[rc$rc!="Sim"], levels=lc1)
    nx <- 15
    ## do not label if less than nx votes
    lc1[table(rc1)<nx] <- ""
    tx <- as.vector(table(rc1))
    fix.y <- function(x=.01) 514*x  
    y <- cumsum(tx)
    y <- y-tx/2
    dfx <- data.frame(x=1, y=y+fix.y(), label=lc1, label.large=paste(lc1,": ",tx, sep=''))
    dfx$label.large <- ifelse(tx>0, dfx$label.large, "")
    ssim <- sum(rc$rc=="Sim")
    plarge <- p + opts(legend.position="none"
                       ##,axis.ticks = theme_blank()
                       )
    psmall <- p+theme_mini() + opts(legend.position="none")
    plarge <- plarge+opts(title=title, axis.text.x=theme_blank())
    psmall <- psmall + geom_text(data=dfx, aes(x=x, y=y, label=label.large))
    plarge <- plarge + geom_text(data=dfx, aes(x=x, y=y, label=label.large))
    if (ssim>0) {
        sy <- sum(rc$rc=="Sim")
        dfy <- data.frame(x=2, y=sy/2 + fix.y(),
                          label="Sim",
                          label.large=paste("Sim:",sy))
        dfy$label.large <- ifelse(sy>0, dfy$label.large, "")
        psmall <- psmall + geom_text(data=dfy, aes(x=x, y=y, label=label.large))
        plarge <- plarge + geom_text(data=dfy, aes(x=x, y=y, label=label.large))
    }
    list(large=plarge, small=psmall)
}

barplot.rc <- function(rc, gov=NA, title="", threshold=NULL) {
    ## FIX:
    ## 1st column: ausencias and obstrucao
    ## 2nd column: Nao
    ## 3rd column: Sim
    ## abstencoes: pile up on the winning vote (sim or nao)
    ##rc <- subset(rc, !rc%in%c("Ausente"))
    require(RColorBrewer)
    require(ggplot2)
    rc$rc <- factor(rc$rc, levels=c("Não", "Obstrução", "Abstenção", "Ausente", "Sim"))
    rc$rc2 <- factor(with(rc,car::recode(rc,"'Sim'='A Favor';else='Contra'")),levels=c("Contra","A Favor"))
    if (is.na(gov)) {
        colvec <- rep("transparent",2)
    } else {
        colvec <- c("red","darkblue")
        if (gov=="A Favor") {
            colvec <- rev(colvec)
        }
        colvec <- alpha(colvec,"1")
    }
    ## Stacked barchart
    wd <- .95
    theme_set(theme_grey(base_size = 10))
    p <- ggplot(rc, aes(x = rc2))+geom_bar(width = wd,aes(fill = rc))+geom_bar(data=rc,colour=colvec,width=wd,size=2,fill="transparent")+scale_y_continuous(name="",limits=c(0,513),expand=c(0,0))
    p <- p+theme_bw()+opts(axis.title.x = theme_blank(),
                           axis.title.y = theme_blank(),
                           panel.grid.minor = theme_blank(),
                           panel.grid.major=theme_blank(),
                           panel.background=theme_rect(fill = NA, colour = NA),
                           plot.background = theme_rect(colour = NA,fill=NA)
                           ,plot.title = theme_text(size = 10))
    col.rc <- alpha(rev(c(brewer.pal(3,"Blues")[3], brewer.pal(4,"Reds")[1:4]) ), .5)
    tx <- table(rc$rc)
    p <- p + scale_fill_manual(values=col.rc)
    if (!is.null(threshold)) {
        p <- p + geom_hline(data=data.frame(y=threshold), aes(yintercept=y), size=2, colour=alpha("orange", .85))
    }
    ##lc1 <- c("Não", "Obs", "Abs", "Aus")
    lc1 <- c("Não", "Obstrução", "Abstenção", "Ausente")
    rc1 <- factor(rc$rc[rc$rc!="Sim"], levels=lc1)
    nx <- 15
    ## do not label if less than nx votes
    lc1[table(rc1)<nx] <- ""
    tx <- as.vector(table(rc1))
    fix.y <- function(x=.01) 514*x  
    y <- cumsum(tx)
    y <- y-tx/2
    dfx <- data.frame(x=1, y=y+fix.y(), label=lc1)
    ssim <- sum(rc$rc=="Sim")
    p <- p + geom_text(data=dfx, aes(x=x, y=y, label=label))  
    if (ssim>0) {
    dfy <- data.frame(x=2, y=sum(rc$rc=="Sim")/2 + fix.y(), label="Sim")
    p <- p + geom_text(data=dfy, aes(x=x, y=y, label=label))
}
    plarge <- p + opts(legend.position="none"
                       ##,axis.text.y = theme_blank()
                       ##,axis.text.x = theme_blank()
                       ,axis.ticks = theme_blank()
                       ##,panel.border = theme_blank()
                       )
    ##   psmall <- p+opts(legend.position="none",
    ##                    axis.text.y = theme_blank(),
    ##                    axis.text.x = theme_blank()
    ##                    ,axis.ticks = theme_blank()
    ##                    ,panel.border = theme_blank()
    ##                    )
  psmall <- p+theme_mini() + opts(legend.position="none")
  plarge <- plarge+opts(title=title)
  list(large=plarge, small=psmall)
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


##FIX: these functions are here and in spatial.R choose 1.
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

## use labels locations located inside the spatial object
readShape <- function(shape.file="~/test.shp") {
    require(maptools)
    map <- readShapePoly(shape.file)
    labelpos <- data.frame(do.call(rbind, lapply(map@polygons, function(x) x@labpt)))
    names(labelpos) <- c("x","y")                        
    map@data <- data.frame(map@data, labelpos)
    map
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
getLeaders <- function(x) {    #x is a string with the name of the vote.file (.txt), or the voteid field from
  print(x)
  if((nchar(x)>8))   {
    ## there is no data for these rcvoteid's
    return(NULL)
  }
  st <- paste("select * from br_leaders where rcvoteid=",x,sep='')
  res <- dbGetQuery(connect,st)
  if (nrow(res)>0) return(NULL)
  vote.name<-as.numeric(x)
  the.url <- paste("http://www.camara.gov.br/internet/votacao/mostraVotacao.asp?ideVotacao=",vote.name,sep="")
  ## download file
  tfile <- tempfile()
  ## we try downloading the file first because
  ## readLines directly chokes when the page is missing an end of file code
  ## using wget since camara is gving 403 errors with default
  down <- try(download.file(the.url,tfile, method="wget"))
  raw.data <-try(readLines(tfile,500),silent=TRUE)
  if(!any(grepl("ORDINÁRIA",raw.data))) {
      ## fix encoding
      raw.data <-try(readLines(tfile,500,encoding="latin1"),silent=TRUE)
  }
  if(!any(grepl("ORDINÁRIA",raw.data))) {
      stop("encoding problems")
  }
  if(class(raw.data)=="try-error") {
      print(the.url)
      cat("Connection problems",vote.name,"Will try again soon\n")
      Sys.sleep(10)
      cat("\t Attempting to connect...\n")
      flush.console()
      down <- try(download.file(the.url,tfile))
    raw.data <-try(readLines(tfile,500),silent=TRUE)
      if(class(raw.data)=="try-error") {
          warning("\t No data for",vote.name,"\n")
          return(NULL)
      }
  }
  orientation.line <- grep("Orientação",raw.data)
  ##Check for encoding problems here  
  if(length(orientation.line)==0){
      cat("############################\n")
      cat("No data for",vote.name,"\n")
      cat("############################\n")
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
  print("waiting a few seconds to give time to server")
  Sys.sleep(2)
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
  if (nrow(data)>0) {
    ## now the capital letter splits coalitions
    res <- lapply(1:nrow(data),function(x) {
      dx <- data[x,]
      party <- splitBlock(dx$block)
      np <- length(party)
      data.frame(data[rep(x,np),],party)
    })
    res <- do.call(rbind,res)
  } else {
    res <- NULL
  }
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

if (1==2) {
Layout <- grid.layout( nrow = 2, ncol = 1
                      ,widths = unit(c(2,2), c("null","null") ),
                      heights = unit (c(1,1), c("null", "null") )
                      )
vplayout <- function (...) {
  grid.newpage()
  pushViewport(viewport(
                        layout= Layout
                        ))
}
subplot <- function(x, y) viewport(layout.pos.row=x, layout.pos.col=y)
vplayout()
print(p1, vp=subplot(1,1))
print(p2, vp=subplot(2,1))
}



##function to decode accents from html format
decode.html <- function(x) {
  html.mat <- matrix(c("À","&Agrave", "à","&agrav", "Á","&Aacute", "á","&aacute", "Â","&Acirc", "â","&acirc", "Ã","&Atilde", "ã","&atilde", "Ç","&Ccedil", "ç","&ccedil", "È","&Egrave", "è","&egrave", "É","&Eacute", "é","&eacute", "Ê","&Ecirc", "ê","&ecirc", "Ì","&Igrave", "ì","&igrave", "Í","&Iacute", "í","&iacute", "Ï","&Iuml", "ï","&iuml", "Ò","&Ograve", "ò","&ograve", "Ó","&Oacute", "ó","&oacute", "Õ","&Otilde", "õ","&otilde", "Ù","&Ugrave", "ù","&ugrave", "Ú","&Uacute", "ú","&uacute", "Ü","&Uuml", "ü","&uuml"),ncol=2,byrow=TRUE)
  for (i in 1:nrow(html.mat)) {
    x <- gsub(paste(html.mat[i,2],";",sep=''),html.mat[i,1],x)
  }
  x
}


mosaic.rc <- function(rc, ...) {
    pmedians <- dbGetQueryU(connect, "select * from br_partymedians where finaldate=(select max(finaldate) from br_partymedians) ")
    pmedians <- pmedians[order(pmedians$coord1D),]
    require(ggplot2)
    require(RColorBrewer)
    require(reshape)
    rc1 <- merge(pmedians, rc,  by.y="party", by.x="Partido", all.y=TRUE)
    rc1$Partido[is.na(rc1$coord1D)] <- "Outros partidos"
    rc1$Partido <- factor(rc1$Partido,levels=pmedians$Partido)
    ## order by size
    ##rc1$Partido <- reorder(rc1$Partido,ave(rc1$legis,rc1$Partido,FUN=length))
    rc1$ct <- 1
    lrc <- (c("Sim","Não","Obstrução","Abstenção","Ausente"))
    rc1$Voto <- factor(rc1$rc,levels=rev(lrc))
    ##m1 <- readShape.cent("~/reps/CongressoAberto/data/maps/BRASIL.shp","UF")
    rcc <- recast(rc1,Partido~Voto,measure.var="ct",margins="grand_col")
    rcc <- rcc[order(rcc$Partido),]
    rcc$xmax <- cumsum(rcc$`(all)`)
    rcc$xmin <- with(rcc,xmax-`(all)`)
    rcc$`(all)` <- NULL
    rcc <- data.frame(rcc)
    ## melt data
    dfm <- melt(rcc,id=c("Partido", "xmin","xmax"))
    ## calculate ymin and ymax
    dfm$variable <- factor(dfm$variable,levels=(lrc))
    dfm <- dfm[order(dfm$Partido,dfm$variable),]
    dfm1 <- ddply(dfm,.(Partido),transform,ymax=cumsum(value/sum(value)))
    dfm1 <- ddply(dfm1,.(Partido),transform,ymin=ymax-value/sum(value))
    ##Position of text
    dfm1$xtext <- with(dfm1, xmin + (xmax-xmin)/2)
    ##dfm1$xtext <- with(dfm1, xmin)
    dfm1$ytext <- with(dfm1, ymin + (ymax-ymin)/2)
    ## Partido sizes
    dfm1$Partidosize <- with(dfm1,xmax-xmin)
    ## text only for large Partido size
    dfm1$valuet <- with(dfm1,ifelse((Partidosize>(.02*513)) & (value>0),round(value),""))
    dfm1$Partidot <- with(dfm1,ifelse(Partidosize>(.02*513),as.character(Partido),""))
    dfm1$variable <- factor(dfm1$variable,levels=rev(lrc))
    p <- ggplot(dfm1, aes(ymin=ymin, ymax=ymax, xmin=xmin, xmax=xmax, fill=variable))
    ##Use grey border to distinguish between the Partidos
    p <- p + geom_rect(colour="gray70")
    ## Formatting adjustments
    p <- p + theme_bw() + labs(x=NULL, y=NULL, fill=NULL) +
        opts(##legend.position="none",
             panel.border=theme_blank(),
             panel.grid.major=theme_line(colour=NA),axis.text.x=theme_blank(),axis.text.y=theme_blank(),axis.ticks=theme_blank(),
             panel.grid.minor=theme_line(colour=NA))+
                 coord_equal(ratio=1/508)+
                     scale_fill_manual(values=rev(c(
                                       alpha(brewer.pal(3,"Blues")[3],.8),
                                       ##"grey20",
                                       alpha(rev(brewer.pal(4,"Reds")[1:4]), .8))
                                       ##gray(c(.4,.6,.7,.9)))
                                       ))
    ## party labels
    textdf <- unique(dfm1[,c("xtext","Partidot")])
    ##browser()
    ## small (no legends, party names inside plot)
    ## name just the large parties
    tx <- table(rc$party)>30
    lp <- names(tx)[tx]
    print(lp)
    textdfsmall <- textdf[textdf$Partidot%in%lp,]
    psmall <- p + annotate("text",x=textdfsmall$xtext, y=.1, label=paste(textdfsmall$Partidot),size=8, angle=45,just="left")
    psmall <- psmall + opts(plot.margin = unit(c(0, 0, 0, 0), "lines"), legend.position = "none") + theme_mini()
    ## large
    ## Add text labels. Ifelse used for Partido A labels.
    plarge <- p + geom_text(aes(x=xtext , y=ytext, label=valuet),size=4)
    ## Add Partido labels.
    plarge <- plarge + annotate("text",x=textdf$xtext, y=1.05, label=paste(textdf$Partidot),size=3.5, angle=75,just="right")
    list(small=psmall, large=plarge)
}



state.a2L <- function(object) {
  object <- as.character(tolower(object))
  require(car)
  car:::recode(object,"'ac' ='Acre';
                       'al' ='Alagoas';
                       'am' ='Amazonas';
                       'ap' ='Amapá';
                       'ba' ='Bahia';
                       'ce' ='Ceará';
                       'df' ='Distrito Federal';
                       'es' ='Espírito Santo';
                       'go' ='Goiás';
                       'ma' ='Maranhão';
                       'mg' ='Minas Gerais';
                       'ms' ='Mato Grosso do Sul';
                       'mt' ='Mato Grosso';
                       'pa' ='Pará';
                       'pb' ='Paraíba';
                       'pe' ='Pernambuco';
                       'pi' ='Piauí';
                       'pr' ='Paraná';
                       'rj' ='Rio de Janeiro';
                       'rn' ='Rio Grande do Norte';
                       'ro' ='Rondônia';
                       'rr' ='Roraima';
                       'rs' ='Rio Grande do Sul';
                       'sc' ='Santa Catarina';
                       'se' ='Sergipe';
                       'sp' ='São Paulo';
                       'to' ='Tocantins'")
}



## improved list of objects
## from http://stackoverflow.com/questions/1358003/tricks-to-manage-the-available-memory-in-an-r-session

.ls.objects <- function (pos = 1, pattern, order.by,
                        decreasing=FALSE, head=FALSE, n=5) {
    napply <- function(names, fn) sapply(names, function(x)
                                         fn(get(x, pos = pos)))
    names <- ls(pos = pos, pattern = pattern)
    obj.class <- napply(names, function(x) as.character(class(x))[1])
    obj.mode <- napply(names, mode)
    obj.type <- ifelse(is.na(obj.class), obj.mode, obj.class)
    obj.size <- napply(names, object.size)
    obj.dim <- t(napply(names, function(x)
                        as.numeric(dim(x))[1:2]))
    vec <- is.na(obj.dim)[, 1] & (obj.type != "function")
    obj.dim[vec, 1] <- napply(names, length)[vec]
    out <- data.frame(obj.type, obj.size, obj.dim)
    names(out) <- c("Type", "Size", "Rows", "Columns")
    if (!missing(order.by))
        out <- out[order(out[[order.by]], decreasing=decreasing), ]
    if (head)
        out <- head(out, n)
    out
}
# shorthand
lsos <- function(..., n=10) {
    .ls.objects(..., order.by="Size", decreasing=TRUE, head=TRUE, n=n)
}



## constants

states <- c("AC","AL","AP","AM","BA","CE","DF","ES","GO","MA","MT","MS","MG", "PA","PB","PR","PE","PI","RJ","RN","RS","RO","RR","SC","SP","SE","TO")


## plots pdf and png maps of party's electoral strengh in each state
map.elec <- function(the.data, filenow='',title='', large=TRUE, percent=FALSE) { 
  if (percent) {
    pct <- 100
    legend.title <- "Votos para Deputado Federal (%)"    
  } else {
    pct <- 1
    legend.title <- "Votos para Deputado Federal"    
  }
  the.data$UF <- the.data$state
  m2 <- merge.sp(m1,the.data,by="UF")
  par(bg="grey90")
  n1 <- 4
  seqx <- c(0,0.01,0.05,.1,.2,1)*pct
  tmp.col <- brewer.pal((length(seqx)-2),"Blues")
  col.vec <- c("white",tmp.col) 
  #idea here is to include white as first color
    if (large) {
        pdf(file=paste(pty,"map.pdf",sep=""), bg="transparent", width=6, height=6) 
        par(mai=c(0,0,0.6,0))
        plot.heat(m2,NULL,"vs",title=legend.title,breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,main=filenow)
        with(m2@data,text(x,y,UF,cex=0.8),col="grey30")
        mtext(title,3, cex=.9)
          dev.off()
        convert.png(file=paste(pty,"map.pdf",sep=""))#
      } else {
        pdf(file=paste(pty,"mapsmall.pdf",sep=""), bg="transparent", width=6, height=6) 
        par(mai=c(0,0,0,0))
        plot.heat(m2,NULL,"vs",breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,main=filenow,plot.legend=FALSE)
          dev.off()
        convert.png(file=paste(pty,"mapsmall.pdf",sep=""))
      }
}



getThresh <- function(rc, billtype, billdescription) {
  Nao <-  sum(rc=="Não")
  Sim <-  sum(rc=="Sim")
  Abs <-  sum(rc=="Abstenção")
  ## simple majority
  smaj <- round((Nao+Sim)/2)
  if (any(grepl("requerimento", billdescription, ignore.case=TRUE))) {
      ## camara rules says that requerimento only needs a simple majority
      ## with quorum
      thresh <- smaj
  }
  if (billtype=="PEC") {
      ## constitutional amendments need 3/5
      thresh <- 308
  } else if (billtype=="PLP") {
      ## lei complementar need 1/2
      thresh <- 257
  } else {
      ## else need simple majority
      thresh <- smaj
  }
  ## But note, you still need a quorum.
  ## So if e. g.
  ## Sim=100
  ## Nao=100
  ## Abst, Obs, = 0
  ## then
  ## needed to complete the quorum: 257-(Sim+Nao+Abs)
  ## thresh = Sim + max(0, 257-(Sim+Nao+Abs))
  ## this is the "effective threshold" for _approving_ legislation
  ## FIX: double check this
  quorum <- Sim+Nao+Abs
  if (quorum>256) thresh else thresh+256-quorum
}


govwins <- function(rcnow, rcgov, thresh) {
  if (nrow(rcgov)==0) return(NA)
  pro <- sum(rcnow$rc=="Sim")-thresh
  if (rcgov$rc=="Sim") {
    progov <- pro
  } else  { ## FIX: is this doing it right for abstentions and the like?
    progov <- -pro
  } 
  progov
}



govpos <- function(rcgov) {
  if (nrow(rcgov)==0) return(NA)
  res <- NA
  ifelse(rcgov$rc=="Sim", "A Favor", "Contra")
}

sumroll <- function(rcnow, margin, rcgov) {
  res <- NULL
  ## FIX: what happens when there is less than 513 deputies?
  quorum <- sum(rcnow$rc%in%c("Ausente", "Obstrução"))<257
  if (!quorum) {
      res <- c(res, "Não houve quorum.")
  }
  tx <- table(rcnow$rc)
  ntx <- c("Sim", "Não", "Obstrução", "Abstenção",  "Ausente")
  tx <- tx[ntx]
  tx[is.na(tx)] <- 0
  ntx <- c("Sim", "Não", "Obstrução", "Abstenção",  "Ausentes")
  names(tx) <- ntx
  res <- c(res,paste(names(tx)%+%": ",tx,collapse="; "))
  if (tx["Sim"]==0 | tx["Não"]==0 ) {
      res <- c(res, "A votação foi unânime. ")
  } else {
      if (!is.na(margin)) {
          res <- c(res, paste("Posição do governo:",rcgov$rc, ". ", sep=''))
          if (quorum) {
              if (margin>0) {
                  res <- c(res, "O governo venceu a votação.")
              } else {
                  res <- c(res, "O governo foi derrotado.")
              }
          }
      } else {
          res <- c(res, "Não houve indicação do governo.")
      }
  }
  paste(paste("<p>", res, collapse="</p>"), "</p>", collapse=" ")
}
  
postroll <- function(rcid=2797, saveplot=TRUE, post=TRUE) {
  print(rcid)
  rcs <- dbGetQueryU(connect, "select * from br_votacoes where rcvoteid="%+%rcid)
  rcnow <- dbGetQueryU(connect, "select * from br_votos where rcvoteid="%+%rcid)
  ## fix pfl
  rcnow$party <- recode.party(rcnow$party)
  ## FIX: what to do with abstentions, etc
  rcgov <- dbGetQueryU(connect, "select * from br_leaders where block='GOV' and rc!='Liberado' and rcvoteid="%+%rcid)
  fulltext <- paste(rcs,collapse="\n")
  ## create post data
  title <- rcs$billproc
  post_category <- data.frame(slug="votacoes",name="Votações")
  popname <- dbGetQuery(connect, "select a.*, b.* from  br_proposition_names as a, br_votacoes as b where a.billyear=b.billyear and a.billno=b.billno and a.billtype=b.billtype and b.rcvoteid="%+%rcid)
  if (nrow(popname)>0) {
      title <- paste(popname$billname[1], " - ", title, sep='')
      post_category <- rbind(post_category, data.frame(slug="Featured",name="Featured"))
  }
  name <- with(rcs,encode(paste(bill,rcvoteid,sep="-")))
  content <- paste('<script language="php">$rcvoteid = ',rcs$rcvoteid,';include("php/rc.php");</script>')
  date <- wptime(rcs$rcdate)
  tagsname <- with(rcs,sapply(c(billtype,billyear),
                              encode))
  tagslug <- gsub("[-,.]+","_",tagsname)
  tags <- data.frame(slug=tagslug,name=tagsname)
  billtype <- toupper(rcs$billtype)
  threshold <- getThresh(billtype=rcs$billtype,
                         billdescription=rcs$billdescription,
                         rc=rcnow$rc)
  margin <- govwins(rcnow, rcgov, threshold)
  post_excerpt <- sumroll(rcnow, margin, rcgov)
  img <- paste("images/rollcalls/bar",rcid, sep='')
  if (!is.na(margin))  {
    if (margin>0) {
      post_category <- rbind(post_category, data.frame(slug="governo_venceu",name="Governo venceu"))
    } else {
      post_category <- rbind(post_category, data.frame(slug="governo_perdeu",name="Governo foi derrotado"))
    }
    if (margin<10) {
      img <- paste("images/rollcalls/mosaic",rcid, sep='')
      post_category <- rbind(post_category, data.frame(slug="Featured",name="Featured"))
    }
  } else {
  }
  ## write plots to disk
  print.png.old <- function(plots, fn, crop=TRUE, small=5, large=6) {
    fns <- rf(fn%+%"small.pdf")
    fnl <- rf(fn%+%"large.pdf")
    pdf(file=fns, bg="white", width=small, height=small)
    print(plots[["small"]])
    dev.off()
    convert.png(fns, crop=crop)
    pdf(file=fnl, bg="white", width=large, height=large)
    print(plots[["large"]])
    dev.off()
    convert.png(fnl, crop=crop)
  }
  print.png <- function(plots, fn, crop=TRUE, small=5, large=6) {
      ## crop does nothing
      fns <- webdir(fn%+%"small.png")
      fnl <- webdir(fn%+%"large.png")
      png(file=fns, bg="white", width=small*100, height=small*100, res=200)
      print(plots[["small"]])
      dev.off()
      png(file=fnl, bg="white", width=large*100, height=large*100, res=100)
      print(plots[["large"]])
      dev.off()
  }
  if (saveplot) {
      barplots <- barplot.rc.simple(rcnow, govpos(rcgov), threshold=threshold)  
      mosaicplots <- mosaic.rc(rcnow, pmedians)
      print.png(barplots, paste("images/rollcalls/bar",rcid, sep=''), crop=FALSE, small=4)
      print.png(mosaicplots, paste("images/rollcalls/mosaic",rcid, sep=''), small= 7, large = 7)
      ## maps
      ## small
      ## large
      fn <- paste("images/rollcalls/map",rcid, sep='')
      fns <- webdir(fn%+%"small.png")
      fnl <- webdir(fn%+%"large.png")
      png(file=fns, width=400, height=400, bg="white")
      map.rc(rcnow, large=FALSE, percent=TRUE)
      dev.off()
      png(file=fnl,  width=1000, height=1000, bg="white", res=160)
      map.rc(rcnow, large=TRUE, percent=TRUE)
      dev.off()
      ##convert.png(fns, crop=TRUE)
      ##convert.png(fnl, crop=TRUE)
  }
  if (post) {
      postid <- wpAddByName(conwp,post_title=title,post_type="post",post_content=content,post_date=date$brasilia,post_date_gmt=date$gmt,fulltext=fulltext,post_excerpt=post_excerpt,post_category=unique(post_category),
                            custom_fields=data.frame(meta_key="Image",meta_value=img%+%"small.png"),
                            post_name=name,tags=tags)
      ##FIX: create table in mysql
      dbWriteTableU(connect,"br_rcvoteidpostid",data.frame(postid,rcvoteid=rcs$rcvoteid),append=TRUE)
    res <- c(rcid,postid)
      print(res)
      res
  }
}

if (1==2) {
  ## template - there is also a wpAddbyTitle
  postid <- wpAddByTitle( ## usually better to add by name -- we (try) to use  unique names
                        ## by "add by" we mean that the function searches for a post with matching names or title
                        conwp, ## connection
                        post_title="post title",
                        post_type="page", ## can be page
                        post_content="post content",
                        ## dates have a special format. use the function wptime
                        ##post_date,    ## only needed if back dating (e.g. for roll calls, we'd like the date to be the roll call date) - there is a special format.
                        ##post_date_gmt=date$gmt,  ## not sure why there is two date fields, but whatever
                        fulltext="full text", ## put in the full text field terms that you'd like the search function to use to  find this post
                        post_excerpt=" excerpt", ## summary of the post. it is what is shown in the front page, or in the search results.
                        post_category=data.frame(slug="category_slug",name="category name"), ## categories: can have multiple lines.
                        custom_fields=data.frame(meta_key="Image",meta_value="small.png"), ## this is what is shown in the search results or in the front page you do not need to add the php thumbnail thing here, just the link                      
                        post_name=  name <- encode("post name"), ## post name. needs to be "nice" (e.g. no accents, spaces, etc.). Use the encode function for this purpose 
                        tags=data.frame(slug="tagslug",name="tags name") ## tag the post  format similar to categories and custom fields
                        )  
}



    
