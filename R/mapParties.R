### Creates a map of electoral strength of parties by state
### Does not need to be updated
library(RColorBrewer)
setwd(paste(rf("images"),"/partymaps",sep=""))  #set appropriate directory (to save pdf and png files)



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


#Get the electoral data
the.office <- "DEPUTADO FEDERAL"  #could be based on other elections
elec <- dbGetQuery(connect,    
                    paste("select year, office, candidate_code, votes, state  from br_vote_mun where office='",the.office,"'",sep="")
                    )
elec$party <- as.numeric(substr(elec$candidate_code,1,2)) #create the party variable from candidate code
vote <- function(d) {sum(d$votes,na.rm=TRUE)}  #function to sum votes
elec.stateparty <- ddply(elec, .(party,state), "vote") #summarize data by state/party
elec.totstate <- ddply(elec, .(state), "vote")
elec <- merge(elec.stateparty,elec.totstate,by="state",suffixes=c("",".total"))
elec$vs <- elec$vote/elec$vote.total*100
rm(elec.stateparty,elec.totstate,the.office)


#Get the parties to plot
parties <- dbGetQuery(connect,    
                    paste("select t1.*, t2.name  from br_partyindices as t1, br_parties_current as t2 where t1.partyid=t2.number")
                    )

#Plot
m1 <- readShape.cent(rf("data/maps/BRASIL.shp"),"UF")

for(i in 1:nrow(parties)){
    pty<-parties$partyname[i]
    party.vs <- subset(elec,party==parties$partyid[i])[,c("state","vs")]
    map.elec(party.vs,large=TRUE,percent=TRUE)
   
}



#function is in caFunctions.R, just repeated here as a backup
#map.elec <- function(the.data, filenow='',title='', large=TRUE, percent=FALSE) { #plots pdf and png maps of party's electoral strengh in each state
#  if (percent) {
#    pct <- 100
#    legend.title <- "Votos para Deputado Federal (%)"    
#  } else {
#    pct <- 1
#    legend.title <- "Votos para Deputado Federal"    
#  }
#  the.data$UF <- the.data$state
#  m2 <- merge.sp(m1,the.data,by="UF")
#  par(bg="grey90")
#  n1 <- 4
#  seqx <- c(0,0.01,0.05,.1,.2,1)*pct
#  tmp.col <- brewer.pal((length(seqx)-2),"Blues")
#  col.vec <- c("white",tmp.col) 
#  #idea here is to include white as first color
#    if (large) {
#        pdf(file=paste(pty,"map.pdf",sep=""), bg="transparent", width=6, height=6) 
#        par(mai=c(0,0,0.6,0))
#        plot.heat(m2,NULL,"vs",title=legend.title,breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,main=filenow)
#        with(m2@data,text(x,y,UF,cex=0.8),col="grey30")
#        mtext(title,3, cex=.9)
#          dev.off()
#        convert.png(file=paste(pty,"map.pdf",sep=""))#
#      } else {
#        pdf(file=paste(pty,"mapsmall.pdf",sep=""), bg="transparent", width=6, height=6) 
#        par(mai=c(0,0,0,0))
#        plot.heat(m2,NULL,"vs",breaks=seqx,reverse=FALSE,cex.legend=1,bw=1,col.vec=col.vec,main=filenow,plot.legend=FALSE)
#          dev.off()
#        convert.png(file=paste(pty,"mapsmall.pdf",sep=""))
#      }

#}


####
