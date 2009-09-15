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



## all deps
##dall <- dbGetQueryU(connect,"SELECT bioid FROM br_bio")[,1]

## ## what are the current deps?
## dnow <- dbGetQueryU(connect,"SELECT b.namelegis as Nome, upper(a.state) as Estado, a.party as Partido, round(c.ausente_prop*100) `% faltas no ultimo ano`,
## postid
## FROM
## br_deputados_current as a,
## br_bio as b,
## br_ausencias as c,
## br_bioidpostid as d
## WHERE a.bioid=b.bioid and a.bioid=c.bioid and a.bioid=d.bioid
## ")

## add parent page (deputados)
pname <- "Deputados"
pdeps <- wpAddByTitle(conwp,post_title=pname,
                      post_content='<?php include_once("php/legislist.php"); ?>')
