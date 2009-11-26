##FIX: rewrite so that you can call one legislator and get the maps
##FIX: is it writing out the code for the google maps?


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
usource(rf("R/ggShape.R"))




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
snow <- "AC"


for (snow in states[25:27]) {
##for (snow in c("PI","MA")) {    

  ## number of seats
  elected <- dbGetQuery(connect,paste("select * from br_vote_candidates where  office='DEPUTADO FEDERAL' AND year='2006' AND state='",snow,"' AND sit in ('MÉDIA','ELEITO')",sep=''))
  
  nseats <- nrow(elected)  
  ## let's try to plot one deputy
  ## get votes for all candidates in each municip
  res0 <- dbGetQuery(connect,paste("select municipality, state, sum(votes) as votes_total from br_vote_mun where  office='DEPUTADO FEDERAL' AND year='2006' AND state='",snow,"' AND elec_round=1 AND (candidate_code not in (95,96))  group by municipality",sep=''))
  ## all deps in state
  ## with names
  dep <- dbGetQuery(connect,paste("select a.*, b.* from br_bioidtse as a, br_bio as b where a.bioid=b.bioid AND a.office='DEPUTADO FEDERAL' AND a.year='2006' AND a.state='",snow,"'",sep=''))  
  ## table with all deps in state
  res <- dbGetQuery(connect,paste("select * from br_vote_mun where office='DEPUTADO FEDERAL' AND year='2006' AND  state='",snow,"'",sep=''))
  res.it <-  dbGetQuery(connect,paste("select * from br_bioidtse where office='DEPUTADO FEDERAL' AND year='2006' AND  state='",snow,"'",sep=''))
  res.mun <-  dbGetQuery(connect,paste("select * from br_municipios where year='2006' AND  state_tse06='",snow,"'",sep=''))
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
  res.m$dominance <- res.m$`Votos conquistados\nno município (%)` <- round(res.m$vote_prop*100)  
  
  if (FALSE) {
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
      fn <- webdir(paste("images/elections/2006/","deputadofederal",snow,".RData",sep=""))
      save(p,file=fn)
  }
  
  ## plot one
  m2 <- m1[m1$SIGLA==snow,]
  m <- get_borders(m2)  
  m$rowid <- 1:nrow(m)
  m$x <- m$long
  m$y <- m$lat
  m <- m[order(m$rowid,m$id),]
  p <- qplot(x,y,data=m,geom="polygon",fill=I("gray90"),colour=I("white"),group=id, size=I(.2))
  p <- p+coord_equal()
  ##p  
  i <- 1
  
  for (i in 1:nrow(dep)) {    

      print(i)
      print(snow)
      cand <- dep$candidate_code[i]
      pnew <- p+geom_point(aes(x=x,y=y,
                               ##colour=`Votos conquistados\nno município (%)`,
                               label=municipality_tse06,size=eq),
                           ,alpha=I(2/3),
                           data=subset(res.m,candidate_code==cand))
      pnew <- pnew+scale_area(name="Votos",to = c(0.001, 20),limits=c(0,1),
                              breaks=bs,
                              labels=round(bs*eq1/100)*100)
      pnew <- pnew+theme_bw()
      pnew <- pnew + opts(axis.ticks = theme_blank(),
                          axis.text=theme_blank(),
                          panel.grid.minor=theme_blank(),
                          panel.grid.major=theme_blank(),
                          axis.title.x=theme_blank(),
                          axis.title.y=theme_blank(),
                          axis.text.x=theme_blank(),
                          legend.key.size = unit(1.2, 
                          "lines"),
                          axis.text.y=theme_blank())+
                              scale_x_continuous(name="")+
                                  scale_y_continuous(name="")
      legend_size <- pnew + opts(keep="legend_box")      
      pnew <- pnew+geom_point(aes(x=x,y=y, colour=`Votos conquistados\nno município (%)`, label=municipality_tse06,size=eq), ,alpha=I(2/3), data=subset(res.m,candidate_code==cand))      
      dnow <- subset(res.m,candidate_code==cand)
      pnew <- pnew+scale_colour_continuous(limits=c(0,100))
      vmax <- which.max(dnow$votes)
      pmax <- which.max(dnow$dominance)      
      pnew <- pnew+geom_text(mapping=aes(label=municipality_tse06),data=dnow[unique(c(pmax,vmax)),],size=4,vjust=2)
      fn <- webdir(paste("images/elections/2006/","deputadofederal",snow,cand,".png",sep=""))
      legend_color<- qplot(x=rnorm(100),fill=rnorm(100)) +scale_fill_continuous(name="Votos (%)\nno município", limits=c(0,100))
      legend_color <- legend_color+ opts(keep = "legend_box",
                                         legend.key.size = unit(2, "lines"),
                                         legend.text = theme_text(size = 12),
                                         legend.title = theme_text(size = 17,
                                         face = "bold", hjust = 0))
      legend_size <- legend_size+ opts(keep = "legend_box",
                                       legend.key.size = unit(2, "lines"),
                                       legend.text = theme_text(size = 12),
                                       legend.title = theme_text(size = 17,
                                       face = "bold", hjust = 0))      
      ## Plotting
      ## Plot Layout Setup
      Layout <- grid.layout( nrow = 1, ncol = 3,
                            widths = unit (c(2,.7,.7), c("null", "null")),
                            heights = unit (c(2,2,2), c("null", "null")) 
                            )
      vplayout <- function (...) {
          grid.newpage()
          pushViewport(viewport(layout= Layout))
      }
      subplot <- function(x, y) viewport(layout.pos.row=x, layout.pos.col=y)
      png(file=fn,height=600, width=800)
      vplayout()
      print(pnew + opts(legend.position="none"), vp=subplot(1,1))
      print(legend_size, vp=subplot(1,2))
      print(legend_color, vp=subplot(1,3))
      dev.off()
      gc()      
  }
  
}



res.mun <-  dbGetQuery(connect,paste("select * from br_municipios where year='2006'",sep=''))

res <- dbGetQuery(connect,paste("select * from br_vote_mun where office='PRESIDENTE' AND year='2006' and candidate_code=13",sep=''))

res.xy <- m1@data
res$municipality <- as.numeric(as.character(res$municipality))
res.mun$municipalitytse <- as.numeric(as.character(res.mun$municipalitytse))
res.m <- merge(res.mun,res,by.x=c("municipalitytse","state_tse06","year"),
               by.y=c("municipality","state","year"))
res.m <- merge(res.m,res.xy,
               by.x=c("geocodig_m"),
               by.y=c("GEOCODIG_M"),all.y=TRUE)



## create a spatial points data frame from the state data:
statesp <- data.frame(subset(res.m, state_tse06=="BA",
                             select=c(votes, x, y, municipality_tse06, state_tse06) ))
statesp$Name <- statesp$municipality_tse06
statesp$municipality_tse06 <- NULL
coordinates(statesp) = cbind(statesp$x,statesp$y)
statesp$x <- statesp$y <- NULL
## how to produce three layers:

osmMap(webmaps::layer(statesp,name="state1",lstyle(fillColor="red")), outputDir="/Users/eduardo/tmp")


var state1 = new OpenLayers.Layer.GML("state1","state1.gml",{
    strategies: [new OpenLayers.Strategy.Cluster()],
    projection: new OpenLayers.Projection("EPSG:4326"),
    styleMap: new OpenLayers.Style(OpenLayers.Util.applyDefaults({'fillColor': 'red'
                                                              },OpenLayers.Feature.Vector.style['default']))    
});


var style = new OpenLayers.Style({
    strokeWidth: "${width}"
  } , {
    context: {
      width: function(feature) {
        return (feature.cluster) ? 2 : 1;
      }
    }
  );

var state1 = new OpenLayers.Layer.GML("state1","state1.gml",{
    strategies: [
                 new OpenLayers.Strategy.Cluster()
                 ],
    projection: new OpenLayers.Projection("EPSG:4326"),

