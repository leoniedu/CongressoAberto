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
res <- dbGetQueryU(connect,sql)

res$presente <- as.numeric(res$rc!="Ausente")


library(lme4)

## model1. bioid
m1 <- glmer(presente~(1|bioid),data=res,family=binomial)

## model2. party,bioid
m2<- glmer(presente~(1|party)+(1|bioid),data=res,family=binomial)

## model3. party,bioid,rc
m3<- glmer(presente~(1|party)+(1|bioid)+(1|rcfile),data=res,family=binomial)


m3z<- zelig(presente~tag(1|party)+tag(1|bioid)+tag(1|rcfile),data=res,model="logit.mixed")

## model3. party,bioid,rc
## state does not help predict abstentions
## date does not help either (just a linear term might help a little bit)
## billtype neither
##m4<- glmer(presente~(1|party)+(1|bioid)+(1|rcfile)+(1|state),data=res,family=binomial)

x.high <- setx(m3z, bioid="119138")
x.low <-  setx(m3z, bioid=160448)


res$date <- as.numeric(as.Date(res$rcdate))
res$date <- res$date-min(res$date)
res$date2 <- res$date^2

m5<- glmer(presente~(1|party)+(1|bioid)+(1|rcfile)+billtype,data=res,family=binomial)
## date might help


## get current list of legislators
deps <- dbReadTableU(connect,"br_deputados_current")

## check if all parties are present
if (!all(unique(deps$party)%in%rownames(ranef(m3z)$party))) stop("parties missing")
## party ranef as df
rparty <- data.frame(ranef(m3z)$party)
rparty$party <- rownames(rparty)
names(rparty)[1] <- c("party.ranef")

## bioid ranef as df
rbio <- data.frame(ranef(m3z)$bioid)
rbio$bioid <- rownames(rbio)
names(rbio)[1] <- c("bioid.ranef")

## generate predicted values
pred <- merge(deps,rparty)
pred <- merge(pred,rbio)
if (nrow(pred)!=(nrow(deps))) stop("deps not matched")
pred$pres.score <- with(pred,party.ranef+bioid.ranef)
pred$pres.score <- round(plogis(pred$pres.score)*100)/100

library(ggplot2)




phist <- function(data,i,oneplot=FALSE) {
  p1 <- ggplot(data,aes(pres.score))
  p1 <- p1+geom_histogram(aes(y = ..count..), binwidth = 0.05,fill="gray30")
  p1 <- p1+scale_x_continuous(name="",limits=c(0,1),expand=c(0,0),formatter="percent")+scale_y_continuous(name="")+
    theme_bw()
  if (oneplot) {
    ds <- subset(pred,party==pred$party[i])
    p1 <- p1+geom_histogram(data=ds,fill="blue")
  }
  p1 <- p1+geom_vline(xintercept=pred$pres.score[i],col="red",size=2)
  p1
}


phist(pred,71,TRUE)

## Do simulations to get the percentile (and conf interv) of the legislators in
## terms of abstenteism




