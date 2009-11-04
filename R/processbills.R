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
setwd(run.from)


connect.db()

##tramit <- res <- NULL

fx <-  function(j)  {
  print(j)
  file <- rf(paste("data/www.camara.gov.br/sileg/Prop_Detalhe.asp?id=", j, sep=''))
  i <- which(billsf$billid==j)
  res <- try(read.bill(file))
  tramit <- try(read.tramit(file))
  if (!"try-error" %in% class(res)) {
      if (length(grep("Apensado", tramit)>0)) stop()
      billst <- data.frame(billsf[i,],res)
      dbGetQuery(connect, "delete from br_bills where billid="%+%billsf$billid[i])
      ##FIX THIS. Should only add rows that are not in yet.
      dbWriteTableU(connect, "br_bills", billst, append=TRUE)
  } else {
      res <- NULL
  }
  if (!"try-error" %in% class(tramit)) {
      tramit <- data.frame(billid=billsf[i,"billid"],tramit)
      dbGetQuery(connect, "delete from br_tramit where billid="%+%billsf$billid[i])      
      dbWriteTableU(connect, "br_tramit", tramit, append=TRUE)      
  } else {
      tramit <- NULL
  }
  closeAllConnections()
  gc()
  list(res, tramit)
}

billsf <- dbReadTableU(connect, "br_billid")
toup <- sort(na.omit(billsf$billid))
tmp <- lapply(toup, fx)


## for ( i in toup) {
##   print(i)
##   file <- rf(paste("data/www.camara.gov.br/sileg/Prop_Detalhe.asp?id=", billsf$billid[i], sep=''))
##   resnow <- try(readbill(file))
##   if (!"try-error" %in% class(resnow)) {
##     if (length(grep("Apensado", resnow$tramit)>0)) stop()
##     res <- rbind(res, resnow[["info"]])
##     tramit <- rbind(tramit,resnow[["tramit"]])
##     billst <- data.frame(billsf[toup,],res)
##   }
## }
## billst <- data.frame(billsf[toup,],res)







print(Sys.time())



