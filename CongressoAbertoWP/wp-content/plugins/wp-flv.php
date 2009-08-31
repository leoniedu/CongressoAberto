<?php

/*
Plugin Name: WP-FLV
Version: 0.2
Plugin URI: http://roel.meurders.nl/wordpress-plugins/wp-flv-video-player-plugin/
Description: This plugin eases insertion of Jeroen Wijerings FLV Video Player
Author: Roel Meurders
Author URI: http://roel.meurders.nl/
Update:
*/

// SCRIPT INFO ///////////////////////////////////////////////////////////////////////////

/*
	WP-FLV for Wordpress
	(C) 2005 Roel Meurders - GNU General Public License

	This plugin requires:
		FLASH VIDEO PLAYER 2.2 from Jeroen Wijering.
		Download it at: http://www.jeroenwijering.com/?item=Flash_Video_Player
		Please not that this player is released under Creative Commons "BY-NC-SA" License
		Full text at: http://creativecommons.org/licenses/by-nc-sa/2.0/

	This Wordpress plugin is released under a GNU General Public License. A complete version of this license
	can be found here: http://www.gnu.org/licenses/gpl.txt

	This Wordpress plugin has been tested with Wordpress 1.5.2;

	This Wordpress plugin is released "as is". Without any warranty. The author cannot
	be held responsible for any damage that this script might cause.

*/

// NO EDITING HERE!!!!! ////////////////////////////////////////////////////////////////

	add_action('admin_menu', 'wpflv_admin_menu');
	add_filter('the_content', 'wpflv_replace', '1');

	function wpflv_admin_menu(){
		add_options_page('WP-FLV, options page', 'WP-FLV', 9, basename(__FILE__), 'wpflv_options_page');
	}

	function wpflv_replace($content){
		$o = wpflv_get_options();

		$flvVars = array("PLAYER", "HREF", "WIDTH", "HEIGHT", "AUTOSTART");
		$flvVals = array($o['playerurl'], '', $o['width'], $o['height'], $o['autostart']);
		if ($o['xhtmlvalid'] == 'y'){
			$flvCode = '<object type="application/x-shockwave-flash" width="%WIDTH%" height="%HEIGHT%" data="%PLAYER%?file=%HREF%%AUTOSTART%"><param name="movie" value="%PLAYER%?file=%HREF%%AUTOSTART%" /></object>';
		} else {
			$flvCode = <<<EOT
				<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="%WIDTH%" height="%HEIGHT%">
					<param name="movie" value="%PLAYER%?file=%HREF%%AUTOSTART%" />
					<param name="quality" value="high" />
					<param name="wmode" value="transparent" />
					<embed src="%PLAYER%?file=%HREF%%AUTOSTART%" quality="high" wmode="transparent" width="%WIDTH%" height="%HEIGHT%" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
				</object>
EOT;
		}

		preg_match_all ('!<flv([^>]*)[ ]*[/]*>!i', $content, $matches);

		$flvStrings = $matches[0];
		$flvAttributes = $matches[1];
		for ($i = 0; $i < count($flvAttributes); $i++){
			preg_match_all('!(href|width|height|autostart)="([^"]*)"!i',$flvAttributes[$i],$matches);
			$tmp = $flvCode;
			$flvSetVars = $flvSetVals = array();
			for ($j = 0; $j < count($matches[1]); $j++){
				$flvSetVars[$j] = strtoupper($matches[1][$j]);
				$flvSetVals[$j] = $matches[2][$j];
			}
			for ($j = 0; $j < count($flvVars); $j++){
				$key = array_search($flvVars[$j], $flvSetVars);
				$val = (is_int($key)) ? $flvSetVals[$key] : $flvVals[$j];
				if ($flvVars[$j] == 'AUTOSTART')
					$val = ($val == 'false') ? '&amp;autoStart=false;' : '';
				if ($flvVars[$j] == 'HEIGHT')
					$val = intval($val) + 20;
				$tmp = str_replace('%'.$flvVars[$j].'%', $val, $tmp);
			}
			$content = str_replace($flvStrings[$i], "\n\n".$o['prehtml'].$tmp.$o['posthtml']."\n\n", $content);
		}
		return $content;
	}


	function wpflv_get_options(){
		$defaults = array();
		$defaults['quicktags'] = 'y';
		$defaults['prehtml'] = '<div class="flvPlayer">';
		$defaults['posthtml'] = '</div>';
		$defaults['playerurl'] = 'https://media.dreamhost.com/mediaplayer.swf';
		$defaults['videourl'] = 'http://www.yoursite.com/media.flv';
		$defaults['width'] = '320';
		$defaults['height'] = '240';
		$defaults['autostart'] = 'n';
		$defaults['xhtmlvalid'] = 'n';

		$options = get_option('rmnlFLVsettings');
		if (!is_array($options)){
			$options = $defaults;
			update_option('rmnlFLVsettings', $options);
		}

		return $options;
	}


	function wpflv_options_page(){
		if ($_POST['wpflv']){
			$_POST['wpflv']['prehtml'] = stripslashes($_POST['wpflv']['prehtml']);
			$_POST['wpflv']['posthtml'] = stripslashes($_POST['wpflv']['posthtml']);
			update_option('rmnlFLVsettings', $_POST['wpflv']);
			$message = '<div class="updated"><p><strong>Options saved.</strong></p></div>';
		}

		$o = wpflv_get_options();

		$qtyes = ($o['quicktags'] == 'y') ? ' checked="checked"' : '';
		$qtno = ($o['quicktags'] == 'y') ? '' : ' checked="checked"';
		$asyes = ($o['autostart'] == 'true') ? ' checked="checked"' : '';
		$asno = ($o['autostart'] == 'true') ? '' : ' checked="checked"';
		$xvyes = ($o['xhtmlvalid'] == 'y') ? ' checked="checked"' : '';
		$xvno = ($o['xhtmlvalid'] == 'y') ? '' : ' checked="checked"';
		echo <<<EOT
		<div class="wrap">
			<h2>WP-FLV Options</h2>
			{$message}
			<form name="form1" method="post" action="options-general.php?page=wp-flv.php">
			<fieldset class="options">
				<legend>Required WP-FLV setting</legend>
				<p>To make this plugin actually do anything it is required that you set the option below correctly.</p>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform">
					<tr valign="top">
						<th width="33%" scope="row">(Full) URL to flvplayer.swf</th>
						<td><input type="text" value="{$o['playerurl']}" name="wpflv[playerurl]" size="50" /></td>
					</tr>
				</table>
			</fieldset>
			<br />

			<fieldset class="options">
				<legend>Optional WP-FLV settings</legend>
				<p>To make optimal use of the WP-FLV plugin you can set the options below.</p>
				<table width="100%" cellspacing="2" cellpadding="5" class="editform">
					<tr valign="top">
						<th width="33%" scope="row">Use Quicktag to insert FLV</th>
						<td>
							<input type="radio" value="y" name="wpflv[quicktags]"{$qtyes} /> yes<br />
							<input type="radio" value="n" name="wpflv[quicktags]"{$qtno} /> no
						</td>
					</tr>
					<tr valign="top">
						<th>Default URL to video files</th>
						<td><input type="text" value="{$o['videourl']}" name="wpflv[videourl]" size="50" /></td>
					</tr>
					<tr valign="top">
						<th>Default movie size (W x H)</th>
						<td>
							<input type="text" value="{$o['width']}" name="wpflv[width]" size="3" maxlength="4" /> x
							<input type="text" value="{$o['height']}" name="wpflv[height]" size="3" maxlength="4" />
						</td>
					</tr>
					<tr valign="top">
						<th>Autostart movies (consider bandwidth!)</th>
						<td>
							<input type="radio" value="true" name="wpflv[autostart]"{$asyes} /> yes<br />
							<input type="radio" value="false" name="wpflv[autostart]"{$asno} /> no
						</td>
					</tr>
					<tr valign="top">
						<th>Insert XHTML valid code?</th>
						<td>
							<input type="radio" value="y" name="wpflv[xhtmlvalid]"{$xvyes} /> yes<br />
							<input type="radio" value="n" name="wpflv[xhtmlvalid]"{$xvno} /> no
						</td>
					</tr>
					<tr valign="top">
						<th>(X)HTML to be placed before each player</th>
						<td><textarea name="wpflv[prehtml]" rows="3" cols="50">{$o['prehtml']}</textarea></td>
					</tr>
					<tr valign="top">
						<th>(X)HTML to be placed after each player</th>
						<td><textarea name="wpflv[posthtml]" rows="3" cols="50">{$o['posthtml']}</textarea></td>
					</tr>
				</table>
			</fieldset>
			<p class="submit">
				<input type="submit" name="Submit" value="Update Options &raquo;" />
			</p>
			</form>
		</div>
EOT;
	}

	if(strpos($_SERVER['REQUEST_URI'], 'post.php') || strpos($_SERVER['REQUEST_URI'], 'page-new.php') ) {

		add_action('admin_footer', 'flvAddQuicktag');

		function flvAddQuickTag(){
			$o = wpflv_get_options();

			if($o['quicktags'] == 'y'){
				echo <<<EOT
				<script type="text/javascript">
					<!--
						var flvToolbar = document.getElementById("ed_toolbar");
						if(flvToolbar){
							var flvNr = edButtons.length;
							//edButtons[edButtons.length] = new edButton('ed_popin','','','</a>','');
							edButtons[edButtons.length] = new edButton('ed_flv','','','','');
							var flvBut = flvToolbar.lastChild;
							while (flvBut.nodeType != 1){
								flvBut = flvBut.previousSibling;
							}
							flvBut = flvBut.cloneNode(true);
							flvToolbar.appendChild(flvBut);
							//toolbar.appendChild(flvBut);
							flvBut.value = 'FLV';
							flvBut.onclick = edInsertFLV;
							flvBut.title = "Insert a Flash Video";
							flvBut.id = "ed_flv";
						}

						function edInsertFLV() {
							if(!edCheckOpenTags(flvNr)){
								var U = prompt('Give the Url of the Flash Video File' , '{$o["videourl"]}');
								var W = prompt('Give the width of this video' , '{$o["width"]}');
								var H = prompt('Give the height of this video' , '{$o["height"]}');
								var A = confirm('Do you want this video to start playing automatically?');
								var theTag = '<flv href="' + U + '" width="' + W + '" height="' + H + '" autostart="';
								theTag += (A) ? 'true" />' : 'false" />';
								edButtons[flvNr].tagStart  = theTag;
								edInsertTag(edCanvas, flvNr);
							} else {
								edInsertTag(edCanvas, flvNr);
							}
						}

					//-->
				</script>
EOT;
			}
		}
	}

?>
