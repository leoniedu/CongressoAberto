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



posts <- dbGetQuery(conwp, paste("select * from ",tname("posts")))

## create parent pages if they do not exist
## Proposicoes
## check that it does not exist

propid <- wpAddByTitle(conwp,post_title="Proposições",post_name="proposicoes",post_content='<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( "child_of=".$thePostID."&title_li="); ?></ul>')


bills <- dbGetQueryU(connect, "select * from br_bills")
billsin <- dbReadTable(connect, "br_billidpostid")

billsnow <- bills$billid[!bills$billid%in%billsin$billid]
##billsnow <- bills$billid[sample(1:nrow(bills),2)]
##billsnow <- bills$billid[1:nrow(bills)]

for (i in billsnow) {
    postbill(i, propid=propid)
}
