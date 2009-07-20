## produces the kml file for google maps for each candidate
## to start we get those with bioids
library(ggplot2)
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




connect.db()




## get map
library(maptools) 

load(rf("data/maps/ibge2007.RData"))
## take out lagoa dos patos (missing state sigla) from map
m1 <- m1[!is.na(m1@data$SIGLA),]
## get label x y positions, only largest polygon in each city is not NA
m1$area <- sapply(m1@polygons,function(poly) poly@area)
m1$x <- sapply(m1@polygons,function(poly) poly@labpt[1])
m1$y <- sapply(m1@polygons,function(poly) poly@labpt[2])
## only largest area not NA
## m1.sub <- ddply(m1@data,"municipality",function(x) subset(x,area==max(area)))
## m1$x[!m1$ID%in%m1.sub$id] <- NA
## m1$y[!m1$ID%in%m1.sub$id] <- NA
##res <- dbGetQuery(connect,paste("select * from br_vote_mun where office='DEPUTADO FEDERAL' AND year='2006'  AND state='",snow,"'",sep=''))




## select a state j
snow <- states[j]
snow <- "AL"



## let's try to plot one deputy
## get votes for all candidates in state
res0 <- dbGetQuery(connect,paste("select municipality, state, sum(votes) as votes_total from br_vote_mun where  office='DEPUTADO FEDERAL' AND year='2006' AND state='",snow,"' AND elec_round=1 group by municipality",sep=''))

## all deps in state
dep <- dbGetQuery(connect,paste("select * from br_bioidtse where office='DEPUTADO FEDERAL' AND year='2006' AND state='",snow,"'",sep=''))

## select one cand in state
i <- 1
cand <- dep$candidate_code[i]



## res <- dbGetQuery(connect,paste("select candidate_code, sum(votes) as vsum from br_vote_mun where office='DEPUTADO FEDERAL' AND year='2006' AND  state='",snow,"' group by candidate_code",sep=''))



res <- dbGetQuery(connect,paste("select * from br_vote_mun where office='DEPUTADO FEDERAL' AND year='2006' AND candidate_code=",cand," AND state='",snow,"'",sep=''))
res <- merge(subset(res0,state==snow),res,by=c("state","municipality"),all=TRUE)

res$vote_prop <- with(res,votes/votes_total)












## assign ibge codes to votes
##m0 <- dbGetQuery(connect,"select * from br_municipios where state_tse06='BA'")
m0 <- dbGetQuery(connect,"select * from br_municipios")
m0$municipalitytse <- as.numeric(as.character(m0$municipalitytse))
m0$GEOCODIG_M <- m0$geocodig_m
m0 <- merge(res,m0,by.x=c("municipality"),by.y=c("municipalitytse"),all.x=TRUE)


## map for current state
m2 <- merge.sp(m1[m1@data$SIGLA==snow,],m0,by="GEOCODIG_M")
m2$votes[is.na(m2$votes)] <- 0

## breaks for color ramp
##bs <- seq(0,max(m2$vote_prop,na.rm=TRUE),length=9)
bs <- c(0,.025,.05,.1,.2,.4,.6,.8,1)

library(colorspace)
alpha.now <- .95
##col.now <- alpha(diverge_hcl(length(bs)),alpha.now)
col.now <- alpha(sequential_hcl(length(bs)),alpha.now)

m2 <- color.heat(m2,"vote_prop",col.vec=col.now,
                 breaks=bs,reverse=TRUE)
m2@data$zCat <- as.character(m2@data$zCat)
m2@data$zCat[is.na(m2@data$zCat)] <- "transparent"

m2@data$parea <- with(m2@data,votes_total/sum(votes_total))
m2@data$pone <- with(m2@data,votes/sum(votes))

##FIX: create table for description.
##FIX: microregiao for whole brazil map.
## creates a KML file containing the polygons of South Africa, Switzerland, and Canada


##sapply(m2@polygons,function(poly) list(ID=poly@ID,labpt=poly@labpt,area=poly@area))
## get polygons by municipality with the largest areas

plot.heat(m2,"vote_prop",state.map=NULL,col.vec=col.now,breaks=bs,reverse=TRUE)

## plot(m2)
## with(m2@data,points(x,y,cex=20*(votes_total/sum(votes_total)),pch=19,col=as.character(zCat)))
## ##with(m2@data,text(x,y,label=municipality_tse06,cex=.3))


## this is likely the best
p <- qplot(x,y,colour=vote_prop,data=subset(m2@data,votes>0),label=municipality_tse06,alpha=I(2/3),size=votes)+theme_bw()+coord_equal() +scale_area(name="votes",to = c(0.001, 20),breaks=c(100,1000,10000))

p

p+geom_text(data=subset(m2@data,votes>0),aes(size=sqrt(votes_total)))











pointKml <- function(data,title='Hello',description='World!',file="~/Desktop/tmp.kml") {
  kmlStyles <- sapply(1:nrow(data),function(i){
    paste(
          '<Style id="',data$id[i],'">
                       <IconStyle>
                         <Icon>
           <href>',data$image[i],'</href>
             </Icon>
               </IconStyle>
                 </Style>',sep='')
  })
  kmlPoints <- sapply(1:nrow(data),function(i) {
    paste('<Placemark>',
          '<name><![CDATA[',data$name[i],']]></name>',
          '<description><![CDATA[',data$description[i],']]></description>',
          '<styleUrl>#',data$id[i],'</styleUrl>',
          '<Point>',
          '<coordinates>',data$x[i],",",data$y[i],0,'</coordinates>',
          '</Point>',
          '</Placemark>'
          ,sep='')
  })
  ## pics
  sapply(1:nrow(data),function(i) {
    with(dnow[i,],{
      png(file=paste("~/Desktop/images/pic",id,".png",sep=''),bg="transparent",heigh=200,width=200,pointsize=1)
      ##print(grid.circle(gp=gpar(fill=as.character(zCat[i]))))
      print(grid.circle(r=size,gp=gpar(fill=alpha("red",.75))))
    }
         )
    dev.off()
  })
  cat('<?xml version="1.0" encoding="UTF-8"?>
    <kml xmlns="http://earth.google.com/kml/2.2">
      <Document>
        <name>',title,'</name>
          <description><![CDATA[<i>',description,'</i>]]></description>',
      kmlStyles,
      kmlPoints,
      '</Document></kml>',file=file)  
}

dnow <- m2@data
dnow$id <- dnow$GEOCODIG_M
dnow$image <- paste("http://files.eduardoleoni.com/pic",dnow$id,".png",sep='')
dnow$name <- dnow$municipality_tse06
dnow$description <- dnow$votes
mm <- function(x,lo,hi) ifelse(x<lo,lo,ifelse(x>hi,hi,x))
dnow$size <- with(dnow,(votes/max(votes)))*.5

pointKml(dnow,file="~/Desktop/tmp.kml")

system("scp ~/Desktop/images/pic*.png leoniedu@cluelessresearch.com:files.eduardoleoni.com/.")

system("scp ~/Desktop/tmp.kml leoniedu@cluelessresearch.com:files.eduardoleoni.com/tmp6.kml")






###old


qplot(x,y,size=votes_total/sum(votes_total),colour=vote_prop,data=m2@data,geom="text",label=municipality_tse06)


qplot(x,y,colour=vote_prop,data=m2@data,alpha=I(1/2),size=parea)+theme_bw()+coord_map() +scale_area("votes")








dir.now <- "~/Desktop/"

## add Brazil states layer?
sw <- m2
tokml(sw,"BR",compress=1)

for (state.now in states) {  
  sw <- m2[which(m2@data$state==state.now),]
  tokml(sw,state.now)
}

  




## bioids
res <- dbGetQuery(connect,"select * from br_bioidtse where year=2006")

res <- dbGetQuery(connect,"select * from br_vote_mun limit 10")


v1 <- dbGetQuery(connect,"select a.bioid, b.*    from
 (select * from br_bioidtse where year=2006 limit 1) as a,
 (select * from br_vote_mun where  year=2006 and office='Deputado Federal') as b 
 where a.candidate_code=b.candidate_code and
 a.state=b.state")


## votes by municipality
v <- dbGetQuery(connect,"select a.bioid, b.*    from
 (select * from br_bioidtse where year=2006) as a,
 (select * from br_vote_mun where  year=2006 and office='Deputado Federal') as b 
 where a.candidate_code=b.candidate_code and
 a.state=b.state")


        
