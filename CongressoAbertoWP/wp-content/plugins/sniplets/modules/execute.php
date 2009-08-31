<?php

if (!defined ('SNIPLETS'))
	die ('No direct access allowed');

$text = trim($text);

/* Name: Execute as PHP */
if (substr ($text, 0, 4) == '<?php')
	$text = '?>'.$text;

eval ('?>'.$text);

