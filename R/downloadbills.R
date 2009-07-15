source("~/reps/CongressoAberto/R/caFunctions.R")

connect.db()


if (update.all) {
  bills <- dbReadTableU(connect,"br_votacoes")
  ##FIX: unique bills by billtype billno billyear
  billsf <- unique(bills[,c("billtype","billno","billyear")])
  billsf <- subset(billsf,!is.na(billno))
  billsf$billid <- NA
  billsf$billurl <- NA
} else {
  billsf <- dbReadTableU(connect, "br_billid")
}

##update.all conditional
toup <- which(is.na(billsf$billid))
if (update.all) {
  toup <- 1:nrow(billsf)
}

for ( i in toup) {
  print(i)
  billsf[i, c("billurl", "billid")] <- with(billsf,getbill(billtype[i],billno[i],billyear[i],overwrite=download.now))
}

toupdate <- billsf[toup, ]

dbWriteTableU(connect, "br_billid", toupdate, append=TRUE)






