## needs update.all
## downloads the current deputies files

##update.all  <- TRUE;source("~/reps/CongressoAberto/R/deputados.R",echo=TRUE)
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
usource(rf("R/mergeApprox.R"))
run.from <- rf("data/camara/")
setwd(run.from)

library(gdata)
the.url <- "http://www.camara.gov.br/internet/deputado/deputado.xls"

fp <- file.info("deputado.xls")
tmp <- system(paste("wget -Nc -P . ",the.url))
fn <- file.info("deputado.xls")
new <- fn$mtime>fp$mtime
if (is.na(new)|update.all) new <- TRUE
get.deps <- function() {
  deps <- read.xls("deputado.xls",encoding="latin1")
  rn <- matrix(c("Nome.Parlamentar","namelegis"
                 ,"Partido","party"
                 ,"UF","state"
                 ,"Titular.Suplente.Efetivado", "type"
                 ,"Endereço","address"
                 ,"Anexo","building"
                 ,"Endereço..continuação.","address2"
                 ,"Gabinete", "office"
                 ,"Endereço..complemento.","address3"
                 ,"Telefone","phone"
                 ,"Fax","fax"
                 ,"Mês.Aniversário","birthmonth"        
                 ,"Dia.Aniversário","birthdate"
                 ,"Correio.Eletrônico","mailaddress"
                 ,"Nome.sem.Acento","namelegisclean"        
                 ,"Tratamento","title"
                 ,"Profissões","profession"
                 ,"Nome.Civil","name"
                 ),ncol=2,byrow=TRUE)  
  deps.rn <- rename.vars(deps,from=rn[,1],to=rn[,2])
  ## remove \n (eol) characters from strings/factors
  deps.rn <- data.frame(lapply(deps.rn,function(x) {
    if (is.character(x)) x <- gsub("\n"," ",x)
    if (is.factor(x)) levels(x) <- gsub("\n"," ",levels(x))
    x
  }))
  deps.rn$loaddate <- Sys.Date()
  deps.rn
}
if (new) {
  ## there might new deputies, process bio
  ##source(rf("R/bioprocess.R"), echo=TRUE)
  deps <- get.deps()
  connect.db()
  dbWriteTableU(connect,"br_deputados_current",deps,append=TRUE)
}

##FIX
download.all <- TRUE
update.all <- FALSE
session.now <- 53
usource(rf("R/bioprocess.R"), echo=TRUE)

findDep <- function(tomatch,session.now=53) {
  ##try to find the bioid for new deps
  if (nrow(tomatch)==0) {
    return(NULL)
  }
  bio <- dbGetQueryU(connect,paste("select * from br_bio where legisserved like '%",get.legis.text(get.legis.year(session.now)),"%'",sep=''))
  idname <- dbGetQueryU(connect,paste("select * from br_bioidname where legis='",session.now,"'",sep=''))
  res <- merge.approx(states,tomatch,
                      bio,"state","namelegis")
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
  
  idname$namelegis <- idname$name
  ##browser()
  
  res <- merge.approx(states,idname,
                      tomatch,"state","namelegis")
