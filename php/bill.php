<script language="php">
$host  = "mysql.cluelessresearch.com";
$con = mysql_connect($host,"monte","e123456");
$database = 'congressoaberto';
mysql_select_db($database, $con);

$table = 'br_bills';
##$bioid = 96734;
##$bioid = $_GET["bioid"];

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
</script>
