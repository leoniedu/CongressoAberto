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
source(rf("R/wordpress.R"))

connect.db()
connect.wp()



## parent page
##prop <- dbGetQuery(connect,paste("select * from wp_hufib7_terms where slug ='proposicoes'"))
##prop <- dbGetQuery(connect,paste("select * from wp_hufib7_terms where name ='Projetos de lei'"))



##posts <- dbGetQuery(conwp, paste("select * from ",tname("posts")))

## create parent pages if they do not exist
## Proposicoes
## check that it does not exist

##propid <- dbGetQuery(conwp, "select ID from wp_posts where post_type='page' and post_title='Proposições'")[[1]]

propid <- wpAddByTitle(conwp,post_title="Proposições",post_name="proposicoes",post_content='<?php include("php/bill_list.php"); ?>')



bills <- dbGetQueryU(connect, "select * from br_bills")
billsin <- dbReadTable(connect, "br_billidpostid")

##billsnow <- bills$billid[!bills$billid%in%billsin$billid]
##billsnow <- bills$billid[sample(1:nrow(bills),2)]
billsnow <- bills$billid[1:nrow(bills)]

for (i in billsnow) {
    postbill(i, propid=propid)
}
