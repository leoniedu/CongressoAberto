# In construction
#
# Need to group Bring functions together
# Redo all functions to operate on long table rather than wide one for speed gains?
# Check the U functions, whether they are necessary and working
# Using all roll calls, independent of quorum, as long as not secrete votes
# Withexec 
#       Considers only votes that were actually taken. If legislator did not vote, he is counted as NA (could, conceivably, refine this)
#       Relative voting with government is measured only over votes that the executive declared position
#
# 1) Assemble wide set
# 2) Run wnominate and keep in memory for later
# 3) Compute party indices, including wnominate
# 4) Compute legislator indices (Eduardo was already doing this
# 5) Return three objects with the previous issue for SQL storage and webpage usage
##
library(ggplot2)
library(wnominate)


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
  }
}


## FUNCTIONS TO COLLECT  ######################
convert.png <- function(file="tmp.pdf") { #Convert pdf figures to, temporarily hear, but erase later., SHould be in caFunctions.R
  file <- path.expand(file)
  if (.Platform$OS.type!="unix") {
    command <- paste('"c:/Program Files/gs/gs8.63/bin/gswin32.exe"'," -q -dNOPAUSE -dBATCH -sDEVICE=pngalpha -r300 -dEPSCrop -sOutputFile=",gsub(".pdf",".png",file)," ",file,sep='')
  } else {
    command <- paste(gs," -q -dNOPAUSE -dBATCH -sDEVICE=pngalpha -r300 -dEPSCrop -sOutputFile=",gsub(".pdf",".png",file)," ",file,sep='')
  }
  print(command)  
  system(command,wait=TRUE)
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
## ASSEMBLE THE WIDE TABLE ######################################################
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
  rc$quorum <- with(rc, ave(!(rc%in%c("Ausente","Obstrução")),rcfile,FUN=sum))
  #rc <- subset(rc,quorum>256)   #Turned off subseting
  rc$secret <- with(rc, ave(!(rc%in%c("Presente","Ausente")),rcfile,FUN=sum))==0  #TRUE IS SECRET VOTE
  rc <- subset(rc,secret==FALSE)   #Droping secret votes
  rc$partyR <- recode.party1(rc$party)
  rc$rcr <- car::recode(rc$rc,"'Sim'=1;'Ausente'=9;else=0")  #orgiinal coding is Abtenção, Não, Sim, Obstrução, and for secret votes Presente, Ausente.
                                                             #Here we're lumping Absteção, Não, Obstrucao and Presente together
  rc$biopartyR <- with(rc,paste(bioid,partyR,sep=";"))
  rcc <- recast(rc,bioid+partyR+biopartyR~rcvoteid,measure.var="rcr")
  #get leaders votes into wide format
  rc.leaders <- dbGetQuery(connect,
                    paste("select t1.*, t2.legis  from br_leaders as t1, br_votacoes as t2 where  t1.rcvoteid=t2.rcvoteid AND t2.legis=",j)
                    )
  rc.leaders$rcr <- car::recode(rc.leaders$rc,"'Sim'=1;'Liberado'=9;else=0")  #note que estou colocando liberado como 9, para ser diferente de NA (sem indicacao)
  rc.leaders$partyR <- recode.party1(rc.leaders$party)
  rcc.leaders <- recast(rc.leaders,partyR~rcvoteid,measure.var="rcr") #leaving block out because otherwise we get more ovservations by party

####### ESTIMATE IDEAL POINTS ##################################################################################
#  rcr <- rollcall(data.frame(rcc)[,-c(1:3)],yea=1, nay=0, missing=NA, notInLegis=9,legis.names=rcc[,3],legis.data=rcc[,1:3])
#  pol <- c(f(c("PFL","DEM")),f(c("PPB","PP")))
#  wnall[[paste(j)]] <- wnominate(rcr,polarity=pol,dims=2)  
#
#  Aqui, pegar votos das liderancas e incluir no rcc
#################################################################################################################

#### PARTY INDICES ##############################################################################################
comp.divisiveness <- function(rollcalls,vote.string="\\d",party.var="partyR",criteria=NULL){ #This function computes a matrix, not a summary, to be called by other
    parties <- levels(factor(rollcalls[,party.var]))        #returns either a vector of rice indices or a logical vector of whether the vote was competitive
    x <- rollcalls[,grep(vote.string,names(rollcalls),perl=TRUE)]
    rice <- vector(length=ncol(x))
    names(rice)<-colnames(x)
    yea <- apply(x==1,2,sum,na.rm=TRUE)
    nay <- apply(x==0,2,sum,na.rm=TRUE)
    for(i in 1:ncol(x)){rice[i] <- (abs(yea[i]-nay[i]))/(yea[i]+nay[i]) }
    if(is.numeric(criteria)){rice<- rice<=criteria}
    return(rice)
}
comp.rice <- function(rollcalls,vote.string="\\d",party.var="partyR",save.plot=FALSE){ #x is a typical roll call matrix, 
                                                                #vote.string is the string that identifies columns with votes
                                                                #part.var is the column with the party names
    parties <- levels(factor(rollcalls[,party.var])) #has to be factor level to later match the "by" command. Cannot use unique()
    x <- rollcalls[,grep(vote.string,names(rollcalls),perl=TRUE)]
    rice.matrix <- matrix(NA,nrow=length(parties),ncol=ncol(x),dimnames=list(c(parties),c(names(x))))
    for(i in 1:ncol(x)){
        yea <- as.numeric(by(x[,i]==1,rollcalls$partyR,sum,na.rm=TRUE))
        nay <- as.numeric(by(x[,i]==0,rollcalls$partyR,sum,na.rm=TRUE))
        rice.matrix[,i] <- (abs(yea-nay))/(yea+nay)
    }
    mean.by.party <- apply(rice.matrix,1,mean,na.rm=TRUE)
    #Plot cohesion over all votes?
    return(mean.by.party)
}


comp.abs <- function(rollcalls,vote.string="\\d",party.var="partyR"){ #x is a typical roll call matrix, 
            parties <- levels(factor(rollcalls[,party.var])) 
            x <- rollcalls[,grep(vote.string,names(rollcalls),perl=TRUE)]
            abs.matrix <- relabs.matrix <- size.matrix <- matrix(NA,nrow=length(parties),ncol=ncol(x),dimnames=list(c(parties),c(names(x))))
            for(i in 1:ncol(x)){
                size.matrix[,i] <-  as.numeric(by(is.na(x[,i])==FALSE,rollcalls$partyR,sum,na.rm=TRUE)) 
                abs.matrix[,i] <- as.numeric(by(x[,i]==9,rollcalls$partyR,sum,na.rm=TRUE))
                relabs.matrix[,i]   <- as.numeric(by(x[,i]==9,rollcalls$partyR,sum,na.rm=TRUE))/
                                        size.matrix[,i]#size of parties in each vote
            }
            size.last.vote <- size.matrix[,ncol(size.matrix)]
            size.by.party <-  apply(size.matrix,1,mean,na.rm=TRUE)
            abs.by.party <-apply(abs.matrix,1,mean,na.rm=TRUE)
            relabs.by.party <- apply(relabs.matrix,1,mean,na.rm=TRUE)
            output<-list(current.size=size.last.vote,ave.size=size.by.party,absentees=abs.by.party,share.absent=relabs.by.party)
            return(output)
}
comp.withexec <- function(rollcalls,leaders,vote.string="\\d",party.var="partyR",save.plot=FALSE){ #x is a typical roll call matrix, 
            parties <- levels(factor(rollcalls[,party.var])) 
            x <- rollcalls[,grep(vote.string,names(rollcalls),perl=TRUE)]
            x.leaders <- leaders[,grep(vote.string,names(leaders),perl=TRUE)]
            if(ncol(x)!=ncol(x.leaders)) stop("Roll call matrix does not match leadership matrix")
            we.matrix <- relwe.matrix <- relvotingwe.matrix <- matrix(NA,nrow=length(parties),ncol=ncol(x),dimnames=list(c(parties),c(names(x))))
            exec.line <-  which(leaders$partyR=="GOV")
            exec.declared <- which(is.na(x.leaders[exec.line,])==FALSE)
            divisive.votes <- which(comp.divisiveness(rollcalls,criteria=0.5))
            for(i in 1:ncol(x)){
                we.matrix[,i] <- as.numeric(by(x[,i]==x.leaders[exec.line,i],rollcalls$partyR,sum,na.rm=TRUE)) #number of votes with president
                relwe.matrix[,i] <- we.matrix[,i] / #number of votes with president
                                          as.numeric(by(is.na(x[,i])==FALSE,rollcalls$partyR,sum,na.rm=TRUE)) #size of party in EACH VOTE
                relvotingwe.matrix[,i] <- we.matrix[,i] / #number of votes with president
                                          as.numeric(by(is.element(x[,i],c(0,1)),rollcalls$partyR,sum,na.rm=TRUE)) #legislators actually voting in each vote
              
            }          
            we.by.party <-apply(we.matrix[,exec.declared],1,mean,na.rm=TRUE)
            relwe.by.party <- apply(relwe.matrix[,exec.declared],1,mean,na.rm=TRUE)        
            relwedivisive.by.party <-  apply(relwe.matrix[,intersect(exec.declared,divisive.votes)],1,mean,na.rm=TRUE)
            relvotingwedivisive.by.party <-  apply(relvotingwe.matrix[,intersect(exec.declared,divisive.votes)],1,mean,na.rm=TRUE)
            output<-list(we= we.by.party ,
                        relwe=relwe.by.party, 
                        relwedivise=relwedivisive.by.party,
                        relvotingwedivise=relvotingwedivisive.by.party)
            
            if(save.plot==TRUE){    #Plot governmentness over all votes?
            setwd(paste(rf("images"),"/governism",sep=""))
            for(i in rownames(relwe.matrix)){
            if(sum(is.na(relwe.matrix[i,exec.declared]))!=0){next} #plot only for main parties
                pdf(file=paste(rownames(relwe.matrix)[i],"governism.pdf",sep=""), bg="transparent", width=10, height=3) 
                par(mar=c(3,3,0.5,0.5))
                plot(relwe.matrix[i,exec.declared],type="l",ylab="",xlab="",xaxt="n",ylim=c(0,1))
                lines(lowess(relwe.matrix[i,exec.declared], f=4),  col = 2)
                mtext(i,side=3)
                low.gvtness <- which(relwe.matrix[i,exec.declared]<=quantile(relwe.matrix[i,exec.declared], probs = 0.005,na.rm=TRUE))
                axis(side=1,at=low.gvtness,labels=names(low.gvtness),las=2,cex=0.7)
                dev.off()
                convert.png(file=paste(rownames(relwe.matrix)[i],"governism.pdf",sep="")) #convert to png using ghostscript
                print(i)
                flush.console()
            } }
            return(output)
}


comp.leadership <- function(rollcalls,leaders,vote.string="\\d",party.var="partyR"){ #x is a typical roll call matrix, 
            parties <- levels(factor(rollcalls[,party.var])) 
            x <- rollcalls[,grep(vote.string,names(rollcalls),perl=TRUE)]
            x.leaders <- leaders[,grep(vote.string,names(leaders),perl=TRUE)]
            if(ncol(x)!=ncol(x.leaders)) stop("Roll call matrix does not match leadership matrix")
            decl.matrix <- matrix(NA,nrow=length(parties),ncol=ncol(x),dimnames=list(c(parties),c(names(x))))
            #for(i in 1:ncol(x)){
            #     decl.matrix[,i] <-  #number of votes with president
            #}
            declared.votes <- apply(is.na(leaders)==FALSE,1,sum,na.rm=TRUE)
            names(declared.votes) <- leaders[,1]
            return(output)           
}

#comp.with.leader <- function
# TO DO
# Frequency in which leader whips votes, cohesion when leader whips
# Create function indicating when leader whipped, and apply it inside the rice function to recalculate whiping power!!!
# Think: difference in these votes is a proxy for whiping power!!!!
#           output<-list(leader.declares=,with.leader=) 


### CALL FUNCTIONS AND  ASSEMBLE ACTUAL TABLES 
we <- comp.withexec(rcc,rcc.leaders,save.plot=TRUE)
ab <-  comp.abs(rcc)
rc <- comp.rice(rcc)

party.data <- data.frame(current.size = ab[["current.size"]],
                         ave.size = round(ab[["ave.size"]],1),
                         cohesionALL = round(rc,2),
                         share.absentALL = round(100*ab[["share.absent"]],1),
                         with.execALL = round(100*we[[3]],1),
                         with.execDIVISIVE = round(100*we[[4]],1))
party.data$partyname <- rownames(party.data)
party.data$partyid <-  car::recode(party.data$partyname,"
                       'PRB'=10;'PP'=11;'PDT'=12;'PT'=13;'PTB'=14;'PMDB'=15;'PSTU'=16;'PDC'=17;
                       'PSC'=20;'PR'=22;'PPS'=23;'DEM'=25;'PAN'=26;'PRTB'=28;'PHS'=31;'PMN'=33;'PTC'=36;'PRP'=38;
                       'PSB'=40;'PSD'=41;'PV'=43;'PRP'=44;'PSDB'=45;'PSOL'=50;'PST'=52;
                       'PCdoB'=65;'PTdoB'=70;'PMSD'=75;'PPN'=76;'PCDN'=78;'PFS'=84;else=0")
party.data <- party.data[-which(party.data$partyname=="S.Part."),]
party.data <- party.data[which(party.data$ave.size>5),]  #report only parties greater than 5 legislators
party.data.ranks <- data.frame(round(nrow(party.data)-apply(party.data,2,rank)+1))
party.data.ranks$partyname <-party.data$partyname
party.data.ranks$partyid <-  party.data$partyid

dbRemoveTable(connect,"br_partyindices")
dbWriteTableU(connect,"br_partyindices",party.data)

dbRemoveTable(connect,"br_partyindices_rank")
dbWriteTableU(connect,"br_partyindices_rank",party.data.ranks)


##################### LEFT OVERS AND TEMPLATES ########################################################

if(1==2){

#### INDIVIDUAL INDICES
# Votes with exec
# Votes with leader
# Proximity with PT or DEM
# 1DM Wnominate



#save(wnall,file=rf("tmp/wnLegisYN.RDta"))

##load(file=rf("tmp/wn3.RDta"))

load(file=rf("tmp/wnLegisYN.RDta"))


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
                     paste("select * from br_votacoes where legis>=50")
                     )
rcnow <- rcnow[order(rcnow$rcdate),]

medians <- NULL
## now, for each roll call, calculated the median
for(i in 1:nrow(rcnow)) {
##for(i in 1:20) {
  if (i%%20==0) {
    print(paste(round(i/nrow(rcnow),2)*100,'%',sep=''))
  }
  ##for(i in 1:10) {
  ## first get the rc votes
  rc <- dbGetQueryU(connect,
                    paste("select * from br_votos where rcfile='",rcnow$rcfile[i],"'",sep='')
                    )
  rc <- merge(rc,rcnow[i,])
  rc$partyR <- recode.party(rc$party)
  ## merge with the wnominate date
  rc <- merge(rc,wb)
  rc <- with(rc,unique(data.frame(legisyear,rcdate,legis,
                                  ct=length(na.omit(dim1)),
                                  median1=median(dim1p,na.rm=TRUE))))
  medians <- rbind(medians,rc)
}
medians$rcdate <- as.Date(as.character(medians$rcdate))
medians$sup1 <- with(medians,ifelse(legisyear>=2003,median1*-1,median1))
### there is a roll call with too many missing party information, so we take it out.
medians <- (subset(medians,!(legisyear<2000 & median1<.55)))
medians$yearmonth <- (as.character(format(medians$rcdate,"%Y%m")))


medians.c <- recast(subset(medians, ct>510),
                    legisyear+legis+yearmonth~.,
                    measure.var="median1",
                    fun.aggregate=function(x) c(median=median(x),count=length(x)))

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
}
