<?php

  //The response should be utf8 encoded
header('Content-Type: text/html; charset=utf-8');

//Include the extended API
include_once("gvServerAPIEx.php");

//------------------------------------------

//-- Add here business logic, if needed
//-- For example users authentication and access control 

//------------------------------------------

// 2 parameters of the protocol are supported: tqx and responseHandler. 
// You should pass them as-is to the gvStreamerEx object
$tqx = $_GET['tqx'];
$resHandler = $_GET['responseHandler'];

// Read the data from MySql
$host  = $_SERVER['HTTP_HOST'];
$con = mysql_connect($host,"root","e321109");
mysql_select_db("DB_Name", $con);
// $sql = "SELECT distinct state, count(party) as count FROM congressoaberto.br_bio group by state";
if($_GET["form"]=="votes") 
{
    ##echo $_GET["form"];
    $sql = "select CAST(b.rcdate AS DATE) as Data, a.party as Partido, a.rc as Voto,   b.billproc as Votacao from congressoaberto.br_votos as a, congressoaberto.br_votacoes as b  where a.bioid=".$_GET["bioid"]." AND a.rcfile=b.rcfile AND a.legis=53 order by Data DESC " ;
}
//concat(DAY(b.rcdate),'/',MONTH(b.rcdate),'/',YEAR(b.rcdate)) as year,
//+MONTH(b.rcdate)+'/'+YEAR(b.rcdate)
$result = mysql_query($sql);

// Initialize the gvStreamerEx object
$gvJsonObj = new gvStreamerEx();

// If there will be an error during the inialization
// gvStreamerEx object will generate an error message
if($gvJsonObj->init($tqx, $resHandler) == true);
{
    //convert the entire query result into the compliant response
    $gvJsonObj->convertMysqlRes(&$result, "%01.1f", "d/m/Y", "G:i:s","d/m/Y G:i:s");
    $gvJsonObj->setColumnPattern(3,"#0.0########");
}

// Close the connection to DB
mysql_close($con);
    
echo $gvJsonObj;


?>