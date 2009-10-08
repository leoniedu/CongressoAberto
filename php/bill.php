<script language="php">
include_once("server.php");
// sending query
// $billid = $_GET['billid'];
//$billid = 422643;

$result = mysql_query("SELECT billauthor, ementa, status, propno  FROM br_bills where billid={$billid} limit 1");
if (!$result) {
  die("Query to show fields from table failed");
 }
$row = mysql_fetch_row($result);

// $statelegis = $row[2]; FIX: make it not depend on row order (Call by name)
echo '<p><a href="http://www.camara.gov.br/sileg/Prop_Detalhe.asp?id='.$billid.'" > Página da proposição no site da Câmara </a> <br>';
echo '<a href="http://www.camara.gov.br/sileg/MostrarIntegra.asp?CodTeor='.$row[3].'"> Íntegra da proposição (pdf) no site da Câmara</a><br>';
echo 'Autoria: '.$row[0].'<br>';
echo 'Ementa: '.$row[1].'<br>';
echo 'Status: '.$row[2].'<br></p>';
//echo '<p>Billid: '.$billid.'</p>';

// what are the roll calls related to this bill
</script>

<script language="javascript" type="text/javascript">
  var billid = "<?php echo $billid; ?>";
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
  var query = new google.visualization.Query('/php/query.php?form=listrcs&limit=100&tqx=reqId:0;&billid='+billid);    
  // Send the query with a callback function.
    query.send(handleQueryResponse);
}
function handleQueryResponse(response) {
  if (response.isError()) {
    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
    return;
  }
  var data = response.getDataTable();
  var mosaic = new google.visualization.TablePatternFormat('<img src="/php/timthumb.php?src=/images/rollcalls/mosaic{0}small.png&w=110&h=0&zc=0&q=90">');
  var bar = new google.visualization.TablePatternFormat('<img src="/php/timthumb.php?src=/images/rollcalls/bar{0}small.png&w=100&h=0&zc=0&q=90">');
  var map = new google.visualization.TablePatternFormat('<img src="/php/timthumb.php?src=/images/rollcalls/map{0}small.png&w=100&h=0&zc=0&q=90">');
  var rc = new google.visualization.TablePatternFormat('<a href="/?p={1}"> {0} </a>');
  rc.format(data, [5,4]);
  bar.format(data, [0]);
  mosaic.format(data, [1]);
  map.format(data, [2]);
  var formatter = new google.visualization.DateFormat({pattern: "dd/MM/yyyy"});
  // Reformat our data.
  formatter.format(data, 3);
  options['allowHtml'] = true;
  options['page'] = 'enable';
  options['pageSize'] = 10;
  //options['pagingSymbols'] = {prev: 'P', next: 'N'};
  options['pagingButtonsConfiguration'] = 'auto';
  var view = new google.visualization.DataView(data);
  view.hideColumns([4]);
  visualization = new google.visualization.Table(document.getElementById('table1'));
  visualization.draw(view, options);
}

google.setOnLoadCallback(drawVisualization);

var visualization2;
var data2;
var options2 = {'showRowNumber': true};
function drawVisualization2() {
  var query = new google.visualization.Query('/php/query.php?form=tramit&limit=100&tqx=reqId:0;&billid='+billid);    
  // Send the query with a callback function.
    query.send(handleQueryResponse2);
}
function handleQueryResponse2(response) {
  if (response.isError()) {
    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
    return;
  }
  var data2 = response.getDataTable();
  options2['allowHtml'] = true;
  options2['page'] = 'enable';
  options2['pageSize'] = 5;
  options2['pagingButtonsConfiguration'] = 'auto';
  visualization2 = new google.visualization.Table(document.getElementById('table2'));
  visualization2.draw(data2, options2);
}

google.setOnLoadCallback(drawVisualization2);

</script>


<h3>  Votações nominais </h3>
<div id="table1">  </div>


<br>
<br>


<h3>  Tramitação </h3>
<div id="table2">
  </div>
