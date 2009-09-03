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



bills <- dbGetQueryU(connect, "select * from br_bills")
## parent page
##prop <- dbGetQuery(connect,paste("select * from wp_hufib7_terms where slug ='proposicoes'"))
##prop <- dbGetQuery(connect,paste("select * from wp_hufib7_terms where name ='Projetos de lei'"))



posts <- dbGetQuery(conwp, paste("select * from ",tname("posts")))

## create parent pages if they do not exist
## Proposicoes
## check that it does not exist
propid <- wpAddByTitle(conwp,post_title="Proposições",post_name="proposicoes",post_content='<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( "child_of=".$thePostID."&title_li="); ?></ul>')

postbill <- function(bill=37642) {
  dnow <- subset(bills,billid==bill)
  fulltext <- paste(dnow,collapse="\n")
  with(dnow,{
    ## create post data
    title <- paste(billtype," ",billno,"/",billyear,sep='')
    name <- encode(title)
    content <- paste('<script language="php">$billid = ',billid,';include( TEMPLATEPATH . "/bill.php");</script>')
    date <- wptime(billdate)
    tagsname <- sapply(c(billtype,tramit,billyear,
                         if (billauthor=="Poder Executivo") "Executivo"),
                       encode)
    tagslug <- gsub("[-,.]+","_",tagsname)
    tags <- data.frame(slug=tagslug,name=tagsname)
    billtype <- toupper(billtype)
    pp <- wpAddByTitle(conwp,post_content="<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( \"child_of=\".$thePostID.\"&title_li=\"); ?></ul>",post_title=billtype,post_parent=propid)
    postid <- wpAddByTitle(conwp,post_title=title,post_content=content,post_date=date$brasilia,post_date_gmt=date$gmt,fulltext=fulltext,
                           tags=tags,post_parent=pp,post_category=data.frame(name="testing",slug="test"))
    dbWriteTableU(connect,"br_billidpostid",data.frame(postid,billid=bill),append=TRUE)
    res <- c(bill,postid)
    print(res)
    res
  })  
}

billsin <- dbReadTable(connect, "br_billidpostid")

##billsnow <- bills$billid[sample(1:nrow(bills),2)]
##billsnow <- bills$billid[1:nrow(bills)]
billsnow <- bills$billid[!bills$billid%in%billsin$billid]

t(sapply(billsnow,postbill))

