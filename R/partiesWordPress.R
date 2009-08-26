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

## connect to databases
connect.db()
connect.wp()

## the name in the wordpress databases include a random string. So we redefine
## the tname function to match the current wordpress installation
tname <- function(name,us="sqkxlx_") paste("wp_",us,name,sep='') 

## the parties we want to post (current) 
## we should post only the ones that it is meaningful to present data for. Hence, get these from the party indices list with the names from party_curent
dp <- dbGetQueryU(connect,"select t1.*, t2.name, t2.number from br_partyindices as t1, br_parties_current as t2 where t2.number=t1.partyid")

## we will use party label + party number as party page names (note: not titles)
dp$pagename <- paste(dp$partyname,dp$number,sep="_")

## add the parent page "Partidos"
pid <- dbGetQuery(conwp, paste("select * from ",tname("posts")," where post_title=",shQuote("Partidos")))
if (nrow(pid)==0) {
  ## let's create it
  ## the content is a short php script to list children pages
  pid <- wpAdd(conwp,post_title="Partidos",post_content='<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( "child_of=".$thePostID."&title_li="); ?></ul>')
} else {
  pid <- pid$ID[1]
}

## add pages
for (i in 1:nrow(dp)) {
  print(i)
  ## we identify the party pages by name
  pp <- dbGetQuery(conwp,paste("SELECT ID FROM ",tname("posts")," where post_name=",shQuote(dp$pagename[i])," and post_type='page'"))
  if (nrow(pp)==0) {
    ## page does not exist
    postid <- NA
  } else {
    postid <- pp$ID[1]
  }
  the.content <- paste('<script language="php">$partyid = ',dp$number[i],';include( TEMPLATEPATH . "/party.php");</script>',sep="")
  
  pp <- wpAdd(conwp,post_title=dp$partyname[i],post_name=dp$pagename[i],postid=postid,
              post_content=the.content,post_parent=pid)
}
