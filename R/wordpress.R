## FIX:
## post names cannot have trailing spaces


## table name
if (!exists("tname")) {
  tname <- function(name,us="") paste("wp_",us,name,sep='') 
}

encode <- function(x) {
    newx <- gsub("-+","-",tolower(clean(gsub(" +|/","-",tolower(x)),cleanmore=FALSE)))
    newx <- gsub("[^0-9a-zA-Z-]*","",newx)
    newx <- URLencode(newx)
    newx
}

wpconfig <- function() {
  ## not needed
  ##dbGetQuery(con,paste("ALTER TABLE  ",`wp_postmeta`  ADD UNIQUE  `postmeta` (  `post_id` ,  `meta_key` );
}

getvalues <- function(x) paste("(",paste(shQuote(x),collapse=","),")")


## time in ISO format
wptime <- function(x=Sys.time()) {
    x <- as.character(x)
    if (is.na(x)|x=="NA") return(list(gmt=NULL,brasilia=NULL))
    x <- as.Date(x)
    ## FIX: this does not take into account the dailight saving time
    gmt <- as.POSIXlt(x, "GMT")+60*60*3
    brasilia <- as.POSIXlt(x)+.001
    res <- list(gmt=gmt,brasilia=brasilia)
    res <- lapply(res,function(x) gsub(" GMT","",x))
    res
}

dbInsert <- function(con,df,table="tmp",update=FALSE,extra="",verbose=FALSE) {
  if (is.data.frame(df)) {
    values <- apply(df,1,getvalues)
  } else {
    values <- getvalues(df)
  }
  if (update) {
    update.st <-   paste("ON DUPLICATE KEY UPDATE ",paste(names(df),paste("VALUES(",names(df),")",sep=""),sep="=", collapse=" , "))
  } else {
    update.st <- ""
  }
  values <- paste(" VALUES ",paste(values,collapse=" , "))
  names <- paste("(",paste(names(df),sep=' ',collapse=","),")")
  st <- paste("INSERT INTO ",table,names,values,update.st,extra)
  if (verbose) cat(st,"\n")                             
  dbGetQuery(con,st)
}


##FIX join addbyname and addbytitle functions together
wpAddByName <- function(con,post_name,new_post_name=NULL,...) {
  pid <- dbGetQuery(conwp, paste("select * from ",tname("posts")," where post_type in ('page','post') and post_name=",shQuote(post_name)))
  if (nrow(pid)==0) {  
    ## let's create it
    pid <- NA
  } else {
    pid <- pid$ID[1]
  }
  if (!is.null(new_post_name)) {
    post_name <- new_post_name
  }
  ## add/edit page
  pid <- wpAdd(conwp,post_name=post_name,...,postid=pid)
  pid
}

wpAddByTitle <- function(con,post_title,new_post_title=NULL,...) {
    pid <- dbGetQuery(conwp, paste("select * from ",tname("posts")," where post_type in ('page','post') and post_title=",shQuote(post_title)))
    ##pid <- dbGetQuery(conwp, paste("select * from ",tname("posts")," where post_title=",shQuote(post_title)))
  if (nrow(pid)==0) {  
    ## let's create it
    pid <- NA
  } else {
    pid <- pid$ID[1]
  }
  if (!is.null(new_post_title)) {
    post_title <- new_post_title
  }
  ## add/edit page
  pid <- wpAdd(conwp,post_title=post_title,...,postid=pid)
  pid
}


setTerms <- function(con,postid,tags,taxonomy="post_tag") {
  oldtaxids <- dbGetQuery(con,paste("select term_taxonomy_id from ",tname("term_relationships"), " where object_id=",postid))
  if (nrow(oldtaxids)>0) {
    oldtaxids <- oldtaxids[,1]
    oldtermids <- dbGetQuery(con,paste("select term_id from ",tname("term_taxonomy"), " where term_taxonomy_id in (",paste(oldtaxids, collapse=", "),")"))[,1]
  } else {
    oldtermids <- oldtaxids <- NULL
  }
  ## add tags if they do not exist
  ## Note:  slug is the unique identifier
  dbInsert(con,tags,table=tname("terms"),update=TRUE)
  ## get the term ids for all tags
  termid <- unlist(lapply(tags$slug,function(x) dbGetQuery(con,paste("select term_id from ",tname("terms"), " where slug=",shQuote(x)))))
  ## update taxonomy and count
  ## only update count if the tag is new for this post
  newtermids <- termid[!termid%in%oldtermids]
  if (length(newtermids)>0) {
    df <- data.frame(term_id=newtermids, taxonomy=taxonomy, count=1)
    res <- dbInsert(con,df,extra=" on duplicate key update count=count+1",table=tname("term_taxonomy"))
  }
  termtaxid <- unlist(lapply(newtermids,function(x) dbGetQuery(con,paste("select term_taxonomy_id from ",tname("term_taxonomy"), " where term_id=",shQuote(x)))))
  ## update table linking posts to tags
  ## FIX: have to remove the old tags, categories and update counts before effectively editing the post
  try(dbInsert(con,data.frame(object_id=postid,term_taxonomy_id=termtaxid),table=tname("term_relationships")),silent=TRUE)
}
## test
## setTerms(conwp, 2643, data.frame(slug="test3", name="testname3"), "category")


wpAdd <- function(con,...,custom_fields=NULL,fulltext=NULL,postid=NA,tags=NULL,post_category=NULL,verbose=FALSE) {
  ## FIX: the editing part is very limited. it does not do all it is supposed to. use with care.
  newpost <- is.na(postid)
  fields <- list(...)
  ctime <- wptime()
  if (is.null(fields$post_modified) ) fields$post_modified <- ctime[["brasilia"]]
  if (is.null(fields$post_modified_gmt) ) fields$post_modified_gmt <- ctime[["gmt"]]
  if (newpost) {
    if (is.null(fields$post_status)) fields$post_status <- "publish"
    if (is.null(fields$post_author) ) fields$post_author <- "1"
    if (is.null(fields$post_type) ) fields$post_type <- "page"
    if (is.null(fields$post_date) ) fields$post_date <- ctime[["brasilia"]]
    if (is.null(fields$post_date_gmt) ) fields$post_date_gmt <- ctime[["gmt"]]
    if (is.null(fields$post_name)) fields$post_name <- encode(fields$post_title)
  }
  ## slugs are lower case
  if(verbose) print(fields)
  ## adding a new page
  ## add the post to _posts
  if (newpost) {
    res <- dbInsert(con,fields,table=tname("posts"))
    postid <- dbGetQuery(con,"select LAST_INSERT_ID()")[1,1]
    if (postid==0) stop("there was a problem in the mysql connection")
  } else {
    res <- dbInsert(con,c(id=postid,fields),table=tname("posts"),update=TRUE)
  }
  if (length(tags)>0) {
    setTerms(con,postid,tags,"post_tag")
  }
  if (length(post_category)>0) {
    setTerms(con,postid,post_category,"category")
  }
  ## update  custom fields FIX: we delete and replace. anything better?
  cf <- data.frame(post_id=postid,meta_key=c("disable_wptexturize","disable_wpautop","disable_convert_chars","disable_convert_smilies"),meta_value=1,stringsAsFactors=FALSE)
  if (!is.null(custom_fields)) {
    cf <- rbind(data.frame(post_id=postid,custom_fields),cf)
  }
  if (!is.null(fulltext)) {
    ## add text to make the posts searchable
    cf <- rbind(cf,data.frame(post_id=postid,meta_key="fulltext",meta_value=fulltext))
  }
  ## delete if exists
  res <- lapply(cf$meta_key,function(key) dbGetQuery(con,paste("delete from ",tname("postmeta")," WHERE meta_key=",shQuote(key)," AND post_id = ",postid)))
  dbInsert(con,cf,table=tname("postmeta"),update=FALSE)
  print(postid)
  ## return the postid
  postid
}





wpClean <- function() {
    res <- lapply(c("postmeta","posts","term_relationships","term_taxonomy","terms"),function(x) dbGetQuery(conwp,paste("truncate ",tname(x))))
    dbGetQuery(connect,paste("truncate br_billidpostid"))
    dbGetQuery(connect,paste("truncate br_bioidpostid"))
    dbGetQuery(connect,paste("truncate br_rcvoteidpostid"))  
    dbDisconnect(conwp)
    dbDisconnect(connect)
    connect.wp()
    connect.db()
    return()
}


postbill <- function(bill=37642, propid=NULL) {
    if (is.null(propid)) {
        propid <- unlist(dbGetQueryU(conwp, " select ID from "%+%tname("posts")%+%" where post_title='Proposições'"))
    }
    dnow <- dbGetQueryU(connect, "select * from br_bills where billid="%+%shQuote(bill))
    ##dnow <- subset(bills,billid==bill)
    ementashort <- dnow$ementashort
    if (is.na(ementashort)) ementashort <- dnow$ementa
    excerpt <- paste(dnow$billauthor, ementashort, dnow$status, sep="<br>")
    fulltext <- paste(dnow,collapse="\n")
    ## create post data
    title <- with(dnow, paste(billtype," ",billno,"/",billyear,sep=''))
    name <- with(dnow, encode(title))
    content <- with(dnow, paste('<script language="php">$billid = ',billid,';include( "php/bill.php");</script>'))
    date <- with(dnow, wptime(billdate))    
    tagsname <- with(dnow, sapply(c(billtype,tramit,billyear, if (billauthor=="Poder Executivo") "Executivo"), encode))
    tagsname <- tagsname[tagsname!="NA"]
    tagslug <- gsub("[-,.]+","_",tagsname)
    tags <- with(dnow, data.frame(slug=tagslug,name=tagsname))
    billtype <- with(dnow, toupper(billtype))
    pp <- wpAddByTitle(conwp,post_content="<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( \"child_of=\".$thePostID.\"&title_li=\"); ?></ul>",post_title=billtype,post_parent=propid)
    ##postid <- wpAddByTitle(conwp,post_title=as.character(title),post_content=content,post_date=date$brasilia,post_date_gmt=date$gmt,fulltext=fulltext,post_excerpt=excerpt, tags=tags,post_parent=pp)
    postid <- wpAddByName(conwp,post_title=title,post_name=encode(title),post_content=content,post_date=date$brasilia,post_date_gmt=date$gmt,fulltext=fulltext,post_excerpt=excerpt, tags=tags,post_parent=pp)    
    dbWriteTableU(connect,"br_billidpostid",data.frame(postid,billid=bill),append=TRUE)
    res <- c(bill,postid)
    print(res)
    res
}  



##wp_delete_object_term_relationships($postid, array('category', 'post_tag'));
