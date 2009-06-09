##bio
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
  birthplace <- gb((gsub(".* - ","",birth)))
  birthdate <-  as.Date(gb(gsub(" - .*","",birth)),format="%d/%m/%Y")
  sessions <- gb(text.now[grep("Legislaturas:",text.now)[1]])
  sessions <- sub(".*: |\\.","",sessions)
  ##sessions <- strsplit(sessions,",")[[1]]
  parties <- toupper(paste(party,";",trim(gsub("<.*>(.*)<.*>","\\1",text.now[grep("Filiações Partidárias",text.now)[1]+5]))))
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
  data.frame(nameshort, name=namelong, partynow=party, state=state, birth=birthdate, birthplace, sessions=sessions, parties=parties , mandates,id,biofile=file.now,imagefile,sessionow=session.now)
}


bio.all <- list()
for (session.now in sessions) {
  cat(session.now,"\n")
  files.list <- dir(paste('../data/bio/',session.now,'/',sep=''),pattern="DepNovos_Detalhe",full.names=TRUE)
  bio.all <- c(bio.all,lapply(files.list,get.bio))
}
bio.all <- do.call(rbind,bio.all)
