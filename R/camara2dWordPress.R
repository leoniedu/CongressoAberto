### TENTANDO POSTAR AUTOMATICAMENTE

a <- dbGetQuery(conwp, 'select * from wp_posts')

pid <- dbGetQuery(conwp, 'select * from '%+%tname("posts")%+%' where post_title="Dados e AnÃ¡lises"')$ID[1] 
the.content <- "<table>
                <tr>
                <td><p><img width=400 src='/images/camara/2dhierarchical20072009.png' alt='Camara em Duas Dimensoes'/></p></td>
                <td><explain>A posicao dos legisladores em duas dimensoes (em cinza claro) e estimada a partir das votacoes nominais realizadas na Camara.
                        Utilizamos um um modelo hierariquico no qual a primeira dimensao e identificada a partir de dados de survey, de modo a corresponder 
                        a clivagem direita-esquerda. A segunda dimensao eh deixada livre, e o resultado eh que os partidos aparecem ordenados pelo que parece
                        ser seu grau de `governismo'. A maioria das votacoes na atual legislatura (omitidas por simplicidade) separam governo de oposicao e 
                        nao esquerda de direita. A cor dos circulos representando a posicao dos partidos vai de branco (sempre no gabinete) a preto 
                        (nunca no gabinete).</explain></td>
                </tr>
                </table>"
sub.content <- NULL

  the.content <- paste(the.content,sub.content)

  postid <- wpAddByTitle(conwp,post_title="A CÃ¢mara em Duas DimensÃµes", 
                        post_name="2dhierarchical20072009",
                        post_author=2,
                        post_type="page", ## can be page
                        post_content=the.content,
                        post_parent=pid,
                        fulltext=paste("votações","posições","ideologia"), ## put in the full text field terms that you'd like the search function to use to  find this post
                        post_excerpt=paste("Pontos ideais na Câmara em duas dimensões"), ## summary of the post. it is what is shown in the front page, or in the search results.
                        tags=data.frame(slug=c("posicoes-ideais","ideologia"),name=c("Posições Ideais","Ideologia"))
                        ) ## tag the post  format similar to categories and custom fields
                      
                    
    
