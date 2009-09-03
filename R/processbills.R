update.all <-  TRUE
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
run.from <- rf("data/camara/")
setwd(run.from)


connect.db()


billsf <- dbReadTableU(connect, "br_billid")


##FIX: update.all conditional
if (update.all) {
  toup <- which(!is.na(billsf$billid))  
} else {
  
}

tramit <- res <- NULL

fx <-  function(i)  {
  print(i)
  file <- rf(paste("data/www.camara.gov.br/sileg/Prop_Detalhe.asp?id=", billsf$billid[i], sep=''))
  resnow <- try(readbill(file))
  if (!"try-error" %in% class(resnow)) {
    if (length(grep("Apensado", resnow$tramit)>0)) stop()
    res <- resnow[["info"]]
    tramit <- resnow[["tramit"]]
    billst <- data.frame(billsf[toup,],res)
    dbWriteTableU(connect, "br_bills", billst, append=TRUE)
    gc()
    closeAllConnections()
  } else {
    res <- NULL
    tramit <- NULL
  }
  list(res, tramit)  
}

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









