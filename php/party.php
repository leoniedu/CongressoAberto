<script language="php">
include_once("server.php");

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
$sharewomen = $row[6];
$nameparty = $row[9];

##Get Ranks
$result = mysql_query("select t1.*, t2.name, t2.number from br_partyindices_rank as t1, br_parties_current as t2 where t1.partyid={$partyid} AND t2.number={$partyid}");
$row = mysql_fetch_row($result);
$ranksizeparty = $row[0];
$rankcohesion = $row[2];
$rankshareabsent = $row[3];
$ranksharegovall = $row[4];
$ranksharegovdiv = $row[5];
$ranksharewomen = $row[6];

##Get number of ranked parties
$result = mysql_query("select count(*) as row_ct from br_partyindices");
$row = mysql_fetch_row($result);
$nparties = $row[0];



echo '<table border="0">';
echo '<tr>';
print("<td width='110'><img src=\"/php/timthumb.php?src=/images/partylogos/".$partyacronym.".jpg&w=100&h=0\"  width=100/></td>");
print("<td width='400'><h3>$nameparty</h3></p>
            Tamanho da Bancada: $sizeparty legisladores ($ranksizeparty&ordm)<br>
            Taxa de Absenteismo  $shareabsent% ($rankshareabsent&ordm) <br>
            Taxa de Governismo: $sharegovdiv% em votações contenciosas  ($ranksharegovdiv&ordm)<br>
            Índice de Coesão:  $cohesion ($rankcohesion&ordm) <br>
            Mulheres:  $sharewomen% ($ranksharewomen&ordm)
            </td>
            <td width ='400'>
            <explain>Rankings consideram apenas os $nparties maiores partidos. Taxa de Absenteísmo é a porcentagem média dos membros do partido ausentes
                das votações nominais. Taxa de Governismo é a porcentagem média dos membros do partido votando com o governo. Votaçoes contenciosas são 
                aquelas em que pelo menos 10% dos presentes votou com a minoria. Índice de Coesão (Rice) varia de 0 a 1, com valores mais altos indicando
                maior coesão do partido em votações.</explain>
</td></tr></table>");


echo '<table border="0">';
echo '<tr>';
print("<br><h3>Comportamento Típico do $partyacronym</h3></tr>");
print("<tr><img src=\"/php/timthumb.php?src=/images/typical/".$partyacronym."typical.png&w=600&h=0\" width=600/></tr></table>");

echo '<table border="0">';
echo '<tr>';
print("<br><h3>Taxa de Governismo do $partyacronym</h3></tr>");
print("<tr><img src=\"/php/timthumb.php?src=/images/governism/".$partyacronym."governism.png&w=600&h=0\" width=600/></tr></table>");

</script>
