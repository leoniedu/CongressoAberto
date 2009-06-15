## revised to be more general
match.state <- function(d1,d2) subset(d2,state==d1$state)
match.office <- function(d1,d2,cargo.now="deputado federal") subset(d2,tolower(cargo)==tolower(cargo.now))
match.birthyear <- function(d1,d2,maxd=10) {
  if (!is.na(d1$birthyear)) {
    y1 <- d1$birthyear
    y2 <- d2$birthyear
    d2[y2%in%seq(y1-maxd,y1+maxd),]
  } else {
    d2
  }
}
match.name <-  function(d1,d2,maxd=.2,name1="name",name2="name") {  
  d2 <- d2[agrep(d1[,name1],d2[,name2],max.distance=maxd,ignore.case=TRUE),]
  ## if multiple matches, decrease max dist
  gseq <- seq(maxd,0,length=10)
  for (i in gseq) {
    ##while ((maxd>0.001)&(nrow(d2)>1)) {
    d2 <- d2[agrep(d1[,name1],d2[,name2],max.distance=i,ignore.case=TRUE),]
    if (nrow(d2)==1) {
      break
    }
  }
  ##if (nrow(d2)>1) stop("still multiple matches")
  d2
}  
match.birth <-  function(d1,d2,maxd=.2) {
  if (!is.na(d1$birth)) {
    d2 <- d2[agrep(as.character(d1$birth),as.character(d2$birth),max.distance=maxd),]
    ## if multiple matches, decrease max dist
    gseq <- seq(maxd,0,length=10)
    for (i in gseq) {
      d2 <- d2[agrep(d1$birth,d2$birth,max.distance=i),]
      if (nrow(d2)==1) {
        break
      }
    }
  }
  d2
}  


match <- function(d1.all,d2.all,match.all=FALSE,id="tseid",fun.match,...) {
  bioid <- matrix(ncol=3,nrow=0)
  for (i in 1:nrow(d1.all)) {
    if (i>1) {
      now <- nrow(bioid)/(i-1)
      if (match.all & (now<1)) stop(i)
    }
    if (i%%20 == 0) cat(round(i/nrow(d1.all),2),",")
    d1 <- d1.all[i,]
    d2 <- d2.all
    ## * Match exactly by office if the deputy is in the 2007-2011 bio db
    if  (d1$scurrent) {
      d2 <- subset(d2,tolower(cargo)==tolower("Deputado Federal"))
    }
    d2 <- fun.match(d1,d2,...)
    if (nrow(d2)>1) {
      stop("multiple matches")
    }  
    if (nrow(d2)==1) {
      idrow <- c(as.character(d1$bioid),as.character(d2[,id]),d2$dist)
      bioid <- rbind(bioid,idrow)
      d2.all <- d2.all[d2.all[,id]!=d2[,id],]
    }
  }
  rownames(bioid) <- NULL
  bioid <- data.frame(bioid)
  names(bioid) <- c("bioid",id,"dist")
  bioid
}



