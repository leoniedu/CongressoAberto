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
	/*************************************************************/
	/* feed generation functions                                 */
	/*************************************************************/

	function podPress_feedSafeContent($input, $aggressive = false)
	{
		GLOBAL $podPress;
		if(!$podPress->settings['protectFeed'] && !$aggressive) {
			return $input;
		}
		$result = htmlentities($input, ENT_NOQUOTES, 'UTF-8');
		if($aggressive) {
			$result = str_replace(array('&amp;', '&lt;', '&gt;', '&'), '', $result);
		}
		return $result;
	}

	function podPress_rss2_ns() {
		echo 'xmlns:itunes="http://www.itunes.com/dtds/podcast-1.0.dtd"'."\n";
		//echo '	xmlns:dtvmedia="http://participatoryculture.org/RSSModules/dtv/1.0"'."\n";
		echo '	xmlns:media="http://search.yahoo.com/mrss/"'."\n";
	}

	function podPress_rss2_head() {
		GLOBAL $podPress, $post, $post_meta_cache, $blog_id;
		if($podPress->settings['enablePremiumContent']) {
			podPress_reloadCurrentUser();
		}
		if(is_array($post_meta_cache[$blog_id])) {
			foreach($post_meta_cache[$blog_id] as $key=>$val) {
				if(isset($post_meta_cache[$blog_id][$key]['enclosure']) && isset($post_meta_cache[$blog_id][$key]['podPressMedia'])) {
					$post_meta_cache[$blog_id][$key]['enclosure_podPressHold'] = $post_meta_cache[$blog_id][$post->ID]['enclosure'];
					unset($post_meta_cache[$blog_id][$key]['enclosure']);
				}
			}
		}

		if(!isset($podPress->settings['category_data'])) {
			podPress_feed_getCategory();
		}
		$data = $podPress->settings['iTunes'];

		$data['podcastFeedURL'] = $podPress->settings['podcastFeedURL'];

		$data['rss_image'] = get_option('rss_image');
		$data['admin_email'] = stripslashes(get_option('admin_email'));

		if($podPress->settings['category_data']['categoryCasting'] == 'true') {
			$data['podcastFeedURL'] = $podPress->settings['category_data']['podcastFeedURL'];

			if($podPress->settings['category_data']['iTunesNewFeedURL'] != '##Global##') {
				$data['new-feed-url'] = $podPress->settings['category_data']['iTunesNewFeedURL'];
			}

			if($podPress->settings['category_data']['iTunesSummaryChoice'] == 'Custom') {
				$data['summary'] = $podPress->settings['category_data']['iTunesSummary'];
			}

			if($podPress->settings['category_data']['iTunesSubtitleChoice'] == 'Custom') {
				$data['subtitle'] = $podPress->settings['category_data']['iTunesSubtitle'];
			}

			if($podPress->settings['category_data']['iTunesKeywordsChoice'] == 'Custom') {
				$data['keywords'] = $podPress->settings['category_data']['iTunesKeywords'];
			}

			if($podPress->settings['category_data']['iTunesAuthorChoice'] == 'Custom' && !empty($podPress->settings['category_data']['iTunesAuthor'])) {
				$data['author'] = $podPress->settings['category_data']['iTunesAuthor'];
			}
			if($podPress->settings['category_data']['iTunesAuthorEmailChoice'] == 'Custom') {
				$data['admin_email'] = $podPress->settings['category_data']['iTunesAuthorEmail'];
			}

			if($podPress->settings['category_data']['iTunesBlock'] != '##Global##' && !empty($podPress->settings['category_data']['iTunesBlock'])) {
				$data['block'] = $podPress->settings['category_data']['iTunesBlock'];
			}
			if($podPress->settings['category_data']['iTunesExplicit'] != '##Global##' && !empty($podPress->settings['category_data']['iTunesExplicit'])) {
				$data['explicit'] = $podPress->settings['category_data']['iTunesExplicit'];
			}
			if($podPress->settings['category_data']['iTunesImageChoice'] == 'Custom') {
				$data['image'] = $podPress->settings['category_data']['iTunesImage'];
			}
			if($podPress->settings['category_data']['rss_imageChoice'] == 'Custom') {
				$data['rss_image'] = $podPress->settings['category_data']['rss_image'];
			}
			if($podPress->settings['category_data']['rss_copyrightChoice'] == 'Custom') {
				$data['rss_copyright'] = $podPress->settings['category_data']['rss_copyright'];
			}
		}

		$data['rss_ttl'] = get_option('rss_ttl');
		if(!empty($data['rss_ttl']) && $data['rss_ttl'] < 1440) {
			$data['rss_ttl'] = 1440;
		}
		echo '	<!-- podcast_generator="podPress/'.PODPRESS_VERSION.'" -->'."\n";
		echo '		<copyright>&#xA9;'.podPress_feedSafeContent($data['author']).' '.podPress_feedSafeContent($data['rss_copyright']).'</copyright>'."\n";
		if($data['new-feed-url'] == 'Enable') {
			if(!empty($data['podcastFeedURL']) && !strpos(strtolower($data['podcastFeedURL']), 'phobos.apple.com') && !strpos(strtolower($data['podcastFeedURL']), 'itpc://')) {
				echo '		<itunes:new-feed-url>'.podPress_feedSafeContent($data['podcastFeedURL']).'</itunes:new-feed-url>'."\n";
			}
		}
		echo '		<managingEditor>'.podPress_feedSafeContent(stripslashes(get_option('admin_email'))).' ('.podPress_feedSafeContent($podPress->settings['iTunes']['author']).')</managingEditor>'."\n";
		echo '		<webMaster>'.podPress_feedSafeContent(get_option('admin_email')).'('.podPress_feedSafeContent($data['author']).')</webMaster>'."\n";
		echo '		<category>'.podPress_feedSafeContent($podPress->settings['rss_category']).'</category>'."\n";
		if(!empty($data['rss_ttl'])) {
			echo '		<ttl>'.$data['rss_ttl'].'</ttl>'."\n";
		}
		echo '		<itunes:keywords>'.podPress_stringLimiter(podPress_feedSafeContent($data['keywords'], true), 255).'</itunes:keywords>'."\n";
		echo '		<itunes:subtitle>'.podPress_stringLimiter(podPress_feedSafeContent($data['subtitle'], true), 255).'</itunes:subtitle>'."\n";
		echo '		<itunes:summary>'.podPress_stringLimiter(podPress_feedSafeContent($data['summary'], true), 4000).'</itunes:summary>'."\n";
		echo '		<itunes:author>'.podPress_feedSafeContent($data['author']).'</itunes:author>'."\n";
		echo '		' .podPress_getiTunesCategoryTags();
		echo '		<itunes:owner>'."\n";
		echo '			<itunes:name>'.stripslashes(podPress_feedSafeContent($data['author'])).'</itunes:name>'."\n";
		echo '			<itunes:email>'.podPress_feedSafeContent($data['admin_email']).'</itunes:email>'."\n";
		echo '		</itunes:owner>'."\n";
		if(empty($data['block'])) {
			$data['block'] = 'No';
		}
		echo '		<itunes:block>'.$data['block'].'</itunes:block>'."\n";
		echo '		<itunes:explicit>'.podPress_feedSafeContent(strtolower($data['explicit'])).'</itunes:explicit>'."\n";
		echo '		<itunes:image href="'.$data['image'].'" />'."\n";
		echo '		<image>'."\n";
		echo '			<url>'.podPress_feedSafeContent($data['rss_image']).'</url>'."\n";
		echo '			<title>'; bloginfo_rss('name'); echo '</title>'."\n";
		echo '			<link>'; bloginfo_rss('url'); echo '</link>'."\n";
		echo '			<width>144</width>'."\n";
		echo '			<height>144</height>'."\n";
		echo '		</image>'."\n";
	}

	function podPress_rss2_item() {
		GLOBAL $podPress, $post, $post_meta_cache, $blog_id;
		$enclosureTag = podPress_getEnclosureTags();
		if($enclosureTag != '') // if no enclosure tag, no need for iTunes tags
		{
			echo '		' . $enclosureTag;

			if($post->podPressPostSpecific['itunes:subtitle'] == '##PostExcerpt##') {
				ob_start();
				the_content_rss('', false, 0, 25);
				$data = ob_get_contents();
				ob_end_clean();
				$post->podPressPostSpecific['itunes:subtitle'] = substr(ltrim($data), 0, 254);
			}
			if(empty($post->podPressPostSpecific['itunes:subtitle'])) {
				$post->podPressPostSpecific['itunes:subtitle'] = get_the_title_rss();
			}
			echo '		<itunes:subtitle>'.podPress_feedSafeContent($post->podPressPostSpecific['itunes:subtitle'], true).'</itunes:subtitle>'."\n";

			if($post->podPressPostSpecific['itunes:summary'] == '##Global##') {
				$post->podPressPostSpecific['itunes:summary'] = $podPress->settings['iTunes']['summary'];
			}
			if(empty($post->podPressPostSpecific['itunes:summary']) || $post->podPressPostSpecific['itunes:summary'] == '##PostExcerpt##') {
				ob_start();
				the_content_rss('', false, 0, '', 2);
				$data = ob_get_contents();
				ob_end_clean();
				$post->podPressPostSpecific['itunes:summary'] = substr(ltrim($data), 0, 4000);
			}
			if(empty($post->podPressPostSpecific['itunes:summary'])) {
				$post->podPressPostSpecific['itunes:summary'] = $podPress->settings['iTunes']['summary'];
			}
			echo '		<itunes:summary>'.podPress_stringLimiter(podPress_feedSafeContent($post->podPressPostSpecific['itunes:summary'], true), 4000).'</itunes:summary>'."\n";

			if($post->podPressPostSpecific['itunes:keywords'] == '##WordPressCats##') {
				$categories = get_the_category();
				$post->podPressPostSpecific['itunes:keywords'] = '';
				if(is_array($categories)) {
					foreach ($categories as $category) {
						$category->cat_name = $category->cat_name;
						if($post->podPressPostSpecific['itunes:keywords'] != '') {
							$post->podPressPostSpecific['itunes:keywords'] .= ', ';
						}
						$post->podPressPostSpecific['itunes:keywords'] .= $category->cat_name;
					}
					$post->podPressPostSpecific['itunes:keywords'] = trim($post->podPressPostSpecific['itunes:keywords']);
				}
			} elseif($post->podPressPostSpecific['itunes:keywords'] == '##Global##') {
				$post->podPressPostSpecific['itunes:keywords'] = $podPress->settings['iTunes']['keywords'];
			}
			echo '		<itunes:keywords>'.podPress_stringLimiter(podPress_feedSafeContent(str_replace(' ', ',', $post->podPressPostSpecific['itunes:keywords']), true), 255).'</itunes:keywords>'."\n";

			if($post->podPressPostSpecific['itunes:author'] == '##Global##') {
				$post->podPressPostSpecific['itunes:author'] = $podPress->settings['iTunes']['author'];
				if(empty($post->podPressPostSpecific['itunes:author'])) {
					$post->podPressPostSpecific['itunes:author'] = stripslashes(get_option('admin_email'));
				}
			}
			echo '		<itunes:author>'.podPress_feedSafeContent($post->podPressPostSpecific['itunes:author'], true).'</itunes:author>'."\n";

			if($post->podPressPostSpecific['itunes:explicit'] == 'Default') {
				$post->podPressPostSpecific['itunes:explicit'] = $podPress->settings['iTunes']['explicit'];
				if(empty($post->podPressPostSpecific['itunes:explicit'])) {
					$post->podPressPostSpecific['itunes:explicit'] = 'No';
				}
			}
			echo '		<itunes:explicit>'.podPress_feedSafeContent(strtolower($post->podPressPostSpecific['itunes:explicit'])).'</itunes:explicit>'."\n";

			if($post->podPressPostSpecific['itunes:block'] == 'Default') {
				$post->podPressPostSpecific['itunes:block'] = $podPress->settings['iTunes']['block'];
				if(empty($post->podPressPostSpecific['itunes:block'])) {
					$post->podPressPostSpecific['itunes:block'] = 'No';
				}
			}
			if(empty($post->podPressPostSpecific['itunes:block'])) {
				$post->podPressPostSpecific['itunes:block'] = 'No';
			}
			echo '		<itunes:block>'.podPress_feedSafeContent($post->podPressPostSpecific['itunes:block']).'</itunes:block>'."\n";
			//echo '<comments>'. get_comments_link() .'</comments>'."\n";
		}
		if(isset($post_meta_cache[$blog_id][$post->ID]['enclosure_podPressHold'])) {
			$post_meta_cache[$blog_id][$post->ID]['enclosure'] = $post_meta_cache[$blog_id][$post->ID]['enclosure_podPressHold'];
			unset($post_meta_cache[$blog_id][$post->ID]['enclosure_podPressHold']);
		}
	}

	function podPress_atom_head() {
		GLOBAL $podPress;
		if(!isset($podPress->settings['category_data'])) {
			podPress_feed_getCategory();
		}
		echo '	<!-- podcast_generator="podPress/'.PODPRESS_VERSION.'" -->'."\n";
		if($podPress->settings['category_data']['categoryCasting'] == 'true' && $podPress->settings['category_data']['rss_imageChoice'] == 'Custom') {
			echo '	<icon>'.podPress_feedSafeContent($podPress->settings['category_data']['rss_image']).'</icon>'."\n";
		} else {
			echo '	<icon>'.podPress_feedSafeContent(get_option('rss_image')).'</icon>'."\n";
		}
	}

	function podPress_atom_entry() {
		$enclosureTag = podPress_getEnclosureTags('atom');
		if($enclosureTag != '') // if no enclosure tag, no need for iTunes tags
		{
			echo '		' . $enclosureTag;
		}
	}

	function podPress_xspf_playlist() {
		GLOBAL $more, $posts, $m;
		header("HTTP/1.0 200 OK");
		header('Content-type: application/xspf+xml; charset=' . get_settings('blog_charset'), true);
		$more = 1;
		echo '<?xml version="1.0" encoding="'.get_settings('blog_charset').'" ?'.">\n";
		echo '<playlist version="0" xmlns="http://xspf.org/ns/0/">'."\n";
		echo "  <title>"; bloginfo_rss('name'); echo "</title>\n";
		echo "  <annotation></annotation>\n";
		echo "  <creator>"; the_author(); echo "</creator>\n";
		echo "  <location>"; bloginfo_rss('url'); echo "</location>\n";
		echo "  <license>http://creativecommons.org/licenses/by-sa/1.0/</license>\n";
		echo "  <trackList>\n";
		if ($posts) {
			foreach ($posts as $post) {
				start_wp();
				$enclosureTag = podPress_getEnclosureTags('xspf');
				if($enclosureTag != '') // if no enclosure tag, no need for track tags
				{
					echo "    <track>\n";
					echo $enclosureTag;
					echo "    </track>\n";
				}
			}
		}
		echo "  </trackList>\n";
		echo "</playlist>\n";
		exit;
	}

	function podPress_getEnclosureTags($feedtype = 'rss2') {
		GLOBAL $podPress, $post;
		$result = '';
		$hasMediaFileAccessible = false;
		if(is_array($post->podPressMedia)) {
			$foundPreferred = false;
			reset($post->podPressMedia);
			while (list($key, $val) = each($post->podPressMedia)) {
				$preferredFormat = false;
				if(!$post->podPressMedia[$key]['authorized']) {
					if($podPress->settings['premiumContentFakeEnclosure']) {
						$post->podPressMedia[$key]['URI'] = 'podPress_Protected_Content.mp3';
						} else {
						continue;
					}
				}
				if(defined('PODPRESS_TORRENTCAST') && !empty($post->podPressMedia[$key]['authorized']['URI_torrent'])) {
					$post->podPressMedia[$key]['URI'] = $post->podPressMedia[$key]['URI_torrent'];
				}
				$hasMediaFileAccessible = true;

				if(isset($_GET['onlyformat']) && $_GET['onlyformat'] != $post->podPressMedia[$key]['ext']) {
					continue;
				}

				if(isset($_GET['format']) && $_GET['format'] == $post->podPressMedia[$key]['ext']) {
					$preferredFormat = true;
				}
				if($post->podPressMedia[$key]['rss'] == 'on' || $post->podPressMedia[$key]['atom'] == 'on' || $preferredFormat == true) {
					if($feedtype == 'atom' && $post->podPressMedia[$key]['atom'] == 'on') {
						$post->podPressMedia[$key]['URI'] = $podPress->convertPodcastFileNameToWebPath($post->ID, $key, $post->podPressMedia[$key]['URI'], 'feed');
						$result .= '<link rel="enclosure" type="'.$post->podPressMedia[$key]['mimetype'].'" href="'.$post->podPressMedia[$key]['URI'].'" length="'.$post->podPressMedia[$key]['size'].'" />'."\n";
					} elseif($feedtype == 'xspf') {
						$post->podPressMedia[$key]['URI'] = $podPress->convertPodcastFileNameToValidWebPath($post->podPressMedia[$key]['URI']);
						if(podPress_getFileExt($post->podPressMedia[$key]['URI']) == 'mp3') {
							$result .= '      <location>'.$post->podPressMedia[$key]['URI']."</location>\n";
							if(!empty($post->podPressMedia[$key]['title'])) {
								$result .= '      <annotation>'.podPress_feedSafeContent($post->podPressMedia[$key]['title'])."</annotation>\n";
								$result .= '      <title>'.podPress_feedSafeContent($post->podPressMedia[$key]['title'])."</title>\n";
							} else {
								$result .= '      <annotation>'.podPress_feedSafeContent($post->post_title)."</annotation>\n";
								$result .= '      <title>'.podPress_feedSafeContent($post->post_title)."</title>\n";
							}
							if(!empty($post->podPressMedia[$key]['previewImage'])) {
									$result .= '      <image>'.$post->podPressMedia[$key]['previewImage']."</image>\n";
							}
					 }
					} elseif($feedtype == 'rss2') {
						$post->podPressMedia[$key]['URI'] = $podPress->convertPodcastFileNameToWebPath($post->ID, $key, $post->podPressMedia[$key]['URI'], 'feed');
						if(!isset($post->podPressMedia[$key]['duration']) || !preg_match("/([0-9]):([0-9])/", $post->podPressMedia[$key]['duration'])) {
							$post->podPressMedia[$key]['duration'] = '00:01:01';
						}
						$durationTag = '<itunes:duration>'.$post->podPressMedia[$key]['duration'].'</itunes:duration>'."\n";

						if($post->podPressMedia[$key]['rss'] == 'on') {
							if(!$preferredFormat && $foundPreferred) {
								continue;
							} elseif($preferredFormat) {
								$foundPreferred = true;
							}
							$result = '<enclosure url="'.$post->podPressMedia[$key]['URI'].'" length="'.$post->podPressMedia[$key]['size'].'" type="'.$post->podPressMedia[$key]['mimetype'].'"/>'."\n";
							$result .= $durationTag;
						} elseif($preferredFormat && !$foundPreferred) {
							$result = '<enclosure url="'.$post->podPressMedia[$key]['URI'].'" length="'.$post->podPressMedia[$key]['size'].'" type="'.$post->podPressMedia[$key]['mimetype'].'"/>'."\n";
							$result .= $durationTag;
							$foundPreferred = true;
						}
					}
				}
			}
		}
		if($hasMediaFileAccessible && $result == '' && $feedtype != 'xspf') {
			echo "<!-- Media File exists for this post, but its not enabled for this feed -->\n";
		}
		return $result;
	}

	function podPress_getiTunesCategoryTags() {
		GLOBAL $podPress, $post;
		$result = '';
		$data = array();
		if($podPress->settings['category_data']['categoryCasting'] == 'true' && is_array($podPress->settings['category_data']['iTunesCategory'])) {
			foreach ($podPress->settings['category_data']['iTunesCategory'] as $key=>$value) {
				if($value == '##Global##') {
					if(!empty($podPress->settings['iTunes']['category'][$key])) {
						$data[] = $podPress->settings['iTunes']['category'][$key];
					}
				} else {
					$data[] = $value;
				}
			}
		}
		if(empty($data)) {
			$data = $podPress->settings['iTunes']['category'];
		}
		if(is_array($data)) {
			foreach($data as $thiscat) {
				if(strstr($thiscat, ':')) {
					list($cat, $subcat) = explode(":", $thiscat);
					$result .= '<itunes:category text="'.str_replace('&', '&amp;', $cat).'">'."\n";
					$result .= '  <itunes:category text="'.str_replace('&', '&amp;', $subcat).'"/>'."\n";
					$result .= '</itunes:category>'."\n";
				}
				elseif(!empty($thiscat))
				{
					$result .= '<itunes:category text="'.str_replace('&', '&amp;', $thiscat).'"/>'."\n";
				}
			}
		}
		if(empty($result)) {
			$result .= '<itunes:category text="Society &amp; Culture"/>'."\n";
		}
		return $result;
	}

	function podPress_feed_getCategory() {
		GLOBAL $podPress, $wpdb, $wp_query;
		if(!is_category()) {
			$podPress->settings['category_data'] = false;
			return $podPress->settings['category_data'];
		}
		$current_catid = $wp_query->get('cat');
		$category = get_category($current_catid);

		$data = podPress_get_option('podPress_category_'.$category->cat_ID);
		$data['id'] = $category->cat_ID;
		$data['blogname'] = $category->cat_name;
		$data['blogdescription'] = $category->category_description;
		$podPress->settings['category_data'] = $data;
		return $podPress->settings['category_data'];

		// old version of this function
		if(!is_category()) {
			//return false;
		}
		$byName = single_cat_title('', false);

		$categories = get_the_category();
		if(is_array($categories)) {
			foreach ($categories as $category) {
				$thisisit = false;
				if($byName == $category->cat_name) {
					$thisisit = true;
				}

				if($thisisit) {
					$data = podPress_get_option('podPress_category_'.$category->cat_ID);
					$data['id'] = $category->cat_ID;
					$data['blogname'] = $category->cat_name;
					$data['blogdescription'] = $category->category_description;
					$podPress->settings['category_data'] = $data;
					return $podPress->settings['category_data'];
				}
			}
		}
		$podPress->settings['category_data'] = false;
		return $podPress->settings['category_data'];
	}

	function podPress_getCategoryCastingFeedData ($selection, $input) {
		GLOBAL $podPress, $feed;
		if(!isset($podPress->settings['category_data'])) {
			podPress_feed_getCategory();
		}

		if(empty($feed) || $podPress->settings['category_data'] === false) {
			return $input;
		} else {
			if(empty($podPress->settings['category_data']['categoryCasting'])) {
				$podPress->settings['category_data']['categoryCasting'] = 'true';
			}

			switch($selection) {
				case 'blogname':
					switch($podPress->settings['category_data']['blognameChoice']) {
						case 'CategoryName':
							if(empty($podPress->settings['category_data']['blogname'])) {
								return $input;
							} else {
								return $podPress->settings['category_data']['blogname'];
							}
							break;
						case 'Append':
							if(empty($podPress->settings['category_data']['blogname'])) {
								return $input;
							} else {
								return $input.' : '.$podPress->settings['category_data']['blogname'];
							}
							break;
						default:
							return $input;
							break;
					}
					break;
				case 'blogdescription':
					if($podPress->settings['category_data']['blogdescriptionChoice'] == 'CategoryDescription' && !empty($podPress->settings['category_data']['blogdescription'])) {
						return $podPress->settings['category_data']['blogdescription'];
					}
					return $input;
					break;
				case 'rss_language':
					if($podPress->settings['category_data']['rss_language'] == '##Global##' || empty($podPress->settings['category_data']['rss_language'])) {
						return $input;
					} else {
						return $podPress->settings['category_data']['rss_language'];
					}
					break;
					case 'rss_image':
					if($podPress->settings['category_data']['rss_imageChoice'] == 'Global' || empty($podPress->settings['category_data']['rss_image'])) {
						return $input;
					} else {
						return $podPress->settings['category_data']['rss_image'];
					}
					break;
				default:
					return $input;
					break;
			}
		}
	}

	function podPress_feedBlogName ($input) {
		return podPress_getCategoryCastingFeedData('blogname', $input);
	}

	function podPress_feedBlogDescription ($input) {
		return podPress_getCategoryCastingFeedData('blogdescription', $input);
	}

	function podPress_feedBlogRssLanguage ($input) {
		return podPress_getCategoryCastingFeedData('rss_language', $input);
	}

	function podPress_feedBlogRssImage ($input) {
		return podPress_getCategoryCastingFeedData('rss_image', $input);
	}
