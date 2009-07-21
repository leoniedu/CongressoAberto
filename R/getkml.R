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
source(rf("R/ggShape.R"))




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
snow <- "BA"


for (snow in states[-c(1:12)]) {

  ## number of seats
  elected <- dbGetQueryU(connect,paste("select * from br_vote_candidates where  office='DEPUTADO FEDERAL' AND year='2006' AND state='",snow,"' AND sit in ('MÉDIA','ELEITO')",sep=''))
  nseats <- nrow(elected)  


  ## let's try to plot one deputy
  ## get votes for all candidates in each municip
  res0 <- dbGetQueryU(connect,paste("select municipality, state, sum(votes) as votes_total from br_vote_mun where  office='DEPUTADO FEDERAL' AND year='2006' AND state='",snow,"' AND elec_round=1 AND (candidate_code not in (95,96))  group by municipality",sep=''))

  ## all deps in state
  ## with names
  dep <- dbGetQueryU(connect,paste("select a.*, b.* from br_bioidtse as a, br_bio as b where a.bioid=b.bioid AND a.office='DEPUTADO FEDERAL' AND a.year='2006' AND a.state='",snow,"'",sep=''))  
  ## table with all deps in state
  res <- dbGetQueryU(connect,paste("select * from br_vote_mun where office='DEPUTADO FEDERAL' AND year='2006' AND  state='",snow,"'",sep=''))
  res.it <-  dbGetQueryU(connect,paste("select * from br_bioidtse where office='DEPUTADO FEDERAL' AND year='2006' AND  state='",snow,"'",sep=''))
  res.mun <-  dbGetQueryU(connect,paste("select * from br_municipios where year='2006' AND  state_tse06='",snow,"'",sep=''))
  res.xy <- subset(m1@data,SIGLA==snow)
  res$municipality <- as.numeric(as.character(res$municipality))
  res.mun$municipalitytse <- as.numeric(as.character(res.mun$municipalitytse))
  res.m <- merge(res.mun,res,by.x=c("municipalitytse","state_tse06","year"),
                 by.y=c("municipality","state","year"))  
  res.m <- merge(res.m,res.it,by.x=c("candidate_code","state_tse06","office","year"),
                 by.y=c("candidate_code","state","office","year"))  
  res.m <- merge(res.m,res0,
               by.x=c("municipalitytse","state_tse06"),
               by.y=c("municipality","state"),all.y=TRUE)  
  res.m <- merge(res.m,res.xy,by.x="geocodig_m",by.y="GEOCODIG_M")
  res.m <- merge(res.m,dep,by="bioid",suffixes=c("",".dep"))  
  res.m$vote_prop <- with(res.m,votes/votes_total)
  eq1 <- with(res.m,(sum(votes)/nseats))
  res.m$eq <- with(res.m,votes/eq1)
  res.m$eq <- ifelse(res.m$eq>1,1,res.m$eq)
  res.m$id <- 1
  res.m$Nome <- factor(res.m$bioid,
                       levels=dep$bioid,labels=paste(dep$namelegis,"\n",dep$candidate_code))  
  ## ## this is likely the best
  bs <- c(0.01,.025,.05,.1,.2,.3,.5,1)    
  ##plot all
  res.m$`Votos conquistados\nno município (%)` <- round(res.m$vote_prop*100)  
  p <- qplot(x,y,colour=`Votos conquistados\nno município (%)`,data=res.m,
             label=municipality_tse06,alpha=I(2/3),
             size=eq,facets=~Nome)+theme_bw()+coord_equal() +
               scale_area(name="Votos",to = c(0.001, 20),limits=c(0,1),
                          breaks=bs,
                          labels=round(bs*eq1/100)*100)      
  p <- p + theme_bw(base_size=10)+opts(axis.ticks = theme_blank(),axis.text=theme_blank(),panel.grid.minor=theme_blank(),panel.grid.major=theme_blank(),axis.title.x=theme_blank(),axis.title.y=theme_blank(),axis.text.x=theme_blank(),axis.text.y=theme_blank())+scale_x_continuous(name="")+scale_y_continuous(name="")  
  ##png(file=rf(paste("data/images/elections/2006/","deputadofederal",snow,".png",sep="")),bg="transparent",heigh=600,width=800,pointsize=1)
  ## FIX: memory segfault happening here (state=MG)
  ## Saving the RData for now
  ##fn <- rf(paste("data/images/elections/2006/","deputadofederal",snow,".pdf",sep=""))
  ## pdf(file=paste(fn),bg="transparent")  
  ##   print(p)
  ##   dev.off()  
  ##system(paste("convert -density 400x400 -resize 500x500 -quality 90 ", fn," ",gsub(".pdf",".png",fn)),wait=TRUE)
  fn <- rf(paste("data/images/elections/2006/","deputadofederal",snow,".RData",sep=""))
  save(p,file=fn)
  ## plot one
  m2 <- m1[m1$SIGLA==snow,]
  m <- get_borders(m2)  
  m$rowid <- 1:nrow(m)
  m$x <- m$long
  m$y <- m$lat
  m <- m[order(m$rowid,m$id),]
  p <- qplot(x,y,data=m,geom="polygon",fill=I("gray90"),colour=I("white"),group=id)
  p <- p+coord_equal()
  ##p  
  i <- 1
  
  for (i in 1:nrow(dep)) {    
    print(i)
    print(snow)
    cand <- dep$candidate_code[i]
    fn <- rf(paste("data/images/elections/2006/","deputadofederal",snow,cand,".pdf",sep=""))
    pnew <- p+geom_point(aes(x=x,y=y,colour=`Votos conquistados\nno município (%)`,
                             label=municipality_tse06,size=eq),
                         ,alpha=I(2/3),
                         data=subset(res.m,candidate_code==cand))+
                           theme_bw(base=10)+
                          scale_area(name="Votos",to = c(0.001, 20),limits=c(0,1),
                                     breaks=bs,
                                     labels=round(bs*eq1/100)*100)+
                                       theme_bw(base_size=10)+opts(axis.ticks = theme_blank(),
                                                  axis.text=theme_blank(),
                                                  panel.grid.minor=theme_blank(),
                                                  panel.grid.major=theme_blank(),
                                                  axis.title.x=theme_blank(),
                                                  axis.title.y=theme_blank(),
                                                  axis.text.x=theme_blank(),
                                                  axis.text.y=theme_blank())+
                                                    scale_x_continuous(name="")+
                                                      scale_y_continuous(name="")
    
    dnow <- subset(res.m,candidate_code==cand)
    pnew <- pnew+scale_colour_continuous(limits=c(0,100))
    
    pdf(file=fn,bg="transparent",width=8)      
    print(pnew+geom_text(mapping=aes(label=municipality_tse06),data=dnow[order(dnow$votes,decreasing=TRUE)[1:min(c(5,nrow(dnow)))],],size=3,vjust=2))    
    dev.off()
    
    system(paste("convert -density 400x400 -resize 500x500 -quality 90 ", fn," ",gsub(".pdf",".png",fn)),wait=TRUE)
    rm(pnew)
    gc()
  }
}













    ## cc <- merge(cc,res.m,all.x=TRUE)
  ## m <- merge(cc,m,all=TRUE)
  ## important ordering info so paths are nice
  ## m <- m[order(m$candidate_code,m$rowid),]
  ##mnow <- merge(res.m,m,by=c("candidate_code","geocodig_m"))
  ## mnow <- mnow[order(mnow$rowid),]
  ##p+geom_point(aes=aes(x,y,colour=vote_prop,label=municipality_tse06,size=votes),data=mnow,alpha=I(2/3),facets=~candidate_code)+
  
  
  ## p <- qplot(x,y,colour=vote_prop,data=mnow,label=municipality_tse06,alpha=I(2/3),size=votes,facets=~candidate_code)+theme_bw()+coord_equal() 
  ## p+geom_path(aes=aes(x=long,y=lat,group=groupid),size=1)
  
  
  ##p+geom_text(data=subset(m2@data,votes>0),aes(size=sqrt(votes_total)))

## res <- dbGetQuery(connect,paste("select * from br_vote_mun where office='DEPUTADO FEDERAL' AND year='2006' AND candidate_code=",cand," AND state='",snow,"'",sep=''))
## res <- merge(subset(res0,state==snow),res,by=c("state","municipality"),all=TRUE)

## res$vote_prop <- with(res,votes/votes_total)

## ## assign ibge codes to votes
## ##m0 <- dbGetQuery(connect,"select * from br_municipios where state_tse06='BA'")
## m0 <- dbGetQuery(connect,"select * from br_municipios")
## m0$municipalitytse <- as.numeric(as.character(m0$municipalitytse))
## m0$GEOCODIG_M <- m0$geocodig_m
## m0 <- merge(res,m0,by.x=c("municipality"),by.y=c("municipalitytse"),all.x=TRUE)


## ## map for current state
## m2 <- merge.sp(m1[m1@data$SIGLA==snow,],m0,by="GEOCODIG_M")
## m2$votes[is.na(m2$votes)] <- 0

## ## breaks for color ramp
## ##bs <- seq(0,max(m2$vote_prop,na.rm=TRUE),length=9)
## bs <- c(0,.025,.05,.1,.2,.4,.6,.8,1)

## library(colorspace)
## alpha.now <- .95
## ##col.now <- alpha(diverge_hcl(length(bs)),alpha.now)
## col.now <- alpha(sequential_hcl(length(bs)),alpha.now)

## m2 <- color.heat(m2,"vote_prop",col.vec=col.now,
##                  breaks=bs,reverse=TRUE)
## m2@data$zCat <- as.character(m2@data$zCat)
## m2@data$zCat[is.na(m2@data$zCat)] <- "transparent"

## m2@data$parea <- with(m2@data,votes_total/sum(votes_total))
## m2@data$pone <- with(m2@data,votes/sum(votes))

## ##FIX: create table for description.
## ##FIX: microregiao for whole brazil map.
## ## creates a KML file containing the polygons of South Africa, Switzerland, and Canada


## ##sapply(m2@polygons,function(poly) list(ID=poly@ID,labpt=poly@labpt,area=poly@area))
## ## get polygons by municipality with the largest areas

## plot.heat(m2,"vote_prop",state.map=NULL,col.vec=col.now,breaks=bs,reverse=TRUE)

## ## plot(m2)
## ## with(m2@data,points(x,y,cex=20*(votes_total/sum(votes_total)),pch=19,col=as.character(zCat)))
## ## ##with(m2@data,text(x,y,label=municipality_tse06,cex=.3))


## ## this is likely the best
## p <- qplot(x,y,colour=vote_prop,data=subset(m2@data,votes>0),label=municipality_tse06,alpha=I(2/3),size=votes)+theme_bw()+coord_equal() +scale_area(name="votes",to = c(0.001, 20),breaks=c(100,1000,10000))

## p

## p+geom_text(data=subset(m2@data,votes>0),aes(size=sqrt(votes_total)))











## pointKml <- function(data,title='Hello',description='World!',file="~/Desktop/tmp.kml") {
##   kmlStyles <- sapply(1:nrow(data),function(i){
##     paste(
##           '<Style id="',data$id[i],'">
##                        <IconStyle>
##                          <Icon>
##            <href>',data$image[i],'</href>
##              </Icon>
##                </IconStyle>
##                  </Style>',sep='')
##   })
##   kmlPoints <- sapply(1:nrow(data),function(i) {
##     paste('<Placemark>',
##           '<name><![CDATA[',data$name[i],']]></name>',
##           '<description><![CDATA[',data$description[i],']]></description>',
##           '<styleUrl>#',data$id[i],'</styleUrl>',
##           '<Point>',
##           '<coordinates>',data$x[i],",",data$y[i],0,'</coordinates>',
##           '</Point>',
##           '</Placemark>'
##           ,sep='')
##   })
##   ## pics
##   sapply(1:nrow(data),function(i) {
##     with(dnow[i,],{
##       png(file=paste("~/Desktop/images/pic",id,".png",sep=''),bg="transparent",heigh=200,width=200,pointsize=1)
##       ##print(grid.circle(gp=gpar(fill=as.character(zCat[i]))))
##       print(grid.circle(r=size,gp=gpar(fill=alpha("red",.75))))
##     }
##          )
##     dev.off()
##   })
##   cat('<?xml version="1.0" encoding="UTF-8"?>
##     <kml xmlns="http://earth.google.com/kml/2.2">
##       <Document>
##         <name>',title,'</name>
##           <description><![CDATA[<i>',description,'</i>]]></description>',
##       kmlStyles,
##       kmlPoints,
##       '</Document></kml>',file=file)  
## }

## dnow <- m2@data
## dnow$id <- dnow$GEOCODIG_M
## dnow$image <- paste("http://files.eduardoleoni.com/pic",dnow$id,".png",sep='')
## dnow$name <- dnow$municipality_tse06
## dnow$description <- dnow$votes
## mm <- function(x,lo,hi) ifelse(x<lo,lo,ifelse(x>hi,hi,x))
## dnow$size <- with(dnow,(votes/max(votes)))*.5

## pointKml(dnow,file="~/Desktop/tmp.kml")

## system("scp ~/Desktop/images/pic*.png leoniedu@cluelessresearch.com:files.eduardoleoni.com/.")

## system("scp ~/Desktop/tmp.kml leoniedu@cluelessresearch.com:files.eduardoleoni.com/tmp6.kml")






## ###old


## qplot(x,y,size=votes_total/sum(votes_total),colour=vote_prop,data=m2@data,geom="text",label=municipality_tse06)


## qplot(x,y,colour=vote_prop,data=m2@data,alpha=I(1/2),size=parea)+theme_bw()+coord_map() +scale_area("votes")








## dir.now <- "~/Desktop/"

## ## add Brazil states layer?
## sw <- m2
## tokml(sw,"BR",compress=1)

## for (state.now in states) {  
##   sw <- m2[which(m2@data$state==state.now),]
##   tokml(sw,state.now)
## }

  




## ## bioids
## res <- dbGetQuery(connect,"select * from br_bioidtse where year=2006")

## res <- dbGetQuery(connect,"select * from br_vote_mun limit 10")


## v1 <- dbGetQuery(connect,"select a.bioid, b.*    from
##  (select * from br_bioidtse where year=2006 limit 1) as a,
##  (select * from br_vote_mun where  year=2006 and office='Deputado Federal') as b 
##  where a.candidate_code=b.candidate_code and
##  a.state=b.state")


## ## votes by municipality
## v <- dbGetQuery(connect,"select a.bioid, b.*    from
##  (select * from br_bioidtse where year=2006) as a,
##  (select * from br_vote_mun where  year=2006 and office='Deputado Federal') as b 
##  where a.candidate_code=b.candidate_code and
##  a.state=b.state")


        
