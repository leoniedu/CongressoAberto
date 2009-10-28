<script type="text/javascript" src="http://www.google.com/jsapi"></script>
<script type="text/javascript">
  google.load('visualization', '1', {packages: ['table']});
</script>
<script language="javascript" type="text/javascript">
  var rcvoteid = "<?php echo $rcvoteid; ?>";
</script>
<script type="text/javascript">
var visualization;
var data;
var options = {'showRowNumber': true};
function drawVisualization() {
  var query = new google.visualization.Query('/php/query.php?form=rcvotes1&rcvoteid='+rcvoteid);    
    query.send(handleQueryResponse);
}
function handleQueryResponse(response) {
  if (response.isError()) {
    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
    return;
  }
  var data = response.getDataTable();
  options['page'] = 'enable';
  options['pageSize'] = 20;
  options['pagingButtonsConfiguration'] = 'auto';
  options['allowHtml'] = true;
  var view = new google.visualization.DataView(data);
  visualization = new google.visualization.Table(document.getElementById('table1'));
  visualization.draw(view, options);
}
 google.setOnLoadCallback(drawVisualization);
 
 </script>

<script language="php">
include_once("server.php");

// find the billid
$result = mysql_query("SELECT 
b.billid as billid, 
c.postid as postid, 
a.bill as bill, 
b.ementa as `Ementa` 
FROM br_votacoes as a, br_bills as b, br_billidpostid as c where a.rcvoteid={$rcvoteid} and b.billid=c.billid and a.billtype=b.billtype and a.billno=b.billno and a.billyear=b.billyear limit 1");
if (!$result) {
  die("Query to show fields from table failed");
 }
$row = mysql_fetch_row($result);

echo '<p>Proposição (clique para ver detalhes): <a href="'.get_permalink($row[1]).'"> '.get_the_title($row[1]).'.</a></p>' ;

echo '<p>Ementa: "'.$row[3].'"</p>' ;

the_excerpt();

echo '<h3> Resultado da Votação </h3>';
echo '<a href="/php/timthumb.php?src=/images/rollcalls/bar'.$rcvoteid.'large.png">   <img src="/php/timthumb.php?src=/images/rollcalls/bar'.$rcvoteid.'large.png&h=300&w=300&zc=0&q=100"> </a>' ;
// echo '<img src="/images/rollcalls/bar'.$rcvoteid.'large.png"  width=400 >'  ;

echo '<h3> Votação por partido </h3>';
echo '<img src="/php/timthumb.php?src=/images/rollcalls/mosaic'.$rcvoteid.'large.png&w=500&zc=0&q=100">' ;


echo '<h3> Votação por estado </h3>';
echo '<img src="/php/timthumb.php?src=/images/rollcalls/map'.$rcvoteid.'large.png&w=350&zc=0&q=100">' ;

 
</script>

<p><h3> Tabela com o Resultado da Votação </h3></p>

<div id="table1">
</div>


<?php 
print("<p><a href=\"/php/query.php?form=rcvotes&rcvoteid=$rcvoteid&tqx=reqId:0;out:csv;csvFile:votacao_rc$rcvoteid\"> Download da votação em formato csv</a></p>");
?>