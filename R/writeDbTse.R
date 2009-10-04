## FIX: recode distrital as estadual
## "VOTO_SECAO_" + (Sigla da UF) + "1998T" + (Número do Turno) + ".ZIP" 
##  O conteúdo do arquivo é formado pela composição dos seguintes campos:  
##   - Código do Municipio (99999) - de acordo com arquivo UE_1998.TXT 
##   - Número da Zona Eleitoral (9999) 
##   - Número da Seção Eleitoral (9999) 
##   - Código do Cargo (9) - de acordo com arquivo CARGO_1998.TXT 
##   - Tipo do Votável (9) - de acordo com arquivo VOTAVEL_TIPO_1998.TXT 
##   - Quantidade de Votos 


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
run.from <- rf("data/camara/rollcalls")

library(reshape)
library(RMySQL)
source(rf("R/readTSEfunctions.R"))


connect.db()
##states <- "RR"


##FIX change to truncate table
##dbRemoveTable(connect,"br_vote_state")
##dbRemoveTable(connect,"br_vote_info_mun")
##dbRemoveTable(connect,"br_vote_candidates")
##FIX CREATE ABOVE TABLES IN SQL
dir.now <- rf("data/electoral/tse/sections/2006sections/")
year.now <- 2006

office.codes <- read.csv2(file=
                          paste(dir.now,"commontables/cargo_",year.now,".txt",sep='')
                          ,encoding="latin1",header=FALSE)

names(office.codes) <- c("code","office","electorate","votable","mainofficecode")

parties <- get.party(year=year.now,dir=dir.now)
dbRemoveTable(connect, "br_vote_parties")

dbWriteTableU(connect,"br_vote_parties",parties,append=TRUE)

cand <- get.candidates(year=year.now,dir=dir.now)
cand$office <- recode.office(cand$office)
sit.codes <- read.csv2(file=paste(dir.now,"commontables/candidato_sit_tot_",year.now,".txt",sep=''),encoding="latin1",header=FALSE)
cand$sit <- factor(cand$sit,levels=sit.codes$V1,labels=sit.codes$V2)

##FIX: create table/indexes in sql
dbWriteTableU(connect, name="br_vote_candidates", value=cand,append=TRUE )

lapply(toupper(states),read.all,year=year.now,round=1,dir=dir.now)
##lapply(toupper(states),read.all,year=1998,round=1,dir="/Users/eduardo/doNotBackup/projects/BrazilianPolitics/trunk/electoral/section/1998sections/")
## FIX: missing data for CE and PE
##lapply(toupper(states)[!states%in%c("ce","pe")],read.all,year=2002,round=1,dir="/Users/eduardo/doNotBackup/projects/BrazilianPolitics/trunk/electoral/section/2002sections/")
##lapply(toupper(states)[!states%in%c("ac","al","am","ap","ba","ce","pe")],read.all,year=2002,round=1,dir="/Users/eduardo/doNotBackup/projects/BrazilianPolitics/trunk/electoral/section/2002sections/")
##res <- dbSendQuery(connect, statement = "CREATE table br_vote_state as SELECT year, elec_round, office, state, type, candidate_code, sum(votes) as votes FROM br_vote_mun group by year, elec_round, office, state, type, candidate_code")
##res <- dbSendQuery(connect, statement = "ALTER TABLE br_vote_state ADD PRIMARY KEY(year,elec_round,office(19),state(2),type,candidate_code)")
## municipality table


## FIX CREATE A TMP TABLE THAN ADD TO MAIN TABLE CREATED IN SQL
res <- dbSendQuery(connect, statement = "CREATE table br_vote_mun_ag as SELECT year, state, municipality, office, sum(votes) as votes FROM br_vote_mun where type in (1,4) group by year, state, municipality, office")

##res <- dbSendQuery(connect, statement = "CREATE table br_vote_info_mun as SELECT year, state, municipality, COUNT(DISTINCT zone,section) as nsections FROM br_vote_section where (office=1 AND elec_round=1) group by year, state, municipality")
##res <- dbSendQuery(connect, statement = "ALTER TABLE tbl_info_mun ADD PRIMARY KEY(year,state,municipality)")  



