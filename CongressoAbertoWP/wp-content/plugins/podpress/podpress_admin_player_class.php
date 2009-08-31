<?php
/*
License:
 ==============================================================================

    Copyright 2006  Dan Kuykendall  (email : dan@kuykendall.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-107  USA
*/
	class podPressAdmin_class extends podPress_class
	{
		function podPressAdmin_class() {
			$this->podPress_class();
			return;
		}

		/*************************************************************/
		/* Functions for editing and saving posts                    */
		/*************************************************************/

		function settings_players_edit() {
			podPress_isAuthorized();
			if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
				echo '<div id="message" class="updated fade"><p>'. __('Settings Saved', 'podpress').'</p></div>';
			}

			if($this->settings['player']['bg'] == '') {
				$this->resetPlayerSettings();
			}

			echo '<div class="wrap">'."\n";
			echo '	<h2>'.__('Player Settings', 'podpress').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.mightyseek.com/podpress/#download" target="_new"><img src="http://www.mightyseek.com/podpress_downloads/versioncheck.php?current='.PODPRESS_VERSION.'" alt="'.__('Checking for updates... Failed.', 'podpress').'" border="0" /></a></h2>'."\n";
			echo '	<form method="post">'."\n";
			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('MP3 Player', 'podpress').'</legend>'."\n";
			echo '		<table class="editform" cellpadding="5" cellspacing="2" width="100%">'."\n";
			echo '			<tr>'."\n";
			echo '				<th>'.__('Player', 'podpress').':</th>'."\n";
			echo '				<td colspan="2"><select name="mp3Player"><option value="podango"'; if($this->settings['mp3Player'] == 'podango') { echo ' selected="selected"'; } echo '>Podango (Default)</option><option value="1pixelout"'; if($this->settings['mp3Player'] == '1pixelout') { echo ' selected="selected"'; } echo '>1 Pixel Out (classic)</option></select></td>'."\n";
			echo '				<td>&nbsp;</td>'."\n";
			echo '			</tr>'."\n";


			echo '			<tr>'."\n";
			echo '				<th>'.__('Colour map', 'podpress').':</th>'."\n";
			echo '				<td colspan="2"><img src="'.podPress_url().'images/map.png" width="390" height="124" alt="podpress map" /></td>'."\n";
			echo '				<td>&nbsp;</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_bg_">'.__('Background', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_bg_" name="playerSettings[bg]" size="40" value="'.$this->settings['player']['bg'].'" style="background-color: '.$this->settings['player']['bg'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '				<td rowspan="12" valign="top">'."\n";
			echo '			<br/>	Pick the field you want to change the color for. Then mouse over the color selector, and you will see the color change for the field you chose. Click on the color selector to lock in your selection.<br/><br/>

	<table>
		<tr>
			<td bgcolor="#000000"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#000000\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#000033"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#000033\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#000066"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#000066\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#000099"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#000099\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#0000cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#0000cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#0000ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#0000ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#006600"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#006600\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#006633"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#006633\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#006666"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#006666\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#006699"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#006699\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#0066cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#0066cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#0066ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#0066ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00cc00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00cc00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00cc33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00cc33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00cc66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00cc66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00cc99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00cc99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00cccc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00cccc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00ccff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00ccff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#003300"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#003300\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#003333"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#003333\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#003366"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#003366\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#003399"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#003399\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#0033cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#0033cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#0033ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#0033ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#009900"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#009900\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#009933"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#009933\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#009966"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#009966\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#009999"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#009999\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#0099cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#0099cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#0099ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#0099ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00ff00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00ff00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00ff33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00ff33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00ff66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00ff66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00ff99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00ff99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00ffcc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00ffcc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#00ffff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#00ffff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#330000"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#330000\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#330033"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#330033\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#330066"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#330066\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#330099"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#330099\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#3300cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#3300cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#3300ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#3300ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#336600"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#336600\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#336633"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#336633\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#336666"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#336666\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#336699"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#336699\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#3366cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#3366cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#3366ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#3366ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33cc00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33cc00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33cc33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33cc33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33cc66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33cc66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33cc99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33cc99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33cccc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33cccc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33ccff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33ccff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#333300"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#333300\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#333333"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#333333\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#333366"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#333366\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#333399"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#333399\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#3333cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#3333cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#3333ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#3333ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#339900"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#339900\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#339933"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#339933\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#339966"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#339966\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#339999"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#339999\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#3399cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#3399cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#3399ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#3399ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33ff00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33ff00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33ff33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33ff33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33ff66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33ff66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33ff99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33ff99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33ffcc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33ffcc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#33ffff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#33ffff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#660000"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#660000\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#660033"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#660033\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#660066"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#660066\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#660099"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#660099\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#6600cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#6600cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#6600ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#6600ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#666600"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#666600\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#666633"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#666633\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#666666"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#666666\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#666699"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#666699\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#6666cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#6666cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#6666ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#6666ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66cc00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66cc00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66cc33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66cc33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66cc66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66cc66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66cc99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66cc99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66cccc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66cccc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66ccff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66ccff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#663300"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#663300\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#663333"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#663333\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#663366"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#663366\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#663399"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#663399\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#6633cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#6633cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#6633ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#6633ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#669900"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#669900\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#669933"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#669933\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#669966"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#669966\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#669999"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#669999\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#6699cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#6699cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#6699ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#6699ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66ff00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66ff00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66ff33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66ff33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66ff66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66ff66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66ff99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66ff99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66ffcc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66ffcc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#66ffff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#66ffff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#990000"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#990000\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#990033"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#990033\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#990066"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#990066\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#990099"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#990099\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#9900cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#9900cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#9900ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#9900ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#996600"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#996600\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#996633"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#996633\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#996666"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#996666\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#996699"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#996699\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#9966cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#9966cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#9966ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#9966ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99cc00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99cc00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99cc33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99cc33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99cc66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99cc66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99cc99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99cc99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99cccc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99cccc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99ccff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99ccff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#993300"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#993300\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#993333"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#993333\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#993366"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#993366\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#993399"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#993399\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#9933cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#9933cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#9933ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#9933ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#999900"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#999900\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#999933"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#999933\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#999966"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#999966\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#999999"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#999999\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#9999cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#9999cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#9999ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#9999ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99ff00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99ff00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99ff33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99ff33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99ff66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99ff66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99ff99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99ff99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99ffcc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99ffcc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#99ffff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#99ffff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#cc0000"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc0000\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc0033"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc0033\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc0066"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc0066\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc0099"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc0099\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc00cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc00cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc00ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc00ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc6600"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc6600\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc6633"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc6633\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc6666"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc6666\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc6699"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc6699\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc66cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc66cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc66ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc66ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cccc00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cccc00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cccc33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cccc33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cccc66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cccc66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cccc99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cccc99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cccccc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cccccc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ccccff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ccccff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#cc3300"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc3300\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc3333"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc3333\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc3366"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc3366\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc3399"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc3399\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc33cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc33cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc33ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc33ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc9900"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc9900\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc9933"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc9933\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc9966"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc9966\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc9999"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc9999\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc99cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc99cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#cc99ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#cc99ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ccff00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ccff00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ccff33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ccff33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ccff66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ccff66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ccff99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ccff99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ccffcc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ccffcc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ccffff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ccffff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#ff0000"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff0000\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff0033"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff0033\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff0066"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff0066\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff0099"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff0099\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff00cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff00cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff00ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff00ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff6600"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff6600\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff6633"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff6633\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff6666"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff6666\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff6699"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff6699\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff66cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff66cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff66ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff66ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffcc00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffcc00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffcc33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffcc33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffcc66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffcc66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffcc99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffcc99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffcccc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffcccc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffccff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffccff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
		<tr>
			<td bgcolor="#ff3300"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff3300\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff3333"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff3333\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff3366"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff3366\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff3399"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff3399\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff33cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff33cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff33ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff33ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff9900"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff9900\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff9933"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff9933\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff9966"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff9966\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff9999"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff9999\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff99cc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff99cc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ff99ff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ff99ff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffff00"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffff00\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffff33"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffff33\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffff66"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffff66\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffff99"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffff99\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffffcc"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffffcc\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
			<td bgcolor="#ffffff"><a href="javascript: podPress_colorLock()" onmouseover="javascript: podPress_colorSet(\'#ffffff\'); return true"><img src="'.podPress_url().'images/podpress_spacer.gif" border="0" height="10" width="10" alt="." /></a></td>
		</tr>
	</table>
	<br/>
	<br/>
	<input type="button" name="ResetColors" value="Reset Colors to Defaults" onclick="javascript: podPress_colorReset();" />

			'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_leftbg_">'.__('Left background', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_leftbg_" name="playerSettings[leftbg]" size="40" value="'.$this->settings['player']['leftbg'].'" style="background-color: '.$this->settings['player']['leftbg'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_rightbg_">'.__('Right background', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_rightbg_" name="playerSettings[rightbg]" size="40" value="'.$this->settings['player']['rightbg'].'" style="background-color: '.$this->settings['player']['rightbg'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_rightbghover_">'.__('Right background (hover)', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2"valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_rightbghover_" name="playerSettings[rightbghover]" size="40" value="'.$this->settings['player']['rightbghover'].'" style="background-color: '.$this->settings['player']['rightbghover'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_lefticon_">'.__('Left icon', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_lefticon_" name="playerSettings[lefticon]" size="40" value="'.$this->settings['player']['lefticon'].'" style="background-color: '.$this->settings['player']['lefticon'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_righticon_">'.__('Right icon', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_righticon_" name="playerSettings[righticon]" size="40" value="'.$this->settings['player']['righticon'].'" style="background-color: '.$this->settings['player']['righticon'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_righticonhover_">'.__('Right icon (hover)', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_righticonhover_" name="playerSettings[righticonhover]" size="40" value="'.$this->settings['player']['righticonhover'].'" style="background-color: '.$this->settings['player']['righticonhover'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_text_">'.__('Text', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_text_" name="playerSettings[text]" size="40" value="'.$this->settings['player']['text'].'" style="background-color: '.$this->settings['player']['text'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_slider_">'.__('Slider', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_slider_" name="playerSettings[slider]" size="40" value="'.$this->settings['player']['slider'].'" style="background-color: '.$this->settings['player']['slider'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_track_">'.__('Progress bar track', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_track_" name="playerSettings[track]" size="40" value="'.$this->settings['player']['track'].'" style="background-color: '.$this->settings['player']['track'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_loader_">'.__('Loader bar', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_loader_" name="playerSettings[loader]" size="40" value="'.$this->settings['player']['loader'].'" style="background-color: '.$this->settings['player']['loader'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_border_">'.__('Progress bar border', 'podpress').':</label></th>'."\n";
			echo '				<td colspan="2" valign="top">'."\n";
			echo '					<input type="text" id="playerSettings_border_" name="playerSettings[border]" size="40" value="'.$this->settings['player']['border'].'" style="background-color: '.$this->settings['player']['border'].';" onfocus="javascript: podPress_switchColorInputs(this.id);" onchange="javascript: this.style.background=this.value;" /><br />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="playerSettings_listenWrapper">'.__('Enable Listen Wrapper', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<input valign="top" type="checkbox" name="playerSettings[listenWrapper]" id="playerSettings_listenWrapper" '; if($this->settings['player']['listenWrapper']) { echo 'checked="checked"'; } echo " onclick=\"javascript: podPressShowHideDiv('podPressPlayerSpace_1');\"/>\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<div id="podPressPlayerSpace_1"'; if(!$this->settings['player']['listenWrapper']){ echo 'style="display: none;"'; } echo "></div>\n";
			echo '					<div id="podPressPlayerSpace_1_PlayLink"></div>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";
			echo '<script type="text/javascript"><!--'."\n";
			echo "	podPressMP3PlayerWrapper = true;\n";
			echo "	document.getElementById('podPressPlayerSpace_1').innerHTML = podPressGeneratePlayer(1, 'sample.mp3', '', '');\n";
			echo "--></script>\n";

			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Video Player', 'podpress').'</legend>'."\n";
			echo '		<table class="editform" cellpadding="5" cellspacing="2" width="100%">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="disableVideoPreview">'.__('Disable Video Preview', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<input type="checkbox" name="disableVideoPreview" id="disableVideoPreview" '; if($this->settings['disableVideoPreview']) { echo 'checked="checked"'; } echo " onclick=\"javascript: podPressShowHideRow('videoPreviewImageWrapper'); podPressShowHideRow('videoPreviewPlayerWrapper');\"/>\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">When checked the video preview will be disabled.</td>'."\n";
			echo '			</tr> '."\n";
			if($this->settings['disableVideoPreview']){
				$showVideoPreviewOptions = 'style="display: none;"';
			}
			echo '			<tr id="videoPreviewImageWrapper" '.$showVideoPreviewOptions.'>'."\n";
			echo '				<th width="33%" valign="top"><label for="videoPreviewImage">'.__('Preview Image URL', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top" colspan="2">'."\n";
			echo '					<input type="text" id="videoPreviewImage" name="videoPreviewImage" size="40" value="'.$this->settings['videoPreviewImage'].'" onchange="javascript: document.getElementById(\'podPress_previewImageIMG_2\').src = this.value;" />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr id="videoPreviewPlayerWrapper" '.$showVideoPreviewOptions.'>'."\n";
			echo '				<th width="33%" valign="top"><label for="videoPreviewImage">'.__('Preview Image', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top" colspan="2">'."\n";
			echo '					<div id="podPressPlayerSpace_2"></div>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";
			echo '		</table>'."\n";
			echo '<script type="text/javascript"><!--'."\n";
			echo "	document.getElementById('podPressPlayerSpace_2').innerHTML = podPressGenerateVideoPreview (2, '', 320, 240, '".$this->settings['videoPreviewImage']."', true);\n";
			echo "--></script>\n";
			echo '	</fieldset>'."\n";
			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('All Players', 'podpress').'</legend>'."\n";
			echo '		<table class="editform" cellpadding="5" cellspacing="2" width="100%">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentAutoDisplayPlayer">'.__('Display Player/Preview', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<input type="checkbox" name="contentAutoDisplayPlayer" id="contentAutoDisplayPlayer" '; if($this->settings['contentAutoDisplayPlayer']) { echo 'checked="checked"'; } echo "/>\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">When checked the player/preview will be visible by default.</td>'."\n";
			echo '			</tr> '."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";


			echo '	<input type="hidden" name="podPress_submitted" value="players" />'."\n";
			echo '	<p class="submit"> '."\n";
			echo '	<input type="submit" name="Submit" value="'.__('Update Options', 'podpress').' &raquo;" /> '."\n";
			echo '	</p> '."\n";
			echo '	</form> '."\n";
			echo '</div>'."\n";
		}

		function settings_players_save() {
			if(function_exists('wp_cache_flush')) {
				wp_cache_flush();
			}

			if(isset($_POST['contentAutoDisplayPlayer'])) {
				$this->settings['contentAutoDisplayPlayer'] = true;
			} else {
				$this->settings['contentAutoDisplayPlayer'] = false;
			}

			$this->settings['videoPreviewImage'] = $_POST['videoPreviewImage'];

			if(isset($_POST['disableVideoPreview'])) {
				$this->settings['disableVideoPreview'] = true;
			} else {
				$this->settings['disableVideoPreview'] = false;
			}

			if(isset($_POST['mp3Player']) && $_POST['mp3Player'] == '1pixelout') {
				$this->settings['mp3Player'] = '1pixelout';
			} else {
				$this->settings['mp3Player'] = 'podango';
			}

			if(isset($_POST['playerSettings']['listenWrapper'])) {
				$this->settings['playerSettings']['listenWrapper'] = true;
			} else {
				$this->settings['playerSettings']['listenWrapper'] = false;
			}

		 	$this->settings['player'] = $_POST['playerSettings'];
			delete_option('podPress_config');
			podPress_add_option('podPress_config', $this->settings);

			$location = get_option('siteurl') . '/wp-admin/admin.php?page=podpress/podpress_players.php&updated=true';
			header('Location: '.$location);
			exit;
		}
	}
