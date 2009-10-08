# Code generates the several "TOP TEN" scenarios
# Cesar added contributions TOP TEN on October 08

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

##init.date <- Sys.Date()-365
init.date <- "2007-02-01"

##init.date <- Sys.Date()-60
final.date <- Sys.Date()
date.range <- paste(format(c(init.date+1,final.date-1),"%d-%m-%Y"))
sql <- paste("select  a.*, cast(b.rcdate as date) as rcdate  from br_votos as a, br_votacoes as b, br_deputados_current as c where a.bioid=c.bioid and a.rcvoteid=b.rcvoteid and (rcdate>cast('",init.date,"' as date) ) and (rcdate<cast('",final.date,"' as date) ) ",sep='')
res <- dbGetQueryU(connect,sql)
res$ausente <- res$rc=="Ausente"
res$party <- recode.party(res$party)
##res0 <- res


## merge with leaders table
lead <- dbGetQuery(connect, "select * from br_leaders")

res <- merge(subset(lead, select=c(rcvoteid, rc, party)), res, by=c("rcvoteid", "party"), all.y=TRUE,
             suffixes=c(".party",""))
res <- merge(subset(lead, party=="GOV", select=c(rcvoteid, rc)),
             res, by=c("rcvoteid"), all.y=TRUE, suffixes=c(".gov",""))

rm(lead)


fsum <- function(x) c(prop=sum(x)/length(x), total=length(x), count=sum(x))
ausente <- recast(res,bioid~variable,measure.var="ausente",id.var=c("bioid"),fun.aggregate=fsum)

##last seen in congress
lastseen <- recast(subset(res,rc!="Ausente"),bioid~variable,measure.var="rcdate", id.var=c("bioid"),max)
lastseen<- with(lastseen, data.frame(bioid, lastseen=as.Date(rcdate)))

##follow government
tmp <- subset(res, rc.gov%in%c("Sim", "Não"))
tmp$cgov <- as.numeric(tmp$rc==tmp$rc.gov)
cgov <- recast(tmp, bioid~variable, measure.var="cgov", id.var=c("bioid")
               , fun.aggregate=fsum)


##follow party
tmp <- subset(res, rc.party%in%c("Sim", "Não"))
## only keep those members of the same party throughout the period
## FIX: take care of party renames
nparty <- recast(tmp, bioid ~ variable, fun.aggregate=function(x) length(unique(x)), measure.var="party")
nparty <- subset(nparty, party==1)
tmp <- tmp[tmp$bioid%in%nparty$bioid,]
tmp$cparty <- as.numeric(tmp$rc==tmp$rc.party)
cparty <- recast(tmp, bioid~variable, measure.var="cparty", id.var=c("bioid")
                 , fun.aggregate=fsum)


##contributions
    #Redo query: make a single query to avoid having to do all the mergers later.....
contrib<- dbGetQuery(connect, "select * from br_contrib")  #set of contributions to federal deputeis 
elected<- dbGetQuery(connect, "select * from br_bioidtse")[,c("state","candidate_code","bioid")] #set of those eventually elected 
inoffice<- dbGetQuery(connect, "select * from br_deputados_current")[,c("namelegisclean","bioid")] #set of those currenlty in office

funding_total <- function(d) {sum(d$total,na.rm=TRUE)}
funding_party <- function(d) {sum(subset(d,donortype=="PF")$total,na.rm=TRUE)}
contrib.cand <- ddply(contrib, .(candno,partyno,state), "funding_total") #candidate observations (all contributions)
contrib.candPP <- ddply(contrib, .(candno,partyno,state), "funding_party") #candidate observations (party contributions)
contrib.cand <- merge(contrib.cand,contrib.candPP,by=c("candno","partyno","state")) #merge the two
contrib.cand$funding_private <- contrib.cand$funding_total - contrib.cand$funding_party #create third category (private contributions)
contrib.elec <- merge(elected,contrib.cand,by.x=c("candidate_code","state"),by.y=c("candno","state"),all.x=TRUE) #get bioid -> this could be avoided with query
contrib.elec <- contrib.elec[,-which(is.element(names(contrib.elec),c("candidate_code","state","partyno")))] #keep only candidates actually electe -> this could be avoided with query





stats <- merge(ausente, lastseen, all=TRUE, by="bioid")
stats <- merge(stats, cparty, all=TRUE)
stats <- merge(stats, cgov, all=TRUE)
stats <- merge(stats, contrib.elec, all=TRUE, by="bioid")
## missing info on cparty (due to party change)
mp <- is.na(stats$cparty_prop)
## code missing as zero
## FIX; see if this makes sense
stats$cparty_prop[mp] <- stats$cparty_count[mp] <- stats$cparty_total[mp] <- 0



## write db table ## FIX: should be a long format with dates at some point
dbRemoveTable(connect, "br_legis_stats")
dbWriteTableU(connect, "br_legis_stats", data.frame(stats))

infodeps <- dbGetQueryU(connect,"select a.*, b.* from br_deputados_current as a, br_bio as b where a.bioid=b.bioid")


stats <- merge(stats,infodeps)

stats$sex <- factor(stats$title,
                    levels=c("Exmo. Senhor Deputado", "Exma. Senhora Deputada"),
                    labels=c("Male", "Female"))

getpics <- function(s) {
    statsnow <- stats
    if(length(grep("funding",s))==1){  
    statsnow <- statsnow[with(statsnow, order(statsnow[,s], decreasing=TRUE))[1:10], ]
    }else{
    statsnow <- statsnow[with(statsnow, order(get(s%+%"_count"),get(s%+%"_prop"), decreasing=TRUE))[1:10], ]
    }
    ## their pics
    statsnow.pics <- webdir(paste("images/bio/polaroid/foto",statsnow$bioid,".png", sep=""))
    ## create one pic
    fn <- "images/"%+%s%+%"top"%+%format(Sys.Date(),"%Y%m")%+%".png"
    cmd <- paste("convert ",
                 " \\( -size 300x xc:none ",statsnow.pics[1]," +append \\)",
             " \\( ", paste(statsnow.pics[2:4], collapse=" "), " +append \\)",
                 " \\( ", paste(statsnow.pics[5:7], collapse=" "), " +append \\)",
                 " \\( ", paste(statsnow.pics[8:10], collapse=" ")," +append \\)",
                 "-background none -append -resize 200x  -quality 95 -depth 8  ", webdir(fn), collapse=" ")
    system(cmd)
    statsnow$npstate <- with(statsnow, paste(capwords(as.character(trimm(namelegis.1))), party, toupper(state), sep=" - "))
    list(statsnow,fn)
}

faltosos <- getpics("ausente")

governistas <- getpics("cgov")

partidarios <- getpics("cparty")

capitalizados <- getpics("funding_total")

capitalizados.2 <- getpics("funding_party")

capitalizados.3 <- getpics("funding_private")


content <- function(statsnow) {
    with(statsnow,{
        art <- ifelse (sex=="Male", "o", "a")
        tshort <- ifelse (sex=="Male", "deputado", "deputada")
        paste(           
              ##foto
              "<img width=100 src=\"/php/timthumb.php?src=/images/bio/polaroid/foto",bioid,".png&w=100\"/> ",
              toupper(art)," ",
              title," ",
              ## nome, partido estado
              capwords(namelegis.1)," (",party, "/", toupper(state),")", sep='',
          ## ultimo dia em que compareceu.
              ' compareceu a votações nominais  na Câmara pela última vez no dia ',
              format.Date(lastseen, "%d/%m/%Y"),". ",          
              ## naturalidade
              "Natural de ", capwords(birthplace), ", ", capwords(namelegis.1), " tem ", diffyear(birthdate.1,Sys.Date()), " anos de idade."
              , " ",toupper(art), " ", tshort,  " vota ", round(cgov_prop*100), "%"
              , " das vezes com o governo, e ", round(cparty_prop*100), 
              "% das vezes com seu partido. Em 2006, declarou ter recebido R$ ", round(funding_private/1000000),
              " milh�es de doadores privados e ",round(funding_party/1000000)," milh�es de seu partido."
              , collapse="<br")
    })
}




statsnow <- governistas[[1]]
fn <- governistas[[2]]
statsnow$npstate <- reorder(statsnow$npstate, statsnow[,"cgov_prop"])
## change final comma to "e" 
excerpt <- paste(paste(statsnow$npstate, collapse=", "), " são os dez deputados que mais seguiram a indicação do governo nas votações nominais na Câmara dos Deputados no  período de ", format(init.date,"%d/%m/%Y"), " a ", format(final.date,"%d/%m/%Y"),".", sep='')
##FIX: insert date in the post?
wpAddByTitle(conwp,post_title="Os Governistas"## %+%format(final.date,"%m/%Y")
             ,post_content=content(statsnow)
             ,post_category=data.frame(name="Headline",slug="headline"), post_excerpt=excerpt,tags=data.frame(name=c("governismo",slug="governismo")),
             ##post_excerpt='Saiba quem são os deputados federais que mais faltam às votações nominais.',
             post_type="post",
             custom_fields=data.frame(meta_key="Image",meta_value=fn))


statsnow <- partidarios[[1]]
fn <- partidarios[[2]]
statsnow$npstate <- reorder(statsnow$npstate, statsnow[,"cparty_prop"])
## change final comma to "e" 
excerpt <- paste(paste(statsnow$npstate, collapse=", "), " são os dez deputados que mais seguiram a indicação dos seus partidos  nas votações nominais na Câmara dos Deputados no  período de ", format(init.date,"%d/%m/%Y"), " a ", format(final.date,"%d/%m/%Y"),".", sep='')
##FIX: insert date in the post?
wpAddByTitle(conwp,post_title="Os Fiéis"## %+%format(final.date,"%m/%Y")
             ,post_content=content(statsnow)
             ,post_category=data.frame(name="Headline",slug="headline"), post_excerpt=excerpt,tags=data.frame(name=c("partidos",slug="partidos")),
             ##post_excerpt='Saiba quem são os deputados federais que mais faltam às votações nominais.',
             post_type="post",
             custom_fields=data.frame(meta_key="Image",meta_value=fn))



statsnow <- faltosos[[1]]
fn <- faltosos[[2]]
statsnow$npstate <- reorder(statsnow$npstate, statsnow[,"ausente_prop"])
## change final comma to "e" 
excerpt <- paste(paste(statsnow$npstate, collapse=", "), " são os dez deputados que mais faltaram às votações nominais na Câmara dos Deputados no  período de ", format(init.date,"%d/%m/%Y"), " a ", format(final.date,"%d/%m/%Y"),".", sep='')
##FIX: insert date in the post?
wpAddByTitle(conwp
             ,post_title="Os Ausentes"## %+%format(final.date,"%m/%Y")           
             ,post_content=content(statsnow)
             ,post_category=data.frame(name="Headline",slug="headline"), post_excerpt=excerpt,tags=data.frame(name=c("absenteismo",slug="absenteismo")),
             ##post_excerpt='Saiba quem são os deputados federais que mais faltam às votações nominais.',
             post_type="post",
             custom_fields=data.frame(meta_key="Image",meta_value=fn))


statsnow <- capitalizados[[1]]
fn <- capitalizados[[2]]
statsnow$npstate <- reorder(statsnow$npstate, statsnow[,"funding_total"])
## change final comma to "e" 
excerpt <- paste(paste(statsnow$npstate, collapse=", "), " são os dez deputados que mais receberam doações de campanha nas eleições de 2006 para a Câmara dos Deputados.", sep='')
##FIX: insert date in the post?
wpAddByTitle(conwp
             ,post_title="As Campanhas Mais Caras"## %+%format(final.date,"%m/%Y")           
             ,post_content=content(statsnow)
             ,post_category=data.frame(name="Headline",slug="headline"), post_excerpt=excerpt,tags=data.frame(name=c("campanhas",slug="campanhas")),
             ##post_excerpt='Saiba quem são os deputados federais em exercicio que mais receberam doações de campanha.',
             post_type="post",
             custom_fields=data.frame(meta_key="Image",meta_value=fn))















##HERE
library(ggplot2)

sql2 <- paste("select  b.*, cast(b.rcdate as date) as rcdate  from br_votacoes as b where legis>49",sep='')
res2 <- dbGetQueryU(connect,sql2)
res2$Data <- as.Date(res2$rcdate)
res2$legisday <- getlegisdays(res2$rcdate)
res2$Legislatura <- get.legis.text(get.legis.year(res2$legis))

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


pt <- "Dados e Análises"
pp <- dbGetQuery(conwp,paste("select * from ", tname("posts"), " where post_title=", shQuote(pt)))$ID[1]



content <- '<table>
<tr>
<td><img width=400 src="/php/timthumb.php?src=/images/camara/abstentions.png&w=400&h=0" alt="Presença em plenário" /></td>
<td>
<explain> explain! </explain>
</td>
</tr>
<tr>
<td>
<explain> explain! </explain>
</td>
<td><img width=400 src="/php/timthumb.php?src=/images/abstentions/byrc.png&w=400&h=0" alt="Presença em plenário" /></td>
</tr>
</table>
'

## page under "desempenho"
wpAddByTitle(conwp,post_title="Presença em plenário", post_category=data.frame(name="Headline",slug="headline"),
             post_content=content
             ,
             post_type="page",post_parent=pp,
             custom_fields=data.frame(meta_key="Image",meta_value="/images/camara/abstentions.png"))
