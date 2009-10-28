## http://www1.folha.uol.com.br/folha/brasil/ult96u640085.shtml
## deputies that received donations from AIB (Associação Imobiliária Brasileira)


if (!exists("update.all", 1)) {  
  update.all <- FALSE
}

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
source(rf("R/spatial.R"))
source(rf("R/wordpress.R"))

connect.db()
connect.wp()






getpics <- function(s,mais=TRUE) {
    statsnow.pics <- webdir(paste("images/bio/polaroid/foto",statsnow$bioid,".png", sep=""))
    ## create one pic
    fn <- "images/"%+%s%+%"top"%+%format(Sys.Date(),"%Y%m")%+%".png"
    cmd <- paste("convert ",
                 " \\( -size 300x xc:none ",statsnow.pics[1]," +append \\)",
                 " \\( ", paste(statsnow.pics[2:4], collapse=" "), " +append \\)",
                 " \\( ", paste(statsnow.pics[5:7], collapse=" "), " +append \\)",
                 "-background none -append -resize 200x  -quality 95 -depth 8  ", webdir(fn), collapse=" ")
    system(cmd)
    statsnow$npstate <- with(statsnow, paste(capwords(as.character(trimm(namelegis))), party, toupper(state), sep=" - "))
    list(statsnow,fn)
}


toreal <- function(x,digits=0) formatC(round(x, digits), big.mark=".", format="d")

content <- function(statsnow) {
    statsnow$title <- gsub("^.*\\s(.*$)","\\1",statsnow$title,perl=TRUE)
    res <- with(statsnow,{
        paste(           
              ##foto
              "<img width=100 src=\"/php/timthumb.php?src=/images/bio/polaroid/foto",bioid,".png&w=100\"/> ",
              ## nome, partido estado
              "<a href=\"/?p=",postid,"\">",capwords(namelegis),"</a> (",party, "/", toupper(state),")", sep='',
              ## ultimo dia em que compareceu.
              " recebeu R$ ",  toreal(contribsum), " da AIB. Esse valor corresponde a ", round(100*contribsum/total),"% do total de doações recebidas pelo deputado.", ## FIX male/female
              collapse="<br")
    })
    paste("<p> No dia 19/10/2009 a Justiça Eleitoral do Estado de São Paulo cassou o mandato de 13 vereadores da capital paulista. Eles são acusados de receber doações  da Associação Imobiliária Brasileira (AIB) consideradas ilegais, pois a organização não estava habilitada a doar a (alta) soma de recursos que doou na campanha elitoral de 2008. Os deputados federais que também receberam recursos da AIB são: </p>", res, " <p> Leia mais no site do <a href=\"http://www.tre-sp.gov.br/noticias/textos2009/not091019.htm\"> Tribunal Regional Eleitoral de São Paulo</a>.</p>")   
}

stats <- dbGetQuery(connect, "select a.*, b.*, c.*, d.*  from br_contrib as a, br_bioidtse as b, br_bioidpostid as c, br_deputados_current as d  where a.donor like '%AIB Associacao%' and a.candno=b.candidate_code and a.state=b.state and b.bioid=c.bioid and b.bioid=d.bioid ")

aib <- getpics("AIB")

aib[[1]]$sex <- factor(statsnow$title,
                       levels=c("Exmo. Senhor Deputado", "Exma. Senhora Deputada"),
                       labels=c("Male", "Female"))




statsnow <- aib[[1]]
statsnow <- statsnow[order(-statsnow$contribsum),]
fn <- aib[[2]]
statsnow$npstate <- reorder(statsnow$npstate, -statsnow[,"contribsum"])

## change final comma to "e" 
excerpt <- paste(paste(statsnow$npstate, collapse=", "), " são os  deputados que receberam doações da AIB (Associação Imobiliária Brasileira) nas eleições de 2006." , sep='')
##FIX: insert date in the post?
wpAddByTitle(conwp
             , post_title="Deputados que receberam doações da AIB"
             , post_content=content(statsnow)
             , post_category=data.frame(name="Headline",slug="headline")
             , post_excerpt=excerpt
             , tags=data.frame(name=c("doações",slug="doacoes"))
             ##post_excerpt='Saiba quem são os deputados federais que mais faltam às votações nominais.',
             , post_type="post"
             , post_status="published"
             , post_date=wptime(Sys.Date())$brasilia
             , custom_fields=data.frame(meta_key="Image",meta_value=fn)
             )

