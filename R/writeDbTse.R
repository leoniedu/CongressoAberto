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

year.now <- 2006
dir.now <- rf(paste("data/electoral/tse/sections/", year.now,"sections/", sep=''))


office.codes <- read.csv2(file=
                          paste(dir.now,"commontables/cargo_",year.now,".txt",sep='')
                          ,encoding="latin1",header=FALSE)
names(office.codes) <- c("code","office","electorate","votable","mainofficecode")

parties <- get.party(2002)

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


## FIX CREATE A TMP TABLE THEN ADD TO MAIN TABLE CREATED IN SQL
res <- dbSendQuery(connect, statement = "CREATE table br_vote_mun_ag as SELECT year, state, municipality, office, sum(votes) as votes FROM br_vote_mun where type in (1,4) group by year, state, municipality, office")

##res <- dbSendQuery(connect, statement = "CREATE table br_vote_info_mun as SELECT year, state, municipality, COUNT(DISTINCT zone,section) as nsections FROM br_vote_section where (office=1 AND elec_round=1) group by year, state, municipality")
##res <- dbSendQuery(connect, statement = "ALTER TABLE tbl_info_mun ADD PRIMARY KEY(year,state,municipality)")  




















### EARLIER YEARS

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
connect.db()




##1994
dnow <- lapply(tolower(states), function(state) {
    load(rf(paste("data/electoral/tse/votomun1994/votomun", state,".RData", sep='')))
    tmp
}
               )
dnow <- do.call(rbind, dnow)
names(dnow) <- tolower(names(dnow))
dnow$year <- 1994
names(dnow) <- car::recode(names(dnow), "'cod_munic'='municipality'; 'cod_cargo'='office'; 'qtd_votos'='votes'; 'num_votavel'='candidate_code'; 'sgl_ue'='state'; 'turno'='elec_round'")

##office codes
load(rf("data/electoral/tse/votomun1994/tabcargo.RData"))
tmp <- rbind(tmp, data.frame(CODCAR=12, NOMCAR="Deputado Distrital", TIPELE="p", VOTAVEL=1, ATIVO=1, AREA="ESTADUAL"))
dnow$office[(dnow$office==8)&(dnow$state=="DF")] <- 12
dnow$office <- factor(dnow$office, levels=tmp$CODCAR, labels=toupper(tmp$NOMCAR))

## load to db
connect.db()
dbWriteTableSeq(connect, "br_vote_mun", dnow, append=TRUE)


##1998
dnow <- lapply(tolower(states), function(state) {
    load(rf(paste("/data/electoral/tse/votomun", 1998,"/voto_mun_", state,".RData", sep='')))
    tmp
} )

dnow <- do.call(rbind, dnow)
names(dnow) <- tolower(names(dnow))
names(dnow) <- car::recode(names(dnow), "'cd_munic'='municipality'; 'cd_cargo'='office'; 'qt_votos'='votes'; 'nr_votavel'='candidate_code'; 'sg_ue'='state'")
dnow$year <- 1998
dnow$elec_round <- 1

##office codes
load(rf("data/electoral/tse/votomun1998/g_cargo.RData"))
tmp$DS_CARGO <- as.character(tmp$DS_CARGO)
tmp$DS_CARGO[tmp$CD_CARGO%in%c(9,10)] <- "Suplente Senador"
tmp$DS_CARGO <- gsub("-"," ", tmp$DS_CARGO)
dnow$office[dnow$office==10] <- 9
tmp <- tmp[tmp$CD_CARGO!=10,]
dnow$office <- factor(dnow$office, levels=tmp$CD_CARGO, labels=toupper(tmp$DS_CARGO))


## load to db
connect.db()
dbWriteTableSeq(connect, name="br_vote_mun", value=dnow, append=TRUE, wait=60, n=240)











##parties
parties <- get.party(1998)
dbWriteTableU(connect,"br_vote_parties",parties,append=TRUE)

cand <- get.candidates(1998)

##office codes from 2006
office.codes <- read.csv2(file=
                          paste(dir.now,"commontables/cargo_",2006,".txt",sep='')
                          ,encoding="latin1",header=FALSE)
names(office.codes) <- c("code","office","electorate","votable","mainofficecode")
cand$office <- recode.office(cand$office)
## sit codes from 2006
sit.codes <- read.csv2(file=paste(dir.now,"commontables/candidato_sit_tot_", 2006, ".txt",sep=''),encoding="latin1",header=FALSE)
cand$sit <- factor(cand$sit,levels=sit.codes$V1,labels=sit.codes$V2)

##FIX: create table/indexes in sql
dbWriteTableU(connect, name="br_vote_candidates", value=cand,append=TRUE )

















##2002
## office codes from 2006
dir.now <- rf(paste("data/electoral/tse/sections/", 2006, "sections/", sep=''))
tmp <- read.csv2(file=
                 paste(dir.now,"commontables/cargo_2006.txt",sep='')
                           ,encoding="latin1",header=FALSE)
names(tmp) <- c("code","office","electorate","votable","mainofficecode")
tmp$office[tmp$code%in%c(9,10)] <- "Suplente Senador"
tmp$office <- gsub("-"," ", tmp$office)


##parties
parties <- get.party(2006)
dbWriteTableU(connect,"br_vote_parties",parties,append=TRUE)



cand <- get.candidates(2002)
##office codes from 2006
office.codes <- read.csv2(file=
                          paste(dir.now,"commontables/cargo_",2006,".txt",sep='')
                          ,encoding="latin1",header=FALSE)
names(office.codes) <- c("code","office","electorate","votable","mainofficecode")
cand$office <- recode.office(cand$office)
## sit codes from 2006
sit.codes <- read.csv2(file=paste(dir.now,"commontables/candidato_sit_tot_", 2006, ".txt",sep=''),encoding="latin1",header=FALSE)
cand$sit <- factor(cand$sit,levels=sit.codes$V1,labels=sit.codes$V2)

dbWriteTableU(connect, name="br_vote_candidates", value=cand, append=TRUE )


##files
lf <- dir(rf("data/electoral/tse/sections/2002sections"), pattern="voto_secao", full.names=TRUE)

library(sqldf)

connect.db()


##tfile <- tempfile()

##FIX
for (i in c(20:length(lf))) {
    print(paste(lf[i]))
    csv <- file(lf[i])
    offices <- sqldf("select distinct V4 from csv", drv="SQLite")
    for (k in unique(offices[,1])) {
        print(paste(i,k))
        dnow <- sqldf(paste("select V1, V4, V5, V6, sum(V7) as V7 from csv where V4=", k, " group by V1, V4, V5, V6" , sep=''), drv="SQLite")
        names(dnow) <- c("municipality",  "office", "type", "candidate_code", "votes")
        dnow$office <- factor(as.numeric(dnow$office), levels=tmp$code, labels=toupper(tmp$office))
        state <- gsub(".*secao_([A-Z]{2}).*", "\\1", lf[i])
        round <- gsub(".*([0-9])\\..*", "\\1", lf[i])
        dnow$elec_round <- 1
        dnow$state <- state
        dnow$year <- 2002
        dbWriteTableSeq(connect, "br_vote_mun", dnow, append=TRUE, n=10)
    }
    closeAllConnections()
}














##rsync -ruvze  ssh ~/reps/CongressoAberto/data/electoral/tse/sections/2002sections  ca@174.143.181.9:reps/CongressoAberto/data/electoral/tse/sections/.



