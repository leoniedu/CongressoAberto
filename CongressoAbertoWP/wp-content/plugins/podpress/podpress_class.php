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
	class podPress_class {
		var $settings = array();

		// Global hardcoded settings
		var $podcastTag_regexp   = "/\[podcast:([^]]+)]/";
		var $podcastTag          = '[display_podcast]';
		var $podtrac_url         = 'http://www.podtrac.com/pts/redirect.mp3?';
		var $blubrry_url         = 'http://media.blubrry.com/';
		var $requiredAdminRights = 'level_7';
		var $realm               = 'Premium Subscribers Content';
		var $justposted          = false;
		var $tempFileSystemPath  = '';
		var $tempFileURLPath     = '';
		var $tempContentAddedTo  = array();
		var $podangoAPI;

		/*************************************************************/
		/* Load up the plugin values and get ready to action         */
		/*************************************************************/

		function podPress_class() {
			//$this->feed_getCategory();
			$this->tempFileSystemPath = ABSPATH.get_option('upload_path').'/podpress_temp';
			$this->tempFileURLPath = get_settings('siteurl').'/'.get_option('upload_path').'/podpress_temp';
			// load up podPress general config
			$this->settings = podPress_get_option('podPress_config');
			// make sure things look current, if not run the settings checker
			if(!is_array($this->settings) || PODPRESS_VERSION > $this->settings['lastChecked']) {
				$this->checkSettings();
			}
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
		}

		/*************************************************************/
		/* Handle all the default values for a new install of plugin */
		/*************************************************************/
		function activate() {
			GLOBAL $wpdb;
			$current = get_option('podPress_version');
			if(!$current) {
				$current = 0;
			}

			if(function_exists('get_role')) {
				$ps_role = get_role('premium_subscriber');
				if(!$ps_role) {
					add_role('premium_subscriber', 'Premium Subscriber', $caps);
					$ps_role = get_role('premium_subscriber');
				}
				$ps_role = get_role('premium_subscriber');
				if(!$ps_role->has_cap('premium_content')) {
					$ps_role->add_cap('premium_content');
				}
				if(!$ps_role->has_cap('read')) {
					$ps_role->add_cap('read');
				}
				$role = get_role('administrator');
				if(!$role->has_cap('premium_content')) {
					$role->add_cap('premium_content');
				}
			}

			// Create stats table
			$create_table = "CREATE TABLE ".$wpdb->prefix."podpress_statcounts (".
			                "postID int(11) NOT NULL default '0',".
			                "media varchar(255) NOT NULL,".
			                "total int(11) default '1',".
			                "feed int(11) default '0',".
			                "web int(11) default '0',".
			                "play int(11) default '0',".
			                "PRIMARY KEY (media)) TYPE=MyISAM;";
			podPress_maybe_create_table($wpdb->prefix."podpress_statcounts", $create_table);

			// Create stats table
			$create_table = "CREATE TABLE ".$wpdb->prefix."podpress_stats (".
			                "id int(11) unsigned NOT NULL auto_increment,".
			                "postID int(11) NOT NULL default '0',".
			                "media varchar(255) NOT NULL default '',".
			                "method varchar(50) NOT NULL default '',".
			                "remote_ip varchar(15) NOT NULL default '',".
			                "country varchar(50) NOT NULL default '',".
			                "language VARCHAR(5) NOT NULL default '',".
			                "domain varchar(255) NOT NULL default '',".
			                "referer varchar(255) NOT NULL default '',".
			                "resource varchar(255) NOT NULL default '',".
			                "user_agent varchar(255) NOT NULL default '',".
			                "platform varchar(50) NOT NULL default '',".
			                "browser varchar(50) NOT NULL default '',".
			                "version varchar(15) NOT NULL default '',".
			                "dt int(10) unsigned NOT NULL default '0',".
			                "UNIQUE KEY id (id)) TYPE=MyISAM;";
			podPress_maybe_create_table($wpdb->prefix."podpress_stats", $create_table);

			if(function_exists('wp_cache_flush')) {
				wp_cache_flush();
			}

			if($current == 0) {
				$current = PODPRESS_VERSION;
				add_option('podPress_version', $current);
			}

			$this->checkSettings();
		}

		function checkSettings() {
			GLOBAL $wp_object_cache, $wp_rewrite;
			if(!is_array($this->settings)) {
				$this->settings = podPress_get_option('podPress_config');
				if(!is_array($this->settings)) {
					$this->settings = array();
				}
			}

			$this->settings['lastChecked'] = PODPRESS_VERSION;

			// Make sure some standard values are set.
			$x = get_option('rss_language');
			if(!$x || empty($x))
			{
				add_option('rss_language', 'en_us');
			}

			$x = get_option('rss_image');
			if(!$x || empty($x))
			{
				podPress_update_option('rss_image', podPress_url().'images/powered_by_podpress.jpg');
			}


			if($this->settings['compatibilityChecks']['themeTested'] !== true) {
				$this->settings['compatibilityChecks']['themeTested'] = false;
			}

			if($this->settings['compatibilityChecks']['wp_head'] !== true) {
				$this->settings['compatibilityChecks']['wp_head'] = false;
			}
			if($this->settings['compatibilityChecks']['wp_footer'] !== true) {
				$this->settings['compatibilityChecks']['wp_footer'] = false;
			}

			if(!is_bool($this->settings['enableStats'])) {
				if($this->settings['enableStats']== 'true') {
					$this->settings['enableStats'] = true;
				} else {
					$this->settings['enableStats'] = false;
				}
			}

			if(!is_bool($this->settings['enableStats'])) {
				if($this->settings['enableStats']== 'true') {
					$this->settings['enableStats'] = true;
				} else {
					$this->settings['enableStats'] = false;
				}
			}

			if(!$this->settings['statMethod'] || empty($this->settings['statMethod']) || $this->settings['statMethod'] == 'htaccess')
			{
				$this->settings['statMethod'] = 'permalinks';
			}

			if(!$this->settings['statLogging'] || empty($this->settings['statLogging']))
			{
				$this->settings['statLogging'] = 'Counts';
			}

			if(empty($this->settings['enable3rdPartyStats'])) {
				$this->settings['enable3rdPartyStats'] = 'No';
			}

			if(!is_bool($this->settings['enableBlubrryStats'])) {
				if($this->settings['enableBlubrryStats']== 'true') {
					$this->settings['enableBlubrryStats'] = true;
				} else {
					$this->settings['enableBlubrryStats'] = false;
				}
			}

			if(!$this->settings['rss_copyright'] || empty($this->settings['rss_copyright']))
			{
				$this->settings['rss_copyright'] = '2006-2007';
			}

			if(podPress_WPVersionCheck('2.0.0')) {
				if(!is_bool($this->settings['enablePremiumContent'])) {
					if($this->settings['enablePremiumContent']== 'true') {
						$this->settings['enablePremiumContent'] = true;
					} else {
						$this->settings['enablePremiumContent'] = false;
					}
				}
			} else {
				$this->settings['enablePremiumContent'] = false;
			}

			if(empty($this->settings['premiumMethod'])) {
				$this->settings['premiumMethod'] = 'Digest';
			}
			if(!defined('PODPRESS_PREMIUM_METHOD')) {
				define('PODPRESS_PREMIUM_METHOD', $this->settings['premiumMethod']);
			}

			if(!is_bool($this->settings['enableTorrentCasting'])) {
				if($this->settings['enableTorrentCasting']== 'true') {
					$this->settings['enableTorrentCasting'] = true;
				} else {
					$this->settings['enableTorrentCasting'] = false;
				}
			}

			if(empty($this->settings['podcastFeedURL'])) {
				if(podPress_WPVersionCheck('2.1')) {
					$this->settings['podcastFeedURL'] = get_settings('siteurl').'/?feed=podcast';
				} else {
					$this->settings['podcastFeedURL'] = get_settings('siteurl').'/?feed=rss2';
				}
			}

			if(empty($this->settings['mediaWebPath'])) {
				$this->settings['mediaWebPath'] = get_settings('siteurl').'/wp-content/uploads';
			}

			unset($this->settings['autoDetectedMediaFilePath']);
			if(!file_exists($this->settings['mediaFilePath'])) {
				$this->settings['autoDetectedMediaFilePath'] = str_replace(get_settings('siteurl'), '', $this->settings['mediaWebPath']);
				$this->settings['autoDetectedMediaFilePath'] = ABSPATH.$this->settings['autoDetectedMediaFilePath'];
				$this->settings['autoDetectedMediaFilePath'] = str_replace('\\\\', '\\', $this->settings['autoDetectedMediaFilePath']);
				$this->settings['autoDetectedMediaFilePath'] = str_replace('//', '/', $this->settings['autoDetectedMediaFilePath']);
				$this->settings['autoDetectedMediaFilePath'] = str_replace('//', '/', $this->settings['autoDetectedMediaFilePath']);
				if(!file_exists($this->settings['autoDetectedMediaFilePath'])) {
					$this->settings['autoDetectedMediaFilePath'] .= ' (Auto Detection Failed.)';
				}
			}

			if(empty($this->settings['maxMediaFiles']) || $this->settings['maxMediaFiles'] < 1) {
				$this->settings['maxMediaFiles'] = 5;
			}

			if(!$this->settings['contentBeforeMore'] || empty($this->settings['contentBeforeMore']))
			{
				$this->settings['contentBeforeMore'] = 'yes';
			}

			if(!$this->settings['contentLocation'] || empty($this->settings['contentLocation']))
			{
				$this->settings['contentLocation'] = 'end';
			}

			if(!$this->settings['contentImage'] || empty($this->settings['contentImage']))
			{
				$this->settings['contentImage'] = 'button';
			}

			if(!$this->settings['contentPlayer'] || empty($this->settings['contentPlayer']))
			{
				$this->settings['contentPlayer'] = 'both';
			}

			if(empty($this->settings['videoPreviewImage'])) {
				$this->settings['videoPreviewImage'] = podPress_url().'images/vpreview_center.png';
			}

			if(!is_bool($this->settings['disableVideoPreview'])) {
				if($this->settings['disableVideoPreview']== 'true') {
					$this->settings['disableVideoPreview'] = true;
				} else {
					$this->settings['disableVideoPreview'] = false;
				}
			}

			if(!$this->settings['contentDownload'] || empty($this->settings['contentDownload']))
			{
				$this->settings['contentDownload'] = 'enabled';
			}

			if(!$this->settings['contentDownloadText'] || empty($this->settings['contentDownloadText']))
			{
				$this->settings['contentDownloadText'] = 'enabled';
			}

			if(!$this->settings['contentDownloadStats'] || empty($this->settings['contentDownloadStats']))
			{
				$this->settings['contentDownloadStats'] = 'enabled';
			}

			if(!$this->settings['contentDuration'] || empty($this->settings['contentDuration']))
			{
				$this->settings['contentDuration'] = 'enabled';
			}

			if(!is_bool($this->settings['contentAutoDisplayPlayer'])) {
				if($this->settings['contentAutoDisplayPlayer'] == 'false') {
					$this->settings['contentAutoDisplayPlayer'] = false;
				} else {
					$this->settings['contentAutoDisplayPlayer'] = true;
				}
			}

			if(!is_bool($this->settings['enableFooter'])) {
				if($this->settings['enableFooter']== 'false') {
					$this->settings['enableFooter'] = false;
				} else {
					$this->settings['enableFooter'] = true;
				}
			}

			if($this->settings['player']['bg'] == '') {
				$this->resetPlayerSettings();
			}

			if(empty($this->settings['iTunes']['summary'])) {
				$this->settings['iTunes']['summary'] = stripslashes(get_option('blogdescription'));
			} else {
				$this->settings['iTunes']['summary'] = stripslashes($this->settings['iTunes']['summary']);
			}
			$this->settings['iTunes']['keywords'] = stripslashes($this->settings['iTunes']['keywords']);
			$this->settings['iTunes']['subtitle'] = stripslashes($this->settings['iTunes']['subtitle']);
			$this->settings['iTunes']['author'] = stripslashes($this->settings['iTunes']['author']);

			$this->settings['iTunes']['FeedID'] = stripslashes($this->settings['iTunes']['FeedID']);
			$this->settings['iTunes']['FeedID'] = str_replace(' ', '', $this->settings['iTunes']['FeedID']);
			if(!empty($this->settings['iTunes']['FeedID']) && !is_numeric($this->settings['iTunes']['FeedID'])) {
				$this->settings['iTunes']['FeedID'] = settype($this->settings['iTunes']['FeedID'], 'double');
			}

			if(empty($this->settings['iTunes']['explicit'])) {
				$this->settings['iTunes']['explicit'] = 'No';
			}

			if(empty($this->settings['iTunes']['image'])) {
				$x = get_option('rss_image');
				if(isset($x) && $x != podPress_url().'images/powered_by_podpress.jpg') {
					$this->settings['iTunes']['image'] = $x;
				} else {
					$this->settings['iTunes']['image'] = podPress_url().'images/powered_by_podpress_large.jpg';
				}
			}

			if(empty($this->settings['iTunes']['new-feed-url'])) {
				$this->settings['iTunes']['new-feed-url'] = 'Disable';
			}
			podPress_update_option('podPress_config', $this->settings);
			if(is_object($wp_rewrite)
				&& is_array($wp_object_cache) 
				&& is_array($wp_object_cache['cache']) 
				&& is_array($wp_object_cache['cache']['options']) 
				&& is_array($wp_object_cache['cache']['options']['alloptions']) 
				&& is_array($wp_object_cache['cache']['options']['alloptions']['rewrite_rules'])
				&& !strpos($wp_object_cache['cache']['options']['alloptions']['rewrite_rules'], 'playlist.xspf')
				) {
					$wp_rewrite->flush_rules();
			}
		}

		function deactivate() {
			// at the moment I have nothing I would want to clean up
		}

		function iTunesLink() {
			return '<a href="http://phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id='.$this->settings['iTunes']['FeedID'].'"><img src="'.podPress_url().'images/itunes.png" border="0" alt="View in iTunes"/></a>';
		}

		function resetPlayerSettings() {
			$result['bg'] = '#F8F8F8';
			$result['leftbg'] = '#F8F8F8';
			$result['text'] = '#666666';
			$result['leftbg'] = '#EEEEEE';
			$result['lefticon'] = '#666666';
			$result['rightbg'] = '#CCCCCC';
			$result['rightbghover'] = '#999999';
			$result['righticon'] = '#666666';
			$result['righticonhover'] = '#FFFFFF';
			$result['slider'] = '#666666';
			$result['track'] = '#FFFFFF';
			$result['loader'] = '#9FFFB8';
			$result['border'] = '#666666';
			$result['listen_wrapper'] = 'off';
			$this->settings['player'] = $result;
			return $result;
		}

		function convertPodcastFileNameToValidWebPath($filename){
			if(strpos(substr($filename, 0, 10), '://')) {
				$url = $filename;
			} else {
				if(substr($filename, 0,1) == '/') {
					$baseurl = strtolower(strtok($_SERVER['SERVER_PROTOCOL'], '/')).'://'.$_SERVER['HTTP_HOST'].$this->settings['mediaWebPath'];
				} elseif(strpos(substr($this->settings['mediaWebPath'], 0, 10), '://')) {
					$baseurl = $this->settings['mediaWebPath'];
				} else {
					$baseurl = get_settings('siteurl').$this->settings['mediaWebPath'];
				}

				if(substr($filename, -1, 1) != '/')
				{
					$baseurl .= '/';
				}
				$url = $baseurl.$filename;
			}
			return $url;
		}

		function convertPodcastFileNameToWebPath($postID, $mediaNum, $filename = '', $method = false){
			$url = $this->convertPodcastFileNameToValidWebPath($filename);
			if($method != false) {
				if($this->settings['enableStats']) {
					$filename_part = podPress_getFileName($url);
					if($this->settings['statMethod'] == 'download.mp3') {
						$url = podPress_url().'download.mp3?'.$method.'='.$postID.'/'.$mediaNum.'/'.$filename_part;
					} else {
						$url = get_settings('siteurl').'/podpress_trac/'.$method.'/'.$postID.'/'.$mediaNum.'/'.$filename_part;
					}
				} elseif($this->settings['enable3rdPartyStats'] == 'Podtrac') {
					$url = str_replace(array('ftp://', 'http://', 'https://'), '', $url);
					$url = $this->podtrac_url.$url;
				} elseif($this->settings['enable3rdPartyStats'] == 'BluBrry' && !empty($this->settings['statBluBrryProgramKeyword'])) {
					$url = $this->blubrry_url.$this->settings['statBluBrryProgramKeyword'].'/'.$url;
				}
			}
			$url = str_replace(' ', '%20', $url);
			return $url;
		}

		function convertPodcastFileNameToSystemPath($filename = ''){
			if(!strpos(substr($filename, 0, 10), '://')) {
				$filename = $this->settings['mediaFilePath'].'/'.$filename;
				if(file_exists($filename))
				{
					return $filename;
				}
			}
			return false;
		}

		function checkWritableTempFileDir($showErrors = true) {
			/* check, if user-upload path is set */
			$this->uploadPath = ABSPATH.get_option('upload_path');
			if (file_exists($this->tempFileSystemPath)) {
				if(is_writable($this->tempFileSystemPath)) {
					return true;
				} else {
					if($showErrors) {
						echo '<p>'.__('Your uploads/podpress_temp directory is not writable. Please set permissions as needed, and make sure <a href="'.get_settings('siteurl').'/wp-admin/options-misc.php">configuration</a> is correct.', 'podpress').'<br />Currently set to:<code>'.get_option('upload_path')."/podpress_temp</code></p>\n";
					}
					return false;
				}
			} elseif (!file_exists($this->uploadPath)) {
				if($showErrors) {
					echo '<p>'.__('Your WordPress upload directory does not exist. Please create it and make sure <a href="'.get_settings('siteurl').'/wp-admin/options-misc.php">configuration</a> is correct.', 'podpress').'<br />Currently set to:<code>'.get_option('upload_path')."</code></p>\n";
				}
				return false;
			} elseif (!is_writable($this->uploadPath)) {
				if($showErrors) {
					echo '<p>'.__('Your WordPress upload directory is not writable. Please set permissions as needed, and make sure <a href="'.get_settings('siteurl').'/wp-admin/options-misc.php">configuration</a> is correct.', 'podpress').'<br />Currently set to:<code>'.get_option('upload_path')."</code></p>\n";
				}
				return false;
			} else {
				$mkdir = @mkdir($this->tempFileSystemPath);
				if (!$mkdir) {
					if($showErrors) {
						echo '<p>'.__('Could not create uploads/podpress_temp directory. Please set permission of the following directory to 755 or 777:', 'podpress').'<br /><code>'.get_option('upload_path')."/podpress_temp</code></p>\n";
					}
					return false;
				}
				return true;
			}
		}
		
		/*************************************************************/
		/* Load up the plugin values and get ready to action         */
		/*************************************************************/

		function addPostData($input, $forEdit = false) {

			$input->podPressMedia = podPress_get_post_meta($input->ID, 'podPressMedia', true);
			if(!is_array($input->podPressMedia)) {
				$x = maybe_unserialize($input->podPressMedia);
				if(is_array($x)) {
					$input->podPressMedia = $x;
				}
				if(!is_array($input->podPressMedia)) {
					$x = maybe_unserialize($input->podPressMedia, true);
					if(is_array($x)) {
						$input->podPressMedia = $x;
					}
				}
			}
			if(is_array($input->podPressMedia)) {
				reset($input->podPressMedia);
				while (list($key) = each($input->podPressMedia)) {
					if(!empty($input->podPressMedia[$key]['URI'])) {
						if($input->podPressMedia[$key]['premium_only'] == 'on' || $input->podPressMedia[$key]['premium_only'] == true) {
							$input->podPressMedia[$key]['content_level'] = 'premium_content';
						} elseif(!isset($input->podPressMedia[$key]['content_level'])) {
							$input->podPressMedia[$key]['content_level'] = 'free';
						}

						if(!isset($input->podPressMedia[$key]['type'])) {
							$input->podPressMedia[$key]['type'] = '';
						}
						settype($input->podPressMedia[$key]['size'], 'int');
						if(0 >= $input->podPressMedia[$key]['size']) {
							$filepath = $this->convertPodcastFileNameToSystemPath($input->podPressMedia[$key]['URI']);
							if($filepath) {
								$input->podPressMedia[$key]['size'] = filesize ($filepath);
							} else {
								$input->podPressMedia[$key]['size'] = 1;
							}
						}

						$input->podPressMedia[$key]['ext'] = podPress_getFileExt($input->podPressMedia[$key]['URI']);
						$input->podPressMedia[$key]['mimetype'] = podPress_mimetypes($input->podPressMedia[$key]['ext']);

						if(!$forEdit && $this->settings['enablePremiumContent'] && $input->podPressMedia[$key]['content_level'] != 'free' && @$GLOBALS['current_user']->allcaps[$input->podPressMedia[$key]['content_level']] != 1) {
							$input->podPressMedia[$key]['authorized'] = false;
							$input->podPressMedia[$key]['URI'] = '';
							$input->podPressMedia[$key]['URI_torrent'] = '';
						} else {
							$input->podPressMedia[$key]['authorized'] = true;
						}
					}
				}
			}

			$input->podPressPostSpecific = podPress_get_post_meta($input->ID, 'podPressPostSpecific', true);
			if(!is_array($input->podPressPostSpecific)) {
				$input->podPressPostSpecific = array();
			}

			if(empty($input->podPressPostSpecific['itunes:subtitle'])) {
				$input->podPressPostSpecific['itunes:subtitle'] = '##PostExcerpt##';
			}
			if(empty($input->podPressPostSpecific['itunes:summary'])) {
				$input->podPressPostSpecific['itunes:summary'] = '##PostExcerpt##';
			}
			if(empty($input->podPressPostSpecific['itunes:keywords'])) {
				$input->podPressPostSpecific['itunes:keywords'] = '##WordPressCats##';
			}
			if(empty($input->podPressPostSpecific['itunes:author'])) {
				$input->podPressPostSpecific['itunes:author'] = '##Global##';
			}
			if(empty($input->podPressPostSpecific['itunes:explicit'])) {
				$input->podPressPostSpecific['itunes:explicit'] = 'Default';
			}
			if(empty($input->podPressPostSpecific['itunes:block'])) {
				$input->podPressPostSpecific['itunes:block'] = 'Default';
			}

			return $input;
		}

		function the_posts($input) {
			GLOBAL $podPress_inAdmin;
			if(!$podPress_inAdmin && !$this->settings['compatibilityChecks']['themeTested']) {
				$this->settings['compatibilityChecks']['themeTested'] = true;
				podPress_update_option('podPress_config', $this->settings);
			}

			if(!is_array($input)) {
				return $input;
			}
			foreach($input as $key=>$value) {
				$input[$key] = $this->addPostData($value);
			}
			return $input;
		}

		function posts_join($input) {
			GLOBAL $wpdb;
			if(defined('PODPRESS_PODCASTSONLY')) {
				$input .= " JOIN ".$wpdb->prefix."postmeta ON ".$wpdb->prefix."posts.ID=".$wpdb->prefix."postmeta.post_id ";
			}
			return $input;
		}

		function posts_where($input) {
			GLOBAL $wpdb;
			if(defined('PODPRESS_PODCASTSONLY')) {
				$input .= "AND ".$wpdb->prefix."postmeta.meta_key='podPressMedia' ";
			}
			return $input;
		}

		function insert_the_excerpt($content = '') {
			GLOBAL $post;
			$this->tempContentAddedTo[$post->ID] = true;
			return $content;
		}

		function insert_the_excerptplayer($content = '') {
			GLOBAL $post;
			unset($this->tempContentAddedTo[$post->ID]);
			$content = $this->insert_content($content);
			unset($this->tempContentAddedTo[$post->ID]);
			return $content;
		}

		function insert_content($content = '') {
			GLOBAL $post, $podPressTemplateData, $podPressTemplateUnauthorizedData, $wpdb;
			
			
			if ( !empty($post->post_password) ) { // if there's a password
				if ( stripslashes($_COOKIE['wp-postpass_'.COOKIEHASH]) != $post->post_password ) {	// and it doesn't match the cookie
					return $content;
				}
			}
			
			if(isset($this->tempContentAddedTo[$post->ID])) {
				if(is_feed()) {
					return str_replace($this->podcastTag,'',$content);
				}
				return $content;
			} else {
				$this->tempContentAddedTo[$post->ID] = true;
			}

			if(is_feed()) {
				if($this->settings['protectFeed'] == 'Yes' && get_option('blog_charset') == 'UTF-8') {
					$content = podPress_feedSafeContent($content);
				}
				if($this->settings['rss_showlinks'] != 'yes') {
					return str_replace($this->podcastTag,'',$content);
				}
			}

			if(!is_array($post->podPressMedia)) {
				return str_replace($this->podcastTag,'',$content);
			}

			$hasLocationDefined = (bool)strstr($content, $this->podcastTag);
			if(!$hasLocationDefined) {
				if($this->settings['contentBeforeMore'] == 'no') {
					return $content;
				}
				if($this->settings['contentLocation'] == 'start') {
					$content = $this->podcastTag.$content;
				} else {
					$content .= $this->podcastTag;
				}
			} 

			$podPressRSSContent = '';
			$showmp3player = false;
			$showvideopreview = false;
			$showvideoplayer = false;
			$podPressTemplateData = array();

			$podPressTemplateData['showDownloadText'] = $this->settings['contentDownloadText'];
			$podPressTemplateData['showDownloadStats'] = $this->settings['contentDownloadStats'];
			$podPressTemplateData['showDuration'] = $this->settings['contentDuration'];
			$this->playerCount++;
			$podPressTemplateData['files'] = array();
			$podPressTemplateData['player'] = array();
			reset($post->podPressMedia);
			while (list($key) = each($post->podPressMedia)) {
				if(empty($post->podPressMedia[$key]['previewImage'])) {
					$post->podPressMedia[$key]['previewImage'] = $this->settings['videoPreviewImage'];
				}

				if($this->settings['disableVideoPreview']) {
					$post->podPressMedia[$key]['disablePreview'] = true;
				}

				if($post->podPressMedia[$key]['feedonly'] == 'on') {
					continue;
				}
				$post->podPressMedia[$key]['title'] = htmlentities(stripslashes($post->podPressMedia[$key]['title']), ENT_QUOTES, get_settings('blog_charset'));
				$post->podPressMedia[$key]['stats'] = false;
				if($this->settings['enableStats']) {
					$pos = strrpos($post->podPressMedia[$key]['URI'], '/');
					$len == strlen($post->podPressMedia[$key]['URI']);
					while(substr($post->podPressMedia[$key]['URI'], $pos, 1) == '/') {
						$pos++;
					}
					$filename = substr($post->podPressMedia[$key]['URI'], $pos);
					$sql = "SELECT * FROM ".$wpdb->prefix."podpress_statcounts WHERE media = '".$filename."'";
					$stats = $wpdb->get_results($sql);
					if($stats) {
						$post->podPressMedia[$key]['stats'] = array('feed'=>$stats[0]->feed, 'web'=>$stats[0]->web, 'play'=>$stats[0]->play, 'total'=>$stats[0]->total);
					}
				}
				$supportedMediaTypes = array('audio_mp3', 'audio_ogg', 'audio_m4a', 'audio_mp4', 'audio_m3u', 'video_mp4', 'video_m4v', 'video_mov', 'video_qt', 'video_avi', 'video_mpg', 'video_asf', 'video_wmv', 'video_wma', 'video_flv', 'video_swf', 'ebook_pdf', 'embed_youtube');
				if(!in_array($post->podPressMedia[$key]['type'], $supportedMediaTypes)) {
					$post->podPressMedia[$key]['type'] = 'misc_other';
				}
				// this loop is for the basics. After this the unauthorized content will stop
				if(empty($post->podPressMedia[$key]['title'])) {
					$post->podPressMedia[$key]['title'] = podPress_defaultTitles($post->podPressMedia[$key]['type']);
				}

				if($this->settings['contentImage'] != 'none') {
					$post->podPressMedia[$key]['image'] = $post->podPressMedia[$key]['type'].'_'.$this->settings['contentImage'].'.png';
				}

				if($post->podPressMedia[$key]['authorized']) {
					$post->podPressMedia[$key]['URI_orig'] = $post->podPressMedia[$key]['URI'];
					$post->podPressMedia[$key]['URI'] = $this->convertPodcastFileNameToWebPath($post->ID, $key, $post->podPressMedia[$key]['URI'], 'web');
					$post->podPressMedia[$key]['URI_Player'] = $this->convertPodcastFileNameToWebPath($post->ID, $key, $post->podPressMedia[$key]['URI'], 'play');
					if(!empty($post->podPressMedia[$key]['URI_torrent'])) {
						$post->podPressMedia[$key]['URI_torrent'] = $this->convertPodcastFileNameToWebPath($post->ID, $key, $post->podPressMedia[$key]['URI_torrent'], 'web');
					}

					if($this->settings['contentDownload'] == 'disabled') {
						$post->podPressMedia[$key]['enableDownload'] = false;
						$post->podPressMedia[$key]['enableTorrentDownload'] = false;
					} else {
						$post->podPressMedia[$key]['enableDownload'] = true;
						$podPressRSSContent .= '<a href="'.$post->podPressMedia[$key]['URI'].'">'.__('Download', 'podpress').' '.__($post->podPressMedia[$key]['title'], 'podpress').'</a><br/>';
						if($this->settings['enableTorrentCasting'] && !empty($post->podPressMedia[$key]['URI_torrent'])) {
							$post->podPressMedia[$key]['enableTorrentDownload'] = true;
						}
					}
					switch($this->settings['contentPlayer']) {
						case 'disabled':
							$post->podPressMedia[$key]['enablePlayer'] = false;
							$post->podPressMedia[$key]['enablePopup'] = false;
							break;
						case 'inline':
							$post->podPressMedia[$key]['enablePlayer'] = true;
							$post->podPressMedia[$key]['enablePopup'] = false;
							break;
						case 'popup':
							$post->podPressMedia[$key]['enablePlayer'] = false;
							$post->podPressMedia[$key]['enablePopup'] = true;
							break;
						case 'both':
							$post->podPressMedia[$key]['enablePlayer'] = true;
							$post->podPressMedia[$key]['enablePopup'] = true;
						default:
					}
					if($post->podPressMedia[$key]['enablePlayer']) {
						// This loop is to put together the player data.
						switch($post->podPressMedia[$key]['type']) {
							case 'audio_mp3':
								$post->podPressMedia[$key]['dimensionW'] = 300;
								$post->podPressMedia[$key]['dimensionH'] = 30;
								break;
							case 'audio_ogg':
								$post->podPressMedia[$key]['dimensionW'] = 300;
								$post->podPressMedia[$key]['dimensionH'] = 30;
								break;
							case 'audio_m4a':
							case 'audio_m4a':
							case 'audio_mp4':
							case 'video_m4v':
							case 'video_mp4':
							case 'video_mov':
							case 'video_qt':
							case 'video_avi':
							case 'video_mpg':
							case 'video_asf':
							case 'video_wma':
							case 'video_wmv':
							case 'video_flv':
							case 'video_swf':
								break;
							case 'embed_youtube':
								$x = parse_url($post->podPressMedia[$key]['URI_orig']);
								$x = explode('&', $x['query']);
								foreach($x as $v) {
									if(substr($v, 0, 2) == 'v=') {
										if(str_replace('/', '', $post->podPressMedia[$key]['previewImage']) == str_replace('/', '', $this->settings['videoPreviewImage'])) {
											$post->podPressMedia[$key]['previewImage'] = 'http://img.youtube.com/vi/'. substr($v, 2).'/default.jpg';
										}
										$post->podPressMedia[$key]['URI_Player'] = substr($v, 2).'.youtube';
										break;
									}
								}
								$post->podPressMedia[$key]['URI'] = $post->podPressMedia[$key]['URI_orig'];
								break;
							case 'audio_m3u':
								$post->podPressMedia[$key]['enableDownload'] = true;
							case 'ebook_pdf':
							default:
								$post->podPressMedia[$key]['enablePlayer'] = false;
								$post->podPressMedia[$key]['enablePopup'] = false;
						}
					}
				}
				if($post->podPressMedia[$key]['disablePlayer']) {
					$post->podPressMedia[$key]['enablePlayer'] = false;
					$post->podPressMedia[$key]['enablePopup'] = false;
				} 

				$podPressTemplateData['files'][] = $post->podPressMedia[$key];
				$post->podPressMedia[$key]['URI'] = $post->podPressMedia[$key]['URI_orig'];
				unset($post->podPressMedia[$key]['URI_orig']);
			}

			if(is_feed()) {
				return str_replace($this->podcastTag,'<br/>'.$podPressRSSContent,$content);
			}

			if(!$this->settings['compatibilityChecks']['wp_head']) {
				$podPressContent = '<code>podPress theme compatibility problem. Please check podPress->General Settings for more information.</code><br/>';
				$this->settings['compatibilityChecks']['wp_head'] = false;
				$this->settings['compatibilityChecks']['wp_footer'] = false;
				podPress_update_option('podPress_config', $this->settings);
			} else {
				/* The theme file needs to populate these */
				$podPressContent = podPress_webContent($podPressTemplateData);
			}
			return str_replace($this->podcastTag,$podPressContent,$content);
		}

		function xmlrpc_post_addMedia($input) {
			$postdata = $input['postdata'];
			$content_struct = $input['content_struct'];
			if(isset($content_struct['enclosure']) && !empty($content_struct['enclosure']['url'])) {
				$media[0]['URI'] = $content_struct['enclosure']['url'];
				$media[0]['authorized'] = true;
				if(!empty($content_struct['enclosure']['type'])) {
					$media[0]['type'] = $content_struct['enclosure']['type'];
				} else {
					$media[0]['type'] = podPress_mimetypes(podPress_getFileExt($content_struct['enclosure']['url']));
				}
				if($media[0]['type'] == 'video/x-ms-wmv') {
					$media[0]['type'] = 'video/wmv';
				} elseif($media[0]['type'] == 'video/x-flv') {
					$media[0]['type'] = 'video/flv';
				}					
				$media[0]['type'] = str_replace('/', '_', $media[0]['type']);

				if(!empty($content_struct['enclosure']['duration'])) {
					$media[0]['duration'] =$content_struct['enclosure']['duration'];
				} else {
					$media[0]['duration'] = 0;
				}
				if(!empty($content_struct['enclosure']['size'])) {
					$media[0]['size'] = $content_struct['enclosure']['size'];
				} else {
						$media[0]['size'] = 0;
				}
				if(!empty($content_struct['enclosure']['title'])) {
					$media[0]['title'] = $content_struct['enclosure']['title'];
				}
				if(!empty($content_struct['enclosure']['previewImage'])) {
					$media[0]['previewImage'] = $content_struct['enclosure']['previewImage'];
				}
				if(!empty($content_struct['enclosure']['rss'])) {
						$media[0]['rss'] = $content_struct['enclosure']['rss'];
				} else {
					$media[0]['rss'] = true;
				}	

				delete_post_meta($postdata['ID'], 'podPressMedia');
				podPress_add_post_meta($postdata['ID'], 'podPressMedia', $media, true) ;
			}
			return true;
		}
	}
