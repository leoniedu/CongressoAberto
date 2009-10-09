## twitter poster for R
## allows delays
##based on http://www.cerebralmastication.com/?p=274
tweet.now <- function(status,userpwd="username:password") {
  require(tcltk)
  require("RCurl")
  opts <- curlOptions(header = FALSE, userpwd = userpwd, netrc = FALSE)
  method <- "http://twitter.com/statuses/update.xml?status="
  encoded_status <- URLencode(status)
  request <- paste(method,encoded_status,sep = "")
  postForm(request,.opts = opts)
  print(paste("You have just twitted: ",status))
}
 
tweet <- function(status, ..., wait=1) {
    if (nchar(status)>140) stop("tweet is too long")
    require(tcltk)
    ## only one tweet permitted
    if (exists(".id")&&(!is.null(.id))) stop("another tweet is in the queue!")
    ##wait is in minutes
    ## convert to ms
    waitS <- wait*60*1000
    z <- function() {
        tweet.now(status, ...)
        tweet.cancel()
    }
    .id <<- tcl("after", waitS, z)
    attr(.id,"status") <- status
}
 
 
tweet.cancel <- function() {
  tcl("after","cancel",.id)
  .id <<- NULL
}
tweet.info <- function()  {
  tcl("after","info",.id)
  print(attr(.id,"status"))
}
