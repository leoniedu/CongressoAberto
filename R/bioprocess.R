##process bio files
## creates: bio.all (one row per legislator)
##          idname (one row per legislator/name/session
                                        # names written in multiple forms)

library(plyr)
library(RMySQL)
source("~/reps/CongressoAberto/R/caFunctions.R")



gb <- function(x) trim(toupper(gsub(".*<b>(.*)<.*b>.*","\\1",x)))

get.bio <- function(file.now) {
  id <- gsub(".*id=([0-9]+)&.*","\\1",file.now)
  text.now <- readLines(file.now,encoding="latin1")
  namelong <- gb(text.now[83])
  bdate <- text.now[grep("Nascimento",text.now)]
  birth <-trim(
               sub(".*:","",
                   text.now[84]
                   )
               )
  imagefile <- gsub(".*\"(.*)\".* width.*","\\1",text.now[grep("img",text.now)[1]])
  imagefile <- gsub(".*/(depnovos.*)&nome.*","\\1",imagefile)
  birthplace <- gb((gsub(".* - ","",birth)))
  birthdate <-  as.Date(gb(gsub(" - .*","",birth)),format="%d/%m/%Y")
  sessions <- gb(text.now[grep("Legislaturas:",text.now)[1]])
  sessions <- gsub(".*: |\\.| +","",sessions)
  ##sessions <- strsplit(sessions,",")[[1]]
  mandates <- gsub("<.*>(.*)<.*>","\\1",trim(text.now[grep("Mandatos Eletivos",text.now)[1]+5]))  
  nameshort <- gb(text.now[65])
  if (substr(nameshort,nchar(nameshort),nchar(nameshort))=="-") {
    ##no party/state info (deputies from older sessions)
    ##cat(file.now,"\n")
    nameshort <- substr(nameshort,1,nchar(nameshort)-2)
    if (is.na(mandates)) {
      ## there is no mandate info
      ##FIX?: assume the person is a deputy of the birth state
      party <- NA
      state <- substr(birthplace,nchar(birthplace)-1,nchar(birthplace))
    } else {
      ##cat(file.now,"\n")
      mandlist <- sapply(strsplit(mandates,";")[[1]],trim)
      mandlist <- mandlist[grep("Deputad[oa] Federal",mandlist)]
      mandlist <- strsplit(mandlist[length(mandlist)],",")[[1]]
      lm <- length(mandlist)    
      party <- trim(mandlist[lm])
      state <- trim(mandlist[lm-1])
    }
  } else {
    partystate <- strsplit(toupper(trim(gsub(".* - ","",nameshort))),"/")
    party <- partystate[[1]][1]
    state <- partystate[[1]][2]
    nameshort <- toupper(trim(gsub(" - .*","",nameshort)))
  }
  parties <- toupper(paste(party,";",trim(gsub("<.*>(.*)<.*>","\\1",text.now[grep("Filiações Partidárias",text.now)[1]+5]))))
  ##print(sessions)
  file.now <- gsub(".*/(DepNovos.*)","\\1",file.now)
  parties <- gsub("\t+| +|^ +|^\t+","",parties)
  mandates <- gsub("\t+| +|^ +|^\t+","",mandates)
  gc()
  data.frame(nameshort, name=namelong, partynow=party, state=state, birth=birthdate, birthplace, sessions=sessions, parties=parties , mandates,bioid=id,biofile=file.now,imagefile)
}
files.list <- dir('../data/bio/all/',pattern="DepNovos_Detalhe",full.names=TRUE)
bio.all <- lapply(files.list,get.bio)
bio.all <- do.call(rbind,bio.all)

##manual fixes
bio.all[bio.all$bioid=="96883","state"] <- "AP"

## create a deputyid/name/session to use when merging data from
## multiple sources (this is slow and memory consuming)
idname <- with(bio.all,
               data.frame(bioid,
                          name,                         
                          nameshort,
                          state,
                          sessions))
idname <- ddply(idname,'bioid',
                function(x) 
                with(x,data.frame(bioid,
                                  name,
                                  nameshort,
                                  state,
                                  sessions=strsplit(as.character(sessions),",")[[1]]
                                  )
                     ),
                .progress="text")
idname <- with(idname,rbind(
                            data.frame(bioid,name=as.character(name),state,sessions),
                            data.frame(bioid,name=as.character(nameshort),state,sessions))
               )
idname <- unique(idname)




##try to join this to votos db
## votos.id <- dbGetQuery(connect, "select distinct br_votos.id, br_votos.name, br_votos.state, br_votacoes.anolegislativo from br_votos,br_votacoes where br_votos.origvote=br_votacoes.origvote")

connect.db()


dbRemoveTable(connect,"br_idname")
dbRemoveTable(connect,"br_bio")

dbWriteTable(connect, "br_idname", idname, overwrite=TRUE,
             row.names = F, eol = "\r\n" )    

dbWriteTable(connect, "br_bio", bio.all, overwrite=TRUE,
             row.names = F, eol = "\r\n" )    


