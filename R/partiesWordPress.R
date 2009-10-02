#This code posts the parties main page to WordPress
#Check ou partylist.php for formating details of the page

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
source(rf("R/wordpress.R"))

## connect to databases
connect.db()

connect.wp() #new wp function gets the random string in table names automatically

## the parties we want to post (current) 
## we should post only the ones that it is meaningful to present data for. Hence, get these from the party indices list with the names from party_curent
dp <- dbGetQueryU(connect,"select t1.*, t2.name, t2.number from br_partyindices as t1, br_parties_current as t2 where t2.number=t1.partyid")

## we will use party label + party number as party page names (note: not titles)
dp$pagename <- paste(dp$partyname,dp$number,sep="_")

## add the parent page "Partidos"

##add party page with table
the.content <- "<?php include_once('php/partylist.php'); ?>"
sub.content <- NULL
the.content <- paste(the.content,sub.content)
pid <- wpAddByTitle(conwp,post_title="Partidos",
                    post_name="partidos",
                    post_author=2,
                    post_type="page", ## can be page
                    post_content=the.content,
                    post_parent=NULL,
                    fulltext=paste("Partidos"), ## put in the full text field terms that you'd like the search function to use to  find this post
                    post_excerpt=paste("Sumario de dados sobre partidos "), ## summary of the post. it is what is shown in the front page, or in the search results.
                    tags=data.frame(slug=c("partidos"),name=c("Partidos"))) ## tag the post  format similar to categories and custom fields


## add pages
partypostid <- NULL
for (i in 1:nrow(dp)) {
  print(i)
  ## we identify the party pages by name
  the.content <- paste("<script language='php'>$partyid = ",dp$number[i],";include( 'php/party.php');</script>",sep="")
  sub.content <- paste("
 <ul><?php global $post;$thePostID = $post->ID;wp_list_pages( 'child_of='.$thePostID.'&title_li='); ?></ul>")
  the.content <- paste(the.content,sub.content)
 # postid <- wpAddByName(conwp,post_name=dp$pagename[i], Original posting, worked, but trying to use the more complete form, below.
 #                       post_title=dp$partyname[i],
 #                       post_content=the.content,post_parent=pid)
  postid <- wpAddByName( ## usually better to add by name -- we (try) to use  unique names
                        ## by "add by" we mean that the function searches for a post with matching names or title
                        conwp, ## connection
                        post_name=dp$pagename[i],
                        post_author=1,
                        post_title=dp$partyname[i],
                        post_type="page", ## can be page
                        post_content=the.content,
                        post_parent=pid,
                        fulltext=paste(dp$partyname[i],"partido",dp$name[i]), ## put in the full text field terms that you'd like the search function to use to  find this post
                        post_excerpt=tmp<-paste("Pagina com informacoes sobre o",dp$partyname[i]), ## summary of the post. it is what is shown in the front page, or in the search results.
                        ## categories are not relevant for pages
                        ##post_category=data.frame(slug="partidos",name="Partidos"), ## categories: can have multiple lines.
                        custom_fields=data.frame(meta_key="Image",meta_value=paste("/images/partylogos/resized/",dp$partyname[i],".jpg",sep="")) ## this is what is shown in the search results or in the front page you do not need to add the php thumbnail thing here, just the link
                        ## there is a one-to-one relationship betweeb
                        ## slug and name
                        ## and i don;t think we need it here
                        ## but an appropriate name slug would be
                        ## slug='partidos' name='Partidos'
                        ##,tags=data.frame(slug=c("Partidos",dp$partyname[i]),
                        ##name=c("Partidos",dp$partyname[i])) ## tag the post  format similar to categories and custom fields
                        )
  partypostid <- rbind(partypostid, data.frame(postid, name=dp$name[i], number=dp$number[i]))
}

dbRemoveTable(connect, "br_partypostid")
dbWriteTable(connect, "br_partypostid", partypostid)
