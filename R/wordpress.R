getvalues <- function(x) paste("(",paste(shQuote(x),collapse=","),")")
fx <- function(x=c("a","b")) 
wptime <- function(x=Sys.time()) {
  ## FIX: this does not take into account the dailight saving time
  gmt <- as.POSIXlt(x, "GMT")
  brasilia <- gmt-60*60*3
  res <- list(gmt=gmt,brasilia=brasilia)
  res <- lapply(res,function(x) gsub(" GMT","",x))
  res
}

dbInsert <- function(con,df,table="tmp",update=FALSE,extra="") {
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
  cat(st,"\n")                             
  dbGetQuery(con,st)
}

##dbInsert(connect,data.frame(post_title=c(1,2),post_content=c(3,4),post_status="publish",post_type="page",post_author=1,post_date="2009-08-23 11:33:46"),table="wp_posts")
wpAdd <- function(...,tags=NULL,custom_fields=NULL,us="",do=TRUE) {
  fields <- list(...)
  ctime <- wptime()
  if (is.null(fields$post_status)) fields$post_status <- "publish"
  if (is.null(fields$post_author) ) fields$post_author <- "1"
  if (is.null(fields$post_type) ) fields$post_type <- "page"
  if (is.null(fields$post_date) ) fields$post_date <- ctime[["brasilia"]]
  if (is.null(fields$post_date_gmt) ) fields$post_date_gmt <- ctime[["gmt"]]
  if (is.null(fields$post_modified) ) fields$post_modified <- ctime[["brasilia"]]
  if (is.null(fields$post_modified_gmt) ) fields$post_modified_gmt <- ctime[["gmt"]]
  print(fields)
  tname <- function(name) paste("wp_",us,name,sep='') 
  ## adding a new page
  ## add the post to _posts
  ##res <- dbGetQuery(connect,st)  
  res <- dbInsert(connect,fields,table=tname("posts"))
  postid <- dbGetQuery(connect,"select LAST_INSERT_ID()")[1,1]
  if (length(tags)>0) {
    ## add tags if they do not exist
    ## Note:  slug is the unique identifier
    dbInsert(connect,tags,table=tname("terms"),update=TRUE)
    ## get the term ids for all tags
    termid <- unlist(lapply(tags$slug,function(x) dbGetQuery(connect,paste("select term_id from ",tname("terms"), " where slug=",shQuote(x)))))
    ## update table linking posts to tags
    dbInsert(connect,data.frame(object_id=postid,term_taxonomy_id=termid),table=tname("term_relationships"))
    ## update taxonomy and count
    dbInsert(connect,data.frame(term_id=termid,taxonomy="post_tag",count=1),extra=" on duplicate key update count=count+1",table=tname("term_taxonomy"))
  }
  list(postid,termid)
}

wpClean <- function() {
  res <- lapply(c("truncate wp_postmeta","truncate wp_posts","truncate wp_term_relationships","truncate wp_term_taxonomy","truncate wp_terms"),function(x) dbGetQuery(connect,x))
  return()
}


  
