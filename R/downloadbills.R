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

billsin <- dbGetQueryU(connect, "select billid, status from br_bills")
if (!update.all) {
    ## take out ended propositions
    billsin <- billsin[!grepl("Arquivada|Transformado em nova proposição|Transformado em Norma Jurídica|Vetado totalmente", billsin$status),]
    ## only props that could be updated in the list
    billsf <- billsf[billsf$billid%in%billsin$billid,]
}


toup <- which(is.na(billsf$billid))
toup <- 1:nrow(billsf)


for ( i in toup) {
    print(i)
    ##billsf[i, c("billurl", "billid")] <- with(billsf,getbill(billtype[i],billno[i],billyear[i],overwrite=download.now))
    ## FIX: when and what to download?
    billsf[i, c("billid")] <- with(billsf,getbill(billtype[i],billno[i],billyear[i],overwrite=download.now))
}

toupdate <- billsf[toup, ]

dbWriteTableU(connect, "br_billid", toupdate, append=TRUE)

