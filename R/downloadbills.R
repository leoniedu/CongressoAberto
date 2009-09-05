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

bills <- dbReadTableU(connect,"br_votacoes")

billsf <- unique(bills[,c("billtype","billno","billyear")])
billsf$billno[billsf$billno=="NA"] <- NA
billsf <- subset(billsf,!is.na(billno))
if (update.all) {  
  ##FIX: unique bills by billtype billno billyear
  billsf$billid <- NA
  ##billsf$billurl <- NA
} else {
  billsf.old <- dbReadTableU(connect, "br_billid")
  billsf <- merge(billsf,billsf.old,all=TRUE)
}

##update.all conditional
toup <- which(is.na(billsf$billid))
if (update.all) {
  toup <- 1:nrow(billsf)
}

for ( i in toup) {
  print(i)
  ##billsf[i, c("billurl", "billid")] <- with(billsf,getbill(billtype[i],billno[i],billyear[i],overwrite=download.now))
  ## FIX: when and what to download?
  billsf[i, c("billid")] <- with(billsf,getbill(billtype[i],billno[i],billyear[i],overwrite=download.now & update.all))
}

toupdate <- billsf[toup, ]

dbWriteTableU(connect, "br_billid", toupdate, append=TRUE)

