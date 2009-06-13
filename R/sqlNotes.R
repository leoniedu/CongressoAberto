xsource("~/reps/CongressoAberto/R/caFunctions.R",encoding="UTF8")
connect.db()


dbio <- dbGetQuery(connect, "select namelong,birth,state,sessions from br_bio")
dvot <- dbGetQuery(connect, "select voto,name,state from br_votos limit 100")

write.csv(dbio,file="~/Desktop/d1.csv")
write.csv(dvot,file="~/Desktop/d2.csv")



dbSendQuery(connect,"update  wp_hufib7_posts set post_title='maçã2' where ID=827")

dbRemoveTable(connect,"tmp")
dbRemoveTable(connect,"tmp2")
dbRemoveTable(connect,"tmp3")

tmp3 <- data.frame(post_title=iconv("maçã5",from='latin1'))

dbWriteTable(connect,"tmp3",tmp3)

dbSendQuery(connect,"update  wp_hufib7_posts set post_title=(select post_title from tmp3)  where ID=827")

(select post_title from tmp3 AS CHAR CHARACTER SET latin1)


dbSendQuery(connect,"create table tmp as select post_title from  wp_hufib7_posts  where ID=827")



dbSendQuery(connect,"create table tmp like  wp_hufib7_posts")


dbSendQuery(connect,"create table tmp2 like  tmp")

m1 <- "maçã4 é bom d+ sô"
m1 <- iconv(m1,from="latin1")
dbWriteTable(connect,"tmp",data.frame(post_title=m1))
dbSendQuery(connect,"update  wp_hufib7_posts set post_title=(select post_title from tmp limit 1) where ID=827")



system.time(replicate(100,dbSendQuery(connect,paste("insert into tmp2 (post_title) VALUES('",m1,"') ",sep=""))))

dbWriteTable(connect,"tmp2",tmp3,append=TRUE)

dbSendQuery(connect,"create table tmp as select post_title from  wp_hufib7_posts  where ID=827")




dbGetQuery(connect,"select * from tmp3")


dbSendQuery(connect,"insert into tmp (post_title) VALUES('maçã3') ")
