res <- try(load("/home/ca/reps/CongressoAberto/data/Rfortunes/i.RData"))
if (inherits(res, "try-error")) i <- 136
i <- i+1

df <- readLines("http://cran.r-project.org/web/packages/fortunes/")
fn <- gsub(".* (fortunes.*.tar.gz) .*", "\\1",df[grep("tar.gz", df)])
url <- paste("http://cran.r-project.org/src/contrib/", fn,sep='')

system(paste("wget -N",  url))
system("rm -rf fortunes")
system(paste("tar -vxzf ", fn))

dnow <- read.csv2("fortunes/inst/fortunes/fortunes.csv", stringsAsFactors=FALSE)

if (i > nrow(dnow)) {
    i <- 1
}

message <- function (x, width = 140) 
{
    if (is.na(x$context)) {
        x$context <- ""
    }
    else {
        x$context <- paste(" (", x$context, ")", sep = "")
    }
    if (is.na(x$source)) {
        x$source <- ""
    }
    if (is.na(x$date)) {
        x$date <- ""
    }
    else {
        x$date <- paste(" (", x$date, ")", sep = "")
    }
    if (any(is.na(x))) 
        stop("'quote' and 'author' are required")
    line1 <- x$quote
    line2 <- paste("   -- ", x$author, x$context, sep = "")
    line3 <- paste("      ", x$source, x$date, sep = "")
    linesplit <- function(line, width, gap = "      ") {
        if (nchar(line) < width) 
            return(line)
        rval <- NULL
        while (nchar(line) > width) {
            line <- strsplit(line, " ")[[1]]
            if (any((nchar(line) + 1 + nchar(gap)) > width)) 
                stop("'width' is too small for fortune")
            breakat <- which(cumsum(nchar(line) + 1) > width)[1] - 
                1
            rval <- paste(rval, paste(line[1:breakat], collapse = " "), 
                "\n", sep = "")
            line <- paste(gap, paste(line[-(1:breakat)], collapse = " "), 
                sep = "")
        }
        rval <- paste(rval, line, sep = "")
        return(rval)
    }
    line1 <- strsplit(line1, "<x>")[[1]]
    for (i in 1:length(line1)) line1[i] <- linesplit(line1[i], 
        width, gap = "")
    line1 <- paste(line1, collapse = "\n")
    line2 <- linesplit(line2, width)
    line3 <- linesplit(line3, width)
    c(line1, line2, line3)
}


send.mail<-function(addr,subject="Mail from R", text="empty text") {
    mail.cmd<-paste("mail ", "-s \"",subject,"\" ", addr, " << EOT &\n", text,"\n", "EOT", sep="",collapse="")
    system(mail.cmd,intern=TRUE)
    mail.cmd
}

mnow <- message(dnow[i,])
if (nchar(mnow[1])>250) {
    tmp <-     mnow[1]
    mnow[1] <- substr(mnow[1], 1, 250)
    mnow[2] <- paste(substr(tmp, 251, 2000), mnow[2], collapse="\n")
}

send.mail("\\#rfortunes+twitter@rfortunes.posterous.com", subject=mnow[1]
          , text=paste(mnow[2:3], collapse="\n"))

save(i, file="/home/ca/reps/CongressoAberto/data/Rfortunes/i.RData")

