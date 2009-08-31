<?php

if (!defined ('SNIPLETS'))
	die ('No direct access allowed');

/* Name: Syntax Highlight */
include_once ("$libpath/geshi/geshi.php");

$language  = '';
$header    = 'pre';
$keywords  = false;
$css       = false;
$numbers   = '';
$highlight = array ();

if (isset ($args['language']))
	$language = $args['language'];

if (isset ($args['header']))
	$header = $args['header'];

if (isset ($args['keywords']) && $args['keywords'] == true)
	$keywords = true;

if (isset ($args['numbers']))
	$numbers = $args['numbers'];
	
if (isset ($args['css']) && $args['css'] == true)
	$css = true;
	
if (isset ($args['highlight']))
{
	$highlight = explode ('|', $args['highlight']);
	
	// Check for any ranges
	if (count ($highlight) > 0)
	{
		foreach ($highlight AS $pos => $line)
		{
			if (strpos ($line, '-') !== false)
			{
				$tmp = explode ('-', $line);
				for ($x = $tmp[0]; $x <= $tmp[1]; $x++)
					$highlight[] = $x;
				unset ($highlight[$pos]);
			}
		}
		
		sort ($highlight);
	}
}
	
if ($language == '' || $language == 'html')
	$language = 'html4strict';

$text = str_replace ("\t", '  ', $text);

// A small hack to allow PHP snippets to be highlighted without needing <?php
if (isset ($args['force']))
	$text = '<?php '.$text;

// Create a Geshi object
$geshi = new GeSHi ($text, $language);

if ($header == 'div')
	$geshi->set_header_type (GESHI_HEADER_DIV);
else if ($header == 'pre')
	$geshi->set_header_type (GESHI_HEADER_PRE);
else
	$geshi->set_header_type (GESHI_HEADER_NONE);
	
if ($numbers == 'fancy')
	$geshi->enable_line_numbers (GESHI_FANCY_LINE_NUMBERS);
else if ($numbers == 'normal')
	$geshi->enable_line_numbers (GESHI_NORMAL_LINE_NUMBERS);

$geshi->add_keyword_group (4, 'color: #600000;', false, array('bloginfo', 'get_settings', 'is_single', 'wp_head', 'wp_get_archives'));

$geshi->enable_keyword_links ($keywords);
$geshi->enable_strict_mode (true);
$geshi->highlight_lines_extra ($highlight);

if ($css)
	$geshi->enable_classes ();

if (isset ($args['showcss']))
	echo '<pre>'.$geshi->get_stylesheet (false).'</pre>';

$text = $geshi->parse_code ();

if (isset ($args['force']))
	$text = str_replace ('<pre class="php"><span class="kw2">&lt;?php</span> ', '<pre class="php">', $text);
echo $text;
?>