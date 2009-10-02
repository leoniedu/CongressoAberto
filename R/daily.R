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
  source(paste(run.from,"/R/caFunctions.R",sep=""))
  if (is.null(x)) {
    run.from
  } else {
    paste(run.from,"/",x,sep='')
  }
}
rf()

connect.db()
connect.wp()

unlink(path.expand(rf("data/camara/rollcalls/*.zip")))
unlink(path.expand(rf("data/camara/rollcalls/extracted/*")))


usource(rf("R/deputados.R"),echo=TRUE)
usource(rf("R/updateVot.R"),echo=TRUE)
usource(rf("R/downloadbills.R"),echo=TRUE)
usource(rf("R/processbills.R"),echo=TRUE)
usource(rf("R/parties.R"),echo=TRUE)

## update wordpress
## update this first, roll calls are needed for the legislator php
usource(rf("R/billsWordPress.R"),echo=TRUE)
usource(rf("R/legisWordPress.R"),echo=TRUE)
usource(rf("R/rollcallsWordPress.R"),echo=TRUE)


## less than daily
##usource(rf("R/indicesCamara.R"),echo=TRUE)
##usource(rf("R/abstentionsWordpress.R"),echo=TRUE)
##usource(rf("R/partiesWordpress.R"),echo=TRUE)
## indices
##usource(rf("R/indicesParties.R"),echo=TRUE)

print(Sys.time())
