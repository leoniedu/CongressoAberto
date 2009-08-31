## table name
if (!exists("tname")) {
  tname <- function(name,us="") paste("wp_",us,name,sep='') 
}

encode <- function(x) {
  newx <- gsub("-+","-",tolower(clean(gsub(" +|/","-",tolower(x)),cleanmore=FALSE)))
  newx <- gsub("º","",newx)
  newx <- URLencode(newx)
}

wpconfig <- function() {
  ## not needed
  ##dbGetQuery(con,paste("ALTER TABLE  ",`wp_postmeta`  ADD UNIQUE  `postmeta` (  `post_id` ,  `meta_key` );
}

getvalues <- function(x) paste("(",paste(shQuote(x),collapse=","),")")


## time in ISO format
wptime <- function(x=Sys.time()) {
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
  pid <- dbGetQuery(conwp, paste("select * from ",tname("posts")," where post_name=",shQuote(post_name)))
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
  pid <- dbGetQuery(conwp, paste("select * from ",tname("posts")," where post_title=",shQuote(post_title)))
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
    print("NOW")
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
  dbDisconnect(conwp)
  dbDisconnect(connect)
  connect.wp()
  connect.db()
  return()
}

##wp_delete_object_term_relationships($postid, array('category', 'post_tag'));
