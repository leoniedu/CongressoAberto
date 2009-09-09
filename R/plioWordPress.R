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
post_content <- "<ul><?php global $post;$thePostID = $post->ID;wp_list_pages( 'child_of='.$thePostID.'&title_li='); ?></ul>"
pid <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content)



post_title <- "Ideologia na Câmara"
post_content <- '<img src="/php/timthumb.php?src=/images/plio/fig-yearrescaled.png&w=400&zc=1"
alt="Ideologia na Câmara" />'
pid1 <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content,
                     post_parent=pid)


post_title <- 'Ideologia dos Partidos'
post_content <- '<img src="/php/timthumb.php?src=/images/plio/fig-allparties2.png&w=400&zc=1" alt="Ideologia Partidária: 1989-2009" />'
pid2 <- wpAddByTitle(conwp,post_title=post_title,
                    post_content=post_content,
                     post_parent=pid)

post_title <- "Ideologia: PT & PSDB"
post_content <- '<img src="/php/timthumb.php?src=/images/plio/fig-ptpsdb.png&w=400&zc=1" alt="Estimativa da posição ideológica de cada partido e do legislador mediano " />'
pid3 <- wpAddByTitle(conwp,post_title=post_title,post_name='ideologia-partidaria-pt-psdb',
                     post_content=post_content,
                     post_parent=pid)



