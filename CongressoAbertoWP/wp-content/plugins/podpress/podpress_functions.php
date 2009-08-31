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

	if(!function_exists('getmicrotime')) {
		function getmicrotime() {
			list($usec, $sec) = explode(" ",microtime());
			return ((float)$usec + (float)$sec);
		}
	}

	function podPress_WPVersionCheck($input = '2.0.0') {
		GLOBAL $wp_version;
		if(substr($wp_version, 0, 12) == 'wordpress-mu') {
			return true;
		}
		return ((float)$input <= (float) $wp_version);
	}

	function podPress_iTunesLink() {
		GLOBAL $podPress;
		echo $podPress->iTunesLink();
	}

	function podPress_siteurl($noDomain = false) {
		if (!defined('PODPRESSSITEURL') || $noDomain) {
			$result = '';
			$urlparts = parse_url(get_settings('siteurl'));
			if(!$noDomain) {
				if(empty($urlparts['scheme'])) {
					$urlparts['scheme'] = 'http';
				}
				$result .= $urlparts['scheme'].'://'.$_SERVER['HTTP_HOST'];
				if($urlparts['port'] != '' && $urlparts['port'] != '80') {
					$result .= ':'.$urlparts['port'];
				}
			}
			if(isset($urlparts['path'])) {
				$result .= $urlparts['path'];
			}

			if(substr($result, -1, 1) != '/') {
				$result .= '/';
			}

			if($urlparts['query'] != '') {
				$result .= '?'.$urlparts['query'];
			}
			if($urlparts['fragment'] != '') {
				$result .= '#'.$urlparts['fragment'];
			}
			if($noDomain) {
				return $result.'wp-content/plugins/';
			}
			define('PODPRESSSITEURL', $result.'wp-content/plugins/');
		}
		return PODPRESSSITEURL;
	}

	function podPress_url($noDomain = false) {
		if($noDomain) {
			if (!defined('PODPRESSURL')) {
				define('PODPRESSURL', podPress_siteurl($noDomain).'podpress/');
			}
			return PODPRESSURL;
		} else {
			$result = get_settings('siteurl');
			if(substr($result, -1, 1) != '/') {
				$result .= '/';
			}
			return $result.'wp-content/plugins/podpress/';
		}
	}

	function podPress_getFileExt($str)
	{
		$pos = strrpos($str, '.');
		$pos = $pos+1;
		return substr(strtolower($str), $pos);
	}

	function podPress_getFileName($str)
	{
		if(strrpos($str, '/')) {
			$pos = strrpos($str, '/');
			$pos = $pos+1;
			return substr($str, $pos);
		} elseif(strrpos($str, ':')) {
			$pos = strrpos($str, ':');
			$pos = $pos+1;
			return substr($str, $pos);
		} else {
			return $str;
		}
	}

	function podPress_wordspaceing($txt, $number = '5', $paddingchar = ' ') {
		$txt_array = array();
		$len = strlen($txt);
		$count=$len/$number;

		$i="0";
		while($i<=$count) {
			if($i=="0") {$ib="0";} else {$ib=($i*$number)+1;}
			$txt_array[$i]=substr($txt,$ib,$number);
			$i++;
		}

		$count_array=count($txt_array)-1; $i="0";
		while ($i<=$count_array) {
			if ($i=="0") {$txt=$txt_array[$i].$paddingchar; } else {$txt .=''.$txt_array[$i].' ';}
			$i++;
		}
		return $txt;
	}	
		
	function podPress_stringLimiter($str, $len, $snipMiddle = false)
	{
		if (strlen($str) > $len) {
			if($snipMiddle) {
				$startlen = $len / 3;
				$startlen = $startlen - 1;
				$endlen = $startlen * 2;
				$endlen = $endlen - $endlen - $endlen;
				return substr($str, 0, $startlen).'...'.substr($str, $endlen);
			} else {
				$len = $len - 3;
				return substr($str, 0, $len).'...';
			}
		} else {
			return $str;
		}
	}

	if(!function_exists('html_print_r')) {
		function html_print_r($v, $n = '', $ret = false) {
			if($ret) {
				ob_start();
			}	
			echo $n.'<pre>';
			print_r($v);
			echo '</pre>';
			if($ret) {
				$result = ob_get_contents();
				ob_end_clean();
				return $result;
			}
		}
	}

	if(!function_exists('comment_print_r')) {
		function comment_print_r($v, $n = '', $ret = false) {
			$result = "<!-- \n";
			$result .= html_print_r($v, $n, true);
			$result .= " -->\n";
			if($ret) {
				return $result;
			}
			echo $result;
		}
	}

	if(!function_exists('maybe_unserialize')) {
		function maybe_unserialize($original, $ss = false) {
			if($ss) {
				$original = stripslashes($original);
			}
			if ( false !== $gm = @ unserialize($original) ) {
				return $gm;
			} else {
				return $original;
			}
		}
	}

	if(!function_exists('isBase64')) {
		function isBase64($str)
		{
			$_tmp=preg_replace("/[^A-Z0-9\+\/\=]/i",'',$str);
			return (strlen($_tmp) % 4 == 0 ) ? true : false;
		}
	}

	function podPress_mimetypes($ext, $mp4_type = 'audio') {
		$ext = strtolower($ext);
		$ext_list = array (
			'jpg' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpe' => 'image/jpeg',
			'gif' => 'image/gif',
			'png' => 'image/png',
			'bmp' => 'image/bmp',
			'tif' => 'image/tiff',
			'tiff' => 'image/tiff',
			'ico' => 'image/x-icon',
			'flv' => 'video/flv',
			'asf' => 'video/asf',
			'wmv' => 'video/wmv',
			'asx' => 'video/asf',
			'wax' => 'video/asf',
			'wmx' => 'video/asf',
			'avi' => 'video/avi',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			'm4v' => 'video/x-m4v',
			'mpeg' => 'video/mpeg',
			'mpg' => 'video/mpeg',
			'mpe' => 'video/mpeg',
			'txt' => 'text/plain',
			'c' => 'text/plain',
			'cc' => 'text/plain',
			'h' => 'text/plain',
			'rtx' => 'text/richtext',
			'css' => 'text/css',
			'htm' => 'text/html',
			'html' => 'text/html',
			'mp3' => 'audio/mpeg',
			'mp4' => $mp4_type.'/mpeg',
			'm4a' => 'audio/x-m4a',
			'aa' => 'audio/audible',
			'ra' => 'audio/x-realaudio',
			'ram' => 'audio/x-realaudio',
			'wav' => 'audio/wav',
			'ogg' => 'audio/ogg',
			'mid' => 'audio/midi',
			'midi' => 'audio/midi',
			'wma' => 'audio/wma',
			'rtf' => 'application/rtf',
			'js' => 'application/javascript',
			'pdf' => 'application/pdf',
			'doc' => 'application/msword',
			'pot' => 'application/vnd.ms-powerpoint',
			'pps' => 'application/vnd.ms-powerpoint',
			'ppt' => 'application/vnd.ms-powerpoint',
			'wri' => 'application/vnd.ms-write',
			'xla' => 'application/vnd.ms-excel',
			'xls' => 'application/vnd.ms-excel',
			'xlt' => 'application/vnd.ms-excel',
			'xlw' => 'application/vnd.ms-excel',
			'mdb' => 'application/vnd.ms-access',
			'mpp' => 'application/vnd.ms-project',
			'swf' => 'application/x-shockwave-flash',
			'class' => 'application/java',
			'tar' => 'application/x-tar',
			'zip' => 'application/zip',
			'gz' => 'application/x-gzip',
			'gzip' => 'application/x-gzip',
			'torrent' => 'application/x-bittorrent',
			'exe' => 'application/x-msdownload'
		);
		if(!isset($ext_list[$ext])) {
			return 'application/unknown';
		}
		return $ext_list[$ext];
	}

	function podPress_maxMemory() {
		$max = ini_get('memory_limit');

		if (preg_match('/^([\d\.]+)([gmk])?$/i', $max, $m)) {
			$value = $m[1];
			if (isset($m[2])) {
				switch(strtolower($m[2])) {
					case 'g': $value *= 1024;  # fallthrough
					case 'm': $value *= 1024;  # fallthrough
					case 'k': $value *= 1024; break;
					default: $value = 2048000;
				}
			}
			$max = $value;
		} else {
		  $max = 2048000;
		}
		return $max/2;
	}

	/**************************************************************/
	/* Functions for supporting the widgets */
	/**************************************************************/
	function podPress_loadWidgets () {
		if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') ) {
			return;
		}
		// Tell Dynamic Sidebar about our new widget and its control
		register_sidebar_widget(array('Feed Buttons', 'widgets'), 'podPress_feedButtons');
		register_widget_control(array('Feed Buttons', 'widgets'), 'podPress_feedButtons_control');

		// Tell Dynamic Sidebar about our new widget and its control
		register_sidebar_widget(array('XSPF Player', 'widgets'), 'podPress_xspfPlayer');
		register_widget_control(array('XSPF Player', 'widgets'), 'podPress_xspfPlayer_control');
	}

	function podPress_feedButtons_control() {
		GLOBAL $podPress;
		$options = $newoptions = get_option('widget_podPressFeedButtons');
		if ( $_POST["podPressFeedButtons-submit"] ) {
			$newoptions['blog'] = isset($_POST['podPressFeedButtons-blog']);
			$newoptions['podcast'] = isset($_POST['podPressFeedButtons-podcast']);
			$newoptions['itunes'] = isset($_POST['podPressFeedButtons-itunes']);
			// iscifi new option for itunes protocol
			$newoptions['iprot'] = isset ($_POST['podPressItunesProtocol-iprot']);
			$newoptions['title'] = strip_tags(stripslashes($_POST["podPressFeedButtons-title"]));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_podPressFeedButtons', $options);
		}
		if(!isset($options['blog'])) {
			$options['blog'] = false;
		}
		if (!isset($options['iprot'])) {
			$options['iprot'] = false;
			}
			
		if(!isset($options['podcast'])) {
			$options['podcast'] = true;
		}
		if(!isset($options['itunes'])) {
			$options['itunes'] = true;
		}

		$blog = $options['blog'] ? 'checked="checked"' : '';
		$podcast = $options['podcast'] ? 'checked="checked"' : '';
		$itunes  = $options['itunes'] ? 'checked="checked"' : '';
		$iprot   = $options['iprot'] ? 'checked="checked"' :'';
		
		if(!isset($options['title'])) {
			$options['title'] = __('Podcast Feeds');
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		?>
		<p style="text-align: right;"><b>A podPress Widget</b></p>
		<p><label for="podPressFeedButtons-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="podPressFeedButtons-title" name="podPressFeedButtons-title" type="text" value="<?php echo $title; ?>" /></label></p>
		<?php
		if($podPress->settings['podcastFeedURL'] != get_settings('rss2_url')) {
		?>
			<p style="text-align:right;margin-right:40px;"><label for="podPressFeedButtons-blog">Site Blog Feed <input class="checkbox" type="checkbox" <?php echo $blog; ?> id="podPressFeedButtons-blog" name="podPressFeedButtons-blog" /></label></p>
		<?php
		}
		?>
		<p style="text-align:right;margin-right:40px;"><label for="podPressFeedButtons-podcast">Show Podcast Feed <input class="checkbox" type="checkbox" <?php echo $podcast; ?> id="podPressFeedButtons-podcast" name="podPressFeedButtons-podcast" /></label></p>
		
		<p style="text-align:right;margin-right:40px;"><label for="podPressFeedButtons-itunes">Show iTunes Music Store <input class="checkbox" type="checkbox" <?php echo $itunes; ?> id="podPressFeedButtons-itunes" name="podPressFeedButtons-itunes" /></label></p>
		<p style="text-align:right;margin-right:40px;"><label for="podPressFeedButtons-iprot">Use Itunes protcol for URL <input class="checkbox" type="checkbox" <?php echo $iprot; ?> id="podPressItunesProtocol-iprot" name="podPressItunesProtocol-iprot" /></label></p>
		<input type="hidden" id="podPressFeedButtons-submit" name="podPressFeedButtons-submit" value="1" />
		<?php
	}

	function podPress_feedButtons ($args) {
		GLOBAL $podPress;
		extract($args);
		$options = get_option('widget_podPressFeedButtons');
		if(!isset($options['title'])) {
			$options['title'] = __('Podcast Feeds');
		}
		$options['title'] = htmlspecialchars($options['title'], ENT_QUOTES);
		if(!isset($options['blog'])) {
			$options['blog'] = false;
		}
		if(!isset($options['podcast'])) {
			$options['podcast'] = true;
		}
		if(!isset($options['itunes'])) {
			$options['itunes'] = true;
		}
		if (!isset($options['iprot'])) {
			$options['iprot'] = false;
		}
			
		echo $before_widget;
		echo $before_title . $options['title'] . $after_title;
		echo "<ul>\n";
		if($options['itunes']) {
		 if ($options['iprot']) {
		   echo ' <li><a href="itpc://';
		 }
		 else {
		   echo ' <li><a href="http://';
		 }
			echo 'phobos.apple.com/WebObjects/MZStore.woa/wa/viewPodcast?id='.$podPress->settings['iTunes']['FeedID'].'"><img src="'.podPress_url().'images/button_itunes.png" border="0" alt="View in iTunes"/></a></li>'."\n";
		}
		if($options['podcast']) {
			echo '	<li><a href="'.$podPress->settings['podcastFeedURL'].'"><img src="'.podPress_url().'images/button_rss_podcast.png" border="0" alt="Any Podcatcher"/></a></li>'."\n";
		}
		if($options['blog']) {
		echo '	<li><a href="'.get_bloginfo ('rss2_url').'"><img src="'.podPress_url().'images/button_rss_blog.png" border="0" alt="Any Feed Reader"/></a></li>'."\n";
		}
		echo "</ul>\n";
		echo $after_widget;
	}

	function podPress_xspfPlayer_control() {
		$options = $newoptions = get_option('widget_podPressXspfPlayer');
		if ( $_POST["podPressXspfPlayer-submit"] ) {
			$newoptions['useSlimPlayer'] = isset($_POST['podPressXspfPlayer-useSlimPlayer']);
			$newoptions['title'] = strip_tags(stripslashes($_POST["podPressXspfPlayer-title"]));
		}
		if ( $options != $newoptions ) {
			$options = $newoptions;
			update_option('widget_podPressXspfPlayer', $options);
		}
		$useSlimPlayer = $options['useSlimPlayer'] ? 'checked="checked"' : '';
		if(!isset($options['title'])) {
			$options['title'] = __('Podcast Player');
		}
		$title = htmlspecialchars($options['title'], ENT_QUOTES);
		?>
		<p style="text-align: right;"><b>A podPress Widget</b></p>
		<p><label for="podPressXspfPlayer-title"><?php _e('Title:'); ?> <input style="width: 250px;" id="podPressXspfPlayer-title" name="podPressXspfPlayer-title" type="text" value="<?php echo $title; ?>" /></label></p>
		<p style="text-align:right;margin-right:40px;"><label for="podPressXspfPlayer-useSlimPlayer">Use Slim Player <input class="checkbox" type="checkbox" <?php echo $useSlimPlayer; ?> id="podPressXspfPlayer-useSlimPlayer" name="podPressXspfPlayer-useSlimPlayer" /></label></p>
		<input type="hidden" id="podPressXspfPlayer-submit" name="podPressXspfPlayer-submit" value="1" />
		<?php
	}

	function podPress_xspfPlayer ($args = '##NOTSET##') {
		GLOBAL $podPress;
		extract($args);
		$options = get_option('widget_podPressXspfPlayer');
		if(!isset($options['title'])) {
			$options['title'] = __('Podcast Player');
		}
		$options['title'] = htmlspecialchars($options['title'], ENT_QUOTES);
		if(!isset($options['useSlimPlayer'])) {
			$options['useSlimPlayer'] = false;
		}
			
		echo $before_widget;
		echo $before_title . $options['title'] . $after_title;

		if($options['useSlimPlayer']) {
			echo '<object type="application/x-shockwave-flash" width="150" height="170" ';
			echo 'data="'.podPress_url().'players/xspf_player_slim.swf?playlist_url='.get_settings('siteurl').'/playlist.xspf">'."\n";
			echo '	<param name="movie" value="'.podPress_url().'players/xspf_player_slim.swf?playlist_url='.get_settings('siteurl').'/playlist.xspf" />'."\n";
			echo '</object>'."\n";
		} else {
			echo '<object type="application/x-shockwave-flash" width="150" height="170" ';
			echo 'data="'.podPress_url().'players/xspf_player.swf?playlist_url='.get_settings('siteurl').'/playlist.xspf">'."\n";
			echo '	<param name="movie" value="'.podPress_url().'players/xspf_player.swf?playlist_url='.get_settings('siteurl').'/playlist.xspf" />'."\n";
			echo '</object>'."\n";
		}
		echo $after_widget;
	}

	/**************************************************************/
	/* Functions for supporting the downloader */
	/**************************************************************/
	
	function podPress_StatCounter($postID, $media, $method) {
		global $wpdb;

		switch($method) {
			case 'feed':
			case 'web':
			case 'play':
				$sqlI = "INSERT INTO ".$wpdb->prefix."podpress_statcounts (postID, media, $method) VALUES ($postID, '$media', 1)";
				$sqlU = "UPDATE ".$wpdb->prefix."podpress_statcounts SET $method = $method+1, total = total+1 WHERE postID = '$postID' AND media = '$media'";
				$wpdb->hide_errors();
				$result = $wpdb->query($sqlI);
				if(!$result) {
					$wpdb->query($sqlU);
				}
				$wpdb->show_errors();
				break;
			default:
				return;
		}
	}
	
	function podPress_StatCollector($postID, $media, $method) {
		global $wpdb;

		$media	= addslashes($media);
		$method	= addslashes($method);

		$ip		= addslashes($_SERVER['REMOTE_ADDR']);
		//$cntry	= addslashes(podPress_determineCountry($ip));
		$cntry	= addslashes('');
		$lang	= addslashes(podPress_determineLanguage());
		$ref	= addslashes($_SERVER['HTTP_REFERER']);
		$url 	= parse_url($ref);
		$domain	= addslashes(eregi_replace('^www.','',$url['host']));
		//$res	= $_SERVER['REQUEST_URI'];
		$ua   = addslashes($_SERVER['HTTP_USER_AGENT']);
		$br		= podPress_parseUserAgent($_SERVER['HTTP_USER_AGENT']);
		$dt		= time();
	
		$query = "INSERT INTO ".$wpdb->prefix."podpress_stats (postID, media, method,remote_ip,country,language,domain,referer,user_agent,platform,browser,version,dt) 
				  VALUES ('$postID','$media','$method','".$ip."','$cntry','$lang','$domain','$ref','$ua','".addslashes($br['platform'])."','".addslashes($br['browser'])."','".addslashes($br['version'])."',$dt)";
		//echo '$query: '.$query."<br/>\n";
		$wpdb->query($query);
		return $wpdb->insert_id;
	}
	
	function podPress_determineCountry($ip) {
		$coinfo = @file('http://www.hostip.info/api/get.html?ip=' . $ip);
		$country_string = explode(':',$coinfo[0]);
		$country = trim($country_string[1]);

		if($country == '(Private Address) (XX)' 
		|| $country == '(Unknown Country?) (XX)' 
		|| $country == '' 
		|| !$country 
		  )return 'Indeterminable';
			
		return $country;
	}
	
	function podPress_parseUserAgent($ua) {
		$browser['platform']	= "Indeterminable";
		$browser['browser']		= "Indeterminable";
		$browser['version']		= "Indeterminable";
		$browser['majorver']	= "Indeterminable";
		$browser['minorver']	= "Indeterminable";
		
		// Test for platform
		if (eregi('Win95',$ua)) {
			$browser['platform'] = "Windows 95";
			}
		else if (eregi('Win98',$ua)) {
			$browser['platform'] = "Windows 98";
			}
		else if (eregi('Win 9x 4.90',$ua)) {
			$browser['platform'] = "Windows ME";
			}
		else if (eregi('Windows NT 5.0',$ua)) {
			$browser['platform'] = "Windows 2000";
			}
		else if (eregi('Windows NT 5.1',$ua)) {
			$browser['platform'] = "Windows XP";
			}
		else if (eregi('Windows NT 5.2',$ua)) {
			$browser['platform'] = "Windows 2003";
			}
		else if (eregi('Windows NT 6.0',$ua)) {
			$browser['platform'] = "Windows Longhorn beta";
			}
		else if (eregi('Windows',$ua)) {
			$browser['platform'] = "Windows";
			}
		else if (eregi('Mac OS X',$ua)) {
			$browser['platform'] = "Mac OS X";
			}
		else if (eregi('Macintosh',$ua)) {
			$browser['platform'] = "Mac OS Classic";
			}
		else if (eregi('Linux',$ua)) {
			$browser['platform'] = "Linux";
			}
		else if (eregi('BSD',$ua) || eregi('FreeBSD',$ua) || eregi('NetBSD',$ua)) {
		$browser['platform'] = "BSD";
			}
		else if (eregi('SunOS',$ua)) {
			$browser['platform'] = "Solaris";
			}

		// Test for browser type
		if (eregi('Mozilla/4',$ua) && !eregi('compatible',$ua)) {
			$browser['browser'] = "Netscape";
			eregi('Mozilla/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Mozilla/5',$ua) || eregi('Gecko',$ua)) {
			$browser['browser'] = "Mozilla";
			eregi('rv(:| )([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[2];
			}
		if (eregi('Safari',$ua)) {
			$browser['browser'] = "Safari";
			$browser['platform'] = "Mac OS X";
			eregi('Safari/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];

			if (eregi('412',$browser['version'])) {
				$browser['version'] 	= 2.0;
				$browser['majorver']	= 2;
				$browser['minorver']	= 0;
				}
			else if (eregi('312',$browser['version'])) {
				$browser['version'] 	= 1.3;
				$browser['majorver']	= 1;
				$browser['minorver']	= 3;
				}
			else if (eregi('125',$browser['version'])) {
				$browser['version'] 	= 1.2;
				$browser['majorver']	= 1;
				$browser['minorver']	= 2;
				}
			else if (eregi('100',$browser['version'])) {
				$browser['version'] 	= 1.1;
				$browser['majorver']	= 1;
				$browser['minorver']	= 1;
				}
			else if (eregi('85',$browser['version'])) {
				$browser['version'] 	= 1.0;
				$browser['majorver']	= 1;
				$browser['minorver']	= 0;
				}
			else if ($browser['version']<85) {
				$browser['version'] 	= "1.0 beta";
				}
			}
		if (eregi('iCab',$ua)) {
			$browser['browser'] = "iCab";
			eregi('iCab ([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Firefox',$ua)) {
			$browser['browser'] = "Firefox";
			eregi('Firefox/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Firebird',$ua)) {
			$browser['browser'] = "Firebird";
			eregi('Firebird/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Phoenix',$ua)) {
			$browser['browser'] = "Phoenix";
			eregi('Phoenix/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Camino',$ua)) {
			$browser['browser'] = "Camino";
			eregi('Camino/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Chimera',$ua)) {
			$browser['browser'] = "Chimera";
			eregi('Chimera/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Netscape',$ua)) {
			$browser['browser'] = "Netscape";
			eregi('Netscape[0-9]?/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('MSIE',$ua)) {
			$browser['browser'] = "Internet Explorer";
			eregi('MSIE ([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('MSN Explorer',$ua)) {
			$browser['browser'] = "MSN Explorer";
			eregi('MSN Explorer ([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('AOL',$ua)) {
			$browser['browser'] = "AOL";
			eregi('AOL ([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('America Online Browser',$ua)) {
			$browser['browser'] = "AOL Browser";
			eregi('America Online Browser ([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('K-Meleon',$ua)) {
			$browser['browser'] = "K-Meleon";
			eregi('K-Meleon/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Beonex',$ua)) {
			$browser['browser'] = "Beonex";
			eregi('Beonex/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Opera',$ua)) {
			$browser['browser'] = "Opera";
			eregi('Opera( |/)([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[2];
			}
		if (eregi('OmniWeb',$ua)) {
			$browser['browser'] = "OmniWeb";
			eregi('OmniWeb/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];

			if (eregi('563',$browser['version'])) {
				$browser['version'] 	= 5.1;
				$browser['majorver']	= 5;
				$browser['minorver']	= 1;
				}
			else if (eregi('558',$browser['version'])) {
				$browser['version'] 	= 5.0;
				$browser['majorver']	= 5;
				$browser['minorver']	= 0;
				}
			else if (eregi('496',$browser['version'])) {
				$browser['version'] 	= 4.5;
				$browser['majorver']	= 4;
				$browser['minorver']	= 5;
				}
			}
		if (eregi('Konqueror',$ua)) {
			$browser['platform'] = "Linux";
	
			$browser['browser'] = "Konqueror";
			eregi('Konqueror/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Galeon',$ua)) {
			$browser['browser'] = "Galeon";
			eregi('Galeon/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Epiphany',$ua)) {
			$browser['browser'] = "Epiphany";
			eregi('Epiphany/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Kazehakase',$ua)) {
			$browser['browser'] = "Kazehakase";
			eregi('Kazehakase/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('amaya',$ua)) {
			$browser['browser'] = "Amaya";
			eregi('amaya/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Crawl',$ua) || eregi('bot',$ua) || eregi('slurp',$ua) || eregi('spider',$ua)) {
			$browser['browser'] = "Crawler/Search Engine";
			}
		if (eregi('Lynx',$ua)) {
			$browser['browser'] = "Lynx";
			eregi('Lynx/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('Links',$ua)) {
			$browser['browser'] = "Links";
			eregi('\(([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		if (eregi('ELinks',$ua)) {
			$browser['browser'] = "ELinks";
			eregi('ELinks/([[:digit:]\.]+)',$ua,$b);
			$browser['version'] = $b[1];
			}
		
		// Determine browser versions
		if (($browser['browser']!='AppleWebKit' || $browser['browser']!='OmniWeb') && $browser['browser'] != "Indeterminable" && $browser['browser'] != "Crawler/Search Engine" && $browser['version'] != "Indeterminable") {
			// Make sure we have at least .0 for a minor version for Safari and OmniWeb
			$browser['version'] = (!eregi('\.',$browser['version']))?$browser['version'].".0":$browser['version'];
			
			eregi('^([0-9]*).(.*)$',$browser['version'],$v);
			$browser['majorver'] = $v[1];
			$browser['minorver'] = $v[2];
			}
		if (empty($browser['version']) || $browser['version']=='.0') {
			$browser['version']		= "Indeterminable";
			$browser['majorver']	= "Indeterminable";
			$browser['minorver']	= "Indeterminable";
			}
		
		return $browser;
	}
	
	function podPress_determineLanguage() {
		$lang_choice = "empty"; 
		if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
			// Capture up to the first delimiter (, found in Safari)
			preg_match("/([^,;]*)/",$_SERVER["HTTP_ACCEPT_LANGUAGE"],$langs);
			$lang_choice = $langs[0];
		}
		return $lang_choice;
	}
	
	function podPress_statsDownloadRedirect($requested = '##NOTSET##') {
		GLOBAL $podPress;
		if($requested == '##NOTSET##') {
			$requested = parse_url($_SERVER['REQUEST_URI']);
			$requested = $requested['path'];
		}
		$pos = 0;
		if (is_404() || $pos = strpos($requested, 'podpress_trac')) {
			if($pos == 0) {
				$pos = strpos($requested, 'podpress_trac');
			}
			$pos = $pos+14;
			if(substr($requested, $pos, 1) == '/') {
				$pos = $pos+1;
			}
			$requested = substr($requested, $pos);
			$parts = explode('/', $requested);
			if(count($parts) == 4) {
				podPress_processDownloadRedirect($parts[1], $parts[2], $parts[3], $parts[0]);
			}
		}
	}

	function podPress_processDownloadRedirect($postID, $mediaNum, $filename, $method = '') {
		GLOBAL $podPress, $wpdb;
		$allowedMethods = array('feed', 'play', 'web');
		$realURL = false;
		$realSysPath = false;
		$statID = false;

		if(substr($filename, -20, 20) == 'podPressStatTest.txt') {
			status_header('200');
			echo 'Worked';
			exit;
		}

		if (in_array($method, $allowedMethods) && is_numeric($postID) && is_numeric($mediaNum)) {
			$mediaFiles = podPress_get_post_meta($postID, 'podPressMedia', true);
			if(isset($mediaFiles[$mediaNum])) {			
				if($mediaFiles[$mediaNum]['URI'] == urldecode($filename)) {
					$realURL = $filename;
				} elseif(podPress_getFileName($mediaFiles[$mediaNum]['URI']) == urldecode($filename)) {
					$realURL = $mediaFiles[$mediaNum]['URI'];
				} elseif(podPress_getFileName($mediaFiles[$mediaNum]['URI_torrent']) == urldecode($filename)) {
					$realURL = $mediaFiles[$mediaNum]['URI_torrent'];
				}
			}
		}			

		if(!$realURL) {
			header('X-PodPress-Location: '.get_settings('siteurl'));
			header('Location: '.get_settings('siteurl'));
			exit;
		}
		$badextensions = array('.smi', '.jpg', '.png', '.gif');
		if($filename && !in_array(strtolower(substr($filename, -4)), $badextensions)) {
			podPress_StatCounter($postID, $filename, $method);
			if($podPress->settings['statLogging'] == 'Full' || $podPress->settings['statLogging'] == 'FullPlus') {
				$statID = podPress_StatCollector($postID, $filename, $method);
			}
		}
		
		$realSysPath = $podPress->convertPodcastFileNameToSystemPath(str_replace('%20', ' ', $realURL));
		$realURL = $podPress->convertPodcastFileNameToValidWebPath($realURL);

		if($podPress->settings['enable3rdPartyStats'] == 'PodTrac') {
			$realURL = str_replace(array('ftp://', 'http://', 'https://'), '', $realURL);
			$realURL = $podPress->podtrac_url.$realURL;

		} elseif( strtolower($podPress->settings['enable3rdPartyStats']) == 'blubrry' && !empty($podPress->settings['statBluBrryProgramKeyword'])) {
			$realURL = str_replace('http://', '', $realURL);
			$realURL = $podPress->blubrry_url.$podPress->settings['statBluBrryProgramKeyword'].'/'.$realURL;
		} elseif ($podPress->settings['statLogging'] == 'FullPlus' && $realSysPath !== false) {
			status_header('200');
			$content_type = podPress_mimetypes(podPress_getFileExt($realSysPath));
			if($method == 'web') {
				header("Pragma: ");
				header("Cache-Control: ");
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
				header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
				header("Cache-Control: post-check=0, pre-check=0", false);
				header('Content-Disposition: attachment; filename="'.$filename.'"');
				header("Content-Description: ".trim(htmlentities($filename)));
				header("Connection: close");
				if(substr($content_type, 0, 4) != 'text') {
					header("Content-Transfer-Encoding: binary");
				}
			} else {
				header("Connection: Keep-Alive");
			}
			header("X-ForcedBy: podPress");
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
			header('Content-type: '.$content_type);
			header('Content-Length: '.filesize($realSysPath));
			set_time_limit(0);
			$chunksize = 1*(1024*1024); // how many bytes per chunk
			if ($handle = fopen($realSysPath, 'rb')) {
				while (!feof($handle) && connection_status()==0) {
					echo fread($handle, $chunksize);
					ob_flush();
					flush();
				}
				fclose($handle);
			}

			if($statID !== false && connection_status()==0 && !connection_aborted()) {
				$sqlU = "UPDATE ".$wpdb->prefix."podpress_stats SET completed=1 WHERE id=".$statID;
				$wpdb->hide_errors();
				$result = $wpdb->query($sqlI);
				if(!$result) {
					$wpdb->query($sqlU);
				}
			}
			exit;
		}
		$realURL = str_replace(' ', '%20', $realURL);
		status_header('302');
		header('X-PodPress-Location: '.$realURL, true, 302);
		header('Location: '.$realURL, true, 302);
		header('Content-Length: 0');
		exit;
	}

	function podPress_remote_version_check() {
		$current = PODPRESS_VERSION;
		$latestVersionCache = podPress_get_option('podPress_versionCheck');
		if(($latestVersionCache['cached']+86400) < time() ) {
			$current = $latestVersionCache['version'];
		} elseif (class_exists(snoopy)) {
			$client = new Snoopy();
			$client->_fp_timeout = 10;
			if (@$client->fetch('http://www.mightyseek.com/podpress_downloads/versioncheck.php?url='.get_settings('siteurl').'&current='.PODPRESS_VERSION) === false) {
				return -1;
			} else {
			  $remote = $client->results;
 		  	if (!$remote || strlen($remote) > 8 ) {
					return -1;
				}
				$current = $remote;
			}
			delete_option('podPress_versionCheck');
			podPress_add_option('podPress_versionCheck', array('version'=>$current, 'cached'=> time()), 'Latest version available', 'yes'); 
		}
	
		if ($current > PODPRESS_VERSION) {
			return 1;
		} else {
			return 0;
		}
	}
	
	/**************************************************************/
	/* Functions for supporting version of WordPress before 2.0.0 */
	/**************************************************************/
	
	function podPress_add_post_meta($post_id, $key, $value, $unique = false) {
		GLOBAL $wpdb;
		if(!podPress_WPVersionCheck('2.0.0')) {
			if ( is_array($value) || is_object($value) ) {
				$value = $wpdb->escape(serialize($value));
			}
		}
		return add_post_meta($post_id, $key, $value, $unique);
	}

	function podPress_get_post_meta($post_id, $key, $single = false) {
		if(podPress_WPVersionCheck('2.0.0') === false) {
			return maybe_unserialize(get_post_meta($post_id, $key, $single));
		}
		return get_post_meta($post_id, $key, $single);
	}
		
	function podPress_add_option($name, $value = '', $description = '', $autoload = 'yes') {
		if(!podPress_WPVersionCheck('2.0.0')) {
			if ( is_array($value) || is_object($value) ) {
				$value = serialize($value);
			}
		}
		return add_option($name, $value, $description, $autoload);
	}

	function podPress_get_option($option) {
		if(!podPress_WPVersionCheck('2.0.0')) {
			return maybe_unserialize(get_option($option));
		}
		return get_option($option);
	}

	function podPress_update_option($option_name, $option_value) {
		delete_option($option_name); 
		podPress_add_option($option_name, $option_value);
		return true;
	}

