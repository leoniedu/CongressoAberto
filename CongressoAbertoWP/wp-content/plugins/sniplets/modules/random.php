<?php

if (!defined ('SNIPLETS'))
	die ('No direct access allowed');
	
/* Name: Randomize */

$type = 'section';
if (isset ($args['type']))
	$type = $args['type'];

switch ($type)
{
	case 'line'    : $split = '[\r\n]'; break;
	case 'word'    : $split = '[\s,]'; break;
	case 'custom'  : $split = $args['custom']; break;
	
	default: $split = '\[section\]'; break;
}

$sections = preg_split ("/$split/", $text);
$sections = array_filter ($sections);

srand ();
echo $sections[array_rand ($sections)];
?>