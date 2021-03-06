rf <- function(x=NULL) {
  if (.Platform$OS.type!="unix") {
    run.from <- "C:/reps/CongressoAberto"
  } else {
    run.from <- "~/reps/CongressoAberto"
  }
  ## side effect: load functions
  source(paste(run.from,"/R/caFunctions.R",sep=""))
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
post_content <- descr::toUTF8("<h3>Pesquisa Legislativa IUPERJ-Oxford</h3>
                 Realizada no primeiro semestre de 2009, a PLIO da continuidade aos surveys realizados originalmente por 
                 <a href='http://www.politics.ox.ac.uk/about/staff/staff.asp?action=show&person=236&special'>Timothy Power</a> (University of Oxford),
                 uma vez por legislatura, desde 1990. A base de dados completa, incluindo as seis edi��es j� realizadas, estar� disponivel at� o final do 
                 ano. As p�ginas abaixo trazem apenas alguns exemplos dos dados que ser�o disponibilizados a todos os interessados.<br><br>
                 <ul><?php global $post;$thePostID = $post->ID;wp_list_pages( 'child_of='.$thePostID.'&title_li='); ?></ul>",from = "WINDOWS-1252") 

pid <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content,
                    fulltext=paste("survey","opini�o","ideologia"), ## put in the full text field terms that you'd like the search function to use to  find this post
                    post_excerpt=paste("Pesquisa Legislativa IUPERJ-Oxford"), ## summary of the post. it is what is shown in the front page, or in the search results.
                    tags=data.frame(slug=c("survey","opiniao","ideologia"),name=c("survey","opini�o","ideologia")))

post_title <- "A Direita Envergonhada"
post_content <- descr::toUTF8("<table>
                 <tr>
                 <td><p><img src='/php/timthumb.php?src=/images/plio/fig-mosaicrep2009.png&w=400&zc=1' alt='Direita Envergonhada 1'/></p></td>
                 <td><explain>
                 A imensa maioria dos legisladores se coloca � esquerda de onde os legisladores de outros
                 partidos posicionam o seu partido. Este resultado indica que o fen�meno da 'Direita Envergonhada,' constatado por estudos anteriores,
                 continua existindo mesmo transcorridos mais de 20 anos desde of final do regime militar. 
                 </explain></td>
                 </tr>
                 </table>
                 <table>
                 <tr>
                 <td><explain> Esta tend�ncia se manifesta tambem no gr�fico ao lado, que mostra que apenas 14% dos legisladores se 
                 coloca a direita de onde eles mesmos posicionam o seu pr�prio partido. Como compara��o, 26% dos legisladores indicam estar a esquerda
                 de seu pr�prio partido. </explain></td>
                 <td> <p><img src='/php/timthumb.php?src=/images/plio/fig-mosaicself2009.png&w=400&zc=1' alt='Direita Envergonhada 2'/></p> </td>
                 </tr>
                 </table>",from = "WINDOWS-1252") 
pid1 <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content,
                     post_parent=pid,
                     fulltext=paste("survey","opini�o","ideologia"), 
                    post_excerpt=paste("Pesquisa Legislativa IUPERJ-Oxford"),
                    tags=data.frame(slug=c("survey","opiniao","ideologia"),name=c("survey","opini�o","ideologia")))


post_title <- 'Ideologia dos Partidos'
post_content <- '<img src="/php/timthumb.php?src=/images/plio/fig-allparties2.png&w=400&zc=1" alt="Ideologia Partidária: 1989-2009" />'
pid2 <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content,
                     post_parent=pid,
                     fulltext=paste("survey","opini�o","ideologia"), 
                    post_excerpt=paste("Pesquisa Legislativa IUPERJ-Oxford"), 
                    tags=data.frame(slug=c("survey","opiniao","ideologia"),name=c("survey","opini�o","ideologia")))

post_title <- "Ideologia: PT & PSDB"
post_content <- '<img src="/php/timthumb.php?src=/images/plio/fig-ptpsdb.png&w=400&zc=1" alt="Estimativa da posição ideológica de cada partido e do legislador mediano " />'
pid3 <- wpAddByTitle(conwp,post_title=post_title,post_name='ideologia-partidaria-pt-psdb',
                     post_content=post_content,
                     post_parent=pid,
                     fulltext=paste("survey","opini�o","ideologia","PT","PSDB"), 
                    post_excerpt=paste("Pesquisa Legislativa IUPERJ-Oxford"), 
                    tags=data.frame(slug=c("survey","opiniao","ideologia","PT","PSDB"),name=c("survey","opini�o","ideologia","PT","PSDB")))
