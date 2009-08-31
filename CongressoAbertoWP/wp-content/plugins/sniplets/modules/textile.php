<?php

if (!defined ('SNIPLETS'))
	die ('No direct access allowed');
	
/* Name: Textile */
include_once ("$libpath/textile/textile.php");

$textile = new Textile();
echo $textile->TextileThis ($text);
?>