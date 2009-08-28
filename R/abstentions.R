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
s  }
}
usource(rf("R/mergeApprox.R"))
run.from <- rf()

dnow <- read.csv(rf("data/camara/Deputados95-99_Camara.csv"),encoding="utf8")[,c(1,4,6,7)]

names(dnow) <- c("name","state","beg","end")
dnow$beg <- as.Date(as.character(dnow$beg),format="%m/%d/%Y")
dnow$end <- as.Date(as.character(dnow$end),format="%m/%d/%Y")
dnow$namelegis <- clean(trimm(as.character(dnow$name)))
dnow$tmpid <- as.numeric(factor(dnow$namelegis))


connect.db()

tomatch <- unique(dnow[,c("namelegis","state","tmpid")])

idname <- dbGetQueryU(connect,paste("select * from br_bioidname where legis=50"))
idname$namelegis <- clean(idname$name)


res <- merge.approx(states,
                    tomatch,idname,"state","namelegis")
if(nrow(tomatch)!=nrow(res)) stop("mismatch!")
matched <- merge(dnow,res[,c("tmpid","bioid")])


## info about the roll calls
vot <- dbGetQuery(connect,"select *  from br_votacoes where legis=50")

votall <- NULL
tofix <- NULL
##for (i in 1:2) {
for (i in 1:nrow(matched)) {
  if (i%%10==0) print(i)
  ## get the rcs for the legis i
  vot.i <- subset(vot,rcdate>=matched$beg[i] & rcdate<=matched$end[i],select=c(rcfile,rcdate,rcvoteid))
  rc.i <- dbGetQuery(connect,paste("select *  from br_votos where legis=50 AND bioid=",matched$bioid[i],sep=''))
  ## if there is no rc in the period, move on to the next row
  if (nrow(vot.i)==0) next
  vot.i$bioid <- matched$bioid[i]
  ## merge
  vot1 <- merge(vot.i,rc.i,all.x=TRUE)
  if (sum(is.na(vot1$state))==nrow(vot1)) {
    print("not enough obs in the period to impute. getting more data.")
    vot1 <- merge(vot.i,rc.i,all=TRUE)
  }
  vot.i <- vot1
  ## order by date
  vot.i <- vot.i[order(vot.i$rcdate),]
  nas <- which(is.na(vot.i$state))
  ## mark as absent when NA
  vot.i$rc[is.na(vot.i$rc)] <- "Ausente"
  ## fill in missing values with previous vote ##FIX should we use the closest vote?
  vs <- c("id","legis","namelegis","party","state")
  ## fix if  first obs is empty
  if((1%in%nas)) {
    vot.i[1,vs] <- vot.i[which(!is.na(vot.i$state))[1],vs]
  }
  ## fill in
  nn <- which(is.na(vot.i$state))
  for (j in nn) {  
    vot.i[j,vs] <- vot.i[j-1,vs]
  }
  vot.i <- vot.i[nas,]
  vot.i$rcdate <- NULL
  votall[[i]] <- vot.i
}


## write result to db
data.votos <- do.call(rbind,votall)

dbWriteTableU(connect, "br_votos",data.votos, append=TRUE)


