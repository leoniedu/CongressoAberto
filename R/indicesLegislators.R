### DEPRECATED: use abstentionsWordpress.R






## Model the most absent legislators
## Basic idea: random effects model with legislator+roll call+party random effects.
## to be Updated monthly?

##library(lme4)
library(ggplot2)

## paths (put on the beg of R scripts)
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

source(rf("R/wordpress.R"))

connect.db()

connect.wp()

init.date <- Sys.Date()-365
final.date <- Sys.Date()
date.range <- paste(format(c(init.date+1,final.date-1),"%d-%m-%Y"))

use.current <- TRUE
##use.current <- FALSE

if (use.current) {
  cdeps <- dbGetQuery(connect,"select bioid from br_deputados_current")[,1]
} else {
  cdeps <- dbGetQuery(connect,"select bioid from br_bio")[,1]
}


sql <- paste("select  a.*, cast(b.rcdate as date) as rcdate  from br_votos as a, br_votacoes as b where a.rcfile=b.rcfile and (rcdate>cast('",init.date,"' as date) ) and (rcdate<cast('",final.date,"' as date) ) ",sep='')

res <- dbGetQueryU(connect,sql)

res <- subset(res,bioid%in%cdeps)

dim(res)

res$ausente <- res$rc=="Ausente"
res$presente <- as.numeric(res$rc!="Ausente")
## pfl -> dem
res$Partido <- recode.party(res$party)


##NOTE: ausente is by bioid, presente takes into account parties
fsum <- function(x) c(prop=sum(x)/length(x), total=length(x), count=sum(x))

presente <- recast(res,bioid+party~variable,measure.var="presente",id.var=c("bioid", "party"),fun.aggregate=fsum)

## recode parties - n largest are kept, others recoded as "outro";
presente$Partido <- recode.party(presente$party,n=8,label.other="Outros Partidos")
p <- qplot(presente_prop,data=presente,geom="histogram",fill=Partido)+scale_fill_brewer(palette="Set3")+theme_bw()
p <- p+coord_cartesian(ylim=c(0,50),xlim=c(0,1))
p <- p + scale_y_continuous("Número de deputados",breaks=seq(0,50,10),labels=c("",seq(10,50,10)))+scale_x_continuous(name="Presença em plenário",formatter="percent")
meanpres <- mean(presente$presente_prop)
p <- p+geom_vline(xintercept=meanpres,col=alpha("red",.75),size=2,stat=NULL)+annotate("text",x=meanpres-.2,y=45,label=paste("Média de\n presença: ",round(meanpres*100),"%",sep=''))


fn <- rf("images/camara/abstentions.pdf")
pdf(file=fn,width=6,height=4)
print(p)
dev.off()
convert.png(fn)


pp <- dbGetQuery(conwp,"select * from "%+%tname("posts")%+%" where post_title="%+%shQuote("Desempenho da Câmara"))$ID[1]










## Absenteism by date
rcs <- dbGetQueryU(connect, "select count(*) as n, rcvoteid, rc from br_votos group by rcvoteid, rc")
rcs$total <- with(rcs, ave(n, rcvoteid, FUN=sum))
rcs <- subset(rcs, rc=="Ausente")
rcs$ausente_prop <- with(rcs, n/total)
rcs <- merge(rcs, res2)

## plot elections
tmp <- rcs
tmp$votes <- 1-tmp$ausente_prop
pe <- function(p,year=2008,label="") {
  p <- p+geom_rect(xmin=getlegisdays(paste(year,"-10-01",sep='')),
                   xmax=getlegisdays(paste(year,"-10-30",sep='')),
                   ymin=-10,
                   ymax=1000,fill=alpha("gray80",.1)
                   )
  p <- p+geom_text(data=data.frame(legisday=getlegisdays(paste(year,"-04-01",sep='')), votes= 15,label=label,Legislatura="1995-1999"),aes(label=label),hjust=0, vjust=0,size=3.5)
  p
}
p <- ggplot(data=tmp,aes(x=legisday,y=votes,group=Legislatura))
p <- pe(p,year=2008,label="Eleições\nlocais")
p <- pe(p,year=2010,label="Eleições\nnacionais")
p <- p+geom_point(aes(colour=Legislatura), size=0.7)
##p <- p+stat_smooth(se=FALSE,size=2)
alphan <- .2
p <- p+stat_smooth(aes(colour=Legislatura),size=1.5,se=FALSE,method=lm,formula=y~splines::ns(x,5),alpha=.2)
##p <- p+stat_smooth(aes(colour=Legislatura),size=1,se=FALSE,method=lm,formula=y~splines::ns(x,3),alpha=.2)
p <- p ## +scale_colour_manual(values = c(alpha("darkgreen",alphan),alpha("darkblue",alphan), alpha("darkred",alphan),"red"))
## function for labels
fx <- function(x=1,year=2006) paste("Dez ",x+year,"\n(",x,'o. ano)',sep='')
p <- p+scale_x_continuous(name="",breaks=as.numeric(getlegisdays(paste(2008:2010,"-02-01",sep=''))),labels=fx(1:3),expand=c(0,0))
p <- p+coord_cartesian(ylim=c(0,1))
p <- p+scale_y_continuous(name="Presença nas votações nominais", breaks=seq(0,1,.2), formatter="percent")
p <- p+scale_colour_manual(values = c(alpha("darkgreen",alphan),alpha("darkblue",alphan), alpha("darkred",alphan),"red"))
p <- p+theme_bw()


fn <- "images/abstentions/byrc.pdf"
pdf(file=rf(fn),height=4,width=5)
print(p)
dev.off()

convert.png(rf(fn))









## page under "desempenho"
wpAddByTitle(conwp,post_title="Presença em plenário", post_category=data.frame(name="Headline",slug="headline"),
             post_content='<p><img width=400 src="/images/camara/abstentions.png" alt="Presença em plenário" /></p>
             <p><img width=400 src="/images/abstentions/byrc.png" alt="Presença em plenário" /></p>'
             ,
             post_type="page",post_parent=pp,
             custom_fields=data.frame(meta_key="Image",meta_value="/images/camara/abstentions.png"))







## Most absent


























test.df <- presente[1,]

p+geom_vline(aes(xintercept=presente_prop), test.df)

p+geom_vline()

##p1 <- p1+geom_vline(xintercept=data$score[i],col="red",size=2)
p+layer( 
        geom = "vline", 
        geom_params = list(fill = "steelblue",xintercept=1), 
        stat = "bin", 
        stat_params = list(binwidth = 2) 
        )



  layer(mapping=aes(xintercept=mean(presente$presente_prop)),data=data.frame(presente_prop=0,count=0))
                                                                                                               




##modal vote
fx <- function(x) {
  res <- names(which.max(table(x)))
  if (is.null(res)) NA
  else res
}

## take out "Ausente" as a possible choice
tmp <- subset(res,rc!="Ausente")
## recast
tmp0 <- tmp <- recast(tmp,rcfile~party,fun.aggregate=fx,measure.var="rc",id.var=c("rcfile","party"))
## select PT!=DEM
tmp <- subset(tmp,PT!=DEM)
## select PT in Sim Nao and DEM in Sim Nao
tmp <- subset(tmp,PT%in%c("Sim","Não"))
tmp <- subset(tmp,DEM%in%c("Sim","Não"))
dim(tmp)
res.d <- merge(subset(res,rc%in%c("Sim","Não")),tmp[,c("PT","DEM","rcfile")])
res.d$agdem <- with(res.d,rc==DEM)
## select out the ausentes
res.d <- subset(res.d,rc!="Ausente")



democratas <- recast(res.d,bioid+party~variable,measure.var="agdem",id.var=c("bioid","party"),fun.aggregate=function(x) sum(x)/length(x))




## dem1 <- recast(res.d,bioid+party~variable,measure.var=c("agdem"),id.var=c("bioid","party"),fun.aggregate=function(x) sum(x)/length(x))
## dem2 <- recast(res.d,bioid+party~variable,measure.var=c("presente"),id.var=c("bioid","party"),fun.aggregate=function(x) sum(x)/length(x))
## dem3 <- recast(res.d,bioid+party~variable,measure.var=c("agdem","presente"),id.var=c("bioid","party"),fun.aggregate=function(x) sum(x)/length(x))

democratas.presente <- merge(democratas,presente)




pred.glmer <- function(deps,m) {
  deps$o <- seq_len(nrow(deps))
  ## check if all parties are present
  if (!all(unique(deps$party)%in%rownames(ranef(m)$party))) stop("parties missing")
  ## party ranef as df
  rparty <- data.frame(ranef(m)$party)
  rparty$party <- rownames(rparty)
  names(rparty)[1] <- c("party.ranef")
  ## bioid ranef as df
  rbio <- data.frame(ranef(m)$bioid)
  rbio$bioid <- rownames(rbio)
  names(rbio)[1] <- c("bioid.ranef")
  ## generate predicted values
  pred <- merge(deps,rparty)
  pred <- merge(pred,rbio,all.x=TRUE)
  ## impute missing values
  ismiss <- is.na(pred$bioid.ranef)
  pred$bioid.ranef[ismiss] <- with(pred,rnorm(sum(ismiss),mean(bioid.ranef,na.rm=TRUE),sd(bioid.ranef,na.rm=TRUE)))
  if (nrow(pred)!=(nrow(deps))) stop("deps not matched")
  pred$score <- fixef(m)[1]+with(pred,party.ranef+bioid.ranef)
  pred$score <- round(plogis(pred$score)*100)/100
  pred <- pred[order(pred$o),]
  pred$score
}










## model3. party,bioid,rc
## state does not help predict abstentions
## date does not help either (just a linear term might help a little bit)
## billtype neither
m3<- glmer(presente~(1|party)+(1|bioid)+(1|rcfile),data=res,family=binomial)
## similar model to predict agree with DEM
##mdem <- glmer(agdem~(1|bioid)+(1|rcfile)+(1|party),data=subset(res.d,rc!="Ausente"),family=binomial)
mdem <- glmer(agdem~(1|bioid)+(1|rcfile)+(1|party),data=subset(res.d,rc!="Ausente"),family=binomial)


deps <- democratas.presente

deps$score.dem <- pred.glmer(deps,mdem)
deps$score.pre <- pred.glmer(deps,m3)



## A function to draw a histogram of the score

phist <- function(data,bioid,score="score.pre",oneplot=FALSE,annotate=FALSE) {
  data$score <- data[,score]
  i <- which(bioid==data$bioid)
  p1 <- ggplot(data,aes(score))
  p1 <- p1+geom_histogram(aes(y = ..count..), binwidth = 0.05,fill="gray30")
  p1 <- p1+scale_x_continuous(name="",limits=c(0,1),expand=c(0,0),formatter="percent")+scale_y_continuous(name="")+
    theme_bw()
  if (oneplot) {
    ds <- subset(data,party==data$party[i])
    p1 <- p1+geom_histogram(data=ds,fill="blue")
  }
  p1 <- p1+geom_vline(xintercept=data$score[i],col="red",size=2)
  if (annotate) {
    p1 <- p1+ geom_text(data=data.frame(x=.75,y=max(table(cut(p1$data$score,seq(0,1,.05))))*.9,label=paste(
                                                                                                      ##data$namelegis[i],"\n",
                                                                                                      tolower(data$party[i])),score=.5),mapping=aes(x=x,y=y,label=label),size=8,colour="darkblue")
  }
  p1
}

phist(deps,100046,oneplot=TRUE,annotate=TRUE,score="score.pre")

## Do simulations to get the percentile (and conf interv) of the legislators in
## terms of abstenteism




