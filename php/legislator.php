<script language="php">
include_once("server.php");

$table = 'br_bio';
##$bioid = 96734;
##$bioid = $_GET["bioid"];

// sending query
$result = mysql_query("SELECT 
a.namelegis, 
a.legisserved, 
b.candidate_code,
b.state,
round(c.cgov_prop*100),
d.party,
round(c.ausente_prop*100),
d.mailaddress,
a.biofile,
c.funding_total,
c.funding_party,
c.funding_number,
c.votes,
c.munname_abs,
c.munname_rel,
round(c.cparty_prop*100)
    FROM br_bio as a, br_bioidtse as b, 
    br_legis_stats as c, 
    br_deputados_current as d
    where a.bioid={$bioid} and 
    a.bioid=b.bioid
    and a.bioid=c.bioid  
    and a.bioid=d.bioid
    and b.year=2006 
     ");
if (!$result) {
  die("Query to show fields from table failed");
 }

$row = mysql_fetch_row($result);
$namelegis = $row[0];
$legisserved = $row[1];
$candno = $row[2];
$state = $row[3];
$percprogov = $row[4];
$party = $row[5];
$percausente = $row[6];
$email = $row[7];
$url = $row[8];
$funding = $row[9];
$fundingparty = $row[10];
$funders =  $row[11];
$votes = $row[12];
$munabs = $row[13];
$munrel = $row[14];
$perccompartido = $row[15];

// $statelegis = $row[2]; FIX: make it not depend on row order (Call by name)
echo '<table style="width: 100%" cellspacing = "12">'; #border='1' rules ='rows'
echo '<tr>';
print("<td rowspan='2'><img src=\"/php/timthumb.php?src=/images/bio/polaroid/foto".$bioid.".png&w=100&h=0&zc=0\" alt=\"$namelegis\" width=100/></td>");
print("<td colspan='3' >$namelegis ($party/$state)<br>
                                            Email: <a href=\"mailto:$email\"> $email </a> (<a href=\"http://www.camara.gov.br/Internet/deputado/$url\">Página Oficial</a>)<br>
                                            Legislaturas: $legisserved <br>
</td>
                </tr>
                <tr>
                                <td>Comportamento em Votações Nominais:<br>
                                    Ausente: $percausente% <br>
                                    Vota com o governo: $percprogov% <br>
                                    Vota com o partido: $perccompartido% <br>
                                </td>
                                <td>Desempenho nas Eleições de 2006: <br>
                                    Total de Votos: $votes <br>
                                    Maior n&uacute;mero de votos: $munabs <br>
                                    Maior porcentagem de votos: $munrel <br> </td>
                                <td>Financiamento de Campanha (Declarado): <br>   
                                    Doa&ccedil&otilde;es Totais: R&#36 $funding <br>
                                    Doa&ccedil&otilde;es do Partido: R&#36 $fundingparty  <br>
                                    N&uacute;mero de Doadores:  $funders<br> </td>
</tr></table>");

</script>


<script language="javascript" type="text/javascript">
  var bioid = "<?php echo $bioid; ?>";
</script>

<script type="text/javascript" src="http://www.google.com/jsapi"></script>
  <script type="text/javascript">
  google.load('visualization', '1', {packages: ['table']});
</script>
<script type="text/javascript">
  var visualization;
var data;
var options = {'showRowNumber': true};
function drawVisualization() {
  var query = new google.visualization.Query('/php/query.php?form=votes&limit=100&bioid='+bioid);    
  // Send the query with a callback function.
    query.send(handleQueryResponse);
}
function handleQueryResponse(response) {
  if (response.isError()) {
    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
    return;
  }
  var data = response.getDataTable();
  var formatter = new google.visualization.DateFormat({formatType: 'long'});
  // Reformat our data.
  var rc = new google.visualization.TablePatternFormat('<a href="/?p={1}"> {0} </a>');
  formatter.format(data, 1);
  rc.format(data, [6,5]);
  options['page'] = 'enable';
  options['pageSize'] = 10;
  //options['pagingSymbols'] = {prev: 'P', next: 'N'};
  options['pagingButtonsConfiguration'] = 'auto';
  options['allowHtml'] = true;
  var view = new google.visualization.DataView(data);
  view.hideColumns([5]);
  visualization = new google.visualization.Table(document.getElementById('table2'));
  visualization.draw(view, options);
}

google.setOnLoadCallback(drawVisualization);

var visualization2;
var data2;
var options2 = {'showRowNumber': true};
function drawVisualization2() {
  var query = new google.visualization.Query('/php/query.php?form=contrib&limit=100&bioid='+bioid);    
  // Send the query with a callback function.
  query.send(handleQueryResponse2);
}
function handleQueryResponse2(response) {
  if (response.isError()) {
    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
    return;
  }
  var data2 = response.getDataTable();
  options2['page'] = 'enable';
  options2['pageSize'] = 10;
  //options2['width'] = '600px';
  //options2['height'] = '600px';
  //options['pagingSymbols'] = {prev: 'P', next: 'N'};
  options2['pagingButtonsConfiguration'] = 'auto';
  visualization2 = new google.visualization.Table(document.getElementById('table3'));
  visualization2.draw(data2, options2);
}

google.setOnLoadCallback(drawVisualization2);

</script>
<h3> Últimas Votações </h3>
<div id="table2">
  </div>
  <script language="php">
  print("<a href=\"/php/query.php?form=votes&bioid=$bioid&tqx=reqId:0;out:csv;csvFile:votacoes$bioid\"> Download de todas as votações do legislador em csv</a>");
</script>


<h3> Doações de Campanha </h3>
<div id="table3">
</div>
<script language="php">
print("<a href=\"/php/query.php?form=contrib&bioid=$bioid&tqx=reqId:0;out:csv;csvFile:doacoes$bioid\"> Download de todas as doações para o legislador em csv</a>");
</script>


<h3> Mapa eleitoral - 2006 </h3>
<script language="php"> 
print("<p><a href=\"/php/olmap.php?candno=$candno&state=$state\"> Explore o mapa eleitoral interativo do deputado. (google maps)</a></p>");
print("<img src=\"/php/timthumb.php?src=/images/elections/2006/deputadofederal$state$candno.png&w=600&h=0\"  alt=\"Electoral Map\"");

</script>
