##FIX: This uses too much memory. Any way to break down the analysis into chuncks? Or to make it more efficient memorywise?

##FIX: Download pics or not?


##process bio files
## creates: bio.all (one row per legislator)
##          idname (one row per legislator/name/session
                                        # names written in multiple forms)

##FIX: ##to download all  (perhaps do this once a week?) [in case individual bios get update]
##but we search for new legislators every day 

library(plyr)
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
setwd(rf())

## need variable download.now set

connect.db()

gb <- function(x) trim(toupper(gsub(".*<b>(.*)<.*b>.*","\\1",x)))

get.bio <- function(file.now) {
  id <- gsub(".*id=([0-9]+)&.*","\\1",file.now)
  cat(id,"\n")
  text.now <- readLines(file.now,encoding="latin1")
  namelong <- gb(text.now[83])
  bdate <- text.now[grep("Nascimento",text.now)]
  birth <-trim(
               sub(".*:","",
                   text.now[84]
                   )
               )
  ##imagefile <- gsub(".*\"(.*)\".* width.*","\\1",text.now[grep("img",text.now)[1]])
  ##imagefile <- gsub("/internet/deputado/","",imagefile)
  ##imagefile <- gsub(".*/(depnovos.*)&nome.*","\\1",imagefile)
  oldimagefile <- dir(rf("data/bio/all"),pattern=paste("foto.asp\\?id=",id,".*",sep=''))[1]
  imagefile <- paste("foto",id,".jpg",sep="")
  file.copy(rf(paste("data/bio/all/",oldimagefile,sep="")),rf(paste("data/images/bio/",imagefile,sep='')),overwrite=TRUE) 
  birthplace <- gb((gsub(".* - ","",birth)))
  birthdate <-  as.Date(gb(gsub(" - .*","",birth)),format="%d/%m/%Y")
  sessions <- gb(text.now[grep("Legislaturas:",text.now)[1]])
  sessions <- gsub(".*: |\\.| +","",sessions)
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
  mandates <- gsub("\t+|^ +|^\t+","",mandates)
  gc()
  data.frame(namelegis=nameshort, name=namelong, party=party, state=state, birthdate, birthplace, legisserved=sessions, prevparties=parties , mandates,bioid=id,biofile=file.now,imagefile)
}
## bio.all.list <- lapply(files.list[1:2],get.bio)  
## bio.all <- do.call(rbind,bio.all.list)


##to download all  (perhaps do this once a week?)
##FIX:  we should also look for new legislators every day, so create a comparison between the old and new files
## FIX: for now we use no clobber  (-nc) so that only new files are downloaded
## should change this to be used only when not update.all
##dir('~/reps/CongressoAberto/data/bio/all/', pattern="DepNovos_Lista*")

index.file <- rf("data/bio/all/DepNovos_Lista.asp?fMode=1&forma=lista&SX=QQ&Legislatura=QQ&nome=&Partido=QQ&ordem=nome&condic=QQ&UF=QQ&Todos=sim")

oldfiles <- dir(rf('data/bio/all'),pattern="DepNovos_Detalhe", full.names=TRUE)
if (download.now) {
  try(file.remove(index.file))
  tmp <- system(paste("wget -nd -r -nc -P ", rf("data/bio/all"), " 'http://www.camara.gov.br/internet/deputado/DepNovos_Lista.asp?fMode=1&forma=lista&SX=QQ&Legislatura=QQ&nome=&Partido=QQ&ordem=nome&condic=QQ&UF=QQ&Todos=sim' 2>&1",sep=''), intern=TRUE)  
  newfiles <- dir('~/reps/CongressoAberto/data/bio/all',pattern="DepNovos_Detalhe",full.names=TRUE)
  if (update.all) {
    files.list <- newfiles
  }  else {
    files.list <- setdiff(newfiles, oldfiles)
  }
}

if (length(files.list)>0) {

  ll <- readLines(index.file,encoding='latin1')
  ll <- gsub("\t+| +"," ",ll)
  ##pe <- ll[grep("[A-Z] - [A-Z]",ll)]
  peloc <- grep("/[A-Z]{2}<",ll)
  pe <- trim(ll[peloc])
  pe <- gsub("</b>","",pe)
  pe <- strsplit(pe,"/")
  np <- sapply(pe,function(x) x[[1]])
  uf <- sapply(pe,function(x) x[[length(x)]])
  np <- strsplit(np," - ")
  name <- sapply(np,function(x) trim(x[[1]]))
  partido.current <- sapply(np,function(x) {
    newx <- try(trim(x[[2]]))
    ifelse ("try-error"%in%class(newx),"",newx)
  })
  id <- as.numeric(gsub(".*id=([0-9]+)&.*","\\1",ll[peloc-1]))
  
  data.legis <- data.frame(bioid=id,nameindex=name,state=uf)##,partido.current=partido.current)
  
  bio.all.list <- lapply(files.list,get.bio)  
  bio.all <- do.call(rbind,bio.all.list)
  
  ## create a deputyid/name/session to use when merging data from
  ## multiple sources (this is slow and memory consuming)
  idname <- with(bio.all,
                 data.frame(bioid,
                            name,                         
                            namelegis,
                            ##state,
                            legisserved))
  idname <- merge(idname,data.legis)
  
  bio.all <- merge(subset(bio.all,select=-state),data.legis)##,by="bioid")
  
  ##FIX: This could be faster by creating just the legis vector and duplicating
  ## the rows of bio
  idname <- ddply(idname,'bioid',
                  function(x) 
                  with(x,data.frame(bioid,
                                    name,
                                    namelegis,
                                    nameindex,
                                    state,
                                    legis=get.legis.n(legisserved)
                                    )
                       ),
                  .progress="text") 
  
  idname <- with(idname,rbind(
                              data.frame(bioid,name=as.character(name),state,legis),
                              data.frame(bioid,name=as.character(namelegis),state,legis),
                              data.frame(bioid,name=as.character(nameindex),state,legis))
                 )
  
  idname <- unique(idname)
  
  ##idname$id <- "" ## Why did I put this here????
  if (update.all) {
    save(idname,file="~/reps/CongressoAberto/data/idname.RData")
    save(bio.all,file="~/reps/CongressoAberto/data/bio.all.RData")
  }
}
  
connect.db()

## load(file="~/reps/CongressoAberto/data/idname.RData")
## load(file="~/reps/CongressoAberto/data/bio.all.RData")
## ##delete all data
## dbGetQuery(connect,"truncate table br_bioidname")
## dbGetQuery(connect,"truncate table br_bio")

##write tables
dbWriteTableU(connect, "br_bioidname", idname, append=TRUE)
dbWriteTableU(connect, "br_bio", bio.all, append=TRUE)




##manual fixes
##source("~/reps/CongressoAberto/R/caFunctions.R")
##connect.db()


## PHILEMON RODRIGUES was a deputy both in MG and in PB
dbSendQuery(connect,"update br_bioidname set state='PB' where (bioid='98291')")
dbSendQuery(connect,"update br_bioidname set state='MG' where (bioid='98291') AND (legis!=52)")
dbGetQuery(connect,"select * from  br_bioidname where bioid='98291'")


##tatico deputy in both DF and GO
dbSendQuery(connect,"update br_bioidname set state='DF' where (bioid='108697') AND (legis=52)")
dbSendQuery(connect,"update br_bioidname set state='GO' where (bioid='108697') AND (legis=53)")
dbGetQuery(connect,"select * from  br_bioidname where bioid='108697'")

## ze indio: 100486
tmp <- iconv.df(dbGetQuery(connect,"select * from  br_bioidname where bioid='100486'"))
tmp$name <- 'JOSÉ ÍNDIO'
tmp <- unique(tmp)
dbWriteTable(connect, "br_bioidname", tmp, overwrite=FALSE,append=TRUE,
             row.names = F, eol = "\r\n" )
##not needed since we have primary keys now
##dedup.db('br_bioidname')


## Mainha is José de Andrade Maia Filho 182632
tmp <- iconv.df(dbGetQuery(connect,"select * from  br_bioidname where bioid='182632'"))
tmp$name <- 'MAINHA'
tmp <- unique(tmp)
dbWriteTable(connect, "br_bioidname", tmp, overwrite=FALSE,append=TRUE,
             row.names = F, eol = "\r\n" )
##dedup.db('br_bioidname')


##Pastor Jorge is Jorge dos Reis Pinheiro 100606
tmp <- iconv.df(dbGetQuery(connect,"select * from  br_bioidname where bioid='100606'"))
tmp$name <- 'PASTOR JORGE'
tmp <- unique(tmp)
dbWriteTable(connect, "br_bioidname", tmp, overwrite=FALSE,append=TRUE,
             row.names = F, eol = "\r\n" )
##dedup.db('br_bioidname')


## approx merge is failing to get these guys right
tmp <- iconv.df(dbGetQuery(connect,"select * from  br_bioidname where bioid='109223'"))
tmp$name <- 'PAULO PIMENTA'
tmp <- unique(tmp)
dbWriteTable(connect, "br_bioidname", tmp, overwrite=FALSE,append=TRUE,
             row.names = F, eol = "\r\n" )

tmp <- iconv.df(dbGetQuery(connect,"select * from  br_bioidname where bioid='160419'"))
tmp$name <- 'PAULO ROBERTO'
tmp <- unique(tmp)
dbWriteTable(connect, "br_bioidname", tmp, overwrite=FALSE,append=TRUE,
             row.names = F, eol = "\r\n" )
