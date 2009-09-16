rf <- function(x=NULL) {
  if (.Platform$OS.type!="unix") {
    run.from <- "C:/reps/CongressoAberto"
  } else {
    run.from <- "~/reps/CongressoAberto"
  }
  ## side effect: load functions
  source(paste(run.from,"/R/caFunctions.R",sep=""),encoding="utf8")
  if (is.null(x)) {
    run.from
  } else {
    paste(run.from,"/",x,sep='')
  }
}
source(rf("R/wordpress.R"))

connect.db()
connect.wp()




post_title <- "PLIO"
post_content <- "<h3>Pesquisa Legislativa IUPERJ-Oxford</h3>
                 Realizada no primeiro semestre de 2009, a PLIO da continuidade aos surveys realizados originalmente por Timothy Power (University of Oxford),
                 uma vez por legislatura, desde 1990. A base de dados completa, incluindo as seis edicoes ate hoje realizadas, estara disponivel ate o final do 
                 ano. As paginas abaixo trazem apenas alguns exemplos dos dados que serao disponibilizados a todos os interessados.<br><br>
                 <ul><?php global $post;$thePostID = $post->ID;wp_list_pages( 'child_of='.$thePostID.'&title_li='); ?></ul>"
pid <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content,
                    fulltext=paste("survey","opinião","ideologia"), ## put in the full text field terms that you'd like the search function to use to  find this post
                    post_excerpt=paste("Pesquisa Legislativa IUPERJ-Oxford"), ## summary of the post. it is what is shown in the front page, or in the search results.
                    tags=data.frame(slug=c("survey","opiniao","ideologia"),name=c("survey","opinião","ideologia")))

post_title <- "A Direita Envergonhada"
post_content <- "<table>
                 <tr>
                 <td><p><img src='/php/timthumb.php?src=/images/plio/fig-mosaicrep2009.png&w=300&zc=1' alt='Direita Envergonhada 1'/></p></td>
                 <td><explain>
                 A imensa maioria dos legisladores se coloca a esquerda de onde os legisladores de outros
                 partidos posicionam o seu partido. Este resultado indica que o fenomeno da 'Direita Envergonhada,' constatado por estudos anteriores,
                 continua existindo mesmo transcorridos mais de 20 anos desde of final do regime militar. 
                 </explain></td>
                 </tr>
                 </table>
                 <table>
                 <tr>
                 <td><explain> Esta tendencia se manifesta tambem no grafico ao lado, que mostra que apenas 14% dos legisladores se 
                 coloca a direita de onde eles mesmos posicionam o seu proprio partido. Como comparacao, 26% dos legisladores indicam estar a esquerda
                 de se proprio partido. </explain></td>
                 <td> <p><img src='/php/timthumb.php?src=/images/plio/fig-mosaicself2009.png&w=300&zc=1' alt='Direita Envergonhada 2'/></p> </td>
                 </tr>
                 </table>"
pid1 <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content,
                     post_parent=pid,
                     fulltext=paste("survey","opinião","ideologia"), 
                    post_excerpt=paste("Pesquisa Legislativa IUPERJ-Oxford"),
                    tags=data.frame(slug=c("survey","opiniao","ideologia"),name=c("survey","opinião","ideologia")))


post_title <- 'Ideologia dos Partidos'
post_content <- '<img src="/php/timthumb.php?src=/images/plio/fig-allparties2.png&w=400&zc=1" alt="Ideologia PartidÃ¡ria: 1989-2009" />'
pid2 <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content,
                     post_parent=pid,
                     fulltext=paste("survey","opinião","ideologia"), 
                    post_excerpt=paste("Pesquisa Legislativa IUPERJ-Oxford"), 
                    tags=data.frame(slug=c("survey","opiniao","ideologia"),name=c("survey","opinião","ideologia")))

post_title <- "Ideologia: PT & PSDB"
post_content <- '<img src="/php/timthumb.php?src=/images/plio/fig-ptpsdb.png&w=400&zc=1" alt="Estimativa da posiÃ§Ã£o ideolÃ³gica de cada partido e do legislador mediano " />'
pid3 <- wpAddByTitle(conwp,post_title=post_title,post_name='ideologia-partidaria-pt-psdb',
                     post_content=post_content,
                     post_parent=pid,
                     fulltext=paste("survey","opinião","ideologia","PT","PSDB"), 
                    post_excerpt=paste("Pesquisa Legislativa IUPERJ-Oxford"), 
                    tags=data.frame(slug=c("survey","opiniao","ideologia","PT","PSDB"),name=c("survey","opinião","ideologia","PT","PSDB")))
