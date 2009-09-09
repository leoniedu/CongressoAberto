<script language="php">
//$host  = "mysql.cluelessresearch.com";
//$con = mysql_connect($host,"monte","e123456");
//$database = 'congressoaberto';
//mysql_select_db($database, $con);
include_once("server.php");
##$partyid=25;
$table = 'br_partyindices';

// sending query
$result = mysql_query("SELECT partyname FROM br_partyindices where partyid={$partyid} limit 1");
if (!$result) {
  die("Query to show fields from table failed");
 }
$row = mysql_fetch_row($result);
$partyacronym = $row[0];

#Get some summary statistics
$result = mysql_query("select t1.*, t2.name, t2.number from br_partyindices as t1, br_parties_current as t2 where t1.partyid={$partyid} AND t2.number={$partyid}");
$row = mysql_fetch_row($result);
$sizeparty = $row[0];
$cohesion = $row[2];
$shareabsent = $row[3];
$sharegovall = $row[4];
$sharegovdiv = $row[5];
$nameparty = $row[8];

##Get Ranks
$sql = "select t1.*, t2.name, t2.number from br_partyindices_rank as t1, br_parties_current as t2 where t1.partyid=t2.number AND t2.number={$partyid}";
##echo $sql;
$result = mysql_query($sql);
$row = mysql_fetch_row($result);
$ranksizeparty = $row[0];
$rankcohesion = $row[2];
$rankshareabsent = $row[3];
$ranksharegovall = $row[4];
$ranksharegovdiv = $row[5];

##Get number of ranked parties
$result = mysql_query("select count(*) as row_ct from br_partyindices");
$row = mysql_fetch_row($result);
$nparties = $row[0];

 echo '<table border="0">';
echo '<tr>';
print("<td><img src=\"/php/timthumb.php?src=/images/partylogos/resized/".$partyacronym.".jpg&w=100&h=0\"  width=100/></td>");
print("<td><p>$nameparty<br></p>
            Tamanho da Bancada: $sizeparty/513 ($ranksizeparty dentre os $nparties maiores partidos)<br>
            Taxa de Absenteismo  $shareabsent% ($rankshareabsent dentre os $nparties maiores partidos)<br>
            Taxa de Governismo: $sharegovdiv% em votações contenciosas ($ranksharegovdiv dentre os $nparties maiores partidos) <br>
            Índice de Coesão:  $cohesion ($rankcohesion dentro os $nparties maiores partidos)
</td></tr></table>");


echo '<table border="0">';
echo '<tr>';
print("<td> </tr><tr>
<img src=\"/php/timthumb.php?src=/images/typical/".$partyacronym."typical.png&w=800&h=0\" width=800/>
 </tr></td></table>");
 

echo '<table border="0">';
echo '<tr>';
print("<td> </tr><tr>
<img src=\"/images/governism/".$partyacronym."governism.png\" width=800/>
 </tr></td></table>");



</script>
