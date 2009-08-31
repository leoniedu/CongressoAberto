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
	if(!defined('PLUGINDIR')) {
		$pos = strpos($_SERVER['REQUEST_URI'], 'wp-content');
		header('Location: '.substr($_SERVER['REQUEST_URI'], 0, $pos));
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
		} elseif(1==2) {
//		} elseif(true) {
			echo '<object width="150" height="170" ';
			echo '  classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" ';
			echo '  codebase="http://fpdownload.macromedia.com/pub/ ';
			echo '  shockwave/cabs/flash/swflash.cab#version=8,0,0,0">'."\n";
			echo '  <param name="movie" value="'.podPress_url().'players/mp3player.swf" />'."\n";
			echo '	<param name="file" value="'.get_settings('siteurl').'/playlist.xspf" />'."\n";
			echo '	<param name="flashvars" value="showdigits=false&lightcolor=0xcc0000&autoscroll=true&showeq=true" />'."\n";
			echo '  <embed src="'.podPress_url().'players/mp3player.swf" width="150" height="170" ';
			echo '    type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" ';
			echo '		flashvars="file='.get_settings('siteurl').'/playlist.xspf&showdigits=false&lightcolor=0xcc0000&autoscroll=true&showeq=true"'."\n";
			echo '     />'."\n";
			echo '</object>'."\n";

/*
			echo '<object type="application/x-shockwave-flash" width="150" height="170" ';
			echo '	data="'.podPress_url().'mp3player.swf?file='.get_settings('siteurl').'/playlist.xspf&showdigits=false&lightcolor=0xcc0000&autoscroll=true&showeq=true">'."\n";
			echo '	<param name="file" value="'.get_settings('siteurl').'/playlist.xspf" />'."\n";
			echo '	<param name="flashvars" value="showdigits=false&lightcolor=0xcc0000&autoscroll=true&showeq=true" />'."\n";
			//echo '	<embed src="'.podPress_url().'mp3player.swf" width="150" height="200" bgcolor="#FFFFFF"'."\n";
			//echo '		type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" '."\n";
			//echo '		flashvars="file='.get_settings('siteurl').'/playlist.xspf&showdigits=false&lightcolor=0xcc0000&autoscroll=true&showeq=true" />'."\n";
			echo '</object>'."\n";
 */
		} else {
			echo '<object type="application/x-shockwave-flash" width="150" height="170" ';
			echo 'data="'.podPress_url().'players/xspf_player.swf?playlist_url='.get_settings('siteurl').'/playlist.xspf">'."\n";
			echo '	<param name="movie" value="'.podPress_url().'players/xspf_player.swf?playlist_url='.get_settings('siteurl').'/playlist.xspf" />'."\n";
			echo '</object>'."\n";
		}
		echo $after_widget;
	}

