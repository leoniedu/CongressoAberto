## Model the most absent legislators
## Basic idea: random effects model with legislator+roll call+party random effects.
## to be Updated
library(ggplot2)
library(splines)

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


## add the parent page "Analises"
##pid <- wpAddByTitle(conwp,post_title="Análises",post_name="analises", post_content='<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( "child_of=".$thePostID."&title_li="); ?></ul>')

pt <- "Dados e Análises"
pid <- wpAddByTitle(conwp,
                    post_title=pt,
                    post_name=encode(pt), post_content='<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( "child_of=".$thePostID."&title_li="); ?></ul>')



sql2 <- paste("select  b.*, cast(b.rcdate as date) as rcdate  from br_votacoes as b where legis>49",sep='')
res2 <- dbGetQueryU(connect,sql2)
res2$Data <- as.Date(res2$rcdate)
res2$legisday <- getlegisdays(res2$rcdate)
res2$Legislatura <- get.legis.text(get.legis.year(res2$legis))
rcbydate <- recast(res2,legisday+Data+Legislatura~variable,measure.var="rcdate",fun.aggregate=function(x) length(unique(x)))
res2$datemonth <- getmonth(res2$rcdate)
res2$legismonth <- getlegisdays(res2$datemonth)

rcbymonth <- recast(res2,datemonth+legismonth+Legislatura+legis~variable,measure.var="rcdate",fun.aggregate=function(x) length(unique(x)))
tmp <- unique(data.frame(datemonth=getmonth(seq.Date(from=min(as.Date(res2$rcdate)),to=max(as.Date(res2$rcdate)), by=7)), rcdate=0))
tmp$legismonth <- getlegisdays(tmp$datemonth)
tmp$Legislatura <- get.legis.text(get.legis.year.date(tmp$datemonth))
tmp$legis <- get.legis(get.legis.year.date(tmp$datemonth))
tmp <- tmp[!tmp$datemonth%in%rcbymonth$datemonth,]
rcbymonth <- merge(tmp,rcbymonth,all=TRUE)
rm(tmp)


alldays <- with(rcbydate,seq.Date(from=min(Data),to=max(Data),by="day"))

tmp <- with(rcbydate,data.frame(legisday=1:max(res2$legisday),value=0))
tmp <- ldply(unique(rcbydate$Legislatura),function(x) data.frame(tmp,Legislatura=x))
tmp <- merge(rcbydate,tmp,all=TRUE)
tmp$dayvotes <- with(tmp,ifelse(is.na(rcdate),value,rcdate))
## FIX: change session
today <- Sys.Date()
ltoday <- getlegisdays(today)
tmp <- subset(tmp,!((Legislatura==get.legis.text(get.legis.year.date(today))) & (legisday>ltoday)))
## data has 1 if there was a vote in that day, 0 otherwise
## in terms of session days with votes per week
tmp$votesweek <- tmp$dayvotes*7
tmp$votesmonth <- tmp$dayvotes*30
tmp$votes <- tmp$dayvotes*30








## plot elections
pe <- function(p,year=2008,label="") {
  p <- p+geom_rect(xmin=getlegisdays(paste(year,"-10-01",sep='')),
                   xmax=getlegisdays(paste(year,"-10-30",sep='')),
                   ymin=-10,
                   ymax=1000,fill=alpha("gray80",.1)
                   )
  p <- p+geom_text(data=data.frame(legisday=getlegisdays(paste(year,"-04-01",sep='')), votes= 15,label=label,Legislatura=1),aes(label=label),hjust=0, vjust=0,size=3.5)
  p
}
p <- ggplot(data=tmp,aes(x=legisday,y=votes,group=Legislatura))
p <- pe(p,year=2008,label="Eleições\nlocais")
p <- pe(p,year=2010,label="Eleições\nnacionais")
##p <- p+stat_smooth(se=FALSE,size=2)
alphan <- .2
p <- p+stat_smooth(aes(colour=Legislatura),size=1.5,se=FALSE,method=lm,formula=y~splines::ns(x,5),alpha=.2)
##p <- p+stat_smooth(aes(colour=Legislatura),size=1,se=FALSE,method=lm,formula=y~splines::ns(x,3),alpha=.2)
p <- p +scale_colour_manual(values = c(alpha("darkgreen",alphan),alpha("darkblue",alphan), alpha("darkred",alphan),"red"))
## function for labels
fx <- function(x=1,year=2006) paste("Dez ",x+year,"\n(",x,'o. ano)',sep='')
p <- p+scale_x_continuous(name="",breaks=as.numeric(getlegisdays(paste(2008:2010,"-02-01",sep=''))),labels=fx(1:3),expand=c(0,0))
p <- p+coord_cartesian(ylim=c(0,25))
p <- p+scale_y_continuous(name="Dias com votaçoes por mês")
p <- p+theme_bw()
## we add a little noise to rcdate=0 to make it show in the graph
p <- p + geom_point(data=rcbymonth,aes(x=legismonth,y=ifelse(rcdate==0,0.05,(rcdate)), colour=Legislatura),size=1.5)

pdf(file=rf("images/camara/rcbymonth.pdf"),width=6,height=4)
print(p)
dev.off()
convert.png(file=rf("images/camara/rcbymonth.pdf"))



dx <- function(x) with(x,{
  x$votes <- 1
  x <- x[order(x$legisday),]
  x$votes <- cumsum(x$votes)
  x
})
tmp <- ddply(res2,"legis",dx)
alphan <- 1
tmp$Legislatura <- get.legis.text(get.legis.year(tmp$legis))

p2 <- qplot(legisday,votes,data=tmp, colour=Legislatura,group=Legislatura,geom=c("line"),size=I(1.2))+scale_x_continuous(name="",breaks=as.numeric(getlegisdays(paste(2008:2010,"-02-01",sep=''))),labels=fx(1:3),expand=c(0,0))+theme_bw()+scale_colour_manual(values = c(alpha("darkgreen",alphan),alpha("darkblue",alphan), alpha("darkred",alphan),"red"))
p2 <- p2+scale_y_continuous(name="Número de Votações")


pdf(file=rf("images/camara/cumulativerc.pdf"),width=6,height=4)
print(p2)
dev.off()

convert.png(file=rf("images/camara/cumulativerc.pdf"))






## numero de votações por mês no casa dos representantes do congresso americano.
us <- read.csv("http://adric.sscnet.ucla.edu/rollcall/static/H111desc.csv")
nmonth <- with(us, as.numeric(max(as.Date(date))-min(as.Date(date)))/30)
vpm.us <- floor(length(unique(us$date))/nmonth)

## numero de votacoes no brasil legis atual
sql3 <- paste("select  cast(b.rcdate as date) as rcdate  from br_votacoes as b where legis=53",sep='')
br <- dbGetQueryU(connect,sql3)
br$date <- as.Date(br$rcdate)
nmonth <- with(br, as.numeric(max(as.Date(date))-min(as.Date(date)))/30)
vpm.br <- floor(length(unique(br$date))/nmonth)




## add page
content <- '<table>
                <tr>
                <td><img width=400 src="/php/timthumb.php?src=/images/camara/rcbyweek.png&w=400&h=0" alt="Número de votações por mês" /></td><td><explain><p>Os pontos no gráfico à esquerda mostram o número dias com votações por mês, que pode ser considerado como um bom indicador do nível de atividade na Câmara.  As linhas mostram a tendência dos dados,  e auxiliam na visualização. A legislatura atual está em vermelho. Observe que nas legislaturas mais recentes o nível de atividade é bastante reduzido no período eleitoral.</p>
<p> Para fins comparativos, na Casa dos Representantes dos Estados Unidos na corrente legislatura há em média  '%+%vpm.us%+%' dias com votações nominais por mês. Na Câmara dos Deputados brasileira na legislatura atual, por outro lado,  em somente '%+%vpm.br%+%' dias por mês são realizadas votações nominais.
     </explain></td>
</tr>
<tr><td><explain>O gráfico ao lado mostra o número total de votações nominais na Câmara para a legislatura atual e as três anteriores. Mais votações ocorreram nessa legislatura do que no mesmo momento nas legislaturas passadas. 
</explain>
</td><td><img width=400 src="/php/timthumb.php?src=/images/camara/cumulativerc.png&w=400&h=0" alt="Número de votações acumuladas" /> 
</tr>
</table>'


    
votid <- wpAddByTitle(conwp,post_title="Número de Votações", post_parent=pid,
                      post_content=content)










sql2 <- paste("select  b.*, cast(b.rcdate as date) as rcdate  from br_votacoes as b where legis>49",sep='')
res2 <- dbGetQueryU(connect,sql2)
res2$legisday <- getlegisdays(res2$rcdate)
res2$Legislatura <- get.legis.text(get.legis.year(res2$legis))


