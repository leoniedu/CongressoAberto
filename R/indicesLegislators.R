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
  source(paste(run.from,"/R/caFunctions.R",sep=""),encoding="utf8")
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
##sql <- paste("select  b.*, cast(b.rcdate as date) as rcdate  from  br_votacoes as b where   (rcdate>cast('",Sys.Date()-30,"' as date)) ",sep='')
##sql <- paste("select  b.*, cast(b.rcdate as date) as rcdate  from br_votacoes as b where   (rcdate>cast('",Sys.Date()-365,"' as date)) ",sep='')
##Lula 1st year
##sql <- paste("select  a.*, b.*, cast(b.rcdate as date) as rcdate  from br_votacoes as b, br_votos as a  where a.rcfile=b.rcfile  and  (rcdate>cast('2003-02-01' as date)) and (rcdate<cast('2004-01-01' as date))  ",sep='')
##FHC
##sql <- paste("select  a.*, b.*, cast(b.rcdate as date) as rcdate  from br_votacoes as b, br_votos as a  where a.rcfile=b.rcfile  and  (rcdate>cast('1995-01-01' as date)) and (rcdate<cast('1996-01-01' as date))  ",sep='')

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

ausente <- recast(res,bioid~variable,measure.var="ausente",id.var=c("bioid"),fun.aggregate=fsum)

## the top 10 in absenteism
infodeps <- dbGetQueryU(connect,"select a.*, b.* from br_deputados_current as a, br_bio as b where a.bioid=b.bioid")

faltosos <- ausente[order(ausente$ausente_count, decreasing=TRUE)[1:10],]
faltosos <- merge(faltosos,infodeps)

## their pics
faltosos.pics <- rf("images/bio/polaroid/foto"%+%faltosos$bioid%+%".png")
## create one pic


fn <- "images/abstentions/top"%+%format(Sys.Date(),"%Y%m")%+%".png"

cmd <- paste("convert ",
             " \\( -size 300x xc:none ",faltosos.pics[1]," +append \\)",
             " \\( ", paste(faltosos.pics[2:4], collapse=" "), " +append \\)",
             " \\( ", paste(faltosos.pics[5:7], collapse=" "), " +append \\)",
             " \\( ", paste(faltosos.pics[8:10], collapse=" ")," +append \\)",
             "-background none -append -resize 200x  -quality 95 -depth 8  ", rf(fn), collapse=" ")

system(cmd)

##FIX: change final comma to "e" 
excerpt <- paste(capwords(paste(trimm(faltosos$namelegis.1), collapse=", ")), "são os dez deputados que mais faltaram às votações nominais na Câmara dos Deputados no  período de ", format(init.date,"%d-%m-%Y"), "a", format(final.date,"%d-%m-%Y"))

wpAddByTitle(conwp,post_title="Os 10 mais faltosos", post_category=data.frame(name="Headline",slug="headline"), post_excerpt=excerpt,
             ##post_excerpt='Saiba quem são os deputados federais que mais faltam às votações nominais.',
             post_type="post",
             custom_fields=data.frame(meta_key="Image",meta_value=fn))


       

       
# convert \( foto123000.jpg foto123001.jpg +append \) \
#           \( foto123002.jpg foto123003.jpg +append \) \
#           -background none -append   append_array.jpg


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
## page under "desempenho"
wpAddByTitle(conwp,post_title="Presença em plenário", post_category=data.frame(name="Headline",slug="headline"), post_content='<p><img width=400 src="/images/camara/abstentions.png" alt="Presença em plenário" /></p>',post_type="page",post_parent=pp,
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




