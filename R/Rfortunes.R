source("/home/ca/reps/CongressoAberto/R/twitter.R")
#install.packages("fortunes")

library(fortunes)
f <- paste(fortune()[c("quote","author")], collapse=" ")
while (nchar(f)>140) {
    f <- paste(fortune()[c("quote","author")], collapse=" ")
}
tweet.now(f, userpwd="Rfortunes:e321109")

