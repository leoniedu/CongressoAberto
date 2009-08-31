<?php
if (!defined ('SNIPLETS'))
	die ('No direct access allowed');
	
/* Name: Include file or URL */
$text = @file_get_contents ($text);
if ($text === false)
	_e ('File does not exist', 'sniplets');
else
	echo $text;
?>