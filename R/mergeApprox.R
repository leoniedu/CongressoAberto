agrep.match <- function(x.names,y.names,x.ind,y.ind,s=seq(0,0.5,.1),exact.first=TRUE) {
  ##merged holds the matched data
  merged <- NULL
  ## first we transform x to help in the matching: ignore cases and kill multiple white space
  x.names <- gsub(" +"," ",tolower(as.character(x.names)))
  y.names <- gsub(" +"," ",tolower(as.character(y.names)))  
  ## create indexes
  x.id <- 1:length(x.names)
  y.id <- 1:length(y.names)
  ##match exactly first
  if (exact.first) {
    tmp <- match(x.names,y.names)
    if (length(tmp)>0) {
      ## save in merged what matches 
      merged <- na.omit(rbind(merged,
                              data.frame(x.id=x.id,
                                         y.id=y.id[tmp],
                                         threshold=-1)[!duplicated(tmp),]))
      ##save the unmatched obs indexes in x.id and y.id
      x.id <- x.id[!(x.id%in%merged[,1])]
      y.id <- y.id[!(y.id%in%merged[,2])]
    }
  }
  ##match approximately for each threshold in s
  for (i in s) {
    ##match both ways
    ##x->y
    tmp <- sapply(x.id,function(x) agrep(x.names[x],y.names[y.id],
                                         max.distance=i)[1])
    ## xm is a index of tmp with the non missing data
    xm <- !is.na(tmp)
    if (sum(xm)>0) {
      ## put in merged 
      merged <- na.omit(rbind(merged,
                              data.frame(x.id=x.id[xm],
                                         y.id=y.id[tmp[xm]],
                                         threshold=i)[!duplicated(tmp),]))
      x.id <- x.id[!(x.id%in%merged[,1])]
      y.id <- y.id[!(y.id%in%merged[,2])]
    }
    ##y->x
    tmp <- sapply(y.id,function(x) agrep(paste(y.names[x]," "),x.names[x.id],
                                         max.distance=list(all=i,substitutions=i,deletions=i,insertions=i))[1])
    xm <- !is.na(tmp)
    if (sum(xm)>0) {
      merged <- na.omit(rbind(merged,
                              data.frame(y.id=y.id[xm],
                                         x.id=x.id[tmp[xm]],
                                         threshold=i)[!duplicated(x.id[tmp[xm]]),]))
      x.id <- x.id[!(x.id%in%merged[,1])]
      y.id <- y.id[!(y.id%in%merged[,2])]
    }
  }
  merged <- data.frame(merged)
  merged$y.ind <- y.ind[merged$y.id]
  merged$x.ind <- x.ind[merged$x.id]
  merged <- subset(merged,select=c(-x.id,-y.id))
  list(matched=merged,unmatched.x=x.ind[x.id],
       unmatched.y=y.ind[y.id])
}

index <- function(x,y) which(y==x)

merge.a.one <- function(x,data1,data2,by1="uf",by2="municipio",maxd=0.3, ...) {
  print(tolower(as.character(x)))  
  obs.x <- index(x,data1[,by1])
  obs.y <- index(x,data2[,by1])
  if ((length(obs.x)!=0)&(length(obs.y)!=0)) {
    tmp <- data.frame(agrep.match(
                                  data1[,by2][obs.x],
                                  data2[,by2][obs.y],
                                  x.ind=obs.x,
                                  y.ind=obs.y,
                                  ...)[[1]])
    tmp[,by1] <- x
  } else {
    tmp <- NULL
  }
  tmp    
}

merge.one <- function(data1,data2,by1="municipio",maxd=0.3, ...) {
  distseq <- seq(0, maxd, length=5)
  tmp <- data.frame(agrep.match(
                                data1[,by1],
                                data2[,by1],
                                x.ind=1:nrow(data1),
                                y.ind=1:nrow(data2),
                                s=distseq, 
                                ...)[[1]])
  data.frame(data1[tmp$x.ind,], data2[tmp$y.ind, ])
}


merge.approx <- function(x,data1,data2,by1,by2,...) {
  x <- tolower(x)
  data1[,by1] <- as.character(tolower(clean(data1[,by1])))
  data2[,by1] <- as.character(tolower(clean(data2[,by1])))
  data1[,by2] <- as.character(tolower(clean(data1[,by2])))
  data2[,by2] <- as.character(tolower(clean(data2[,by2])))
  merged <- lapply(x,merge.a.one,data1=data1,data2=data2,by1=by1,by2=by2,...)
  tmp <- NULL
  for (i in 1:length(merged)) tmp <- rbind(tmp,merged[[i]])
  ##take out repeated by1 column
  data2 <- data.frame(data2[,-match(by1,names(data2))])
  if (ncol(data2)==1) {
    names(data2) <- by1
  }
  tmp <- data.frame(data1[tmp$x.ind,],data2[tmp$y.ind,],
                    with(tmp,data.frame(threshold,x.ind,y.ind)))
  attr(tmp,"data1.miss") <- which(!1:nrow(data1)%in%tmp$x.ind)
  attr(tmp,"data2.miss") <- which(!1:nrow(data2)%in%tmp$y.ind)
  unique(tmp)
}



