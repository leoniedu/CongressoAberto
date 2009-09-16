
## paths (put on the beg of R scripts)
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
rf()

source(rf("R/wordpress.R"))

connect.db()

connect.wp()

content <- "
<p> <a href=\"CongressoAberto.com.br\"> Congressoaberto.com.br </a>  é um site independente que visa aumentar a transparência e contribuir para debates acerca do legislativo brasileiro, facilitando o acesso a informação e análise sobre o tema. <a href=\"CongressoAberto.com.br\"> Congressoaberto.com.br </a> utiliza informações oficiais providas pelo Congresso e outras órgãos do governo para gerar um panorama completo da atuação de parlamentares e partidos, utilizando para isso conceitos e métodos da ciência política moderna. </p>
<p> As informações legislativas são atualizadas diariamente, de modo que as estatísticas geradas utilizam sempre toda a informação disponível. O projeto encontra-se atualmente na fase de testes, estando disponível apenas para um pequeno grupo de potenciais interessados. Nesta fase receberemos sugestões e críticas dos usuários, e buscaremos adicionar informações e melhorar a veiculação daquelas que já estão disponíveis. No futuro, e contingente no interese da comunidade acadêmica e dos formadores de opinião, e na obtenção de financiamento, o escopo do projeto poderá será expandido consideravelmente.</p>
<h2> Missão </h2>
<p> <a href=\"CongressoAberto.com.br\"> Congressoaberto.com.br </a> é uma organização sem fins lucrativos e sem vinculação partidária cujo objetivo é aumentar a transparência no legislativo brasileiro através da divulgação e análise de informações que já estão no domínio público de forma acessível e compreensível a todos os interessados. </p>
 <h2> Equipe </h2>
<p> <a href=\"CongressoAberto.com.br\"> Congressoaberto.com.br </a>  é um projeto inteiramente voluntário desenvolvido pelos cientistas políticos <a href=\"http://eduardoleoni.com\"> Eduardo Leoni </a> e <a href=\"http://www.princeton.edu/~zucco\"> Cesar Zucco Jr.</a> Sugestões e críticas devem ser enviadas para <a href=\"mailto:admin@congressoaberto.com.br?subject=congressoaberto.com.br\">admin@congressoaberto.com.br</p>"






##FIX: insert date in the post?
wpAddByTitle(conwp,post_title="Quem Somos"
             ,post_content=content
             ,post_type="page")

