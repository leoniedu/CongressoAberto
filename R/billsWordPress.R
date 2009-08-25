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


tname <- function(name,us="sqkxlx_") paste("wp_",us,name,sep='') 

posts <- dbGetQuery(conwp, paste("select * from ",tname("posts")))

## create parent pages if they do not exist
## Proposicoes
## check that it does not exist
propid <- dbGetQuery(conwp, paste("select * from ",tname("posts")," where post_title=",shQuote("Proposições")))
if (nrow(propid)==0) {
  ## let's create it
  propid <- wpAdd(conwp,post_title="Proposições",post_name="proposicoes",post_content='<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( "child_of=".$thePostID."&title_li="); ?></ul>')
} else {
  propid <- propid$ID[1]
}

postbill <- function(bill=37642,skip=FALSE) {
  dnow <- subset(bills,billid==bill)
  fulltext <- paste(dnow,collapse="\n")
  with(dnow,{
    ## look for the post in the link table
    postid <- dbGetQuery(connect,paste("select * from br_billidpostid where billid=",billid,sep=""))
    if (nrow(postid)==0) {
      ## this is a new post
      postid <- NA
    } else {
      ## post id
      postid <- postid$postid[1]
      if (skip) return(postid)
    }
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
    posttype <- "page"
    ## check that pages with this name exist
    pp <- dbGetQuery(conwp,paste("select * from ",tname("posts")," where post_title='",billtype,"' AND post_type='page' limit 1",sep=''))
    ## if it does not exist, create one
    if (nrow(pp)==0) {
      ppcontent <- "<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( \"child_of=\".$thePostID.\"&title_li=\"); ?></ul>"
      pp <- wpAdd(conwp,post_title=billtype,post_name=encode(billtype),post_content=ppcontent,post_parent=propid)
    } else {
      ## if it exists get the ID
      pp <- pp$ID[1]
    }
    postid <- wpAdd(conwp,postid=postid,post_title=title,post_content=content,post_date=date$brasilia,post_date_gmt=date$gmt,post_name=encode(name),fulltext=fulltext,
                    tags=tags,post_type=posttype, post_parent=pp)
    dbWriteTableU(connect,"br_billidpostid",data.frame(postid,billid=bill),append=TRUE)
    res <- c(bill,postid)
    print(res)
    res
  })  
}

t(sapply(bills$billid[sample(1:nrow(bills),10)],postbill))

t(sapply(bills$billid[1:nrow(bills)],postbill))
