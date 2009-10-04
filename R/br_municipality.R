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
    as.character(paste(run.from,"/",x,sep=''))
  }
}
rf()

connect.db()

dir.now <- "/Users/eduardo/doNotBackup/projects/BrazilianPolitics/trunk/electoral/section/2006sections/"
year.now <- 2006


## Municipality tables
## UE is unidade eleitoral
ue.codes <- read.csv2(file=paste(dir.now,"commontables/ue_",year.now,".txt",sep=''),encoding="latin1",header=FALSE)[,c(1,3,5)]
names(ue.codes) <- c("municipalitytse","municipality","state")
ue.codes$year <- year.now
##

## ibge  2007
## FIX: I converted the accents in the dbf file separately
i07 <- read.dbf(rf("data/maps/malha2007/55mu2500gsd.dbf"),as.is=TRUE)
names(i07) <- tolower(names(i07))
names(i07)[c(3,4)] <- c("state","municipality")

## fixes:
## http://www.tre-rn.gov.br/nova/inicial/zonas_eleitorais/municipios_com_nomes_alterados/
## Boa Saúde – Antes Januário Cicco, foi denominado Boa Saúde através da Emenda nº 1 à Lei Orgânica Municipal em 02 de fevereiro de 1991
i07$municipality[i07$geocodig_m=="2405306"] <- "BOA SAÚDE" 
## Campo Grande – Em 30 de março de 1870, a Lei  nº 613 restaurou o município com a denominação de Triunfo. Em 28 de agosto de 1903, a Lei nº 192 mudou o nome do município para Augusto Severo. No dia 6 de dezembro de 1991, através da Lei nº 155, o município de Augusto Severo voltou ao seu antigo nome de Campo Grande.
i07$municipality[i07$geocodig_m=="2401305"] <- "Campo Grande" 
## http://pt.wikipedia.org/wiki/Barro_Preto
## Com a lei estadual nº 2449, de 10 de abril de 1967, Barro Preto passou a chamar-se Governador Lomanto Júnior No entanto, pelo parecer da Superintendência de Estudos Econômicos e Sociais do estado da Bahia, órgão responsável pela divisão territorial do estado, o município de Governador Lomanto Júnior voltou a denominar-se Barro Preto.
i07$municipality[i07$geocodig_m=="2903300"] <- "GOVERNADOR LOMANTO JÚNIOR" 
##http://pt.wikipedia.org/wiki/Serra_Caiada
i07$municipality[i07$geocodig_m=="2410306"] <- "SERRA CAIADA" 

##REMAINING ERRORS
## http://www.agenciabrasil.gov.br/noticias/2008/10/02/materia.2008-10-02.7496990827/view
## Nazaria is a new municipality, emancipated from Teresina, PI

res <- merge.approx(states,ue.codes,i07,"state","municipality")
res <- res[order(res$threshold),]

subset(ue.codes[(attr(res,"data1.miss")),],state%in%states)
subset(i07[(attr(res,"data2.miss")),])

dnow <- (res[,c("geocodig_m","municipalitytse")])
dnow <- merge(dnow,ue.codes,all=TRUE,by="municipalitytse")
dnow <- merge(dnow,i07,all=TRUE,by="geocodig_m",suffixes=c("_tse06","_ibge07"))


## write to database
connect.db()
dbWriteTableU(connect,"br_municipios",dnow)


