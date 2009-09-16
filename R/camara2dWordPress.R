# !Mode:: "R:UTF-8"

### TENTANDO POSTAR AUTOMATICAMENTE

a <- dbGetQuery(conwp, 'select * from wp_posts')

pid <- dbGetQuery(conwp, 'select * from '%+%tname("posts")%+%' where post_title="Dados e AnÃ¡lises"')$ID[1] 
the.content <- "<table>
                <tr>
                <td><p><img width=400 src='/images/camara/2dhierarchical20072009.png' alt='Camara em Duas Dimensoes'/></p></td>
                <td><explain>A posição dos legisladores em duas dimensões (em cinza claro) é estimada a partir das votações nominais realizadas na Câmara.
                        Utilizamos um modelo no qual a primeira dimensão é identificada a partir de dados de survey, de modo a corresponder 
                        à clivagem direita-esquerda. A segunda dimensão é deixada livre, e o resultado é que os partidos são ordenados pelo que parece
                        ser seu grau de `governismo'. A maioria das votações na atual legislatura separam governo de oposição e 
                        não esquerda de direita. A cor dos circulos representando a posição dos partidos vai de branco (sempre no gabinete) a preto 
                        (nunca no gabinete).</explain></td>
                </tr>
                </table>"
sub.content <- NULL

  the.content <- paste(the.content,sub.content)

  postid <- wpAddByTitle(conwp,post_title="A Câmara em Duas Dimensões", 
                        post_name="2dhierarchical20072009",
                        post_author=2,
                        post_type="page", ## can be page
                        post_content=the.content,
                        post_parent=pid,
                        fulltext=paste("votações","posições","ideologia"), ## put in the full text field terms that you'd like the search function to use to  find this post
                        post_excerpt=paste("Pontos ideais na Câmara em duas dimensões"), ## summary of the post. it is what is shown in the front page, or in the search results.
                        tags=data.frame(slug=c("posicoes-ideais","ideologia"),name=c("Posições Ideais","Ideologia"))
                        ) ## tag the post  format similar to categories and custom fields
                      
                    
    
