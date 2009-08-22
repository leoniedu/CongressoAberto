## paths (put on the beg of R scripts)
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

connect.db()


vot <- dbGetQueryU(connect,"select * from br_votacoes")

##vots <- ddply(vot,"rcyear",function(x) x[sample(1:nrow(x),3,replace=TRUE),])
##vots <- subset(vot,rcyear=="1998")
res <- lapply(vot$rcvoteid,getLeaders)
## try again
res <- lapply(vot$rcvoteid,getLeaders)

