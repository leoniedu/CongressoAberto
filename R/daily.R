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
usource(rf("R/deputados.R"),echo=FALSE)
usource(rf("R/updateVot.R"),echo=FALSE)
usource(rf("R/downloadbills.R"),echo=FALSE)
usource(rf("R/processbills.R"),echo=FALSE)
##usource(rf("R/getLeaders.R"),echo=FALSE) ## not needed, it runs inside readOne
usource(rf("R/parties.R"),echo=FALSE)
usource(rf("R/billsWordPress.R"),echo=FALSE)
usource(rf("R/legisWordPress.R"),echo=FALSE)
usource(rf("R/rollcallsWordPress.R"),echo=FALSE)
stop()
usource(rf("R/indicesParties.R"),echo=FALSE)
## update wordpress
