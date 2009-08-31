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
rf()

## FIX: create a id variable with bioid+party to identify unique legislators/party
connect.db()

init.date <- Sys.Date()-365
final.date <- Sys.Date()
date.range <- paste(format(c(init.date+1,final.date-1),"%d-%m-%Y"))
sql <- paste("select  a.*, cast(b.rcdate as date) as rcdate  from br_votos as a, br_votacoes as b, br_deputados_current as c where a.bioid=c.bioid and a.rcfile=b.rcfile and (rcdate>cast('",init.date,"' as date) ) and (rcdate<cast('",final.date,"' as date) ) ",sep='')
rc <- dbGetQueryU(connect,sql)

rc$rcr <- car::recode(rc$rc,"'Sim'=1;c('Não', 'Obstrução')=0; else=NA")
rc$bioparty <- with(rc,paste(bioid,party,sep=";"))
recode.party.now <- function(x) recode.party(x,n=13, label.other="Outros partidos")
rc$Partido <- recode.party.now(rc$party)
rcc <- recast(rc,bioid+Partido+bioparty~rcvoteid,measure.var="rcr")

library(wnominate)
rcr <- rollcall(data.frame(rcc)[,-c(1:4)],legis.names=rcc[,4],legis.data=rcc[,1:4])
wn1 <- wnominate(rcr,polarity=c(which(rcc$bioparty=="98523;DEM"),which(rcc$bioparty=="96839;PP")))
pmedians <- recast(wn1$legislators,Partido~variable,measure.var="coord1D",fun.aggregate=median,na.rm=TRUE)
pmedians <- pmedians[order(pmedians$coord1D),]
pmedians <- data.frame(pmedians, initdate=init.date, finaldate=final.date)

dbWriteTableU(connect, "br_partymedians", pmedians, append=TRUE)


