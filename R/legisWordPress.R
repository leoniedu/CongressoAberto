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

connect.db()
connect.wp()



## all deps
##dall <- dbGetQueryU(connect,"SELECT bioid FROM br_bio")[,1]

## what are the current deps?
dnow <- dbGetQueryU(connect,"SELECT bioid, state FROM br_deputados_current")

if (!update.all) {
    idin <- dbGetQueryU(connect,paste("SELECT * FROM br_bioidpostid"))  
    dnow <- dnow[!dnow$bioid%in%idin$bioid,]
}


## add parent page (deputados)
pname <- "Deputados"
pp <- dbGetQuery(conwp,paste("SELECT ID FROM ",tname("posts")," where post_title=",shQuote(pname)," and post_type='page'"))
if (nrow(pp)==0) {
    stop("create the legislators main page first!")
} else {
    pdeps <- pp$ID[1]
}

## add parent pages (state)
for (i in unique(toupper(dnow$state))) {
    ##i <- state.a2L(i)
    pp <- wpAddByName(conwp,post_title=toupper(i), post_name=tolower(i),
                      post_content='<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( "child_of=".$thePostID."&title_li="); ?></ul>',post_parent=pdeps)
}





postlegis <- function(bioid, skip=FALSE) {
    idname <- dbGetQueryU(connect,paste("SELECT a.bioid, b.namelegis, a.state, a.party, b.* FROM br_deputados_current as a, br_bio as b where a.bioid=b.bioid and a.bioid=",bioid))
    parentp <- dbGetQuery(conwp,paste("SELECT ID FROM ",tname("posts")," where post_name=",shQuote(idname$state)," and post_type='page'"))
    parent_id <- parentp$ID[1]
    title <- idname$namelegis
    post_content <- paste('<script language=\"php\">$bioid = ',bioid,';include(\'php/legislator.php\');</script>')
    fulltext <-  paste(dbGetQueryU(connect,paste("select * from br_bio where bioid=",bioid)),collapse="\n")
    post_excerpt = with(idname, paste(namelegis, toupper(state), party, sep=" / "))
    pid <- wpAddByTitle(conwp,post_title=title,post_name=encode(title),
                        post_content=post_content,
                        post_parent=parent_id,
                        post_excerpt=post_excerpt,
                        fulltext=fulltext)
    print(pid)
    res <- data.frame(bioid=bioid,postid=pid)
  dwp <- dbWriteTableU(connect,"br_bioidpostid",res,append=TRUE)
}


lapply(dnow$bioid,postlegis)

