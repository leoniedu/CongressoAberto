# Computes party statistics and plots party graphs
# Outputs: Table of party stats
#          Table of ranks in party stats
#          Graphs
#
# Currently:
# Does everything in LONG format 
# Using all roll calls, independent of quorum, as long as not secrete votes
# Withexec 
#       Considers only votes that were actually taken. If legislator did not vote, he is counted as NA (could, conceivably, refine this)
#       Relative voting with government is measured only over votes that the executive declared position
# 09-28 Added number of women per party: computes directly from current composition of legislature and adds to output tables in the end
#
# TODO
# Create map of parties
# Check the U functions, whether they are necessary and working#
library(ggplot2)

## FIX: WNOMINATE DOES NOT WORK ON THE SERVER (YET)
##library(wnominate)


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
## Read in the Basic Long Tables ######################################################
rf()
connect.db()
legis <- j <-53
wnall <- vector(mode="list",length=length(legis))
names(wnall) <- legis

rcnow <- dbGetQuery(connect,    ### Turned "off" the "conversion" function (dgGetQueryU) due to incompatibility with my system
                    paste("select * from br_votacoes where legis=",j)
                    )
rc <- dbGetQuery(connect,
                 paste("select t1.*, t2.legis  from br_votos as t1, br_votacoes as t2 where  t1.rcvoteid=t2.rcvoteid AND t2.legis=",j)   #rcfile
                 )



rc$quorum <- with(rc, ave(!(rc%in%c("Ausente","Obstrução")),rcvoteid,FUN=sum))
#rc <- subset(rc,quorum>256)   #Turned off subseting
rc$secret <- with(rc, ave(!(rc%in%c("Presente","Ausente")),rcfile,FUN=sum))==0  #TRUE IS SECRET VOTE
rc <- subset(rc,secret==FALSE)   #Droping secret votes
rc$partyR <- recode.party1(rc$party)
rc$biopartyR <- with(rc,paste(bioid,partyR,sep=";"))
rc$rcr <- car::recode(rc$rc,"'Sim'=1;'Ausente'=9;else=0")  #orgiinal coding is Abtenção, Não, Sim, Obstrução, and for secret votes Presente, Ausente.
                                        #Here we're lumping Absteção, Não, Obstrucao and Presente together

rc.leaders <- dbGetQuery(connect,
                         paste("select t1.*, t2.legis  from br_leaders as t1, br_votacoes as t2 where  t1.rcvoteid=t2.rcvoteid AND t2.legis=",j)
                         )
rc.leaders$partyR <- recode.party1(rc.leaders$party)
rc.leaders$rcr <- car::recode(rc.leaders$rc,"'Sim'=1;'Ausente'=9;else=0")  #orgiinal coding is Abtenção, Não, Sim, Obstrução, and for secret votes Presente, Ausente.
                                        #Here we're lumping Absteção, Não, Obstrucao and Presente together                       

rcnew <- merge(rc,rc.leaders,by=c("rcvoteid","partyR","legis"),suffixes=c("",".ldr"),all.x=TRUE) #this excludes gov votes, but keeps parties without leaders
rm(rc)
gc()

rc.gov <- subset(rc.leaders,party=="GOV")[,c("rcvoteid","rc","rcr")] #sepparate out the gov votes from leaders, to add as variables in rcparty
rcnew <- merge(rcnew,rc.gov,by="rcvoteid",suffixes=c("",".gov"),all.x=TRUE)
rcnew$withldr <- as.numeric(with(rcnew,rcr.ldr==rcr))
rcnew$withgov <- as.numeric(with(rcnew,rcr.gov==rcr))
##rcnew is the modified long table with legislator/vote observations


#Assemble a PARTY/VOTE table
#Functions to compute variable of interest while creating party/vote observations
    withgov <- function(d) {sum(d$withgov)} #not using na.rm so that votes where gov didn't declare show up as NA
    withldr <- function(d) {sum(d$withldr)}
    yea <- function(d) {sum(d$rcr==1,na.rm=TRUE) }
    nay <- function(d) {sum(d$rcr==0,na.rm=TRUE)} #extend "concept" of nay
    absent <- function(d) {sum(d$rcr==9,na.rm=TRUE)}
#Create table with party/vote observations
rcparty <- ddply(rcnew, .(partyR,rcvoteid,rc.gov,rc.ldr), "nrow") #rc.gov and rc.ldr do not varry with rcvoteid, so this is okay!
rcparty <- merge(rcparty,ddply(rcnew, .(partyR,rcvoteid), "withgov"),by=c("partyR","rcvoteid"))
rcparty <- merge(rcparty,ddply(rcnew, .(partyR,rcvoteid), "withldr"),by=c("partyR","rcvoteid"))
rcparty <- merge(rcparty,ddply(rcnew, .(partyR,rcvoteid), "yea"),by=c("partyR","rcvoteid"))
rcparty <- merge(rcparty,ddply(rcnew, .(partyR,rcvoteid), "nay"),by=c("partyR","rcvoteid"))
rcparty <- merge(rcparty,ddply(rcnew, .(partyR,rcvoteid), "absent"),by=c("partyR","rcvoteid"))
rcparty$rice <- (abs(rcparty$yea-rcparty$nay))/(rcparty$yea+rcparty$nay)
   divisive <- function(v) {  yea <- v$rcr==1 #computes the overall rice score or divisiveness of each vote
                              nay <- v$rcr==0 
                              div <- ifelse(abs(sum(yea)-sum(nay))/ (sum(yea)+sum(nay))>0.8,FALSE,TRUE)  
                              return(div)}    
            
   winningside <- function(v) {yea <- sum(v$rcr==1,na.rm=TRUE) #computes the overall winning position in the each vote
                                nay <- sum(v$rcr==0,na.rm=TRUE) 
                                res <- ifelse(yea>nay,"Sim","Não")
                                return(res) }
divisive.vote <- ddply(rcnew, .(rcvoteid), "divisive") #whether each vote was divisive
winning.side <- ddply(rcnew, .(rcvoteid), "winningside")  #the majority position in each vote
rcparty <- merge(rcparty,divisive.vote,by=c("rcvoteid"),all.x=TRUE)
rcparty <- merge(rcparty,winning.side,by=c("rcvoteid"),all.x=TRUE)
rcparty$govdeclared <- car::recode(rcparty$rc.gov,"c('Sim','Não','Obstrução')=TRUE;else=FALSE")
rm(rcnew)
gc()

#Compute party summary statistics over the entire period
current.size <-  rcparty[which(rcparty$rcvoteid==max(rcparty$rcvoteid)),c("partyR","nrow")] #
ave.size <- as.matrix(by(rcparty$nrow,rcparty$party,na.rm=TRUE,mean))
cohesionALL <- as.matrix(by(rcparty$rice,rcparty$party,na.rm=TRUE,mean))
share.absent <-  as.matrix(by(rcparty$absent/rcparty$nrow,rcparty$party,na.rm=TRUE,mean))
rcpartyDIV <- subset(rcparty,divisive==TRUE)
cohesionDIV <- as.matrix(by(rcpartyDIV$rice,rcpartyDIV$party,na.rm=TRUE,mean))
rcpartyGOV <- subset(rcparty,govdeclared==TRUE) #With government stats are computed only over votes in which the government declares votes
with.execALL <- as.matrix(by(rcpartyGOV$withgov/rcpartyGOV$nrow,rcpartyGOV$party,mean,na.rm=TRUE) )
rcpartyGOVDIV <- subset(rcparty,divisive==TRUE & govdeclared==TRUE)
with.execDIV <- as.matrix(by(rcpartyGOVDIV$withgov/rcpartyGOVDIV$nrow,rcpartyGOVDIV$party,na.rm=TRUE,mean) )
with.majorityALL <- as.matrix(by(rcparty$winningside==rcparty$rc.ldr,rcparty$party,na.rm=TRUE,sum)) / length(unique(rcparty$rcvoteid))
with.majorityDIV <- as.matrix(by(rcpartyDIV$winningside==rcpartyDIV$rc.ldr,rcpartyDIV$party,na.rm=TRUE,sum))/ 
                                    length(unique(rcpartyDIV$rcvoteid)) #with.majority is measured by leader's vote (not majority of party)
                                                                        #its a share of ALL votes, not necessarily of those in which leadership declared a vote
                                                                        #large parties leadership declares votes in almost all votes, to check see
                                                                        #party.declared <- length(unique(rcparty$rcvoteid))- as.matrix(by(is.na(rcparty$rc.ldr),rcparty$party,sum))

rm(rcpartyGOV,rcpartyGOVDIV)



#Get number of women in current composiiton of congress
depcurrent <- dbGetQuery(connect,    ### Turned "off" the "conversion" function (dgGetQueryU) due to incompatibility with my system
                    paste("select * from br_deputados_current")
                    )
depcurrent$sex <- 0
depcurrent$sex[grep("Senhora", depcurrent$title)] <- 1
wom <- table(depcurrent$party,depcurrent$sex)[,2]
rm(depcurrent)


#Assemble outrput 
party.data <- data.frame(current.size=0,
                         ave.size = round(ave.size,2) ,
                         cohesionALL = round(cohesionALL,3),
                         share.absent = round(100*share.absent,1),
                         with.execALL = round(100*with.execALL,1),
                         with.execDIV = round(100*with.execDIV,1),
                         with.majorityALL = round(100*with.majorityALL,1),
                         with.majorityDIV = round(100*with.majorityDIV,1)
                         )
party.data[current.size$party,"current.size"] <- current.size$nrow  #merge like this because parties might be missing in last vote
party.data <- party.data[-which(rownames(party.data)=="S.Part."),]
party.data$share.wom <- round(100*ifelse(is.na(wom[rownames(party.data)]),0,wom[rownames(party.data)]/party.data$current.size),2) #add number of women per party
party.data <- party.data[which(party.data$ave.size>5),]  #report only parties greater than 5 legislators


party.data.ranks <- data.frame(round(nrow(party.data)-apply(party.data,2,rank,ties.method="max")+1))
party.data.ranks$partyname <-party.data$partyname <- rownames(party.data)      
party.data.ranks$partyid <-  party.data$partyid <- car::recode(party.data$partyname,"
                       'PRB'=10;'PP'=11;'PDT'=12;'PT'=13;'PTB'=14;'PMDB'=15;'PSTU'=16;'PDC'=17;
                       'PSC'=20;'PR'=22;'PPS'=23;'DEM'=25;'PAN'=26;'PRTB'=28;'PHS'=31;'PMN'=33;'PTC'=36;'PRP'=38;
                       'PSB'=40;'PSD'=41;'PV'=43;'PRP'=44;'PSDB'=45;'PSOL'=50;'PST'=52;
                       'PCdoB'=65;'PTdoB'=70;'PMSD'=75;'PPN'=76;'PCDN'=78;'PFS'=84;else=0")


       
dbRemoveTable(connect,"br_partyindices")
dbWriteTableU(connect,"br_partyindices",party.data)

dbRemoveTable(connect,"br_partyindices_rank")
dbWriteTableU(connect,"br_partyindices_rank",party.data.ranks)                       



            
### Plot governism graphs #####
for(i in 1:nrow(party.data)){
    setwd(paste(rf("images"),"/governism",sep=""))
    pty<-rownames(party.data)[i]
    the.party <- subset(rcparty,partyR==pty)
    the.party <- merge(the.party,rcnow,by="rcvoteid")
    the.party$rcdate <- as.Date(the.party$rcdate,format="%Y-%m-%d")
    share.withgov <- function(d) {round(mean(d$withgov/d$nrow, na.rm=TRUE)*100,1)}   #with gvt graphs  
    to.plot <- na.omit(ddply(the.party, .(rcdate), "share.withgov"))
    #To plot all votes (and not an average by day), replace previous two lines with the next two lines
    #to.plot <- the.party
    #to.plot$share.withgov <- round(to.plot$with.gov/to.plot$nrow*100,1)
    pdf(file=paste(pty,"governism.pdf",sep=""), bg="transparent", width=8, height=3) 
    par(mar=c(3,3,1.5,0.5))
    the.plot <- qplot(as.Date(rcdate), share.withgov, data = to.plot,xlab="",ylab="",ylim=c(0,100))
    print(the.plot + stat_smooth() + scale_x_date(major="6 months", format="%b-%Y") + labs(x = "", y = "Votos com o governo (em %)"))
    dev.off()
    convert.png(file=paste(pty,"governism.pdf",sep="")) #convert to png using ghostscript
}


### Plot typical party graphs #####
for(i in 1:nrow(party.data)){
    setwd(paste(rf("images"),"/typical",sep=""))
    pty<-rownames(party.data)[i]
    typical.pty <- data.frame(party=pty,
                              rc=factor(c(rep("Ausente",round(party.data[pty,"current.size"]*party.data[pty,"share.absent"]/100)),
                                   rep("Com Governo",round(party.data[pty,"current.size"]*party.data[pty,"with.execDIV"]/100)),
                                   rep("Contra Governo",max(0,party.data[pty,"current.size"]- #rounding could lead to -1 legislaotrs against the government
                                                           round((party.data[pty,"share.absent"]/100*party.data[pty,"current.size"]+
                                                             party.data[pty,"current.size"]*party.data[pty,"with.execDIV"]/100)))))))
    typical.pty$rc2 <- car::recode(typical.pty$rc,c("c('Com Governo','Contra Governo')='Presentes';else='Ausentes'"))  
    colvec <-c("red","darkblue","orange")
    colvec <- alpha(colvec,"1")
    theme_set(theme_grey(base_size = 10))
    pdf(file=paste(pty,"typical.pdf",sep=""), bg="transparent", width=8, height=3) 
    the.plot<- ggplot(typical.pty,  aes(x=rc2, fill=rc))  + 
        geom_bar(width=1) + labs(x=" ", y=" ") + 
        scale_y_continuous(name="Legisladores") +
        coord_flip() # +  opts(legend.position="none")
    print(the.plot)
    dev.off()
    convert.png(file=paste(pty,"typical.pdf",sep=""))
} 
