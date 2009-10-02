## FIX: create a id variable with bioid+party to identify unique legislators/party
## FIX: drop rcs with too few votes
library(ggplot2)
library(wnominate)


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


legis <- 50:53
wnall <- vector(mode="list",length=length(legis))
names(wnall) <- legis

##most frequent voter for party pnow
f <- function(pnow=c("PFL","DEM")) {
  res <- lapply(pnow,function(n) {
    (names(which.max(table(subset(rc,partyR==n & rc%in%c("Sim","Não"),biopartyR)))))
})
  unlist(res)[1]
}

recode.party1 <- function(x) {
  x <- car::recode(x,'"PFL"="DEM"')
  if (rc$legis[1]>51) x <- car::recode(x,'"PPB"="PP"')
  x
}



for (j in legis) {
  rcnow <- dbGetQueryU(connect,
                       paste("select * from br_votacoes where legis=",j)
                       )
  rc <- dbGetQueryU(connect,
                    paste("select t1.*, t2.legis  from br_votos as t1, br_votacoes as t2 where  t1.rcfile=t2.rcfile AND t2.legis=",j)
                    )
  rc$quorum <- with(rc, ave(!(rc%in%c("Ausente","Obstrução")),rcfile,FUN=sum))
  rc <- subset(rc,quorum>256)
  rc$partyR <- recode.party1(rc$party)
  rc$rcr <- car::recode(rc$rc,"'Sim'=1;'Ausente'=NA;else=0")
  rc$biopartyR <- with(rc,paste(bioid,partyR,sep=";"))
  rcc <- recast(rc,bioid+partyR+biopartyR~rcfile,measure.var="rcr")
  rcr <- rollcall(data.frame(rcc)[,-c(1:3)],legis.names=rcc[,3],legis.data=rcc[,1:3])
  pol <- c(f(c("PFL","DEM")),f(c("PPB","PP")))
  wnall[[paste(j)]] <- wnominate(rcr,polarity=pol,dims=2)  
}

save(wnall,file=rf("out/wnLegisYN.RDta"))

##load(file=rf("tmp/wn3.RDta"))

load(file=rf("out/wnLegisYN.RDta"))


wb <- NULL
for (i in 1:length(wnall)) {
  tmp <- wnall[[i]]$legislators[,c("coord1D","coord2D","bioid","partyR")]
  tmp$legis <- as.numeric(names(wnall)[i])
  wb <- rbind(wb,tmp)
}
## calculate medians
wb$pmedian1 <- with(wb,ave(coord1D,partyR,legis,FUN=function(x) median(na.omit(x))))
## fill in missing values with partyR medians (by period)
wb$dim1 <- with(wb,ifelse(is.na(coord1D),pmedian1,coord1D))
## FIX: fill in remaining missing values using chamber medians?
pmedians <- recast(wb,legis~partyR,measure.var="dim1",fun.aggregate=median)
##pmedians$right <- with(pmedians,ifelse(is.na(PFL),DEM,PFL))
pmedians$right <- with(pmedians,DEM)
pmedians$left <- with(pmedians,PT)
wb <- merge(wb,pmedians[,c("right","left","legis")])
wb$dim1p <- with(wb,(dim1-left)/((right-left)/2)-1)
pmedians <- recast(wb,legis~partyR,measure.var="dim1p",fun.aggregate=median)
wb <- subset(wb,select=c(legis,bioid,partyR,dim1p,dim1))


## get list of votacoes
rcnow <- dbGetQueryU(connect,
                     paste("select rcvoteid, rcdate, legisyear  from br_votacoes where legis>=50")
                     )
dim(rcnow)
##FIX this vote has too much missin party information
rcnow <- subset(rcnow, rcvoteid!="100000747")
dim(rcnow)
rcnow <- rcnow[!duplicated(rcnow$rcdate),]
dim(rcnow)
rcnow <- rcnow[order(rcnow$rcdate),]
dim(rcnow)



medians <- NULL
## now, for each roll call, calculated the median
for(i in 1:nrow(rcnow)) {
##for(i in 1:20) {
    if (i%%20==0) {
        print(paste(round(i/nrow(rcnow),2)*100,'%',sep=''))
        print(table(medians$legisyear))
    }
  ##for(i in 1:10) {
    ## first get the rc votes
    rc <- dbGetQueryU(connect,
                      paste("select * from br_votos where rcvoteid='",rcnow$rcvoteid[i],"'",sep='')
                    )
    ## There exists rollcalls with missing party info. break if this happens
    if (sum(rc$party=="S.Part.">50)) stop("missing party information!")
    rc <- merge(rc,rcnow[i,])  
    rc$partyR <- recode.party1(rc$party)
    ## merge with the wnominate date
    rc <- merge(rc,wb)
    rc <- with(rc,unique(data.frame(legisyear,rcdate,legis,
                                    ct=length(na.omit(dim1)),
                                    median1p=median(dim1p,na.rm=TRUE),
                                    min1p=min(dim1p,na.rm=TRUE),
                                    max1p=max(dim1p,na.rm=TRUE),
                                    median1=median(dim1,na.rm=TRUE),
                                    min1=min(dim1,na.rm=TRUE),
                                    max1=max(dim1,na.rm=TRUE)
                                    )))  
    medians <- rbind(medians,rc)
}
medians0 <- medians


medians$rcdate <- as.Date(as.character(medians$rcdate))
medians$sup1 <- with(medians,ifelse(legisyear>=2003,median1p*-1,median1p))
medians$yearmonth <- (as.character(format(medians$rcdate,"%Y%m")))
medians$mdate <- format.Date(medians$rcdate, "15/%m/%Y")


medians.c <- recast(subset(medians, ct>510),
                    legisyear+legis+yearmonth+mdate~variable,
                    id.var=c("yearmonth","legisyear","legis", "mdate"),
                    measure.var=c("median1p",
                    "min1p","max1p","median1","min1","max1"),
                    fun.aggregate=function(x) c(median=median(x)))
medians.c$mdate <- as.Date(medians.c$mdate, format="%d/%m/%Y")

write.csv(medians.c, file="~/Desktop/medians.csv")


## p <- qplot(rcdate,median1,data=subset(medians,ct>500),geom=c("smooth"),group=legis)+scale_y_continuous(limits=c(-1,1))+geom_point(aes(colour=factor(legis)),alpha=1/3,size=2)

## library(Acinonyx)
## idev()
## p


## pdf(file="/Users/eduardo/Desktop/p2.pdf",width=8)
## print(p)
## dev.off()


## tmp <- melt(with(rc,table(rcvoteid,partyR)))
## tmp1 <- subset(tmp,value>0)














## dbRemoveTable(connect,"br_wnominate")
## dbWriteTableU(connect,"br_wnominate",wb)

## dbRemoveTable(connect,"tmp")
## rc <- dbGetQueryU(connect,
##                   "create table tmp select br_votos.*, t3.rcdate, br_wnominate.dim1p from
## (select t1.*, t2.legisyear, t2.rcdate  from br_votos as t1, br_votacoes as t2
##  where t1.rcfile=t2.rcfile) as t3,
## br_votos, br_wnominate
## where (br_votos.bioid=br_wnominate.bioid AND br_votos.partyR=br_wnominate.partyR AND t3.legisyear=br_wnominate.legisyear)")






                    

## dbGetQuery(connect,"select * from br_votos,br_wnominate where (br_votos.bioid=br_wnominate.bioid AND br_votos.partyR=br_wnominate.partyR AND br_votos.legisyear=br_wnominate.legisyear) limit 10")


## save(wnall,file=rf("tmp/wn3.RDta"))


## pmedians <- recast(wnall[[15]]$legislators,partyR~variable,measure.var="coord1D",fun.aggregate=median,na.rm=TRUE)
## pmedians <- pmedians[order(pmedians$coord1D),]
## pmedians


