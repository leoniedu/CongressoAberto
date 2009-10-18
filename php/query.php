<?php
 
//The response should be utf8 encoded
header('Content-Type: text/html; charset=utf-8');
 
//Include the extended API
include_once("gvServerAPIEx.php");
 
include_once("server.php");
 
//------------------------------------------
 
//-- Add here business logic, if needed
//-- For example users authentication and access control 
 
//------------------------------------------
 
// 2 parameters of the protocol are supported: tqx and responseHandler. 
// You should pass them as-is to the gvStreamerEx object
$tqx = $_GET['tqx'];
$resHandler = $_GET['responseHandler'];
 
//mysql_query("set names utf8");
 
if($_GET["form"]=="partylist")
{
  $sql = "SELECT      
  					  t2.name as Nome
  					, t3.postid
  					, t2.party as 'Mapa eleitoral'
  					, t2.party as Sigla
                    , t1.current_size as Cadeiras
                    , t1.share_absent as Absentismo
                    , t1.with_execDIV as 'Com Governo'
                    
FROM
br_partyindices as t1,
br_parties_current as t2,
br_partypostid as t3
WHERE t2.number=t1.partyid and t2.number=t3.number
";
}
 
if($_GET["form"]=="bill_list") {
  $sql = "   
  select e.postid,  concat(d.billtype, ' No. ', d.billno, '/	', d.billyear) as Proposicao, d.billauthor as Autoria, d.status as Situacao,   count(*) as 'Votacoes nominais'  from br_votacoes as c, 
  (select a.billtype, a.billyear, a.billno, a.billauthor, a.status, b.billid 
  		from 	br_bills as a, 
  				br_billid as b 
  		where  a.billno=b.billno and a.billtype=b.billtype and a.billyear=b.billyear) 
  	as d, 
  	br_billidpostid as e
  where d.billid=e.billid and d.billno=c.billno and d.billyear=c.billyear and d.billtype=c.billtype group by d.billno, d.billtype, d.billyear order by d.billyear desc, d.billtype, d.billno" ;  
 #$sql = "select billyear from br_bills  where billid=440177";
 }
 
 
if($_GET["form"]=="listrcs") {
  ## called from bill.php
  ## given a bill, return all related roll calls
  $billid=$_GET["billid"];  
  $sql = "select   
b.rcvoteid as 'Resultado',   
b.rcvoteid as 'Por partido', 
b.rcvoteid as 'Por estado', 
CAST(b.rcdate AS DATE) as Data,   
c.postid as Votacao, 
b.billdescription as Votacao
from (select * from br_bills as d where d.billid=".$billid.") as a,  
br_votacoes as b, 
br_rcvoteidpostid as c  
where a.billyear=b.billyear and a.billno=b.billno and a.billtype=b.billtype  
and b.rcvoteid=c.rcvoteid
order by Data desc, a.billtype DESC" ;  
 #$sql = "select billyear from br_bills  where billid=440177";
 }
 
if($_GET["form"]=="tramit") {
  ## called from bill.php
  ## given a bill, return tramitacao
  $sql = "select date as Data, event as Evento
  from br_tramit
  where  billid=".$_GET["billid"]."
  order by Data DESC
  ";
 }
 
if($_GET["form"]=="allbills") {
  ##  return all bills
  $sql = "select *  from  br_bills order by billyear desc" ;  
  ##$sql = "select billyear from br_bills  where billid=37642";
 }
 
 
if($_GET["form"]=="votes") {
 ## given a bioid, return roll call votes
$sql = "select CAST(b.rcdate AS DATE) as Data, a.party as Partido, a.rc as Voto, d.rc as 'Partido', e.rc as Governo, c.postid, b.billproc as `Votacao`  from br_votos as a
left join br_leaders as d on (a.rcvoteid=d.rcvoteid and a.party=d.party)
left join br_leaders as e on (a.rcvoteid=e.rcvoteid)
, br_votacoes as b, br_rcvoteidpostid as c  where a.bioid=".$_GET["bioid"]." 
AND e.party='GOV'
AND a.rcfile=b.rcfile AND b.rcvoteid=c.rcvoteid AND a.legis=53 order by Data DESC
" ; 
    #$sql = "select * from br_votos limit 1";
	#echo $sql;
 }
 
if($_GET["form"]=="absvotes") {
 ## given a bioid, return roll call votes
$sql = "select max(CAST(b.rcdate AS DATE)) as Data, a.party as Partido    from 
	br_votos as a, 
	br_votacoes as b,
	br_rcvoteidpostid as c  
    where a.bioid=".$_GET["bioid"]." AND a.rcfile=b.rcfile AND b.rcvoteid=c.rcvoteid AND a.legis=53" ; 
    #$sql = "select * from br_votos limit 1";
	#echo $sql;
 }
 
if($_GET["form"]=="rcvotes") {
$sql = "select CAST(b.rcdate AS DATE) as Data, a.party as Partido, a.namelegis as nome, a.state as Estado, a.rc as Voto, b.bill as Proposicao, b.billdescription as Votacao, b.rcvoteid as ID  
	from 
		br_votos as a, 
		br_votacoes as b  
	where 
		a.rcvoteid=b.rcvoteid AND 
		a.rcvoteid=".$_GET["rcvoteid"]." order by Partido DESC" ;  
 }
 
 
 
if($_GET["form"]=="contrib") 
{
  ## given a bioid, returns the contributors	
  $sql = "select a.donor as Doador, a.donortype as 'Tipo de doador', a.cpfcnpj as 'CPF/CNPJ do doador', a.contribsum as 'Valor da doacao' from br_contrib as a, br_bioidtse as b where b.bioid=".$_GET["bioid"]." AND a.candno=b.candidate_code AND a.state=b.state AND a.year=b.year order by a.contribsum DESC";
}
 
 
// FIX: when null values exist this code breaks!
if($_GET["form"]=="legislist") 
{
  $sql = "SELECT b.namelegis as Nome
  					, d.postid
  					, a.party as Partido 
  					, cast(upper(a.state) as binary) as Estado
	  				, round(c.ausente_prop*100) `Ausencias (%)`
	  				, c.cgov_count `Segue o governo`
	  				, c.cgov_total `Segue o governo`
	  				, c.cparty_count `Segue o partido`
	  				, c.cparty_total `Segue o partido`
	  				, c.nparty `Numero de partidos`
FROM
br_deputados_current as a,
br_bio as b,
br_legis_stats as c,
br_bioidpostid as d
WHERE a.bioid=b.bioid and a.bioid=c.bioid and a.bioid=d.bioid  and c.nparty is not null
order by Estado ASC, Nome ASC
";
}



// , , 
//, 
//
 
// , , 
//, 
//order by Estado, Partido DESC
 
if($_GET["limit"]!="") 
  {
    $sql = $sql." limit ".$_GET["limit"];
  }
 
 
if($_GET["form"]=="test") 
  {
	$sql = "select * from br_votos limit 10"; 
  }
 
 
 
//concat(DAY(b.rcdate),'/',MONTH(b.rcdate),'/',YEAR(b.rcdate)) as year,
//+MONTH(b.rcdate)+'/'+YEAR(b.rcdate)
$result = mysql_query($sql);
if ($_GET["mode"]=="test") {
  echo $sql;
  $result = mysql_query($sql);
  $row = mysql_fetch_row($result);
  echo $row[0];
}
// Initialize the gvStreamerEx object
$gvJsonObj = new gvStreamerEx();
 
// If there will be an error during the inialization
// gvStreamerEx object will generate an error message
if($gvJsonObj->init($tqx, $resHandler) == true);
{
    //convert the entire query result into the compliant response
    $gvJsonObj->convertMysqlRes($result, "%01.0f", "d/m/Y", "G:i:s","d/m/Y G:i:s");
//    $gvJsonObj->setColumnPattern(3,"#0.0########");
}
 
// Close the connection to DB
mysql_close($con);
    
echo $gvJsonObj;
 
 
?>