<?php
define('PODPRESS_VERSION', '8.8');
/*
 Info for WordPress:
 ==============================================================================
 Plugin Name: podPress
 Version: 8.8
 Plugin URI: http://www.mightyseek.com/podpress/
 Description: The podPress pluggin gives you everything you need in one easy plugin to use WordPress for Podcasting. Set it up in <a href="admin.php?page=podpress/podpress_feed.php">'podPress'->Feed/iTunes Settings</a>. If you this plugin works for you, send us a comment.
 Author: Dan Kuykendall (Seek3r)
 Author URI: http://www.mightyseek.com/

 podPress - Podcasting made easy for WordPress
 ==============================================================================

 This plugin makes it much easier and organized to use WordPress for Podcasting.

 The plugin was created as a way for me to merge Garrick Van Buren's  WP-iPodCatter
 and Martin Laine's Audio Player with some hacks I made to Word Press 2.0.
 I had tweaked the player to have the [audio:filename.mp3] entry to drive the
 whole podcasting need. In the rss2.php I had tweaked it to generate the
 enclosure tag from it. So thats how the plugin took birth, and I have been adding
 features to make the process cleaner over time.

 Feel free to visit my website under www.mightyseek.com or contact me at
 dan [at] kuykendall [dot] org

 Have fun!

 Installation:
 ==============================================================================
 1. Upload the full directory into your wp-content/plugins directory
 2. Activate it in the Plugin options
 3. Edit or publish a post or click on Rebuild Sitemap on the Sitemap Administration Interface


 Contributors:
 ==============================================================================
 Developer              Dan Kuykendall      http://www.mightyseek.com/
 Developer              David Maciejewski   http://www.macx.de/
 Forum Support/BugBoy   Jeff Norris         http://www.iscifi.tv/

 Audio player           Martin Laine        http://www.1pixelout.net
 WP-iPodCatter          Garrick Van Buren   http://garrickvanburen.com/

 Thanks to all contributors and bug reporters! :)

 Release History:
 ==============================================================================
 Instead of maintaining the history in here, Im just going to maintain it at
 http://www.mightyseek.com/podpress/changelog/

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

//ini_set('memory_limit', '1M');
$podPress_memoryUsage = array();
$podPress_memoryIncrease = 0;
$podPress_feedHooksAdded = false;
$GLOBALS['podPressPlayer'] = 0;  // Global counter of Players

if (!defined('PLUGINDIR')) {
	define('PLUGINDIR', 'wp-content/plugins');
}

if (!defined('PODPRESSPLUGINDIR')) {
	define('PODPRESSPLUGINDIR', ABSPATH.PLUGINDIR);
}

if(!function_exists('memory_get_usage')) {
	unset($_GET['podpress_showmem']);
	function memory_get_usage() { return 0;	}
	if(!function_exists('podPress_bytes')) {
		function podPress_bytes($i) { return $i;	}
		function podPress_checkmem() { return;	}
	}
} elseif(!function_exists('podPress_bytes')) {
	function podPress_bytes($input, $dec=0) {
		$unim = array('B','KB','MB','GB','TB','PB');
		$value = round($input, $dec);
	 $i=0;
	 while ($value>1024) { $value /= 1024; $i++; }
		return round($value, $dec).$unim[$i]; 
	}

	function podPress_checkmem($txt, $start = false) {
		GLOBAL $podPress_memoryUsage, $podPress_memoryIncrease;
		if(isset($_GET['podpress_showmem'])) {
			$mem = memory_get_usage();
			if($start) {
				$podPress_memoryUsage[$txt] = array('start'=>$mem);
			} else {
				if(!is_array($podPress_memoryUsage[$txt])) {
					if(count($podPress_memoryUsage) > 0) {
						$prevval = end($podPress_memoryUsage);
						$prevval = $prevval['finish'];
					} else {
						$prevval = $mem;
					}
					$podPress_memoryUsage[$txt] = array('start'=>$prevval, 'fromprev'=>'X');
					unset($prevval);
				}
				$podPress_memoryUsage[$txt]['finish'] = $mem;
				$increase = $mem - $podPress_memoryUsage[$txt]['start'];
				$podPress_memoryUsage[$txt]['increase'] = $increase;
				$podPress_memoryIncrease = $podPress_memoryIncrease+$increase;
				if($_GET['podpress_showmem'] == 1) {
					echo $txt.': Increased memory '.podPress_bytes($increase)." for a total of ".podPress_bytes($mem)."<br/>\n";
				}
			}
		}
	}
}

if($_GET['podpress_showmem'] == 1) {
	echo 'PHP has a memory_limit set to: '.ini_get('memory_limit').'<br/>';
}
podPress_checkmem('podPress start');

if(file_exists(ABSPATH.PLUGINDIR.'/podpress.php')) {
	echo __('It appears you are upgrading podPress, but left the pre-4.x version of podpress.php file in the plugins directory. Please delete this file to continue.', 'podpress');
	exit;
}

if(!class_exists ('podPress_class')) {
	if(function_exists('load_plugin_textdomain')) {
		load_plugin_textdomain('podpress',PLUGINDIR.'/podpress');
	}
	require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_class.php');
	podPress_checkmem('podPress base class included');
	require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_functions.php');
	podPress_checkmem('podPress functions loaded');

	if($podPress_x = @parse_url($_SERVER['REQUEST_URI'])) {
		$podPress_x = $podPress_x['path'];
		if (strpos($podPress_x, 'crossdomain.xml')) {
			podPress_crossdomain();
		} elseif ($pos = strpos($podPress_x, 'podpress_trac')) {
			/* short circut the loading process for a simple redirect */
			podPress_checkmem('standard podPress class loaded', true);
			$podPress = new podPress_class;
			podPress_checkmem('standard podPress class loaded');
			podPress_statsDownloadRedirect($podPress_x);
			exit;
		}
		unset($podPress_x);
	}

	$customThemeFile = ABSPATH.'/wp-content/themes/'.get_option('template').'/podpress_theme.php';
	if(file_exists($customThemeFile)) {
		require_once($customThemeFile);
	podPress_checkmem('podPress custom theme file loaded');
	}
	require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_theme.php');
	podPress_checkmem('podPress core theme file loaded');

	/*************************************************************/
	/* Simple wrapper functions, since I dont think I can        */
	/* register object functions                                 */
	/*************************************************************/

	function podPress_init() {
		GLOBAL $podPress;

		if(function_exists('add_feed')) {
			add_feed('podcast', 'podPress_do_feed_podcast');
			add_feed('premium', 'podPress_do_feed_premium');
			add_feed('torrent', 'podPress_do_feed_torrent');
			add_feed('playlist.xspf', 'podPress_do_feed_xspf');
		}
		add_action('do_feed_rss', 'podPress_do_feed_rss2', 1, 1);
		add_action('do_feed_rss2', 'podPress_do_feed_rss2', 1, 1);
		remove_action('do_feed_atom', 'do_feed_atom', 10, 1);
		add_action('do_feed_atom1', 'podPress_do_feed_atom1', 1, 1);

		if(is_feed()) {
			podPress_addFeedHooks();
			$podPress->feed_getCategory();
		}
	}

	function podPress_add_menu_page() {
		GLOBAL $podPress;
		if(podPress_WPVersionCheck('2.0.0')) {
			$permission_needed = $podPress->requiredAdminRights;
		} else {
			$permission_needed = 1;
		}
		if (function_exists('add_menu_page')) {
			if($podPress->settings['enableStats'] == true) {
				$starting_point = 'podpress_stats';
			} else {
				$starting_point = 'podpress_feed';
			}
			add_menu_page('podPress', 'podPress', $permission_needed, 'podpress/'.$starting_point.'.php');
		}
		if (function_exists('add_submenu_page')) {
			if($podPress->settings['enableStats'] == true) {
				$starting_point = 'podpress_stats';
			} else {
				$starting_point = 'podpress_feed';
			}

			if($podPress->settings['enableStats'] == true) {
				add_submenu_page('podpress/'.$starting_point.'.php', __('Stats'), __('Stats'), $permission_needed, 'podpress/podpress_stats.php');
			}
			add_submenu_page('podpress/'.$starting_point.'.php', __('Feed/iTunes Settings', 'podpress'), __('Feed/iTunes Settings', 'podpress'), $permission_needed, 'podpress/podpress_feed.php');
			add_submenu_page('podpress/'.$starting_point.'.php', __('General Settings', 'podpress'), __('General Settings', 'podpress'), $permission_needed, 'podpress/podpress_general.php');

			if($podPress->settings['contentPlayer'] != 'disabled') {
				add_submenu_page('podpress/'.$starting_point.'.php', __('Player Settings', 'podpress'), __('Player Settings', 'podpress'), $permission_needed, 'podpress/podpress_players.php');
			}

			if($podPress->settings['enablePodangoIntegration'] == true) {
				add_submenu_page('podpress/'.$starting_point.'.php', __('Podango Settings', 'podpress'), __('Podango Settings', 'podpress'), $permission_needed, 'podpress/podpress_podango.php');
			}
		}
	}

	function podPress_switch_theme() {
		GLOBAL $podPress;
		$podPress->settings['compatibilityChecks']['themeTested'] = false;
		$podPress->settings['compatibilityChecks']['wp_head'] = false;
		$podPress->settings['compatibilityChecks']['wp_footer'] = false;
		podPress_update_option('podPress_config', $podPress->settings);
	}

	function podPress_wp_head() {
		GLOBAL $podPress, $podPress_inAdmin;
		if(!$podPress_inAdmin) {
			if(!$podPress->settings['compatibilityChecks']['themeTested']) {
				$podPress->settings['compatibilityChecks']['themeTested'] = true;
				podPress_update_option('podPress_config', $podPress->settings);
			}
			if(!$podPress->settings['compatibilityChecks']['wp_head']) {
				$podPress->settings['compatibilityChecks']['wp_head'] = true;
				podPress_update_option('podPress_config', $podPress->settings);
			} else {
				$podPress->settings['compatibilityChecks']['wp_head'] = true;
			}
		}
		echo '<script type="text/javascript" src="'.podPress_url().'podpress.js"></script>'."\n";
		echo '<script type="text/javascript"><!--'."\n";
		echo 'var podPressBackendURL = location.protocol;'."\n";
		echo 'if(location.port != "80" && location.port != "443") {podPressBackendURL = podPressBackendURL+location.port; } '."\n";
		echo 'podPressBackendURL = podPressBackendURL+"//"+location.hostname+"'.podPress_url(true).'";'."\n";
		echo 'var podPressDefaultPreviewImage = podPressBackendURL+"/images/vpreview_center.png";'."\n";

		if($podPress->settings['enablePodangoIntegration'] || $podPress->settings['mp3Player'] != '1pixelout') {
			echo 'var podPressPlayerFile = "podango_player.swf";'."\n";
		} else {
			echo 'var podPressPlayerFile = "1pixelout_player.swf";'."\n";
		}

		$playerOptions = '';
		if(@!is_array($podPress->settings['player'])) {
			$podPress->resetPlayerSettings();
		}
		foreach($podPress->settings['player'] as $key => $val) {
			$val = str_replace('#', '0x', $val);
			$playerOptions .= '&amp;' . $key . '=' . rawurlencode($val);
		}
		echo 'var podPressMP3PlayerOptions = "'.$playerOptions.'&amp;";'."\n";

		if($podPress->settings['player']['listenWrapper']) {
			echo 'var podPressMP3PlayerWrapper = true;'."\n";
		} else {
			echo 'var podPressMP3PlayerWrapper = false;'."\n";
		}

		echo 'var podPressText_PlayNow = "'.__('Play Now', 'podpress').'";'."\n";
		echo 'var podPressText_HidePlayer = "'.__('Hide Player', 'podpress').'";'."\n";
		echo '--></script>'."\n";

		if(file_exists(ABSPATH.'/wp-content/themes/'.get_option('template').'/podpress.css')) {
			echo '<link rel="stylesheet" href="'.podPress_siteurl().'/wp-content/themes/'.get_option('template').'/podpress.css" type="text/css" />'."\n";
		} else {
			echo '<link rel="stylesheet" href="'.podPress_url().'podpress.css" type="text/css" />'."\n";
		}
	}

	function podPress_admin_javascript() {
		podPress_wp_head();
		echo '<script type="text/javascript" src="'.podPress_url().'podpress_admin.js"></script>'."\n";
		if(function_exists('wp_admin_tiger_css')) {
			$admincss = 'podpress_admin_tigercheck.css';
		} else {
			$admincss = 'podpress_admin.css';
		}
		echo '<link rel="stylesheet" href="'.podPress_url().$admincss.'" type="text/css" />'."\n";
	}

	function podPress_activity_box() {
		GLOBAL $podPress, $wpdb;
		if($podPress->settings['enableStats']) {
			echo '<h3>podPress&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.mightyseek.com/podpress/#download" target="_new"><img src="http://www.mightyseek.com/podpress_downloads/versioncheck.php?current='.PODPRESS_VERSION.'" alt="'.__('Checking for updates... Failed', 'podpress').'" border="0" /></a></h3>'."\n";
			if($podPress->settings['enableStats']) {
				$sql = "SELECT SUM(total) as cnt_total,
				               SUM(feed) as cnt_feed ,
				               SUM(web) as cnt_web ,
				               SUM(play) as cnt_play
				               FROM ".$wpdb->prefix."podpress_statcounts";
				$stats = $wpdb->get_results($sql);
				if($stats) {
					echo '			<legend>'.__('Stats Summary', 'podpress').'</legend>'."\n";
					echo '			<table class="the-list-x" width="100%" cellpadding="1" cellspacing="1">'."\n";
					echo '				<tr><th>'.__('Feed', 'podpress').'</th><th>'.__('Web', 'podpress').'</th><th>'.__('Play', 'podpress').'</th><th>'.__('Total', 'podpress').'</th></tr>'."\n";
					echo '				<tr><td align="center">'.$stats[0]->cnt_feed.'</td><td align="center">'.$stats[0]->cnt_web.'</td><td align="center">'.$stats[0]->cnt_play.'</td><td align="center">'.$stats[0]->cnt_total.'</td></tr>'."\n";
					echo '			</table>'."\n";
				}
			}
		}
	}

	function podPress_admin_head () {
		GLOBAL $action;
		podPress_wp_head(false);
		echo '<script type="text/javascript" src="'.podPress_url().'podpress_admin.js"></script>'."\n";
		if(function_exists('wp_admin_tiger_css')) {
			$admincss = 'podpress_admin_tigercheck.css';
		} else {
			$admincss = 'podpress_admin.css';
		}
		echo '<link rel="stylesheet" href="'.podPress_url().$admincss.'" type="text/css" />'."\n";
		if ((strpos($_SERVER['REQUEST_URI'], 'plugins.php') !== false) && (podPress_remote_version_check() == 1)) {
			$atmp = parse_url(podPress_url());
			$xyz = $atmp['path'];

			echo "<script type='text/javascript' src='" . $xyz . "/prototype-1.4.0.js'></script>\n";
			$alert = "\n";
			$alert .= "\n<script type='text/javascript'>";
			$alert .= "\n//<![CDATA[";
			$alert .= "\nfunction alertNewPodPressVersion() {";
			$alert .= "\n	pluginname = 'podPress';";
			$alert .= "\n	allNodes = document.getElementsByClassName('name');";
			$alert .= "\n	for(i = 0; i < allNodes.length; i++) {";
			$alert .= "\n			var regExp=/<\S[^>]*>/g;";
			$alert .= "\n	    temp = allNodes[i].innerHTML;";
			$alert .= "\n	    if (temp.replace(regExp,'') == pluginname) {";
			$alert .= "\n		    Element.setStyle(allNodes[i].getElementsByTagName('a')[0], {color: '#f00'});";
			$alert .= "\n		    new Insertion.After(allNodes[i].getElementsByTagName('strong')[0],'<br/><small>" .  __('new version available', 'podpress') . "</small>');";
			$alert .= "\n	  	}";
			$alert .= "\n	}";
			$alert .= "\n}";

			$alert .= "\naddLoadEvent(alertNewPodPressVersion);";

			$alert .= "\n//]]>";
			$alert .= "\n</script>";
			$alert .= "\n";
			echo $alert;
		}
	}

	function podPress_admin_footer () {
		GLOBAL $action;
		if ((strpos($_SERVER['REQUEST_URI'], 'categories.php') !== false) && $action == 'edit') {
			//echo "<script type=\"text/javascript\">var x=1; var y = document.getElementByName('editcat').innerHTML; alert('y: '+y);</script>";
		}
	}

	function podPress_wp_footer() {
		GLOBAL $podPress, $podPress_inAdmin;
		if(!$podPress_inAdmin) {
			if(!$podPress->settings['compatibilityChecks']['themeTested']) {
				$podPress->settings['compatibilityChecks']['themeTested'] = true;
				podPress_update_option('podPress_config', $podPress->settings);
			}
		}
		if(!$podPress_inAdmin) {
			if(!$podPress->settings['compatibilityChecks']['wp_footer']) {
				$podPress->settings['compatibilityChecks']['wp_footer'] = true;
				podPress_update_option('podPress_config', $podPress->settings);
			} else {
				$podPress->settings['compatibilityChecks']['wp_footer'] = true;
			}
		}

		if($podPress->settings['enableFooter']) {
			$diplay = 'block';
		} else {
			$diplay = 'none';
		}
		echo '<div id="podPress_footer" style="display: '.$diplay.'; text-align: center;"><cite>'.__('Podcast Powered by ', 'podpress').'<a href="http://www.mightyseek.com/podpress/" title="podPress, '.__('the dream plugin for podcasting with WordPress', 'podpress').'"><strong>podPress (v'.PODPRESS_VERSION.')</strong></a></cite></div>';
	}

	function podPress_get_the_guid($guid) {
		GLOBAL $post, $wpdb;
		if(empty($guid)) {
			$guid = rand();
			if(is_object($post) && !empty($post->ID)) {
				$wpdb->query("UPDATE ".$wpdb->posts." SET guid = '".$guid."' WHERE ID=".$post->ID);
			}
		}
		return $guid;
	}

	function podPress_get_attached_file($file, $id='') {
		if(is_feed()) {
			return '';
		}	else {
			return $file;
		}
	}
	
	function podPress_wp_get_attachment_metadata($data, $id='') {
		if(is_feed()) {
			return '';
		}	else {
			return $file;
		}
	}

	function podPress_crossdomain() {
		header("HTTP/1.0 200 OK");
		header('Content-type: text/xml; charset=' . get_settings('blog_charset'), true);
		echo '<?xml version="1.0"?>'."\n";
		echo '<!DOCTYPE cross-domain-policy SYSTEM "http://www.macromedia.com/xml/dtds/cross-domain-policy.dtd">'."\n";
		echo '<cross-domain-policy>'."\n";
		echo '  <allow-access-from domain="*" />'."\n";
		echo '</cross-domain-policy>'."\n";
		exit;
	}

	function podPress_do_feed_rss2($withcomments) {
		podPress_addFeedHooks();
		if(!function_exists('do_feed_rss2')) {
			load_template(ABSPATH.'wp-rss2.php');
		}
	}

	function podPress_do_feed_premium($withcomments) {
		GLOBAL $cache_lastpostmodified;
		unset($_SERVER['HTTP_IF_MODIFIED_SINCE']);
		$cache_lastpostmodified = date('Y-m-d h:i:s', time()+36000);
		podPress_addFeedHooks();
		define('PREMIUMCAST', true);
		podPress_validateLogin();
		if(!function_exists('do_feed_rss2')) {
			load_template(ABSPATH.'wp-rss2.php');
		}
		podPress_do_feed_rss2($withcomments);
	}

	function podPress_do_feed_podcast($withcomments) {
		GLOBAL $wp_query;
		podPress_addFeedHooks();
		define('PODPRESS_PODCASTSONLY', true);
		$wp_query->get_posts();
		if(!function_exists('do_feed_rss2')) {
			load_template(ABSPATH.'wp-rss2.php');
		}
		do_feed_rss2($withcomments);
	}

	function podPress_do_feed_atom1() {
		podPress_addFeedHooks();
		load_template(ABSPATH.PLUGINDIR.'/podpress/wp-atom1.php');
	}

	function podPress_do_feed_torrent() {
		GLOBAL $podPress, $posts;
		podPress_addFeedHooks();
		define('PODPRESS_TORRENTCAST', true);
		$posts = $podPress->the_posts($posts);
		if(!function_exists('do_feed_rss2')) {
			load_template(ABSPATH.'wp-rss2.php');
		}
	}

	function podPress_do_feed_xspf() {
		GLOBAL $wp_query;
		podPress_addFeedHooks();
		define('PODPRESS_PODCASTSONLY', true);
		$wp_query->get_posts();
		podPress_xspf_playlist();
	}

	function podPress_addFeedHooks() {
		GLOBAL $podPress, $podPress_feedHooksAdded, $wp_query;
		$wp_query->is_feed = true;

		if(!$podPress_feedHooksAdded) {
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_feed_functions.php');
			podPress_checkmem('podPress feed functions loaded');
			add_filter('option_blogname', 'podPress_feedblogname');
			add_filter('option_blogdescription', 'podPress_feedblogdescription');
			add_filter('option_rss_language', 'podPress_feedblogrsslanguage');
			add_filter('option_rss_image', 'podPress_feedblogrssimage');

			/* stuff that goes in the rss feed */
			add_action('the_content_rss', array(&$podPress, 'insert_content'));
			add_action('rss2_ns', 'podPress_rss2_ns');
			add_action('rss2_head', 'podPress_rss2_head');
			add_action('rss2_item', 'podPress_rss2_item');

			/* stuff that goes in the atom feed */
			add_action('atom_head', 'podPress_atom_head');
			add_action('atom_entry', 'podPress_atom_entry');
			$podPress_feedHooksAdded = true;
		}
	}
}

	/*************************************************************/
	/* !!! BEGINNING OF THE ACTION !!!                           */
	/*************************************************************/

	/*************************************************************/
	/* Create the podPress object                                */
	/*************************************************************/

	if(!is_object ($podPress)) {
		if(get_option('podPress_version') < PODPRESS_VERSION) {
			$podPress_inUpgrade = true;
		} else {
			$podPress_inUpgrade = false;
		}
		$podPress_inAdmin = strpos($_SERVER['REQUEST_URI'], 'wp-admin');
		if($podPress_inUpgrade) {
			podPress_checkmem('podpress admin class loaded', true);
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_functions.php');
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_upgrade_class.php');
			podPress_checkmem('podpress upgrade class loaded');
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_class.php');
			podPress_checkmem('podpress admin class loaded');
			$podPress = new podPressUpgrade_class(get_option('podPress_version'));
			header('Location: '.$_SERVER['REQUEST_URI']);
			exit;
		} elseif ($podPress_inAdmin) {
			podPress_checkmem('podpress admin functions loaded', true);
			require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_functions.php');
			podPress_checkmem('podpress admin functions loaded');
			if(isset($_GET['page'])) {
				$podPress_adminPage = $_GET['page'];
			} elseif(isset($_POST['podPress_submitted'])) {
				$podPress_adminPage = 'podpress/podpress_'.$_POST['podPress_submitted'].'.php';
			} else {
				$podPress_adminPage = 'usedefault';
			}
			switch($podPress_adminPage) {
				case 'podpress/podpress_general.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_general_class.php');
					podPress_checkmem('admin general code loaded');
					break;
				case 'podpress/podpress_feed.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_feed_class.php');
					podPress_checkmem('admin feed code loaded');
					break;
				case 'podpress/podpress_players.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_player_class.php');
					podPress_checkmem('admin player code loaded');
					break;
				case 'podpress/podpress_stats.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_stats_class.php');
					podPress_checkmem('admin stats code loaded');
					break;
				case 'podpress/podpress_podango.php':
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_podango_class.php');
					podPress_checkmem('admin podango code loaded');
					break;
				default:
					require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_class.php');
					podPress_checkmem('admin code loaded');
			}

			$podPress = new podPressAdmin_class();
			
			if($podPress->settings['enablePodangoIntegration']) {
				podPress_checkmem('PodangoAPI code loaded', true);
				require_once(ABSPATH.PLUGINDIR.'/podpress/podango-api.php');
				$podPress->podangoAPI = new PodangoAPI ($podPress->settings['podangoUserKey'], $podPress->settings['podangoPassKey']);
				if(!empty($podPress->settings['podangoDefaultPodcast'])) {
					$podPress->podangoAPI->defaultPodcast = $podPress->settings['podangoDefaultPodcast'];
				}
				if(!empty($podPress->settings['podangoDefaultTranscribe'])) {
					$podPress->podangoAPI->defaultTranscribe = (int)$podPress->settings['podangoDefaultTranscribe'];
				}
				podPress_checkmem('PodangoAPI code loaded');
			}
		} else {
			podPress_checkmem('standard podPress class loaded', true);
			$podPress = new podPress_class;
			podPress_checkmem('standard podPress class loaded');
		}
	}
	/*************************************************************/
	/* Register all the actions and filters                      */
	/*************************************************************/
	/* Add podpress data to each post */
	if(!podPress_WPVersionCheck()) {
		// WP 1.5 legacy vars support
		if(isset($table_prefix) && !isset($wpdb->prefix)) {
			$wpdb->prefix = $table_prefix;
		}
		if(isset($tablecomments) && !isset($wpdb->comments)) {
			$wpdb->comments = $tablecomments;
		}
	}

	add_action('init', 'podPress_init');

	/* Add podpress data to each post */
	if(podPress_WPVersionCheck()) {
		add_action('the_posts', array(&$podPress, 'the_posts'));
	} else {
		add_filter('the_posts', array(&$podPress, 'the_posts'));
	}

	add_action('xmlrpc-mw_newPost', array(&$podPress, 'xmlrpc_post_addMedia'));
	add_action('xmlrpc-mw_editPost', array(&$podPress, 'xmlrpc_post_addMedia'));

	add_filter('posts_join', array(&$podPress, 'posts_join'));
	add_filter('posts_where', array(&$podPress, 'posts_where'));

	/* stuff that goes in the display of the Post */
	add_action('the_content', array(&$podPress, 'insert_content'));
	add_action('get_the_excerpt', array(&$podPress, 'insert_the_excerpt'), 1);
	add_action('the_excerpt', array(&$podPress, 'insert_the_excerptplayer'));

	add_filter('get_attached_file', 'podPress_get_attached_file');
	add_filter('wp_get_attachment_metadata', 'podPress_wp_get_attachment_metadata');
	
	/* stuff that goes in the HTML header */
	add_action('wp_head', 'podPress_wp_head');
	add_action('wp_footer', 'podPress_wp_footer');
	add_action('switch_theme', 'podPress_switch_theme');

	/* misc stuff */
	add_action('activity_box_end', 'podPress_activity_box');
	if($podPress->settings['enableStats'] == true) {
		add_action('template_redirect', 'podPress_statsDownloadRedirect');
	}
	add_filter('get_the_guid', 'podPress_get_the_guid');

	/* stuff that goes into all feeds */

	if(is_feed()) {
		podPress_addFeedHooks();
	}

	/* Widgets */
	add_action('widgets_init', 'podPress_loadWidgets');

	/* stuff for premium podcasts */
	if($podPress->settings['enablePremiumContent']) {
		require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_premium_functions.php');
		podPress_checkmem('premium functions included');
		add_action('wp_login', 'podpress_adddigestauth');
	}

	/* stuff that goes into setting up the site for podpress */
	if($podPress_inAdmin) {
		add_action('activate_podpress/podpress.php', array(&$podPress, 'activate'));
		add_action('deactivate_podpress/podpress.php', array(&$podPress, 'deactivate'));

		/* if this is an admin page, run the function to add podpress tab to options menu */
		add_action('admin_menu', 'podPress_add_menu_page');
		add_action('admin_head', 'podPress_admin_head');
		add_action('admin_footer', 'podPress_admin_footer');

		add_action('simple_edit_form', array(&$podPress, 'post_form'));
		add_action('edit_form_advanced', array(&$podPress, 'post_form'));
		add_action('edit_page_form', array(&$podPress, 'page_form'));
		add_action('save_post', array(&$podPress, 'post_edit'));
		add_action('edit_post', array(&$podPress, 'post_edit')); // seems to be a duplicate

		/* stuff that goes in the category */
		add_action('create_category', array(&$podPress, 'edit_category'));
		add_action('edit_category_form', array(&$podPress, 'edit_category_form'));
		add_action('edit_category', array(&$podPress, 'edit_category'));
		add_action('delete_category', array(&$podPress, 'delete_category'));

		/* stuff for editing settings */
		if(isset($_POST['podPress_submitted']) && method_exists($podPress,'settings_'.$_POST['podPress_submitted'].'_save')) {
			$funcnametouse = 'settings_'.$_POST['podPress_submitted'].'_save';
			$podPress->$funcnametouse();
		}

		/* stuff for editing settings */
		$wp_importers['podcast'] = array ('Podcast RSS2', 'podPress import of posts from a Podcast RSS2 feed.', array(&$podPress, 'import_dispatch'));
		//if(function_exists('register_importer')) {
		//	register_importer('podcast', __('Podcast RSS2'), __('Import posts from an RSS2 Podcast feed'), array (&$podPress, 'import_dispatch'));
		//}

	} else {
	}

	//podPress_checkmem('podPress end');
	if(!function_exists('podPress_shutdown')) {
		function podPress_shutdown() {
			GLOBAL $podPress_memoryUsage, $podPress_memoryIncrease;
			if($_GET['podpress_showmem'] == 1) {
				echo "Total podpress mem: ".podPress_bytes($podPress_memoryIncrease)." out of a total ".podPress_bytes(memory_get_usage())."<br/>\n";
			}

			if($_GET['podpress_showmem'] == 2) {
				html_print_r($podPress_memoryUsage);
			} elseif($_GET['podpress_showmem'] == 3) {
				comment_print_r($podPress_memoryUsage);
			}
		}
		add_action( 'shutdown', 'podPress_shutdown', 1);
	}
