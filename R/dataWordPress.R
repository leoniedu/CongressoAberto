## paths (put on the beg of R scripts)
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


connect.db()


## electoral data

s <- "AC"


election <- 2006
offices <- dbGetQuery(connect, paste("select distinct office from br_vote_mun where year=",shQuote(election),sep=''))[,1]

status.levels <- read.csv2(rf("data/electoral/tse/sections/2006sections/commontables/candidato_sit_2006.txt"), encoding="latin1", header=FALSE)

for (s in states) {
    for (o in offices) {
        for (r in 1:2) {
            print(paste(s,o,r))
            ## all deps in state
            ## with names
            ## table with all deps in state
            res <- dbGetQuery(connect,
                              paste("select * from br_vote_mun where office=",shQuote(o)," AND year=", election," AND elec_round=", r, " AND  state=",shQuote(s),sep='')
                              )
            if (nrow(res)!=0) {
                res.mun <-  dbGetQuery(connect,paste("select * from br_municipios where year=",election, " AND  state_tse06='",s,"'",sep=''))
                res$municipality <- as.numeric(as.character(res$municipality))
                res.mun$municipalitytse <- as.numeric(as.character(res.mun$municipalitytse))
                res.can <- dbGetQuery(connect,
                                      paste("select * from br_vote_candidates where office=",shQuote(o)," AND year=", election," AND  state=",shQuote(s),sep='')
                                      )
                res.can$status <- factor(res.can$status, levels=1:max(status.levels$V1), labels=status.levels$V2)
                res.m <- merge(res,res.mun,by.y=c("municipalitytse","state_tse06","year"),
                               by.x=c("municipality","state","year"))
                res.m <- merge(res.m, res.can,
                           by=c("candidate_code", "state", "year", "office")
                               ,all=TRUE
                               )
                ## status codes
                ## party names
                pn <- dbGetQuery(connect, "select * from br_vote_parties")
                res.m <- merge(res.m, pn)
                res.m <- subset(res.m, select=-c(uf))
                res.c <- recast(res.m,municipality+state+elec_round+geocodig_m+municipality_tse06+state_ibge07+municipality_ibge07+regi_o+mesorregi_+ nome_meso+ microrregi+ nome_micro~candidate_code, measure.var="votes", fill=0)
                os <- tolower(gsub(" ","_",o))
                dir.name <- paste("/var/www/data/eleicoes/",election,"/",os,"/votacao/", sep="")
                dir.create(dir.name, recursive=TRUE)
                file.name <- paste(dir.name, os , election, "_",s,"turno",r,".csv", sep="")
                write.csv(res.c, file=file(description=file.name,encoding="latin1") , row.names=FALSE)
                dir.name <- paste("/var/www/data/eleicoes/",election,"/",os,"/candidatos/", sep="")
                dir.create(dir.name, recursive=TRUE)
                file.name <- paste(dir.name, os , election, "_",s,"turno",r,".csv", sep="")
                write.csv(res.can, file=file(description=file.name,encoding="latin1") , row.names=FALSE)
            } } } }






## roll call data

##  leaders table
lead <- dbGetQuery(connect, "select * from br_leaders")

year <- 2009
legislatura <- "2007_2010"
for (year in 2007:2011) {
    ##date.range <- paste(format(c(init.date+1,final.date-1),"%d-%m-%Y"))
    ##sql <- paste("select  a.namelegis as nome, a.party as partido, a.state as estado, a.rc as voto, a.rcvoteid as idvotacao, a.bioid, b.legisyear as ano_legislativo, cast(b.rcdate as date) as data, b.billdescription as votacao,  b.bill as proposicao   from br_votos as a, br_votacoes as b where  a.rcvoteid=b.rcvoteid and b.legisyear=",year, " limit 1000", sep='')
    sql <- paste("select  a.namelegis as nome, a.party, a.state as estado, a.rc, a.rcvoteid, a.bioid, b.legisyear as ano_legislativo, cast(b.rcdate as date) as data, b.billdescription as votacao,  b.bill as proposicao   from br_votos as a, br_votacoes as b where  a.rcvoteid=b.rcvoteid and b.legisyear=",year,
                 ##" limit 1000",
                 sep='')
    res <- dbGetQueryU(connect,sql)
    if (nrow(res)>0) {
        res$party <- recode.party(res$party)
        res <- merge(subset(lead, select=c(rcvoteid, rc, party)), res, by=c("rcvoteid", "party"), all.y=TRUE,
                     suffixes=c(".party",""))
        res <- merge(subset(lead, party=="GOV", select=c(rcvoteid, rc)),
                     res, by=c("rcvoteid"), all.y=TRUE, suffixes=c(".gov",""))
        res <- subset(res, select=c(bioid, nome, estado, party, rc, rc.party, rc.gov, rcvoteid, data, votacao, proposicao))
        dir.name <- paste("/var/www/data/votacoes_nominais/",legislatura,"/", sep="")
        dir.create(dir.name, recursive=TRUE)
        file.name <- paste(dir.name, "votacoes_nominais_ano_legislativo_",year,".csv", sep="")
        write.csv(res, file=file(description=file.name,encoding="latin1") , row.names=FALSE)        
    }
}


## electoral finance data

