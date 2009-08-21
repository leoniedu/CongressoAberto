## Model the most absent legislators
## Basic idea: random effects model with legislator+roll call+party random effects.
## to be Updated monthly?

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

connect.db()


sql <- paste("select  a.*, b.*, cast(b.rcdate as date) as rcdate  from br_votacoes as b, br_votos as a  where a.rcfile=b.rcfile  and  (rcdate>cast('",Sys.Date()-365,"' as date)) ",sep='')
sql <- paste("select  a.*, b.*, cast(b.rcdate as date) as rcdate  from br_votacoes as b, br_votos as a  where a.rcfile=b.rcfile  and  (rcdate>cast('1995-01-01' as date)) and (rcdate<cast('1996-01-01' as date))  ",sep='')
res <- dbGetQueryU(connect,sql)

res$presente <- as.numeric(res$rc!="Ausente")
res$party[res$party=="PFL"] <- "DEM"

library(lme4)

## model1. bioid
##m1 <- glmer(presente~(1|bioid),data=res,family=binomial)
## model2. party,bioid
##m2<- glmer(presente~(1|party)+(1|bioid),data=res,family=binomial)
## model3. party,bioid,rc
m3<- glmer(presente~(1|party)+(1|bioid)+(1|rcfile),data=res,family=binomial)
## model3. party,bioid,rc
## state does not help predict abstentions
## date does not help either (just a linear term might help a little bit)
## billtype neither
##m4<- glmer(presente~(1|party)+(1|bioid)+(1|rcfile)+(1|state),data=res,family=binomial)
##m5<- glmer(presente~(1|party)+(1|bioid)+(1|rcfile)+billtype,data=res,family=binomial)
## date might help

## get current list of legislators
deps <- dbReadTableU(connect,"br_deputados_current")

deps <- subset(res,rcfile==rcfile[1],select=c(bioid,namelegis,party,state))



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
  pred$score <- with(pred,party.ranef+bioid.ranef)
  pred$score <- round(plogis(pred$score)*100)/100
  pred <- pred[order(pred$o),]
  pred$score
}

library(ggplot2)

##modal vote of PT
fx <- function(x) {
  res <- names(which.max(table(x)))
  if (is.null(res)) NA
  else res
}

tmp <- recast(subset(res,rc!="Ausente"),rcfile~party,fun.aggregate=fx,measure.var="rc",id.var=c("rcfile","party"))
tmp <- subset(tmp,PT!=DEM)
res.d <- merge(res,tmp[,c("PT","DEM","rcfile")])
res.d$agdem <- with(res.d,rc==DEM)


tmp <- recast(res,bioid~variable,measure.var="presente",id.var=c("bioid"),fun.aggregate=function(x) sum(x)/length(x))
deps <- merge(deps,tmp)






mdem <- glmer(agdem~(1|bioid)+(1|rcfile)+(1|party),data=res.d,family=binomial)

deps$score.dem <- pred.glmer(deps,mdem)
deps$score.pre <- pred.glmer(deps,m3)


phist <- function(data,bioid,oneplot=FALSE,annotate=FALSE) {
  i <- which(bioid==data$bioid)
  p1 <- ggplot(data,aes(pres.score))
  p1 <- p1+geom_histogram(aes(y = ..count..), binwidth = 0.05,fill="gray30")
  p1 <- p1+scale_x_continuous(name="",limits=c(0,1),expand=c(0,0),formatter="percent")+scale_y_continuous(name="")+
    theme_bw()
  if (oneplot) {
    ds <- subset(data,party==data$party[i])
    p1 <- p1+geom_histogram(data=ds,fill="blue")
  }
  p1 <- p1+geom_vline(xintercept=data$pres.score[i],col="red",size=2)
  if (annotate) {
    p1 <- p1+ geom_text(data=data.frame(x=.75,y=max(table(cut(p1$data$pres.score,seq(0,1,.05))))*.9,label=paste(data$namelegis[i],"\n",tolower(data$party[i])),pres.score=.5),mapping=aes(x=x,y=y,label=label),size=8,colour="darkblue")
  }
  p1
}


phist(pred,97521,TRUE,TRUE)

## Do simulations to get the percentile (and conf interv) of the legislators in
## terms of abstenteism




