## Data from TSE with zonas
## electoral
load("../data/electoral/2006.RData")

##votos.x situacao.x turno.x votos.y situacao.y turno.y
library(gtools)
tmp <- smartbind(depfed.nom,depest.nom,senador,governador1,presidente1)
tmp <- subset(tmp,situacao!='renuncia/falecimento com substituicao')

library(reshape)
mun <- data.frame(recast(tmp,uf+municipio+cargo+nome+numero+nome.urna+partido.sigla+legenda+situacao~variable,measure.var="votos",fun.aggregate=sum))
mun$tseid <- with(mun,paste(cargo,toupper(uf),numero,sep=";"))
rm(tmp)

connect.db()
dbRemoveTable(connect,"br_tse2006mun")
dbRemoveTable(connect,"br_tse2006legis")
dbWriteTableSeq(connect,"br_tse2006mun",mun,500)
dbWriteTableSeq(connect,"br_tse2006legis",tmp,500)
