## needs update.all
## downloads the current deputies files

session.now <- 53

##update.all  <- TRUE;source("~/reps/CongressoAberto/R/deputados.R",echo=TRUE)
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
run.from <- rf("data/camara/")
usource(rf("R/mergeApprox.R"))
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
                 ,"Profissões","occupation"
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
find.deps <- function(tomatch,session.now=53) {
  ##try to find the bioid for new deps
  if (nrow(tomatch)==0) {
    return(NULL)
  }
  idname <- dbGetQueryU(connect,paste("select * from br_bioidname where legis='",session.now,"'",sep=''))
  idname$namelegis <- idname$name
  res <- merge.approx(states,tomatch,
                      idname,"state","namelegis")
  ##might have multiple matches. We discard if the 
  ##tripple (id,bioid, session) is still unique
  res <- unique(res[,c(names(tomatch),"bioid")])
  ##fix: check explicitly for multiple ids.
  if(min(tomatch$id%in%res$id)==0) {
    print(tomatch[!tomatch$id%in%res$id,])
    stop("Some legislators not yet in db")
  } else if (sum(duplicated(res$bioid))) {
    print(res[with(res,bioid%in%bioid[which(duplicated(bioid))]),])
    stop("Some ids are duplicated ")
  }
  ##write new matches to db
  res
}

if (new) {
  ## there might new deputies, process bio
  deps <- get.deps()
  download.all <- TRUE
  update.all <- TRUE
  session.now <- 53
  usource(rf("R/bioprocess.R"), echo=TRUE)
  deps.bioid <- find.deps(deps,session.now)
  ## delete rows in deputados current table
  dbGetQuery(connect,"truncate br_deputados_current")
  ##dbGetQuery(connect,"truncate br_deputados")
  ## insert new values
  dbWriteTableU(connect,"br_deputados_current",deps.bioid,append=TRUE)
  ## insert into all deputados deps, appending
  ## Fix: use insert from the current deputies table
  ##TODO: update wordpress
  source(rf("R/twitter.R"))           
  load(rf("R/up.RData"))
  tw <- paste("List of deputies updated!")
  ns <- tweet(tw, userpwd=usrpwd, wait=0)
}
