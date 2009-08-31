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

		function settings_feed_edit() {
			podPress_isAuthorized();
			if (isset($_GET['updated']) && $_GET['updated'] == 'true') {
				echo '<div id="message" class="updated fade"><p>'. __('Settings Saved', 'podpress').'</p></div>';
			}
			echo '<div class="wrap">'."\n";
			echo '	<h2>'.__('Podcast Settings', 'podpress').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.mightyseek.com/podpress/#download" target="_new"><img src="http://www.mightyseek.com/podpress_downloads/versioncheck.php?current='.PODPRESS_VERSION.'" alt="'.__('Checking for updates... Failed.', 'podpress').'" border="0" /></a></h2>'."\n";
			echo '	<form method="post">'."\n";

			podPress_DirectoriesPreview('feed_edit');

			echo '	<fieldset class="options">'."\n";
			echo '		<legend>'.__('Podcast Feed Options', 'podpress').'</legend>'."\n";
			/*
			echo '		<p class="submit"> '."\n";
			echo '		<input type="submit" name="Submit" value="'.__('Update Options', 'podpress').' &raquo;" /> '."\n";
			echo '		</p> '."\n";
			*/
			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform" >'."\n";
			echo '			<tr>'."\n";
			echo '				<td width="50%"><h3>'.__('iTunes Settings', 'podpress').'</h3></td>'."\n";
			echo '				<td width="50%"><h3>'.__('Standard Settings', 'podpress').'</h3></td>'."\n";
			echo '			</tr>'."\n";
			echo '			<tr>'."\n";
			echo '				<td width="100%" colspan="2"><a href="http://www.feedvalidator.org/check.cgi?url='.urlencode(get_settings('siteurl').'?feed=podcast').'"></a></td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesFeedID"><strong>'.__('iTunes:FeedID', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input name="iTunes[FeedID]" id="iTunesFeedID" type="text" value="'.htmlentities($this->settings['iTunes']['FeedID'], ENT_QUOTES, get_settings('blog_charset')).'" size="10" />';
			echo '					<input type="button" name="Ping_iTunes_update" value="Ping iTunes Update" onclick="javascript: if(document.getElementById(\'iTunesFeedID\').value != \'\') { window.open(\'https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=\'+document.getElementById(\'iTunesFeedID\').value); }"/>'."\n";
			if(1==2 && !empty($this->settings['iTunes']['FeedID'])) {
				echo '				<font border="1">';
				echo '				http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id='.$this->settings['iTunes']['FeedID'];
				echo '				</font>';
			}
			echo '				</td>'."\n";

			echo '				<td width="50%">';
			echo '					<label for="podcastFeedURL"><strong>'.__('Podcast Feed URL', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="podcastFeedURL" name="podcastFeedURL" size="40" value="'.htmlentities(stripslashes($this->settings['podcastFeedURL']), ENT_QUOTES, get_settings('blog_charset')).'" onchange="podPress_updateFeedSettings();" /><br />'.__('The RSS Feed URL to your podcast.', 'podpress');
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesNewFeedURL"><strong>'.__('iTunes:New-Feed-Url', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesNewFeedURLHelp\');">(?)</a>';
			echo '					<br/>';
			echo '					<select name="iTunes[new-feed-url]" id="iTunesNewFeedURL">'."\n";
			echo '						<option value="Disable" '; if($this->settings['iTunes']['new-feed-url'] != 'Enable') { echo 'selected="selected"'; } echo '>'.__('Disable', 'podpress').'</option>'."\n";
			echo '						<option value="Enable" '; if($this->settings['iTunes']['new-feed-url'] == 'Enable') { echo 'selected="selected"'; } echo '>'.__('Enable', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('Enabling will set the tag to the "Podcast Feed URL".  <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesNewFeedURLHelp\');"><font color="red">(Warning)</font></a>', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<input type="button" value="Validate your Feed" onclick="javascript: if(document.getElementById(\'podcastFeedURL\').value != \'\') { window.open(\'http://www.feedvalidator.org/check.cgi?url=\'+document.getElementById(\'podcastFeedURL\').value); }"/>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr id="iTunesNewFeedURLHelp" style="display: none;">'."\n";
			echo '				<td width="50%">';
			echo '					'.__('Setting this will tell the iTunes Podcast directory and all your iTunes listeners to use the the URL from the "Podcast Feed URL" setting. ', 'podpress')."\n";
			echo '					'.__('Be careful to only use this if you understand that making a mistake with this setting could cause loss of listeners and headaches with your listing in the iTunes Directory', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">&nbsp;</td>'."\n";
			echo '			</tr> '."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">&nbsp;</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="blogname"><strong>'.__('Blog/Podcast title', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="blogname" name="blogname" size="40" value="'.htmlentities(get_option('blogname'), ENT_QUOTES, get_settings('blog_charset')).'" onchange="podPress_updateFeedSettings();" /><br />'.__('Used for both Blog and Podcast.', 'podpress');
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesSummary"><strong>'.__('iTunes:Summary', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<textarea id="iTunesSummary" name="iTunes[summary]" rows="4" cols="40" onchange="podPress_updateFeedSettings();">'.htmlentities($this->settings['iTunes']['summary'], ENT_QUOTES, get_settings('blog_charset')).'</textarea>';
			echo '					<br />'.__('Used as iTunes description.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="blogdescription"><strong>'.__('Blog Description', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<textarea id="blogdescription" name="blogdescription" rows="4" cols="40" onchange="podPress_updateFeedSettings();">'.htmlentities(stripslashes(get_option('blogdescription')), ENT_QUOTES, get_settings('blog_charset')).'</textarea>';
			echo '					<br/>'.__('Standard feed description.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">'."\n";
			echo '					<label for="iTunesImage"><strong>'.__('iTunes:Image', 'podpress').' (300*300 pixels)</strong></label>'."\n";
			echo '					<br/>';
			echo '					<input id="iTunesImage" type="text" name="iTunes[image]" value="'.$this->settings['iTunes']['image'].'" size="40" onchange="podPress_updateFeedSettings();"/>'."\n";
			echo '					<br />';
			echo '					<img id="iTunesImagePreview" width="300" height="300" alt="Podcast Image - Big" src="" />'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">'."\n";
			echo '					<label for="rss_image"><strong>'.__('Blog/RSS Image', 'podpress').' (144*144 pixels)</strong></label>'."\n";
			echo '					<br/>';
			echo '					<input id="rss_image" type="text" name="rss_image" value="'.get_option('rss_image').'" size="40" onchange="podPress_updateFeedSettings();"/>'."\n";
			echo '					<br />';
			echo '					<img id="rss_imagePreview" width="144" height="144" alt="Podcast Image - Small" src="" />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesAuthor"><strong>'.__('iTunes:Author/Owner', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input name="iTunes[author]" type="text" id="iTunesAuthor" value="'.htmlentities($this->settings['iTunes']['author'], ENT_QUOTES, get_settings('blog_charset')).'" size="40" onchange="podPress_updateFeedSettings();"/>';
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="admin_email"><strong>'.__('Owner E-mail address', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input name="admin_email" type="text" id="admin_email" value="'.htmlentities(stripslashes(get_option('admin_email')), ENT_QUOTES, get_settings('blog_charset')).'" size="40" />';
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesSubtitle"><strong>'.__('iTunes:Subtitle', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<textarea name="iTunes[subtitle]" id="iTunesSubtitle" rows="4" cols="40">'.htmlentities($this->settings['iTunes']['subtitle'], ENT_QUOTES, get_settings('blog_charset')).'</textarea>';
			echo '					<br/>'.__('Used as default Podcast Episode Title', 'podpress').' (255 '.__('characters', 'podpress').')'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="rss_language"><strong>'.__('Language', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select id="rss_language" name="rss_language" onchange="podPress_updateFeedSettings();">'."\n";
			echo '						<option value="#">'.__('Select Language', 'podpress').'</option>'."\n";
			podPress_itunesLanguageOptions(get_option('rss_language'));
			echo '					</select>'."\n";
			echo '					<br/>'.__('Used as default Podcast Episode Title', 'podpress').' (255 '.__('characters', 'podpress').')'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesKeywords"><strong>'.__('iTunes:Keywords', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<textarea name="iTunes[keywords]" id="iTunesKeywords" rows="4" cols="40">'.htmlentities($this->settings['iTunes']['keywords'], ENT_QUOTES, get_settings('blog_charset')).'</textarea>';
			echo '					<br/>('.__('Comma seperated list', 'podpress').', '.__('max 8', 'podpress').')';
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="rss_showlinks"><strong>'.__('Show Download Links in RSS Encoded Content', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select name="rss_showlinks" id="rss_showlinks">'."\n";
			echo '						<option value="yes" '; if($this->settings['rss_showlinks'] == 'yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '						<option value="no" '; if($this->settings['rss_showlinks'] != 'yes') { echo 'selected="selected"'; }  echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('Yes will put download links in the RSS encoded content. That means users can download from any site displaying the link.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td>';
			echo '					<label for="iTunesCategory_0"><strong>'.__('iTunes:Categories', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select id="iTunesCategory_0" name="iTunes[category][0]" onchange="podPress_updateFeedSettings();">'."\n";
			echo '						<option value="#">'.__('Select Primary', 'podpress').'</option>'."\n";
			podPress_itunesCategoryOptions(htmlentities($this->settings['iTunes']['category'][0]), ENT_QUOTES, get_settings('blog_charset'));
			echo '					</select>'."\n";
			echo '					<select name="iTunes[category][1]">'."\n";
			echo '						<option value="#">'.__('Select Second', 'podpress').'</option>'."\n";
			podPress_itunesCategoryOptions(htmlentities($this->settings['iTunes']['category'][1]), ENT_QUOTES, get_settings('blog_charset'));
			echo '					</select>'."\n";
			echo '					<select name="iTunes[category][2]">'."\n";
			echo '						<option value="#">'.__('Select Third', 'podpress').'</option>'."\n";
			podPress_itunesCategoryOptions(htmlentities($this->settings['iTunes']['category'][2]), ENT_QUOTES, get_settings('blog_charset'));
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">'."\n";
			echo '					<label for="rss_category"><strong>'.__('RSS Category', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input name="rss_category" type="text" id="rss_category" value="'.htmlentities($this->settings['rss_category'], ENT_QUOTES, get_settings('blog_charset')).'" size="45%" />'."\n";
			echo '					<br />Anything you want. This is for everyone except iTunes.'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesExplicit"><strong>'.__('iTunes:Explicit', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select name="iTunes[explicit]" id="iTunesExplicit">'."\n";
			echo '						<option value="No" '; if($this->settings['iTunes']['explicit'] == 'No') { echo 'selected="selected"'; } echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '						<option value="Yes" '; if($this->settings['iTunes']['explicit'] == 'Yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '						<option value="Clean" '; if($this->settings['iTunes']['explicit'] == 'Clean') { echo 'selected="selected"'; } echo '>'.__('Clean', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('Sets the parental advisory graphic in name column.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">'."\n";
			echo '					<label for="rss_copyright"><strong>'.__('RSS Copyright', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input name="rss_copyright" type="text" id="rss_copyright" value="'.htmlentities($this->settings['rss_copyright'], ENT_QUOTES, get_settings('blog_charset')).'" size="25%" />'."\n";
			echo '					<br />The copyright dates. For example: 2006-2007'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			$data['rss_ttl'] = get_option('rss_ttl');
			if(!empty($data['rss_ttl']) && $data['rss_ttl'] < 1440) {
				$data['rss_ttl'] = 1440;
			}

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="rss_ttl"><strong>'.__('iTunes:TTL', 'podpress').' ('.__('time-to-live', 'podpress').')</strong></label>';
			echo '					<br/>';
			echo '					<input name="rss_ttl" id="rss_ttl" type="text" value="'; if($data['rss_ttl']) { echo $data['rss_ttl']; } else { echo '1440'; } echo '" size="4" />';
			echo '					<br/>'.__('Mins. Minimum is 24hrs which is 1440 mins.', 'podpress')."\n";
			echo '				</td>'."\n";

			echo '				<td width="50%">';
			echo '					<label for="posts_per_rss"><strong>'.__('Show the most recent', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="posts_per_rss" name="posts_per_rss" size="3" value="'.htmlentities(get_option('posts_per_rss'), ENT_QUOTES, get_settings('blog_charset')).'" /> '.__('posts', 'podpress');
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesBlock"><strong>'.__('iTunes:Block', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select name="iTunes[block]" id="iTunesBlock">'."\n";
			echo '						<option value="No" '; if($this->settings['iTunes']['block'] != 'Yes') { echo 'selected="selected"'; } echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '						<option value="Yes" '; if($this->settings['iTunes']['block'] == 'Yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('Use this if you are no longer creating a podcast and you want it removed from iTunes.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="blog_charset"><strong>'.__('Encoding for pages and feeds', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="blog_charset" name="blog_charset" size="20" value="'.htmlentities(get_option('blog_charset'), ENT_QUOTES, get_settings('blog_charset')).'" /><br />'.__('The character encoding you write your blog in', 'podpress').'('.__('UTF-8 is', 'podpress').'<a href="http://developer.apple.com/documentation/macos8/TextIntlSvcs/TextEncodingConversionManager/TEC1.5/TEC.b0.html" target="_new">'.__('recommended', 'podpress').'</a>)';
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">&nbsp;</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="protectFeed"><strong>'.__('Aggressively Protect the Feed', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select name="protectFeed" id="protectFeed">'."\n";
			echo '						<option value="No" '; if($this->settings['protectFeed'] != 'Yes') { echo 'selected="selected"'; } echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '						<option value="Yes" '; if($this->settings['protectFeed'] == 'Yes') { echo 'selected="selected"'; } echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('This will strip out any invalid characters, as well as images and links from your post content in the feeds. Only works with UTF-8 feeds.', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '		</table>'."\n";
			echo '		<p class="submit"> '."\n";
			echo '		<input type="submit" name="Submit" value="'.__('Update Options', 'podpress').' &raquo;" /> '."\n";
			echo '		</p> '."\n";
			echo '	</fieldset>'."\n";

			echo '<script type="text/javascript">'." podPress_updateFeedSettings();</script>";

			echo '	<input type="hidden" name="podPress_submitted" value="feed" />'."\n";
			echo '	</form> '."\n";
			echo '</div>'."\n";

			//end of settings_feed_edit function
		}

		function settings_feed_save() {
			if(function_exists('wp_cache_flush')) {
				wp_cache_flush();
			}
			if(isset($_POST['iTunes'])) {
				$iTunesSettings = $_POST['iTunes'];
				$iTunesSettings['category'] = array();

				if(is_array($_POST['iTunes']['category'])) {
					foreach ($_POST['iTunes']['category'] as $value) {
						if($value != '#') {
							$iTunesSettings['category'][] = $value;
						}
					}
				}
				$this->settings['iTunes'] = $iTunesSettings;
			}
			if(isset($_POST['blogname'])) { podPress_update_option('blogname', stripslashes($_POST['blogname'])); }
			if(isset($_POST['blogdescription'])) { podPress_update_option('blogdescription', $_POST['blogdescription']); }
			if(isset($_POST['admin_email'])) { podPress_update_option('admin_email', $_POST['admin_email']); }

			if(isset($_POST['blog_charset'])) { podPress_update_option('blog_charset', stripslashes($_POST['blog_charset'])); }
			if(isset($_POST['posts_per_rss'])) { podPress_update_option('posts_per_rss', stripslashes($_POST['posts_per_rss'])); }

			if(isset($_POST['rss_language'])) { podPress_update_option('rss_language', $_POST['rss_language']);	}
			if(isset($_POST['rss_ttl'])) { podPress_update_option('rss_ttl', $_POST['rss_ttl']);	}
			if(isset($_POST['rss_image'])) { podPress_update_option('rss_image', $_POST['rss_image']);	}

			if(isset($_POST['rss_category'])) {
				$this->settings['rss_category'] = $_POST['rss_category'];
			}
			if(isset($_POST['rss_copyright'])) {
				$this->settings['rss_copyright'] = $_POST['rss_copyright'];
			}
			if(isset($_POST['rss_showlinks'])) {
				$this->settings['rss_showlinks'] = $_POST['rss_showlinks'];
			}
			if(isset($_POST['podcastFeedURL'])) {
				$this->settings['podcastFeedURL'] = $_POST['podcastFeedURL'];
			}
			if(isset($_POST['protectFeed'])) {
				$this->settings['protectFeed'] = $_POST['protectFeed'];
			}

			podPress_update_option('podPress_config', $this->settings);

			$location = get_option('siteurl') . '/wp-admin/admin.php?page=podpress/podpress_feed.php&updated=true';
			header('Location: '.$location);
			exit;
		}
	}
