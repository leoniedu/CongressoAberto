##by hadley wickham
library(plyr)
library(gpclib)

invert <- function (L) {
 t1 <- unlist(L)
 names(t1) <- rep(names(L), lapply(L, length))
 tapply(names(t1), t1, c)
}

get_attributes <- function(shape) {
 attr <- as.data.frame(shape)
 names(attr) <- tolower(names(attr))
 attr
}

get_borders <- function(shape) {
 attr <- get_attributes(shape)
 cp <- polygons(shape)

 # Figure out how polygons should be split up into the region of interest
 # Currently just assume that this given by the first variable in the
 # attributes
 polys <- split(as.numeric(row.names(attr)), list(attr[, 1]))

 # Union together all polygons that make up a region
 unioned <- unionSpatialPolygons(cp, invert(polys))

 # Extract coordinates of regions
 get_coords <- function(x) {
   df <- as.data.frame(x@polygons[[1]]@Polygons[[1]]@coords)
   names(df) <- c("long", "lat")
   df
 }
 coords <- llply(seq_along(polys), function(i) get_coords(unioned[i]))

 thinned <- coords

 # Add column to identify region
 named <- mlply(cbind(df = thinned, id = names(polys)),
   function(df, id) transform(df, id = id))
 
 do.call("rbind", named)
}
