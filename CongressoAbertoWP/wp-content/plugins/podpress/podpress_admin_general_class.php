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

		function settings_general_edit() {
			GLOBAL $wpdb, $wp_rewrite;
			podPress_isAuthorized();
			if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
				echo '<div id="message" class="updated fade"><p>'. __('Settings Saved', 'podpress').'</p></div>';
			}

			if(!$this->settings['compatibilityChecks']['wp_head'] || !$this->settings['compatibilityChecks']['wp_footer']) {
				echo '<div class="wrap">'."\n";
				if($this->settings['compatibilityChecks']['themeTested']) {
					echo '	<h2>'.__('Theme Compatibility Problem', 'podpress').'</h2>'."\n";
				} else {
					echo '	<h2>'.__('Theme Compatibility Check Required', 'podpress').'</h2>'."\n";
				}
				echo '	<fieldset class="options">'."\n";
				if($this->settings['compatibilityChecks']['themeTested']) {
				echo '		<legend>'.__('Current Theme is not compliant', 'podpress').'</legend>'."\n";
				} else {
				echo '		<legend>'.__('Current Theme needs to be tested', 'podpress').'</legend>'."\n";
				}
				echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
				echo '			<tr>'."\n";
				echo '				<td>'."\n";
				if($this->settings['compatibilityChecks']['themeTested']) {
					echo '				podPress has found the "'.get_current_theme().'" theme fails to meet important requirements.<br/><br/>'."\n";
				} else {
					echo '				podPress has not yet detected the "'.get_current_theme().'" theme to be compliant. Please visit your <a href="'.podPress_siteurl().'">main blog page</a> for podPress to re-check.<br/><br/>'."\n";
				}

				if(!$this->settings['compatibilityChecks']['wp_head']) {
					echo '				The header.php in your theme needs to be calling wp_head(); before the closing head tag.<br/>'."\n";
					echo '				Change this:<br/>'."\n";
					echo '				<code>&lt;head&gt;</code><br/>'."\n";
					echo '				To this:<br/>'."\n";
					echo '				<code>&lt;?php wp_head(); ?&gt;'."<br/>\n".'&lt;head&gt;</code><br/>'."\n";
					echo '				<br/>'."\n";
				}
				if(!$this->settings['compatibilityChecks']['wp_footer']) {
					echo '				The footer.php in your theme needs to be calling wp_footer(); before the closing body tag.<br/>'."\n";
					echo '				Change this:<br/>'."\n";
					echo '				<code>&lt;/body&gt;</code><br/>'."\n";
					echo '				To this:<br/>'."\n";
					echo '				<code>&lt;?php wp_footer(); ?&gt;'."<br/>\n".'&lt;/body&gt;</code><br/>'."\n";
				}
				echo '				Look at the default theme files for example.<br/>'."\n";
				echo '				</td>'."\n";
				echo '			</tr> '."\n";
				echo '		</table>'."\n";
				echo '	</fieldset>'."\n";
				echo '</div>'."\n";
			}

			echo '<div class="wrap">'."\n";
			echo '	<h2>'.__('General Options', 'podpress').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.mightyseek.com/podpress/#download" target="_new"><img src="http://www.mightyseek.com/podpress_downloads/versioncheck.php?current='.PODPRESS_VERSION.'" alt="'.__('Checking for updates... Failed.', 'podpress').'" border="0" /></a></h2>'."\n";
			echo '	<form method="post">'."\n";
			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Media File Locations', 'podpress').'</legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="mediaWebPath">'.__('URI of media files directory', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'mediaWebPathHelp\');">(?)</a>:</th>'."\n";
			echo '				<td>'."\n";
			echo '					<input type="text" id="mediaWebPath" name="mediaWebPath" size="40" value="'.stripslashes($this->settings['mediaWebPath']).'" /><br/>'."\n";
			if(!isset($this->settings['mediaWebPath']) || empty($this->settings['mediaWebPath'])){
				echo "<br />\n";
				echo __('Suggested', 'podpress').': <code>wp-content/files</code>'."\n";
			}
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr id="mediaWebPathHelp" style="display: none;">'."\n";
			echo '				<th width="33%" valign="top">&nbsp;</th>'."\n";
			echo '				<td>';
			echo '					'.__('Point this to the full url where you put your media files.', 'podpress').'<br/>'.__('For example', 'podpress').':<br/><code>http://www.yoursite.com/wp-content/files/</code>'."\n";
			echo '					<br/>'.__('Can be local or remote location.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="mediaFilePath">'.__('Local path to media files directory (optional)', 'podpress').'</label>  <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'mediaFilePathHelp\');">(?)</a>:</th>'."\n";
			echo '				<td>'."\n";
			echo '					<input type="text" id="mediaFilePath" name="mediaFilePath" size="40" value="'.stripslashes($this->settings['mediaFilePath']).'" /><br />'."\n";
			if(!empty($this->settings['autoDetectedMediaFilePath'])){
				echo "<br/>\n";
				echo __('Directory Not Valid or Not Accessible', 'podpress').'<br/> '.__('Suggested', 'podpress').': <code>'.$this->settings['autoDetectedMediaFilePath'].'</code>'."\n";
			}
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr id="mediaFilePathHelp" style="display: none;">'."\n";
			echo '				<th width="33%" valign="top">&nbsp;</th>'."\n";
			echo '				<td>';
			echo '					This is an optional feature which is used to speed up the time/duration detection process, and is only possible if you host the media files on the same machine as your website (libsyn user need not apply). Instead of having to go download the file, it can read it directly from disk So make this whatever system dir has those media files. It does NOT have to be under the podpress dir.'."\n";
			echo '					<br/>For example this could be...<br/>'."\n";
			echo '					<code>/home/yoursite/http/wp-content/files/</code>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";

			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Download Statistics', 'podpress').'</legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enableStats">'.__('Enable podPress Statistics', 'podpress').':</label></th>'."\n";
			echo '				<td>'."\n";
			echo '					<input type="checkbox" name="enableStats" id="enableStats" '; if($this->settings['enableStats']) { echo 'checked="checked"'; } echo " onclick=\"javascript: podPressShowHideRow('statWarning'); podPressShowHideRow('statMethodWrapper'); podPressShowHideRow('statLoggingWrapper'); \"/>\n";
			echo '				</td>'."\n";
			echo '				<td>This will use the included stats support in podPress.</td>'."\n";
			echo '			</tr> '."\n";
			if(!$this->settings['enableStats']){
				$showStatsOptions = 'style="display: none;"';
			}

			$home_path = get_home_path();
			$hasHtaccess = false;
			if (file_exists($home_path.'.htaccess')) {
				$htaccessContents = file_get_contents($home_path.'.htaccess');
				if($pos = strpos($htaccessContents, 'RewriteCond')) {
					$hasHtaccess = true;
				}elseif($pos = strpos($htaccessContents, 'RewriteRule')) {
					$hasHtaccess = true;
				}
			}
			if(is_writable($home_path) || is_writable($home_path.'.htaccess')) {
				$writable = true;
			} else {
				$writable = false;
			}

			if (!$wp_rewrite->using_index_permalinks()) {
				$usingpi = false;
				if(!$hasHtaccess && $writable) {
					$wp_rewrite->set_permalink_structure('/archives/%post_id%');
					$wp_rewrite->set_permalink_structure('');
					$usingpi = true;
				}
				$wp_rewrite->permalink_structure = '/archives/%post_id%';
			} else {
				$usingpi = true;
			}

			if (!$usingpi && !$hasHtaccess && $this->settings['statMethod'] == 'permalinks') {
				$showPermalinksWarning = 'style="display: block;"';
			} else {
				$showPermalinksWarning = 'style="display: none;"';
			}

			$permalink_structure = get_settings('permalink_structure');
			echo '			<tr id="statWarning" '.$showStatsOptions.'>'."\n";
			echo '				<td colspan="3">'."\n";
			echo '					<div id="permalinksWarning" '.$showPermalinksWarning.'>'."\n";
			echo '						<p style="color: red;">'.__('Warning: It appears you are not using WordPress permalinks or 404 handling. Due to this enabling podPress stats may cause media file downloads to fail.', 'podpress').'</p>'."\n";
			echo '						<p>'._e('According to WordPress, if your <code>.htaccess</code> file in your main WordPress directory were <a href="http://codex.wordpress.org/Make_a_Directory_Writable">writable</a>, we could do this automatically, but it isn&#8217;t so these are the mod_rewrite rules you should have in your <code>.htaccess</code> file. Click in the field and press <kbd>CTRL + a</kbd> to select all.').'</p>'."\n";
			echo '						<textarea rows="5" style="width: 98%;" name="rules">'.htmlentities($wp_rewrite->mod_rewrite_rules(), ENT_QUOTES, get_settings('blog_charset'))."</textarea>\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr id="statMethodWrapper" '.$showStatsOptions.'>'."\n";
			echo '				<th width="33%" valign="top">&nbsp;</th>'."\n";
			echo '				<td>&nbsp;</td>'."\n";
			echo '				<td>';
			echo '					<label for="statMethod">'.__('Stat Method', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'statMethodHelp\');">(?)</a>:';
			echo '					<select name="statMethod" id="statMethod">'."\n";
			echo '						<option value="permalinks" '; if($this->settings['statMethod'] == 'permalinks') { echo 'selected="selected"'; } echo '>'.__('Use WP Permalinks', 'podpress').'</option>'."\n";
			echo '						<option value="podpress_trac_dir" '; if($this->settings['statMethod'] == 'podpress_trac_dir') { echo 'selected="selected"'; } echo '>'.__('Optional Files podpress_trac directory', 'podpress').'</option>'."\n";
			echo '						<option value="download.mp3" '; if($this->settings['statMethod'] == 'download.mp3') { echo 'selected="selected"'; } echo '>'.__('Use download.mp3', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr id="statMethodHelp" style="display: none;">'."\n";
			echo '				<th width="33%" valign="top">&nbsp;</th>'."\n";
			echo '				<td>&nbsp;</td>'."\n";
			echo '				<td>';
			echo '				By default podPress will try and let the included .htaccess file do its magic. If enabling this feature breaks your download links, then you should disable it and look into using the download.mp3<br/><br/>'."\n";
			echo '				<strong>Using the .htaccess file: </strong><br/>'."\n";
			echo '				If your on a server with the Apache Web Server, then this feature is possible to get working, but is not always possible. Everything depends on the configuration of your web server.<br/>'."\n";
			echo '				The .htaccess file is considered an "Override" file and is allowed or not at the server configuration level.<br/>'."\n";
			echo '				Details about the apache config are here: <a href="http://httpd.apache.org/docs/2.0/mod/core.html#allowoverride">http://httpd.apache.org/docs/2.0/mod/core.html#allowoverride</a><br/><br/>'."\n";
			echo '				The directory where podpress is located needs to have<br/>'."\n";
			echo '				allowoverride FileInfo Options<br/>'."\n";
			echo '				or<br/>'."\n";
			echo '				allowoverride All<br/><br/>'."\n";
			echo '				For more updated information <a href="http://www.mightyseek.com/forum/faq.php?faq=podpress#faq_htaccessnotworking">view the FAQ</a><br/><br/>'."\n";
			echo '				<strong>Using the download.mp3 file: </strong><br/>'."\n";
			echo '				This is an alternative to using the .htaccess file. This is provided for sites that are not able to use the .htaccess file, such as sites hosted on Microsoft Internet Information Server (IIS)<br/>'."\n";
			echo '				To use this, you will need to tell your web server to process the .mp3 files the same way it does .php files. This is only nessesary for the podpress direct, and so that download.mp3 will be processed as a .php file.<br/>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr id="statLoggingWrapper" '.$showStatsOptions.'>'."\n";
			echo '				<th width="33%" valign="top">&nbsp;</th>'."\n";
			echo '				<td>&nbsp;</td>'."\n";
			echo '				<td>';
			echo '					<label for="statLogging">'.__('Stat Logging', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'statLoggingHelp\');">(?)</a>:';
			echo '					<select name="statLogging" id="statLogging">'."\n";
			echo '						<option value="Counts" '; if($this->settings['statLogging'] == 'Counts') { echo 'selected="selected"'; } echo '>'.__('Counts Only', 'podpress').'</option>'."\n";
			echo '						<option value="Full" '; if($this->settings['statLogging'] == 'Full') { echo 'selected="selected"'; } echo '>'.__('Full', 'podpress').'</option>'."\n";
			echo '						<option value="FullPlus" '; if($this->settings['statLogging'] == 'FullPlus') { echo 'selected="selected"'; } echo '>'.__('Full+', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'."\n";
			echo '					<label for="statTest"><br/>'."\n";
			echo '					<input type="button" name="statTest" value="Test Stats Support" onclick="podPressTestStats(\''.podPress_siteurl().'/podpress_trac/web/0/0/podPressStatTest.txt\')"/>'."\n";
			unset($x);
			echo '					<input type="text" name="statTestResult" id="statTestResult" size="20" value="" />';
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr id="statLoggingHelp" style="display: none;">'."\n";
			echo '				<th width="33%" valign="top">&nbsp;</th>'."\n";
			echo '				<td>&nbsp;</td>'."\n";
			echo '				<td>';
			echo '				As of version 4.5, podPress will normally store only counts of the downloads and the methods used to get to your media files.<br/>'."\n";
			echo '				If you would like full detailed logging, choose Full. Full will record more than it shows in the Stats page, the data is in the database if you would like to get to it.<br/>'."\n";
			echo '				If you would like to know which users fully downloaded files, then choose Full+ which tracks completed vs partial downloads. <b>Full+ MAY CAUSE DOWNLOAD PROBLEMS. TEST WELL BEFORE DEPENDING ON THIS</b><br/>'."\n";
			echo '				At some point I will add better screens for viewing and exporting the full data collected.'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enablePodTracStats">'.__('Enable PodTrac Statistics', 'podpress').':</label></th>'."\n";
			echo '				<td>'."\n";
			echo '					<input type="radio" name="enable3rdPartyStats" id="enablePodTracStats" value="PodTrac"'; if($this->settings['enable3rdPartyStats'] == 'PodTrac') { echo 'checked="checked"'; } echo " onclick=\"javascript: document.getElementById('statBluBrryWrapper').style.display='none';\"/>\n";
			echo '				</td>'."\n";
			echo '				<td>This will use the PodTrac service. <a href="http://www.podtrac.com/" target="_new">More info...</a></td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enableBlubrryStats">'.__('Enable Blubrry Statistics', 'podpress').':</label></th>'."\n";
			echo '				<td>'."\n";
			echo '					<input type="radio" name="enable3rdPartyStats" id="enableBlubrryStats" value="Blubrry"'; if($this->settings['enable3rdPartyStats'] == 'Blubrry') { echo 'checked="checked"'; } echo " onclick=\"javascript: document.getElementById('statBluBrryWrapper').style.display='';\"/>\n";
			echo '				</td>'."\n";
			echo '				<td>This will use the Blubrry service. <a href="http://www.blubrry.com/podpress/" target="_new">More info...</a></td>'."\n";
			echo '			</tr> '."\n";
			if($this->settings['enable3rdPartyStats'] != 'Blubrry'){
				$showBlubrryStatOptions = 'style="display: none;"';
			}
			echo '			<tr id="statBluBrryWrapper" '.$showBlubrryStatOptions.'>'."\n";
			echo '				<th width="33%" valign="top">&nbsp;</th>'."\n";
			echo '				<td>&nbsp;</td>'."\n";
			echo '				<td>';
			echo '					<label for="statBluBrryProgramKeyword">'.__('Program Keyword', 'podpress').'</label>:';
			echo '					<input type="input" name="statBluBrryProgramKeyword" id="statBluBrryProgramKeyword" value="'.$this->settings['statBluBrryProgramKeyword'].'"/>';
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="disable3rdPartyStats">'.__('Disable 3rd Party Statistics', 'podpress').':</label></th>'."\n";
			echo '				<td>'."\n";
			echo '					<input type="radio" name="enable3rdPartyStats" id="disable3rdPartyStats" value="No"'; if(!in_array($this->settings['enable3rdPartyStats'], array('PodTrac', 'Blubrry'))) { echo 'checked="checked"'; } echo " onclick=\"javascript: document.getElementById('statBluBrryWrapper').style.display='none';\"/>\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '		</table>'."\n";
			echo '		'.__('Note: Only two stats services can be used together. <br/>Libsyn and Feedburner count as well, if your using any of those, they count against the limit of two.', 'podpress')."\n";
			echo '	</fieldset>'."\n";

			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Post Editing', 'podpress').'</legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="maxMediaFiles">'.__('Max Number of Media Files', 'podpress').':</label></th>'."\n";
			echo '				<td>'."\n";
			echo '					<input type="input" name="maxMediaFiles" id="maxMediaFiles" value="'.$this->settings['maxMediaFiles'].'"'."/>\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '			<tr>'."\n";
			echo '				<td colspan="2">The higher the number, the bigger the performance impact when editing Posts.</td>'."\n";
			echo '			</tr> '."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";


			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Podango Integration', 'podpress').' <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'podangoIntegrationHelp\');">(?)</a></legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enablePodangoIntegration">'.__('Enable Podango Integration', 'podpress').':</label></th>'."\n";
			if(!$this->settings['enablePodangoIntegration']){
				$showPodangoOptions = 'style="display: none;"';
			}
			echo '				<td>'."\n";
			echo '					<input type="checkbox" name="enablePodangoIntegration" id="enablePodangoIntegration" '; if($this->settings['enablePodangoIntegration']) { echo 'checked="checked"'; } echo " />\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr id="podangoIntegrationHelp" style="display: none;">'."\n";
			echo '				<td colspan="2">';
			echo '					podPress users can gain additional functionality when used in combination with Podango hosting.<br/>'."\n";
			echo '					Letm e count the ways<br/>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";

			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Premium Content', 'podpress').' <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'premiumPodcastingHelp\');">(?)</a></legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enablePremiumContent">'.__('Enable Premium Content', 'podpress').':</label></th>'."\n";
			if(!podPress_WPVersionCheck('2.0.0')) {
				echo '			<tr>'."\n";
				echo '				<td>Only available in WordPress 2.0.0 or greater</td>'."\n";
				echo '			</tr> '."\n";
			} else {
				if(!$this->settings['enablePremiumContent']){
					$showPremiumOptions = 'style="display: none;"';
				}
				echo '				<td>'."\n";
				echo '					<input type="checkbox" name="enablePremiumContent" id="enablePremiumContent" '; if($this->settings['enablePremiumContent']) { echo 'checked="checked"'; } echo " onclick=\"javascript: podPressShowHideRow('protectedMediaFilePathWrapper'); podPressShowHideRow('premiumMethodWrapper'); podPressShowHideRow('premiumMethodText'); podPressShowHideRow('premiumContentFakeEnclosureWrapper'); podPressShowHideRow('premiumContentFakeEnclosureText');\" />\n";
				echo '				</td>'."\n";
				echo '			</tr> '."\n";
				echo '			<tr id="protectedMediaFilePathWrapper" '.$showPremiumOptions.'>'."\n";
				echo '				<td></td></tr>'."\n";
				echo '			<tr style="display: none;">'."\n";
				echo '				<th width="33%" valign="top"><label for="protectedMediaFilePath">'.__('Local path to protected media', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'protectedMediaFilePathHelp\');">(?)</a>:</th>'."\n";
				echo '				<td>'."\n";
				echo '					<input type="text" id="protectedMediaFilePath" name="protectedMediaFilePath" size="40" value="'.stripslashes($this->settings['protectedMediaFilePath']).'" /><br/>'."\n";
				echo '				</td>'."\n";
				echo '			</tr>'."\n";
				echo '			<tr id="protectedMediaFilePathHelp" style="display: none;">'."\n";
				echo '				<th width="33%" valign="top">&nbsp;</th>'."\n";
				echo '				<td>';
				echo '					This is an optional feature which is used to protect some content from being accessed unless users have the appropriate rights. It should NOT be in a dir under your web root. It should be a dir outside of the web root so that users cannot simply browse to the dir and get access to the files.'."\n";
				echo '					<br/>For example this could be...<br/>'."\n";
				echo '					<code>/home/premium_mp3s/</code>'."\n";
				echo '				</td>'."\n";
				echo '			</tr> '."\n";

				echo '			<tr id="premiumMethodWrapper" '.$showPremiumOptions.'>'."\n";
				echo '				<th width="33%" valign="top"><label for="premiumMethod">'.__('Method', 'podpress').':</label></th>'."\n";
				echo '				<td>'."\n";
				echo '					<select name="premiumMethod" id="premiumMethod">'."\n";
				echo '						<option value="Digest" '; if($this->settings['premiumMethod'] != 'Basic') { echo 'selected="selected"'; } echo '>'.__('Digest', 'podpress').'</option>'."\n";
				echo '						<option value="Basic" '; if($this->settings['premiumMethod'] == 'Basic') { echo 'selected="selected"'; }  echo '>'.__('Basic', 'podpress').'</option>'."\n";
				echo '					</select>'."\n";
				echo '				</td>'."\n";
				echo '			</tr> '."\n";
				echo '			<tr id="premiumMethodText" '.$showPremiumOptions.'>'."\n";
				echo '				<td colspan="2">Digest auth is MUCH better than Basic, which is easily unencrypted.</td>'."\n";
				echo '			</tr> '."\n";
				echo '			<tr id="premiumContentFakeEnclosureWrapper" '.$showPremiumOptions.'>'."\n";
				echo '				<th width="33%" valign="top"><label for="premiumContentFakeEnclosure">'.__('Use fake enclosure', 'podpress').':</label></th>'."\n";
				echo '				<td>'."\n";
				echo '					<input type="checkbox" name="premiumContentFakeEnclosure" id="premiumContentFakeEnclosure" '; if($this->settings['premiumContentFakeEnclosure']) { echo 'checked="checked"'; } echo "/>\n";
				echo '				</td>'."\n";
				echo '			</tr> '."\n";
				echo '			<tr id="premiumContentFakeEnclosureText" '.$showPremiumOptions.'>'."\n";
				echo '				<td colspan="2">If you want the enclosure to always exist (so feed will show up in iTunes), then check this.</td>'."\n";
				echo '			</tr> '."\n";
			}

			echo '			<tr id="premiumPodcastingHelp" style="display: none;">'."\n";
			echo '				<td colspan="2">';
			echo '					Full documentation is still under development on <a href="http://podcasterswiki.com/index.php?title=PodPress_Documentation#Premium_Podcasting">the wiki</a><br/><br/>'."\n";
			echo '					This is the short of it is that you need to get and install the <a href="http://redalt.com/wiki/Role+Manager">Role Manager plugin</a><br/>'."\n";
			echo '					Anyone that should have access to the premium podcasting files need to have the Premium Content role, which can be done by making them Premium Subscribers<br/><br/>'."\n";
			echo '					Then just in each post set the media file as premium content and normal visitors will not be able to see the content via the web or from the feed.<br/>'."\n";
			echo '					If your using Wordpress 2.1, then users can just use http://www.yoursite.com/?feed=premium for their premium content needs.<br/>'."\n";
			echo '					If your using WP 1.5 or 2.0.x, then you need to put premiumcast.php in your main wordpress dir and then have your subscribers use this file as their rss feed.<br/>'."\n";
			echo '					These will cause the site to ask for their user/pass before giving the RSS feed. Juice and iTunes supports this fine.<br/><br/>'."\n";
			echo '					Keep in mine, that this does NOT protect your content if someone discovers the URLS, it only hides the location from showing up on the site or in the feed. To fully protect your files I have also been able to get this working with <a href="http://www.amember.com/">aMemberPro</a><br/>'."\n";
			echo '					aMemberPro will protect the files from being downloaded at all, unless authorized. It also handles monthly subscription issues thru paypal and such. Its a great tool, and combines with WordPress and podPress you can have a full blown premium content podcasting service.<br/>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";

			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Post Content', 'podpress').'</legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentLocation">'.__('Location', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<select name="contentLocation" id="contentLocation">'."\n";
			echo '						<option value="start" '; if($this->settings['contentLocation'] == 'start') { echo 'selected="selected"'; } echo '>'.__('Start', 'podpress').'</option>'."\n";
			echo '						<option value="end" '; if($this->settings['contentLocation'] != 'start') { echo 'selected="selected"'; }  echo '>'.__('End', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">Part of the Post where the podPress content (Player, and links) will go. Default is at the end.</td>'."\n";
			echo '			</tr> '."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentImage">'.__('Image', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<select name="contentImage" id="contentImage">'."\n";
			echo '						<option value="button" '; if($this->settings['contentImage'] == 'button') { echo 'selected="selected"'; }  echo '>'.__('Button', 'podpress').'</option>'."\n";
			echo '						<option value="icon" '; if($this->settings['contentImage'] == 'icon') { echo 'selected="selected"'; }  echo '>'.__('Icon', 'podpress').'</option>'."\n";
			echo '						<option value="none" '; if($this->settings['contentImage'] == 'none') { echo 'selected="selected"'; } echo '>'.__('None', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">The image that shows up before links for the media file.</td>'."\n";
			echo '			</tr> '."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentPlayer">'.__('Player', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<select name="contentPlayer" id="contentPlayer">'."\n";
			echo '						<option value="both" '; if($this->settings['contentPlayer'] == 'both') { echo 'selected="selected"'; }  echo '>'.__('Enabled', 'podpress').'</option>'."\n";
			echo '						<option value="inline" '; if($this->settings['contentPlayer'] == 'inline') { echo 'selected="selected"'; }  echo '>'.__('Inline Only', 'podpress').'</option>'."\n";
			echo '						<option value="popup" '; if($this->settings['contentPlayer'] == 'popup') { echo 'selected="selected"'; } echo '>'.__('Popup Only', 'podpress').'</option>'."\n";
			echo '						<option value="disabled" '; if($this->settings['contentPlayer'] == 'disabled') { echo 'selected="selected"'; } echo '>'.__('Disabled', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">Allow users to make use of the web players for your content</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentDownload">'.__('Download', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<select name="contentDownload" id="contentDownload">'."\n";
			echo '						<option value="enabled" '; if($this->settings['contentDownload'] == 'enabled') { echo 'selected="selected"'; }  echo '>'.__('Enabled', 'podpress').'</option>'."\n";
			echo '						<option value="disabled" '; if($this->settings['contentDownload'] == 'disabled') { echo 'selected="selected"'; } echo '>'.__('Disabled', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">Allow users to download the MP3 directly from the website.</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentDownloadText">'.__('Show Download Text', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<select name="contentDownloadText" id="contentDownloadText">'."\n";
			echo '						<option value="enabled" '; if($this->settings['contentDownloadText'] == 'enabled') { echo 'selected="selected"'; }  echo '>'.__('Enabled', 'podpress').'</option>'."\n";
			echo '						<option value="disabled" '; if($this->settings['contentDownloadText'] == 'disabled') { echo 'selected="selected"'; } echo '>'.__('Disabled', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">If disabled, users can still download using the icon link.</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentDownloadStats">'.__('Show Download Stats', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<select name="contentDownloadStats" id="contentDownloadStats">'."\n";
			echo '						<option value="enabled" '; if($this->settings['contentDownloadStats'] == 'enabled') { echo 'selected="selected"'; }  echo '>'.__('Enabled', 'podpress').'</option>'."\n";
			echo '						<option value="disabled" '; if($this->settings['contentDownloadStats'] == 'disabled') { echo 'selected="selected"'; } echo '>'.__('Disabled', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">Display download stats on each post for everyone to see. This will cause a performance hit of 2 extra SQL queries per post being displayed. Disable this feature if your site is slowing down when podpress is enabled.</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentDuration">'.__('Show Duration', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<select name="contentDuration" id="contentDuration">'."\n";
			echo '						<option value="enabled" '; if($this->settings['contentDuration'] == 'enabled') { echo 'selected="selected"'; }  echo '>'.__('Enabled', 'podpress').'</option>'."\n";
			echo '						<option value="disabled" '; if($this->settings['contentDuration'] == 'disabled') { echo 'selected="selected"'; } echo '>'.__('Disabled', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">Display the duration for each media file.</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="contentBeforeMore">'.__('Before', 'podpress').' &lt;!- More -&gt; '.__('tag', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<select name="contentBeforeMore" id="contentBeforeMore">'."\n";
			echo '						<option value="yes" '; if($this->settings['contentBeforeMore'] == 'yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '						<option value="no" '; if($this->settings['contentBeforeMore'] != 'yes') { echo 'selected="selected"'; }  echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td valign="top">This defines that the Podcast player and download links will always be visible on the short version of the Post</td>'."\n";
			echo '			</tr> '."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";

/*
			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('TorrentCasting', 'podpress').'</legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enableTorrentCasting">'.__('Enable TorrentCasting', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<input type="checkbox" name="enableTorrentCasting" id="enableTorrentCasting" '; if($this->settings['enableTorrentCasting']) { echo 'checked="checked"'; } echo "/>\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '			<tr>'."\n";
			echo '				<td colspan="2">This just allows for you to define a location to the .torrent file for you content. If you enable this you should copy the torrentcast.php file from plugins/podpress/optional_files and into your main wordpress directory.</td>'."\n";
			echo '			</tr> '."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";
*/


			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Feed Caching', 'podpress').'</legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<td valign="top">'."\n";
			echo ' Feedcache files will be stored in the follow directory: <code>'.get_option('upload_path').'/podpress_temp'.'</code>'."\n";
			$this->checkWritableTempFileDir();		
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '			<tr>'."\n";
			echo '				<td colspan="2">If you are using the index.php from optional_files in your main wordpress directory then you should set this value to match the $podPressFeedCacheDir value.</td>'."\n";
			echo '			</tr> '."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";

			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Credit', 'podpress').'</legend>'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enableFooter">'.__('Show PodPress footer.', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<input type="checkbox" name="enableFooter" id="enableFooter" '; if($this->settings['enableFooter']) { echo 'checked="checked"'; } echo '/>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '			<tr>'."\n";
			echo '				<td colspan="2">Enabling this allows you to give us credit for making your podcasting easier, '."\n";
			echo '					and lets other podcasters find out what your using to have such cool features on your podcasting blog ;)<br/>'."\n";
			echo '					If this feature makes your site look bad, please add in podpress with all the other credits, such as the ones in place for WordPress.<br/>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";
			echo '	<input type="hidden" name="podPress_submitted" value="general" />'."\n";
			echo '	<p class="submit"> '."\n";
			echo '	<input type="submit" name="Submit" value="'.__('Update Options', 'podpress').' &raquo;" /> '."\n";
			echo '	</p> '."\n";
			echo '	</form> '."\n";

			echo '	<fieldset class="options">'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enableFooter">'.__('Frapper Map.', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<a href="http://www.frappr.com/mightyseek"><img src="http://www.frappr.com/i/frapper_sticker.gif" alt="Check out our Frappr!" title="Check out our Frappr!" border="0"></a>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '			<tr>'."\n";
			echo '				<td colspan="2">Let us know where you are!</td>'."\n";
			echo '			</tr> '."\n";

			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";

			echo '	<fieldset class="options">'."\n";
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
			echo '			<tr>'."\n";
			echo '				<th width="33%" valign="top"><label for="enableFooter">'.__('Donations Appreciated.', 'podpress').':</label></th>'."\n";
			echo '				<td valign="top">'."\n";
			echo '					<a href="http://www.mightyseek.com/podpress_donate.php"><img alt="Donate to support this project" border="0" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" /></a>'."\n";
			echo '				</td>'."\n";
			echo '			</tr> '."\n";
			echo '			<tr>'."\n";
			echo '				<td colspan="2">This project is a labor of love, feel no obligation what-so-ever to donate. For those that want to, here ya go.</td>'."\n";
			echo '			</tr> '."\n";

			echo '		</table>'."\n";
			echo '	</fieldset>'."\n";

			$sql = "SELECT * FROM ".$wpdb->prefix."postmeta WHERE meta_key IN('podPress_podcastStandardAudio',
			                                                                 'podPress_podcastStandardAudioSize',
			                                                                 'podPress_podcastStandardAudioDuration',
			                                                                 'podPress_podcastEnhancedAudio',
			                                                                 'podPress_podcastEnhancedAudioSize',
			                                                                 'podPress_podcastEnhancedAudioDuration',
			                                                                 'podPress_podcastVideo',
			                                                                 'podPress_podcastVideoSize',
			                                                                 'podPress_podcastVideoDuration',
			                                                                 'podPress_podcastVideoDimension',
			                                                                 'podPress_webVideo',
			                                                                 'podPress_webVideoSize',
			                                                                 'podPress_webVideoDuration',
			                                                                 'podPress_webVideoDimension',
			                                                                 'podPress_podcastEbook',
			                                                                 'podPress_podcastEbookSize',
			                                                                 'itunes:duration'
																																		 )";
			$stats = $wpdb->get_results($sql);
			if($stats) {
				echo '	<form method="post">'."\n";
				echo '	<fieldset class="options">'."\n";
				echo '		<legend>'.__('Complete Upgrade Process', 'podpress').'</legend>'."\n";
				echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform">'."\n";
				echo '			<tr>'."\n";
				echo '				<th width="33%" valign="top"><label for="cleanupOldMetaKeys">'.__('Remove Pre v.40 database clutter.', 'podpress').':</label></th>'."\n";
				echo '				<td valign="top">'."\n";
				echo '					<input type="checkbox" name="cleanupOldMetaKeys" id="cleanupOldMetaKeys"/>'."\n";
				echo '				</td>'."\n";
				echo '			</tr> '."\n";
				echo '		</table>'."\n";
				echo '	</fieldset>'."\n";
				echo '	<input type="hidden" name="podPress_submitted" value="general" />'."\n";
				echo '	<p class="submit"> '."\n";
				echo '	<input type="submit" name="Submit" value="'.__('Update Options', 'podpress').' &raquo;" /> '."\n";
				echo '	</p> '."\n";
				echo '	</form> '."\n";
			}

			echo '</div>'."\n";
		}

		function settings_general_save() {
			GLOBAL $wpdb;
			if(function_exists('wp_cache_flush')) {
				wp_cache_flush();
			}
			// settings for the player
			if(isset($_POST['mediaWebPath'])) {
				if(substr($_POST['podPress_mediaWebPath'], -1) == '/')
				{
					$_POST['mediaWebPath'] = substr($_POST['mediaWebPath'], 0, -1);
				}
				$this->settings['mediaWebPath'] = $_POST['mediaWebPath'];
			}

			if(isset($_POST['mediaFilePath'])) {
				$this->settings['mediaFilePath'] = $_POST['mediaFilePath'];
			}

			if(isset($_POST['enableStats'])) {
				$this->settings['enableStats'] = true;
			} else {
				$this->settings['enableStats'] = false;
			}

			if(isset($_POST['statMethod'])) {
				$this->settings['statMethod'] = $_POST['statMethod'];
			}

			if(isset($_POST['statLogging'])) {
				$this->settings['statLogging'] = $_POST['statLogging'];
			}

			if(isset($_POST['enable3rdPartyStats'])) {
				$this->settings['enable3rdPartyStats'] = $_POST['enable3rdPartyStats'];
			}

			if(isset($_POST['enableBlubrryStats'])) {
				$this->settings['enableBlubrryStats'] = true;
			} else {
				$this->settings['enableBlubrryStats'] = false;
			}
			if(isset($_POST['statBluBrryProgramKeyword'])) {
				$this->settings['statBluBrryProgramKeyword'] = $_POST['statBluBrryProgramKeyword'];
			}

			if(isset($_POST['maxMediaFiles'])) {
				$this->settings['maxMediaFiles'] = $_POST['maxMediaFiles'];
			}

			if(isset($_POST['enablePodangoIntegration'])) {
				$this->settings['enablePodangoIntegration'] = true;
			} else {
				$this->settings['enablePodangoIntegration'] = false;
			}

			if(isset($_POST['enablePremiumContent'])) {
				$this->settings['enablePremiumContent'] = true;
				if(is_object($GLOBALS['wp_rewrite'])
					&& is_array($GLOBALS['wp_object_cache']) 
					&& is_array($GLOBALS['wp_object_cache']['cache']) 
					&& is_array($GLOBALS['wp_object_cache']['cache']['options']) 
					&& is_array($GLOBALS['wp_object_cache']['cache']['options']['alloptions']) 
					&& is_array($GLOBALS['wp_object_cache']['cache']['options']['alloptions']['rewrite_rules'])
					&& !strpos($GLOBALS['wp_object_cache']['cache']['options']['alloptions']['rewrite_rules'], 'playlist.xspf')
					) {
						$GLOBALS['wp_rewrite']->flush_rules();
				}
			} else {
				$this->settings['enablePremiumContent'] = false;
			}

			if(isset($_POST['protectedMediaFilePath'])) {
				$this->settings['protectedMediaFilePath'] = $_POST['protectedMediaFilePath'];
			}

			if(isset($_POST['premiumMethod']) && $_POST['premiumMethod'] == 'Basic') {
				$this->settings['premiumMethod'] = 'Basic';
			} else {
				$this->settings['premiumMethod'] = 'Digest';
			}

			if(isset($_POST['premiumContentFakeEnclosure'])) {
				$this->settings['premiumContentFakeEnclosure'] = true;
			} else {
				$this->settings['premiumContentFakeEnclosure'] = false;
			}

			if(isset($_POST['enableTorrentCasting'])) {
				$this->settings['enableTorrentCasting'] = true;
			} else {
				$this->settings['enableTorrentCasting'] = false;
			}

			if(isset($_POST['feedCacheDir'])) {
				$this->settings['feedCacheDir'] = $_POST['feedCacheDir'];
			}

			if(isset($_POST['contentBeforeMore'])) {
				$this->settings['contentBeforeMore'] = $_POST['contentBeforeMore'];
			}

			if(isset($_POST['contentLocation'])) {
				$this->settings['contentLocation'] = $_POST['contentLocation'];
			}

			if(isset($_POST['contentImage'])) {
				$this->settings['contentImage'] = $_POST['contentImage'];
			}

			if(isset($_POST['contentPlayer'])) {
				$this->settings['contentPlayer'] = $_POST['contentPlayer'];
			}

			if(isset($_POST['contentDownload'])) {
				$this->settings['contentDownload'] = $_POST['contentDownload'];
			}

			if(isset($_POST['contentDownloadText'])) {
				$this->settings['contentDownloadText'] = $_POST['contentDownloadText'];
			}

			if(isset($_POST['contentDownloadStats'])) {
				$this->settings['contentDownloadStats'] = $_POST['contentDownloadStats'];
			}

			if(isset($_POST['contentDuration'])) {
				$this->settings['contentDuration'] = $_POST['contentDuration'];
			}

			if(isset($_POST['enableFooter'])) {
				$this->settings['enableFooter'] = true;
			} else {
				$this->settings['enableFooter'] = false;
			}

			delete_option('podPress_config');
			podPress_add_option('podPress_config', $this->settings);


			if(isset($_POST['cleanupOldMetaKeys'])) {
				$sql = "DELETE FROM ".$wpdb->prefix."postmeta WHERE meta_key IN('podPress_podcastStandardAudio',
				                                                                 'podPress_podcastStandardAudioSize',
				                                                                 'podPress_podcastStandardAudioDuration',
				                                                                 'podPress_podcastEnhancedAudio',
				                                                                 'podPress_podcastEnhancedAudioSize',
				                                                                 'podPress_podcastEnhancedAudioDuration',
				                                                                 'podPress_podcastVideo',
				                                                                 'podPress_podcastVideoSize',
				                                                                 'podPress_podcastVideoDuration',
				                                                                 'podPress_podcastVideoDimension',
				                                                                 'podPress_webVideo',
				                                                                 'podPress_webVideoSize',
				                                                                 'podPress_webVideoDuration',
				                                                                 'podPress_webVideoDimension',
				                                                                 'podPress_podcastEbook',
				                                                                 'podPress_podcastEbookSize',
				                                                                 'itunes:duration',
				                                                                 'enclosure',
				                                                                 'enclosure_hold'
																																			 )";
				$wpdb->query($sql);
			}
			$location = get_option('siteurl') . '/wp-admin/admin.php?page=podpress/podpress_general.php&updated=true';
			header('Location: '.$location);
			exit;
		}
	}
