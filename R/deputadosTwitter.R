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
rf()



library(gdata)

## download current list
fname <- rf("data/camara/DeputadosTwitter.xls")
download.file("http://www2.camara.gov.br/internet/camaraFaz/DeputadosTwitterJan.xls/at_download/file", fname)

dnow0 <- read.xls("~/Downloads/DeputadosTwitter.xls", encoding="latin1")[,1:4]
dnow0 <- dnow0[!grepl("LICENCIADO", dnow0$Deputado),]

dnow <- read.xls(fname, encoding="latin1")[,1:4]
dnow <- dnow[!grepl("LICENCIADO", dnow$Deputado),]



connect.db()
deps <- dbGetQuery(connect, "select * from br_bioidname where legis=53")
deps$name <- clean(deps$name)
dnow$name <- clean(dnow$Deputado)

tmp <- merge(deps, dnow, by.x=c("name", "state"), by.y=c("name", "Estado"), all.y=FALSE)
tmp$twitter_username <- basename(trim(tmp$Endereço.no.Twitter))
tmp <- tmp[, c("bioid", "twitter_username")]




if (nrow(tmp)!=nrow(dnow)) {
    stop("algum deputado não encontrado")
} else {
    dbRemoveTable(connect, "br_twitterAddress")
    dbWriteTableU(connect, "br_twitterAddress", tmp)
}
