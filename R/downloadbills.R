source("~/reps/CongressoAberto/R/caFunctions.R")

connect.db()

bills <- dbReadTableU(connect,"br_votacoes")
##FIX: types (this should be fixed when loading the rcs)
##FIX: Parecer da Câmara (p.c) vs. parecer de comissão (PAR)
##FIX: PLC (Lei complementar) or PLP?
##FIX: unique bills by billtype billno billyear
bills$billtype <- car::recode(bills$billtype,"'PLN'='PL';c('MP','MEDIDA')='MPV';c('MENS','MSG')='MSC';c('PARECER')='PAR';'PDL'='PDC';'PLC'='PLP'")
bills$billno <- gsub("\\.","",bills$billno)
bills$billno <- gsub("[A-Z]*|-","",bills$billno)
bills$billnof <- as.numeric(bills$billno)
billsf <- unique(bills[,c("billtype","billno","billyear")])
billsf <- subset(billsf,!is.na(billno))

bill.up <- do.call(rbind,lapply(283:nrow(billsf)
                                ,
                                function(i) {
                                  print(i)
                                  with(billsf,getbill(billtype[i],billno[i],billyear[i],overwrite=FALSE))
                    }))


