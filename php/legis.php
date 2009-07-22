<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML//EN">
<html> <head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title></title>
</head>

<script language="php">
##$db_host = 'localhost';
$host  = "mysql.cluelessresearch.com";
$con = mysql_connect($host,"monte","*");
$database = 'congressoaberto';
mysql_select_db($database, $con);

$table = 'br_bio';
##$bioid = 96734;
$bioid = $_GET["bioid"];

// sending query
$result = mysql_query("SELECT Convert(Convert((namelegis) using binary) using latin1) FROM br_bio where bioid={$bioid} limit 1");
if (!$result) {
   die("Query to show fields from table failed");
}

$row = mysql_fetch_row($result);
$namelegis = $row[0];

$result = mysql_query("SELECT candidate_code, state FROM  br_bioidtse where bioid={$bioid} and year=2006 limit 1");
$row = mysql_fetch_row($result);
$candno = $row[0];
$state = $row[1];

// $statelegis = $row[2]; FIX: make it not depend on row order (Call by name)
echo '<table border="0">';
echo '<tr>';
print("<td><img src=\"../images/bio/foto$bioid.jpg\" alt=\"$namelegis\" width=100/></td>");
print("<td> 
<p>$namelegis ($state)<br>
Legislaturas: </p>
 </td></tr></table>");

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
    formatter.format(data, 1);
    options['page'] = 'enable';
    // Reformat our data.
    formatter.format(data, 1);
    options['pageSize'] = 10;
    //options['pagingSymbols'] = {prev: 'P', next: 'N'};
    options['pagingButtonsConfiguration'] = 'auto';
    visualization = new google.visualization.Table(document.getElementById('table2'));
    visualization.draw(data, options);

}

google.setOnLoadCallback(drawVisualization);
</script>
<h3> Últimas Votações </h3>
<div id="table2">
</div>
<script language="php">
    print("<a href=\"/php/query.php?form=allvotes&bioid=$bioid&tqx=reqId:0;out:csv;csvFile:votos$bioid\"> Download de todas as votações do legislador em csv</a>");
</script>


<h3> Mapa eleitoral - 2006 </h3>
<script language="php"> 
print("<img src=\"../images/elections/2006/deputadofederal$state$candno.png\"  alt=\"Electoral Map\"");
</script>


<hr>
<address></address>
<!-- hhmts start --> <!-- hhmts end -->
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-9107435-2");
pageTracker._trackPageview();
} catch(err) {}</script>
</body> </html>
