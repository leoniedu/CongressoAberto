library(reshape)
library(RMySQL)
years <- 1999:2000


## before 1999 the files do not show who didn't vote
## (we have to somehow get the list of deputies current at each vote)

filenames <- paste("../data/NECON/brvotes/brvotes",years,".csv",sep='')
data.votos <- lapply(filenames,gv)
data.votos <- do.call(rbind,data.votos)
data.votos <- get.votos(data.votos)
filenames <- paste("../data/NECON/data.voteDescription/data.voteDescription",years,".csv",sep='')
data.votacoes <- lapply(years,gd)
data.votacoes <- do.call(rbind,data.votacoes)

data.deputados <- unique(merge(data.votacoes,data.votos,by="origvote")[,c("name","state","id","legislatura")])

## Code to write to DB goes here
if (exists("connect")) dbDisconnect(connect)
connect<-dbConnect(driver, group="congressoaberto")
##should have a .my.cnf in home directory with the access data to the DB
##since the source is public putting it here causes a (serious) security concern
dbRemoveTable(connect,"br_votos")
dbRemoveTable(connect,"br_deputados")
dbRemoveTable(connect,"br_votacoes")
##put in db
dbWriteTable(connect, "br_votos", dnow, overwrite=TRUE,
             row.names = F, eol = "\r\n" )    
dbWriteTable(connect, "br_votacoes", data.votacoes, overwrite=TRUE,
             row.names = F, eol = "\r\n" )    
dbWriteTable(connect, "br_deputados", data.deputados, overwrite=TRUE,
             row.names = F, eol = "\r\n" )    

