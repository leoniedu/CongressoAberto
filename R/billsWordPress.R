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


propid <- wpAddByName(conwp,post_title="Proposições",post_name="proposicoes",post_content='<?php include("php/bill_list.php"); ?>')


bills <- dbGetQueryU(connect, "select * from br_bills")
billsin <- dbReadTable(connect, "br_billidpostid")

##billsnow <- bills$billid[!bills$billid%in%billsin$billid]
##billsnow <- bills$billid[sample(1:nrow(bills),2)]
billsnow <- bills$billid[1:nrow(bills)]

for (i in billsnow) {
    postbill(i, propid=propid)
}
