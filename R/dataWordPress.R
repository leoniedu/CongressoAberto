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
source(rf("R/wordpress.R"))



connect.db()
connect.wp()

## electoral data

election <- 2006
offices <- dbGetQuery(connect, paste("select distinct office from br_vote_mun where year=",shQuote(election),sep=''))[,1]

status.levels <- read.csv2(rf("data/electoral/tse/sections/2006sections/commontables/candidato_sit_2006.txt"), encoding="latin1", header=FALSE)

res.mun <-  dbGetQuery(connect,paste("select * from br_municipios where year=",election,sep=''))
res.mun$municipalitytse <- as.numeric(as.character(res.mun$municipalitytse))

res.bio <- dbGetQuery(connect, "select * from br_bioidtse")

pn <- dbGetQuery(connect, "select * from br_vote_parties")

for (s in states) {
for (o in offices) {
    for (r in 1:2) {
        print(paste(o,r))
        print(paste(s,o,r))
        ## all deps in state
        ## with names
        ## table with all deps in state
        res <- dbGetQuery(connect,
                          paste("select * from br_vote_mun where office=",shQuote(o)," AND year=", election," AND elec_round=", r
                                , " AND  state=",shQuote(s)
                                , sep='')
                          )
        if (nrow(res)!=0) {
            res <- merge(res.bio, res, all.y=TRUE)
            res$municipality <- as.numeric(as.character(res$municipality))
            res.can <- dbGetQuery(connect,
                                  paste("select * from br_vote_candidates where office=",shQuote(o)," AND year=", election
                                        ," AND  state=",shQuote(s)
                                        , sep='')
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
            res.m <- merge(res.m, pn)
            res.m <- subset(res.m, select=-c(uf))
            os <- tolower(gsub(" ","_",o))
            dir.name <- paste("/var/www/data/eleicoes/",election,"/"
                              , os, "/"
                              , sep="")
            dir.create(dir.name, recursive=TRUE)
            file.name <- paste(dir.name, os , election, "_", s, "_turno",r,".csv", sep="")
            zip.name <- paste(dir.name, os , election, "_", s, "_turno",r,".zip", sep="")            
            write.csv(res.m, file=file(description=file.name,encoding="latin1") , row.names=FALSE)
            system(paste("zip -j ",zip.name,file.name))
            unlink(file.name)
        }
    }
}
}







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
        zip.name <- paste(dir.name, "votacoes_nominais_ano_legislativo_",year,".zip", sep="")
        write.csv(res, file=file(description=file.name,encoding="latin1") , row.names=FALSE)
        system(paste("zip -j ",zip.name,file.name))
        unlink(file.name)        
    }
}



## legis stats


sql <- "SELECT b.namelegis as Nome , cast(a.party as binary) as Partido , cast(upper(a.state) as binary) as Estado , c.ausente_count as 'Ausente' , c.ausente_total as 'Ausente (total)' , c.cgov_count as 'Segue o governo' , c.cgov_total 'Segue o governo (total)' , c.cparty_count as 'Segue o partido' , c.cparty_total as 'Segue o partido (total)' , c.nparty as 'Numero de partidos' , b.bioid FROM br_deputados_current as a, br_bio as b, br_legis_stats as c, br_bioidpostid as d WHERE a.bioid=b.bioid and a.bioid=c.bioid and a.bioid=d.bioid"

lstats <- dbGetQuery(connect,sql)

dir.name <- paste("/var/www/data/estatisticas/", sep="")
dir.create(dir.name, recursive=TRUE)
file.name <- paste(dir.name, "deputados",legislatura,".csv", sep="")
write.csv(lstats, file=file(description=file.name,encoding="latin1") , row.names=FALSE)



pp <- dbGetQuery(conwp,paste("select * from ", tname("posts"), " where post_title='Dados e Análises'"))$ID


content <- "
<!--more-->
<p> Aqui você encontra os principais dados do CongressoAberto.com.br. Dúvidas, pedidos e sugestões devem ser enviadas para <a href=\"mailto:admin@congressoaberto.com.br?subject=download de dados\">admin@congressoaberto.com.br</a>.</p>
<p> Os dados são extraídos de duas fontes principais: </p>
<ol>
<li> <a href=\"http://www.camara.gov.br\"> Câmara dos Deputados </a>: Votações nominais, infomações biográficas dos deputados e informações sobre as proposições.</li>
<li> <a href=\"http://www.tse.gov.br\"> Tribunal Superior Eleitoral </a>: Votos dos candidatos por município, contribuições de campanha e informações básicas dos candidatos.</li>
</ol>
<!--list files \"/data\"-->
<p> CongressoAberto.com.br está em fase experimental, e erros no tratamento dos dados não são só possíveis, como prováveis. Não nos responsabilizamos por erros na codificação e/ou nos dados originais.</p>
<p>Obs: Para juntar os dados eleitorais com as votações nominais utilize a variável <code>bioid</code>.</p> 
"


wpAddByTitle(conwp,post_title="Download de Dados"
             ,post_parent=pp
             ,post_content=content
             ,post_type="page")







## electoral finance data

