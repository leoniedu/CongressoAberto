plot.heat.old <- function(tmp,state.map,z,title=NULL,breaks=NULL,reverse=FALSE,cex.legend=1,bw=.2,col.vec=NULL,main=NULL,plot.legend=TRUE) {
  ##Break down the vote proportions
  if (is.null(breaks)) {
    breaks=
      seq(
          floor(min(tmp@data[,z],na.rm=TRUE)*10)/10
          ,
          ceiling(max(tmp@data[,z],na.rm=TRUE)*10)/10
          ,.1)
  }
  tmp@data$zCat <- cut(tmp@data[,z],breaks,include.lowest=TRUE)
  cutpoints <- levels(tmp@data$zCat)
  if (is.null(col.vec)) col.vec <- heat.colors(length(levels(tmp@data$zCat)))
  if (reverse) {
    cutpointsColors <- rev(col.vec)
  } else {
    cutpointsColors <- col.vec
  }
  levels(tmp@data$zCat) <- cutpointsColors
  ## labels for selected areas
  ##tmp.c <- tmp@data 
  ##tmp.c$name <- capwords(tolower(as.character(tmp.c$NOMEMESO)))
  ##only metropolitana and DF
  ##tmp.c <- tmp.c[c(grep("^Metropolitana",tmp.c$name),grep("^Distrito",tmp.c$name)),]
  ##tmp.c$name <- gsub("Metropolitana D[aeo] ","",tmp.c$name)
  ##plot
  plot(tmp,border=gray(.8), lwd=bw,axes = FALSE, las = 1,col=as.character(tmp@data$zCat),main="A")
  if (!is.null(state.map)) {
      plot(state.map,add=TRUE,lwd=1)
  }
  ##with(tmp.c,text(x,y,name,cex=0.75))
  if (plot.legend) legend("bottomleft", cutpoints, fill = cutpointsColors,bty="n",title=title,cex=cex.legend)
  ##title("Cartogram")
}


##merge sp objects with data
merge.sp <- function(tmp,data,by="uf") {
  by.loc <- match(by,names(data))
  by.data <- data[,by.loc]
  data <- data[,-by.loc]
  tmp@data <- data.frame(tmp@data,
                         data[match(tmp@data[,by],by.data),]
                         )
  tmp
}

##read file and get centroids
readShape.cent <- function(shape.file="~/test.shp",IDvar="NOMEMESO") {
  require(maptools)
  ##  read shape and get centroids
  tmp <- read.shape(shape.file)
  tmp.c <- as.data.frame(get.Pcent(tmp))
  names(tmp.c) <- c("x","y")
  tmp.c[,IDvar] <- tmp$att.data[,IDvar]
  tmp <-  readShapePoly(shape.file,IDvar=IDvar)
  tm <- match(tmp@data[,IDvar],tmp.c[,IDvar])
  tmp@data$x <- tmp.c[tm,1]
  tmp@data$y <- tmp.c[tm,2]
  tmp
}

## use labels locations located inside the spatial object
readShape <- function(shape.file="~/test.shp") {
    require(maptools)
    map <- readShapePoly(shape.file)
    labelpos <- data.frame(do.call(rbind, lapply(map@polygons, function(x) x@labpt)))
    names(labelpos) <- c("x","y")                        
    map@data <- data.frame(map@data, labelpos)
    map
}



dissolve <- function(tmp,id="uf") {
  require(maptools)
  ##tmp is something read with readShapePoly
  pl <- getSpPpolygonsSlot(tmp)
  pl_new <- lapply(pl, checkPolygonsHoles)
  nc2 <- as.SpatialPolygons.PolygonsList(pl_new)
  ##identical(rownames(as(tmp, "data.frame")), getSpPPolygonsIDSlots(nc2))
  reg4 <- unionSpatialPolygons(nc2, tmp@data[,id])
  ##getSpPPolygonsIDSlots(reg4)
  reg4
}


createNonContiguousAreaBasedCartogram <-function(mapObj,targetAttribute,areaAttribute){
  ##cartogram
  outputMapObj <- mapObj

  densities <- sqrt(targetAttribute/areaAttribute)

  maxDensity<-max(densities) # get Maximum of densities
  
  k <- 1/maxDensity

  L <- k * densities

  centroidXY<-get.Pcent(mapObj) # get polygons' centroids
  
  mapObj2 <- mapObj[[1]]
  
  for(i in 1:length(mapObj2)){
    if(mapObj2[[i]]$nParts == 1){
      coords <- mapObj2[[i]]$verts
      newX<-L[i] * (coords[,1] -  centroidXY[i,1]) +
centroidXY[i,1] # new X coordinate
      newY<-L[i] * (coords[,2] -  centroidXY[i,2]) +
centroidXY[i,2] # new Y coordinate
      polygon(newX,newY,col="red") #draw polygon

    }else{
      pStartList <- mapObj2[[i]]$Pstart + 1

      allCoords <- mapObj2[[i]]$verts

      for(j in 1:length(pStartList)){
        if(j == length(pStartList))
          coords <-allCoords[pStartList[j]:nrow(allCoords),]
        else
          coords <- allCoords[pStartList[j]:(pStartList[j
+ 1] -1),]

        centroidXYP<-c(mean(coords[,1]),mean(coords[,2]))

        centroidXYP<-c( centroidXY[i,1], centroidXY[i,2])

        points(centroidXYP)

        print(centroidXYP)

        newX<-L[i] * (coords[,1] -  centroidXYP[1]) +
centroidXYP[1] # new X coordinate
        newY<-L[i] * (coords[,2] -  centroidXYP[2]) +
centroidXYP[2] # new Y coordinate
        polygon(newX,newY,col="blue") #draw polygon


      }
    }
  }
}
