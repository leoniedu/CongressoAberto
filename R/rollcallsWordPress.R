if (!exists("update.all", 1)) {  
  update.all <- FALSE
}

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
source(rf("R/spatial.R"))
source(rf("R/wordpress.R"))

connect.db()
connect.wp()










rcsnow <- dbGetQuery(connect,"select rcvoteid, rcdate from br_votacoes where legis="%+%"53")

## whats already in
if (!update.all) {
    rcsin <- dbGetQuery(connect,"select * from br_rcvoteidpostid")
    rcsnow <- rcsnow[!rcsnow$rcvoteid%in%rcsin$rcvoteid,]
}

## decreasing order by date
rcsnow <- rcsnow[order(rcsnow$rcdate, decreasing=TRUE),]

rcsnow <- rcsnow$rcvoteid


##billsnow <- bills$billid[sample(1:nrow(bills),2)]
##billsnow <- bills$billid[1:nrow(bills)]
##t(sapply(rcsnow[-c(1:10)],postroll, saveplot=TRUE, post=FALSE))
##t(sapply(rcsnow[-c(1:10)],postroll, saveplot=FALSE, post=FALSE))
##t(sapply(tail(rcsnow,10),postroll, saveplot=TRUE, post=TRUE))
##t(sapply(tail(rcsnow,10),postroll, saveplot=TRUE, post=FALSE))
##try(system("syncCA images"))


if (!exists("dopar", 1)) {
  dopar <- FALSE
}

rcsnow <- rcsnow
nrc <- length(rcsnow)
if (nrc>0) {
    ##rcsnow <- rcsnow[1:min(6,nrc)]
    print(rcsnow)
    if (dopar) {
        library(doMC)
        registerDoMC(2)
        fx <- function(x) {
            try(connect.db())
            try(connect.wp())
            dbListTables(connect)
            print(x)
            try(postroll(x, saveplot=TRUE, post=TRUE), silent=FALSE)
        }
      system.time(foreach(x=rcsnow) %dopar% fx(x))
    } else {
        res <- t(sapply(rcsnow, function(x) {
            print(x)
            try(postroll(x, saveplot=TRUE, post=TRUE), silent=FALSE)
        }))
    }
}




