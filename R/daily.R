## daily cron job for R
download.now <- TRUE
update.all <- FALSE
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
rf()
usource(rf("R/updateVot.R"),echo=TRUE)
usource(rf("R/deputados.R"),echo=TRUE)
usource(rf("R/parties.R"),echo=TRUE)
