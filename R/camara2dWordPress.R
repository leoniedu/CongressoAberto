### TENTANDO POSTAR AUTOMATICAMENTE

pid <- dbGetQuery(conwp, 'select * from '%+%tname("posts")%+%' where post_title="Dados e Análises"')$ID[1] 
the.content <- "<table>
                <tr>
<td><p><img width=400 src='/php/timthumb.php?src=/images/camara/2dhierarchical20072009.png&w=400&h=0' alt='Camara em Duas Dimensoes'/></p></td>
                <td><explain>A posição dos legisladores em duas dimensoes (em cinza claro) é estimada a partir das votações nominais realizadas na Camara.
                        Utilizamos um um modelo hierárquico no qual a primeira dimensão é identificada a partir de dados de survey, de modo a corresponder 
                        à clivagem direita-esquerda. A segunda dimensão é deixada livre, e o resultado é que os partidos aparecem ordenados pelo que parece
                        ser seu grau de `governismo'. A maioria das votacoes na atual legislatura (omitidas por simplicidade) separam governo de oposicão e 
                        não esquerda de direita. A cor dos circulos representando a posição dos partidos vai de branco (sempre no gabinete) a preto 
                        (nunca no gabinete).</explain></td>
                </tr>
                </table>"
sub.content <- NULL

the.content <- paste(the.content,sub.content)

postid <- wpAddByName(conwp,post_title="A Câmara em Duas Dimensões", 
                       post_name="2dhierarchical20072009",
                       post_author=2,
                       post_type="page", ## can be page
                       post_content=the.content,
                       post_parent=pid,
                       fulltext=paste("votações","posições","ideologia"), ## put in the full text field terms that you'd like the search function to use to  find this post
                       post_excerpt=paste("Pontos ideais na Câmara em duas dimensões"), ## summary of the post. it is what is shown in the front page, or in the search results.
                       tags=data.frame(slug=c("posicoes-ideais","ideologia"),name=c("Posições Ideais","Ideologia"))
                       ) ## tag the post  format similar to categories and custom fields
                      
                    
    
