<script language="php">
$host  = "mysql.cluelessresearch.com";
$con = mysql_connect($host,"monte","e123456");
$database = 'congressoaberto';
mysql_select_db($database, $con);
// sending query
$result = mysql_query("SELECT Convert(Convert((billauthor) using binary) using latin1), Convert(Convert((ementa) using binary) using latin1), Convert(Convert((status) using binary) using latin1) FROM br_bills where billid={$billid} limit 1");
if (!$result) {
  die("Query to show fields from table failed");
 }
$row = mysql_fetch_row($result);

// $statelegis = $row[2]; FIX: make it not depend on row order (Call by name)
echo '<p>Autoria: '.$row[0].'</p>';
echo '<p>Ementa: '.$row[1].'</p>';
echo '<p>Status: '.$row[2].'</p>';
echo '<p>Billid: '.$billid.'</p>';

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
  var query = new google.visualization.Query('/php/query.php?form=bills&limit=100&tqx=reqId:0;&billid='+billid);    
  // Send the query with a callback function.
    query.send(handleQueryResponse);
}
function handleQueryResponse(response) {
  if (response.isError()) {
    alert('Error in query: ' + response.getMessage() + ' ' + response.getDetailedMessage());
    return;
  }
  var data = response.getDataTable();
  var mosaic = new google.visualization.TablePatternFormat('<img src="http://politica.eduardoleoni.com/wp-content/themes/arthemia/scripts/timthumb.php?src=/images/rollcalls/mosaic{0}small.png&w=100&h=0&zc=0&q=90">');
  var bar = new google.visualization.TablePatternFormat('<img src="http://politica.eduardoleoni.com/wp-content/themes/arthemia/scripts/timthumb.php?src=/images/rollcalls/bar{0}small.png&w=100&h=0&zc=0&q=90">');
  var map = new google.visualization.TablePatternFormat('<img src="/php/timthumb.php?src=/images/rollcalls/map{0}small.png&w=100&h=0&zc=0&q=90">');
  var rc = new google.visualization.TablePatternFormat('<a href="http://politica.eduardoleoni.com/?p={0}"> Link </a>');
  rc.format(data, [4]);
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
  visualization = new google.visualization.Table(document.getElementById('table'));
  visualization.draw(data, options);
}

google.setOnLoadCallback(drawVisualization);
</script>

<h3>  Votações nominais </h3>
<div id="table">
  </div>
