<?php
/*
License:
 ==============================================================================

    Copyright 2006  Dan Kuykendall  (email : dan@kuykendall.org)
    Modifications:  Jeff Norris     (email : jeff@iscifi.tv)
    Thanks to The P2R Team ( prepare2respond.com ) for stats suggestions.


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
class podPressAdmin_class extends podPress_class {
	function podPressAdmin_class() {
		GLOBAL $wpdb;
		$this->path = get_option('siteurl').'/wp-admin/admin.php?page=podpress/podpress_stats.php';
		if (isset($_GET['display']) && is_string($_GET['display'])) {
			$this->path .= '&amp;display='.$_GET['display'];
		}

		$this->wpdb                 = $wpdb;

		/* set default language for graphs */
		$languages        = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$primary_language = $languages[0];
		if (in_array($primary_language, array('de-de', 'de'))) {
			/* German specification */
			setlocale(LC_TIME, 'de_DE@euro:UTF-8', 'de_de:UTF-8', 'de_DE:UTF-8', 'de:UTF-8', 'ge:UTF-8', 'German_Germany.1252:UTF-8');

			$local_settings = array(
			                        'numbers'       => array(',', '.'),
			                        'short_date'    => '%a.%n%d.%m.%y',
			                        'creation_date' => '%d.%m.%y',
			                      );
		} else {
			/* default specification: english */
			setlocale(LC_TIME, 'en_US:UTF-8', 'en_en:UTF-8', 'en:UTF-8');

			$local_settings = array(
			                        'numbers' => array('.', ','),
			                        'short_date' => '%a.%n%m/%d/%y',
			                        'creation_date' => '%m/%d/%y',
			                       );
		}
		$this->local_settings = $local_settings;

		$this->podPress_class();
		return;
	}

	#############################################
	#############################################

	function podSafeDigit($digit = 0) {
		if (is_numeric($digit) && ($digit < 10000)) {
			$digit = abs((int)$digit);
		} else {
			$digit = 0;
		}
		return $digit;
	}

	#############################################
	#############################################

	function paging($start, $limit, $total, $text = null) {
		$pages      = ceil($total / $limit);
		$actualpage = ceil($start / $limit);

		if ($pages > 0) {
    		if ($start > 0) {
    			$start_new = ($start - $limit < 0) ? 0 : ($start - $limit);
    			$right     = '<a href="'.$this->path.'&amp;start='.$start_new.'">'.__('Next Entries &raquo;').'</a>';
    		}

    		if ($start + $limit < $total) {
    			$start_new = ($start + $limit);
    			$left      = '<a href="'.$this->path.'&amp;start='.$start_new.'">'.__('&laquo; Previous Entries').'</a>';
    		}

    		echo '<div id="podPress_paging">'."\n";
    		echo '    <div id="podPress_pagingLeft">'."\n";
    		if ($pages > 1) {
        		if (!is_null($text)) {
        		    echo $text;
        		}
        		echo '    <select name="podPress_pageSelect" onchange="javascript: window.location = \''.$this->path.'&start=\'+this.value;">';
        		$i = 0;
        		while ($i < $total) {
        			$selected = ($i == $start) ? ' selected="selected"': null;
        		    $newi     = ($i + $limit);
        			if ($newi > $total) {
        				$newi = $total;
        			}
        			echo '    	<option value="'.$i.'"'.$selected.'>'.ceil($total - ($newi - 1)).' - '.ceil($total - ($i)).'</option>';
        			#$i = ($newi + 1);
        			$i = $newi;
        		}
        		echo '    </select>';

        		echo '    '.__('of', 'podpress').' '.$total;
    		}
    		echo '    </div>'."\n";

    		echo '    <div id="podPress_pagingRight">';
    		if ($start + $limit < $total) {
                echo $left;
    		}
    		if (($start + $limit < $total) AND ($start > 0)) {
    		    echo '&nbsp;&nbsp;|&nbsp;&nbsp;';
    		}
    		if ($start > 0) {
    		    echo $right;
    		}
    		echo "</div>\n";
    		echo "</div>\n";
		}
	}

	#############################################
	#############################################

	function podGetHighest($data) {
		$highest = 0;
		foreach ($data AS $key => $idata) {
			if ($idata->value > $highest) {
				$highest = $idata->value;
			}
		}
		return $highest;
	}

	#############################################
	#############################################

	function podGraph($data, $splitter = null) {
		$cnt_data   = count($data);

		if ($cnt_data > 0) {
    		$box_width  = 600;
    		$box_height = 400;
    		$bar_margin = 2;
    		$bar_width  = floor(($box_width - ($cnt_data * $bar_margin)) / $cnt_data);
    		$bar_width  = ($bar_width < 2) ? 2: $bar_width;
    		$highest    = $this->podGetHighest($data);
    		$factor     = ($highest / $box_height);

    		$output     = '<div id="podGraph" style="height: '.$box_height.'px; width: '.$box_width.'px; float: left; border: 1px solid #ccc; padding: 0.5em;">';
    		foreach ($data AS $key => $idata) {
    			$image_height  = floor($idata->value / $factor);
    			$margin  = ($box_height - $image_height);
    			$bgcolor = (is_array($splitter) && ($idata->$splitter[0] == $splitter[1])) ? '#aaa': '#ccc';
    			$bgcolor = ($image_height == $box_height) ? '#d00': $bgcolor;
    			$output .= '    <div style="float: left; margin-top: '.$margin.'px; margin-right: '.$bar_margin.'px; height: '.$image_height.'px; background-color: '.$bgcolor.'; width: '.$bar_width.'px; cursor: help;'.$split.'" title="'.$idata->title.' ('.number_format($idata->value, 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' x)"></div>'."\n";
    		}
    		$output .= "</div>\n";
		} else {
            $output = '<p>'.__('We\'re sorry. At the moment we don\'t have enough data collected to display the graph.', 'podpress')."</p>\n";
		}

		return $output;
	}

	#############################################
	#############################################

	function settings_stats_edit() {
		GLOBAL $wpdb;
		podPress_isAuthorized();
		$baseurl = get_option('siteurl') . '/wp-admin/admin.php?page=podpress/podpress_stats.php&display=';
		echo '<div class="wrap">'."\n";
		echo '	<h2>'.__('Download Statistics', 'podpress').'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://www.mightyseek.com/podpress/#download" target="_new"><img src="http://www.mightyseek.com/podpress_downloads/versioncheck.php?current='.PODPRESS_VERSION.'" alt="'.__('Checking for updates... Failed.', 'podpress').'" border="0" /></a></h2>'."\n";

        if($this->settings['statLogging'] == 'Full' || $this->settings['statLogging'] == 'FullPlus') {
            $navi = array(
                'quickcounts'       => __('Quick Counts', 'podpress'),
                'graphbypost'       => __('Graph by Post', 'podpress'),
                'graphbydate'       => __('Graph by Date', 'podpress'),
                'unqiuedownloaders' => __('Unique Downloaders', 'podpress'),
                'topips'            => __('Top IP Addresses', 'podpress'),
                'rawstats'          => __('Raw Stats', 'podpress'),
            );
            echo '    <ul id="podPress_navi">'."\n";
            foreach ($navi AS $key => $value) {
                $active = (($_GET['display'] == $key) OR (!$_GET['display'] AND ($key == 'quickcounts'))) ? ' class="current"': null;
								echo '        <li><a href="'.$baseurl.$key.'"'.$active.'>'.$value.'</a>';
								if($this->checkGD()) {
									if($value == __('Graph by Post', 'podpress')) {
										echo ' (<a href="'.$baseurl.'graphbypostalt">'. __('alt', 'podpress').'</a>)';
									} elseif($value == __('Graph by Date', 'podpress')) {
										echo ' (<a href="'.$baseurl.'graphbydatealt">'. __('alt', 'podpress').'</a>)';
									}
								}
								echo '</li>'."\n";
            }
            echo '	</ul>'."\n";
        } else {
            $_GET['display'] = 'quickcounts';
        }

		// Set Paging-Settings
		$start = (isset($_GET['start'])) ? $this->podSafeDigit($_GET['start']): 0;
		$limit = 25;

		switch($_GET['display']) {
			case 'unqiuedownloaders':
				$total = $wpdb->get_results('SELECT COUNT(DISTINCT remote_ip, user_agent) FROM '.$wpdb->prefix.'podpress_stats GROUP BY media;');
				$total = count($total);

				echo '	<div class="wrap">'."\n";
				echo '		<fieldset class="options">'."\n";
				echo '			<legend>'.__('The Unique Downloaders', 'podpress').'</legend>'."\n";
				echo '			<table class="the-list-x" width="100%" cellpadding="1" cellspacing="1">'."\n";
				echo '				<tr><th align="left">'.__('Media', 'podpress').'</th><th>'.__('Total', 'podpress').'</th></tr>'."\n";

				$sql   = 'SELECT media, COUNT(DISTINCT remote_ip, user_agent) AS total '
				       . 'FROM '.$wpdb->prefix.'podpress_stats GROUP BY media ORDER BY media LIMIT '.$start.', '.$limit.';';
				$stats = $wpdb->get_results($sql);
				if($stats) {
					$i = 0;
					foreach ($stats as $stat) {
						++$i;
						$style = ($i % 2) ? '' : ' class="alternate"';
						echo '				<tr'.$style.'>'."\n";
						echo '                  <td>'.podPress_wordspaceing($stat->media, 20).'</td>'."\n";
						echo '                  <td>'.$stat->total.'</td>'."\n";
						echo '              </tr>'."\n";
					}
				}
				echo '			</table>'."\n";
				echo '		</fieldset>'."\n";
				echo '	</div>';
				break;
			case 'rawstats':
				$total = $wpdb->get_var('SELECT COUNT(postID) FROM '.$wpdb->prefix.'podpress_stats WHERE postID != 0;');

				echo '	<div class="wrap">'."\n";
				echo '		<fieldset class="options">'."\n";
				echo '			<legend>'.__('The raw list', 'podpress').'</legend>'."\n";
				echo '			<table class="the-list-x" width="100%" cellpadding="1" cellspacing="1">'."\n";
				echo '				<tr><th align="left">'.__('Media', 'podpress').'</th><th>'.__('Method', 'podpress').'</th><th>'.__('IP', 'podpress').'</th><th>'.__('UserAgent', 'podpress').'</th><th align="left">'.__('Timestamp', 'podpress').'</th></tr>'."\n";
				$stats = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'podpress_stats ORDER BY id DESC LIMIT '.$start.', '.$limit.';');
				if($stats) {
					$i = 0;
					foreach ($stats as $stat) {
						++$i;
						$style = ($i % 2) ? '' : ' class="alternate"';
						echo '				<tr'.$style.'>'."\n";
						echo '                  <td>'.podPress_wordspaceing($stat->media, 20).'</td>'."\n";
						echo '                  <td>'.$stat->method.'</td>'."\n";
						// iscifi : mod of stats output to create a link to domaintools.com whois lookup
						// domaintools seems faster and provides more concise infomation, url can not have trailing /

 						echo '                  <td><a href="http://whois.domaintools.com/'.$stat->remote_ip.'" target="_blank">'.$stat->remote_ip.'</a></td>'."\n";
						// OLD code where this .echo '                  <td>'.$stat->remote_ip.'</td>'."\n";
						echo '                  <td>'.substr($stat->user_agent,0,20).'</td>'."\n";
						echo '                  <td>'.strftime('%Y-%m-%d (%I:%M %p)', $stat->dt).'</td>'."\n";
						echo '              </tr>'."\n";
					}
				}
				echo '			</table>'."\n";
				echo '		</fieldset>'."\n";
				echo '	</div>';
				break;
			case 'topips':
				$total = $wpdb->get_results('SELECT COUNT( * ) AS TotalCount FROM '.$wpdb->prefix.'podpress_stats GROUP BY remote_ip;');
				$total = count($total);

				echo '	<div class="wrap">'."\n";
				echo '		<fieldset class="options">'."\n";
				echo '			<legend>'.__('Top IP Addresses', 'podpress').'</legend>'."\n";
				echo '			<table class="the-list-x" width="100%" cellpadding="1" cellspacing="1">'."\n";
				echo '				<tr><th align="left">'.__('IP Address', 'podpress').'</th><th align="left">'.__('Total Count', 'podpress').'</th></tr>'."\n";
				$sql   = 'SELECT remote_ip AS IPAddress, COUNT( * ) AS TotalCount FROM '.$wpdb->prefix.'podpress_stats GROUP BY remote_ip ORDER BY TotalCount DESC LIMIT '.$start.', '.$limit.';';
				$stats = $wpdb->get_results($sql);
				if($stats) {
					$i = 0;
					foreach ($stats as $stat) {
						++$i;
						$style = ($i % 2) ? '' : ' class="alternate"';
						echo '				<tr'.$style.'>'."\n";
						echo '                  <td>'.$stat->IPAddress.'</td>'."\n";
						echo '                  <td>'.$stat->TotalCount.'</td>'."\n";
						echo '              </tr>'."\n";
					}
				}
				echo '			</table>'."\n";
				echo '		</fieldset>'."\n";
				echo '	</div>';
				break;
			case 'graphbydate':
			    if ($this->checkGD() && ($this->settings['statLogging'] == 'Full' || $this->settings['statLogging'] == 'FullPlus')) {
						$this->graphByDate();
			    } else {
						$this->graphByDateAlt('With <a href="http://us2.php.net/manual/en/ref.image.php">gdlib-support</a> you\'ll have access to more detailed graphicals stats. Please ask your provider.');
			    }
				break;
			case 'graphbydatealt':
				$this->graphByDateAlt();
				break;
			case 'graphbypost':
			    if ($this->checkGD()) {
						$this->graphByPost();
			    } else {
						$this->graphByPostAlt('With <a href="http://us2.php.net/manual/en/ref.image.php">gdlib-support</a> you\'ll have access to more detailed graphicals stats. Please ask your provider.');
					}
				break;
			case 'graphbypostalt':
				$this->graphByPostAlt();
				break;
			case 'quickcounts':
			default:
				$total         = $wpdb->get_var('SELECT COUNT(postID) FROM '.$wpdb->prefix.'podpress_statcounts WHERE postID != 0;');

				if ($total > 0) {
    				// Load highest values
    				$sql           = "SELECT 'blah' AS topic, MAX(total) AS total, MAX(feed) AS feed, MAX(web) AS web, MAX(play) AS play FROM ".$wpdb->prefix.'podpress_statcounts GROUP BY topic;';
    				$highest       = $wpdb->get_results($sql);
    				$highest       = $highest[0];

    				$highlight_css = ' background-color: green; color: #fff;';
    				$sql           = 'SELECT sc.postID, sc.media, sc.total, sc.feed, sc.web, sc.play, p.post_title, p.post_date, UNIX_TIMESTAMP(p.post_date) AS pdate '
    				               . 'FROM '.$wpdb->prefix.'podpress_statcounts AS sc, '.$wpdb->prefix.'posts AS p '
    				               . 'WHERE (sc.postID = p.ID) ORDER BY p.post_date DESC LIMIT '.$start.', '.$limit.';';
    				$stats         = $wpdb->get_results($sql);
    				$cnt_stats     = count($stats);

    				echo '	<div class="wrap">'."\n";
    				echo '		<fieldset class="options">'."\n";
    				echo '			<legend>'.__('The counts', 'podpress').'</legend>'."\n";
    				echo '			<table class="the-list-x" width="100%" cellpadding="1" cellspacing="1">'."\n";
    				echo "				<tr>\n";
    				echo '                  <th align="left" rowspan="2">'.__('Post', 'podpress')."</th>\n";
    				echo '                  <th align="left" rowspan="2">'.__('Date', 'podpress')."</th>\n";
    				echo '                  <th align="left" rowspan="2">'.__('Media', 'podpress')."</th>\n";
    				echo '                  <th colspan="2">'.__('Feed', 'podpress')."</th>\n";
    				echo '                  <th colspan="2">'.__('Download', 'podpress')."</th>\n";
    				echo '                  <th colspan="2">'.__('Play', 'podpress')."</th>\n";
    				echo '                  <th rowspan="2">'.__('Total', 'podpress')."</th>\n";
    				echo "              </tr>\n";
    				echo "				<tr>\n";
    				echo '                  <th>'.__('Files', 'podpress')."</th>\n";
    				echo '                  <th>'.__('%', 'podpress')."</th>\n";
    				echo '                  <th>'.__('Files', 'podpress')."</th>\n";
    				echo '                  <th>'.__('%', 'podpress')."</th>\n";
    				echo '                  <th>'.__('Files', 'podpress')."</th>\n";
    				echo '                  <th>'.__('%', 'podpress')."</th>\n";
    				echo "              </tr>\n";

    				if ($cnt_stats > 0) {
    					$i = 0;
    					foreach ($stats AS $stat) {
    						$style         = ($i % 2 != 0) ? '' : ' class="alternate"';
    						$highest_feed  = ($stat->feed  == $highest->feed)  ? $highlight_css: null;
    						$highest_web   = ($stat->web   == $highest->web)   ? $highlight_css: null;
    						$highest_play  = ($stat->play  == $highest->play)  ? $highlight_css: null;
    						$highest_total = ($stat->total == $highest->total) ? $highlight_css: null;
    						$perc_feed     = number_format(($stat->feed * 100 / $stat->total), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]);
    						$perc_web      = number_format(($stat->web  * 100 / $stat->total), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]);
    						$perc_play     = number_format(($stat->play * 100 / $stat->total), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]);
    						echo '				<tr'.$style.'>'."\n";
    						echo '                  <td><a href="'.get_option('siteurl').'/wp-admin/post.php?action=edit&amp;post='.$stat->postID.'">'.$stat->post_title."</a></td>\n";
    						echo '                  <td>'.strftime('%Y-%m-%d (%I:%M %p)', $stat->pdate)."</td>\n";
    						echo '                  <td>'.podPress_wordspaceing($stat->media, 30)."</td>\n";
    						echo '                  <td style="text-align: right;'.$highest_feed.'">'.number_format($stat->feed, 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1])."</td>\n";
    						echo '                  <td style="text-align: right; color: #888;'.$highest_feed.'">'.$perc_feed."</td>\n";
    						echo '                  <td style="text-align: right;'.$highest_web.'" >'.number_format($stat->web, 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1])."</td>\n";
    						echo '                  <td style="text-align: right; color: #888;'.$highest_feed.'">'.$perc_web."</td>\n";
    						echo '                  <td style="text-align: right;'.$highest_play.'">'.number_format($stat->play, 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1])."</td>\n";
    						echo '                  <td style="text-align: right; color: #888;'.$highest_feed.'">'.$perc_play."</td>\n";
    						echo '                  <td style="text-align: right; font-weight: bold;'.$highest_total.'">'.number_format($stat->total, 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1])."</td>\n";
    						echo '              </tr>'."\n";
    						$i++;
    					}
    				}
    				echo '			</table>'."\n";
    				echo '		</fieldset>'."\n";
    				echo '	</div>';
				} else {
        			echo '<p>'.__('We\'re sorry. At the moment we don\'t have enough data collected to display the graph.', 'podpress')."</p>\n";
				}
				break;
		}

		// Show paging
		if ($_GET['display'] != 'graphbydate' && $_GET['display'] != 'graphbypost') {
			echo $this->paging($start, $limit, $total, 'Posts');
		}
		echo '<div style="clear: both;"></div>'."\n";

		echo '</div>';
	}

	#############################################
	#############################################

	/**
	 * Check GD-Library-Support
	 *
	 * @return unknown
	 */
	function checkGD() {
	    if (!function_exists('gd_info')) {
	        /* php > 4.3 */
    		$info = gd_info();
    		if (isset($info) AND ($info['JPG Support'] == 1) AND ($info['FreeType Support'] == 1)) {
    			return true;
    		}
	    } else {
	        /* php < 4.3 */
            ob_start();
            phpinfo(8);
            $info   = ob_get_contents();
            ob_end_clean();
            $info   = stristr($info, 'gd version');
            preg_match('/\d/', $info, $match);
            $gd_ver = $match[0];
            if ($gd_ver > 0) {
                return true;
            }
	   }
		return false;
	}

	#############################################
	#############################################

	function checkFontFile() {
		clearstatcache();

		/* check font */
		$file     = 'share-regular.ttf';
		$fontface = ABSPATH.PLUGINDIR.'/podpress/optional_files/'.$file;
		if (!file_exists($fontface)) {
			$fontface = '../optional_files/'.$file;
		}
		if (!is_readable($fontface)) {
			echo '<p>'.__('Could not include font-file. Please reinstall podPress.', 'podpress')."</p>\n";
			return false;
		}
		$this->fontface = $fontface;

		return true;
	}

	#############################################
	#############################################

	function graphByDate() {
		if ($this->checkWritableTempFileDir() && $this->checkFontFile()) {
			$chronometry1 = getmicrotime();
			$start        = (isset($_GET['start'])) ? $this->podSafeDigit($_GET['start']): 0;
			$image_width  = 800;
			$image_height = 470;
			$col_width    = 20;
			$filename     = 'graph-by-date.jpg';
			$file         = $this->tempFileSystemPath.'/'.$filename;
			$sidemargin   = 20;
			$baseline     = ($image_height - 90);
			$topmargin    = 60;
			$maxheight    = ($baseline - $topmargin);
			$weeks        = 5;
			$timelimit    = (time() - (($weeks * 7) * 86400));

			/* get the data in time range ($timelimit) */
			$query        = 'SELECT dt AS timestamp, DAYOFWEEK(FROM_UNIXTIME(dt)) AS weekday, method, '
                          . 'DATE_FORMAT(FROM_UNIXTIME(dt), "%Y-%m-%d") AS day '
                          . 'FROM '.$this->wpdb->prefix.'podpress_stats WHERE dt >= "'.$timelimit.'" '
                          . 'ORDER BY dt ASC;';
			$data         = $this->wpdb->get_results($query);

			/* create array with grouped stats */
			$stats        = array();
			$stats_total  = array();
			foreach ($data AS $idata) {
				$stats[$idata->day][$idata->method]++;
                $stats[$idata->day]['total']++;
                $stats_total[$idata->method]++;
                $stats_total['total']++;
			}

			/* get min an max values */
			$value_min = 0;
			$value_max = 0;
			$i         = 0;
			$cnt_days  = count($stats);
			foreach ($stats AS $day => $idata) {
				/* set min and max values */
				if ($value_min == 0) {
					$value_min = $idata['total'];
				} elseif ($idata['total'] < $value_min) {
					$value_min = $idata['total'];
				} elseif ($idata['total'] > $value_max) {
					$value_max = $idata['total'];
				}
				/* set first and last day */
				if ($i == 0) {
                    $first_day = strtotime($day);
				} elseif ($i == ($cnt_days - 1)) {
                    $last_day  = strtotime($day);
				}
				$i++;
			}

			/* Do we have enough data? */
			if (($value_max > 0) && ($cnt_days > 5)) {
    			$h_cscale = ($maxheight / $value_max);
    			$h_vscale = ($maxheight / $value_min / 4);
    			$pos_x    = ($sidemargin + 35);
    			$points   = array( 'total' => array(0 => $pos_x, 1 => $baseline) );
    			foreach ($stats AS $day => $idata) {
    				$points['total'][] = $pos_x;
    				$points['total'][] = ($baseline - ($idata['total'] * $h_cscale));
    				$points['feed'][]  = ($baseline - ($idata['feed'] * $h_cscale));
    				$points['web'][]   = ($baseline - ($idata['web'] * $h_cscale));
    				$points['play'][]  = ($baseline - ($idata['play'] * $h_cscale));
    				$pos_x             = ($pos_x + $col_width);
    			}
    			$points['total'][] = ($pos_x - $col_width);
    			$points['total'][] = $baseline;


    			/* create image */
    			$chronometry2 = getmicrotime();
    			$image        = imagecreatetruecolor($image_width, $image_height);
    			if (function_exists('imageantialias')) {
        			imageantialias($image, 1);
    			}

    			/* set colors */
    			$colors = array(
    			                'background' => imagecolorallocate($image, 51, 51, 51),
    			                'line'       => imagecolorallocate($image, 79, 79, 79),
    			                'total'      => imagecolorallocate($image, 79, 79, 79),
    			                'feed'       => imagecolorallocate($image, 255, 0, 0),
    			                'web'        => imagecolorallocate($image, 12, 223, 0),
    			                'play'       => imagecolorallocate($image, 223, 204, 0),
    			                'text'       => imagecolorallocate($image, 255, 255, 255),
    			                'copytext'   => imagecolorallocate($image, 79, 79, 79),
    			                'days_first' => imagecolorallocate($image, 143, 143, 143),
    			                'days_other' => imagecolorallocate($image, 95, 95, 95),
    			                /* gray-scales: 175 159 143 127 111 95 79 63 */
    			               );
    			imagefill($image, 0, 0, $colors['background']);

    			/* create the legend */
    			imagettftext($image, 14, 0, $sidemargin, 25, $colors['text'], $this->fontface, get_option('blogname'));
    			imagettftext($image, 8, 0, $sidemargin, 42, $colors['text'], $this->fontface, __('last', 'podpress').' '.$weeks.' '.__('weeks', 'podpress').' &#187; '.strftime('%d.%m.%Y', $first_day).' - '.strftime('%d.%m.%Y', $last_day));

    			/* Total stats */
    			$text_total = number_format($stats_total['total'], 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]);
    			$text_feed  = number_format($stats_total['feed'], 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' ('.number_format(($stats_total['feed'] * 100 / $stats_total['total']), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).'%)';
    			$text_web   = number_format($stats_total['web'], 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' ('.number_format(($stats_total['web'] * 100 / $stats_total['total']), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).'%)';
    			$text_play  = number_format($stats_total['play'], 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' ('.number_format(($stats_total['play'] * 100 / $stats_total['total']), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).'%)';
    			$text_total = __('Total stats in time range', 'podpress').':  '.$text_total.'  /  '.__('Feed', 'podpress').': '.$text_feed.'  /  '.__('Download', 'podpress').': '.$text_web.'  /  '.__('Play', 'podpress').': '.$text_play.' ';
    			imagettftext($image, 8, 0, $sidemargin, ($image_height - 15), $colors['text'], $this->fontface, $text_total);

    			$pos_y = ($image_height - 32);

    			imagefilledrectangle($image, ($sidemargin + 0), ($pos_y - 10), ($sidemargin + 10), ($pos_y - 0), $colors['total']);
    			imagettftext($image, 8, 0, ($sidemargin + 15), $pos_y, $colors['text'], $this->fontface, __('Total', 'podpress'));

    			imageline($image, ($sidemargin + 50), ($pos_y - 4), ($sidemargin + 60), ($pos_y - 4), $colors['feed']);
    			imagettftext($image, 8, 0, ($sidemargin + 65), $pos_y, $colors['text'], $this->fontface, __('Feed', 'podpress'));

    			imageline($image, ($sidemargin + 100), ($pos_y - 4), ($sidemargin + 110), ($pos_y - 4), $colors['web']);
    			imagettftext($image, 8, 0, ($sidemargin + 115), $pos_y, $colors['text'], $this->fontface, __('Download', 'podpress'));

    			imageline($image, ($sidemargin + 175), ($pos_y - 4), ($sidemargin + 185), ($pos_y - 4), $colors['play']);
    			imagettftext($image, 8, 0, ($sidemargin + 190), $pos_y, $colors['text'], $this->fontface, __('Play', 'podpress'));

    			imagettftext($image, 23, 0, ($image_width - 128), 30, $colors['copytext'], $this->fontface, 'podPress');
    			imagettftext($image, 8, 0, ($image_width - 115), 43, $colors['copytext'], $this->fontface, __('Plugin for WordPress', 'podpress'));

    			imagettftext($image, 6, 90, ($image_width - 15), ($image_height - 10), $colors['copytext'], $this->fontface, strftime($this->local_settings['creation_date'], time()).' ('.PODPRESS_VERSION.')');

    			/* draw total-stats */
    			$cnt_total = (count($points['total']) / 2);
    			imagefilledpolygon($image, $points['total'], $cnt_total, $colors['total']);

    			/* draw background-lines and scale-text */
    			$step = 0;
    			$h    = (($baseline - $topmargin) / 10);
    			while ($step <=  10) {
    				$pos_y = ($topmargin + ($h * $step));
    				imageline($image, 0, ($topmargin + ($h * $step)), $image_width, ($topmargin + ($h * $step)), $colors['line']);
    				imagettftext($image, 8, 0, ($sidemargin), ($pos_y + 13), $colors['line'], $this->fontface, number_format((($baseline - $pos_y) / $h_cscale), 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]));
    				$step++;
    			}

    			$pos_x    = ($sidemargin + 35);
    			foreach ($stats AS $day => $idata) {
    				/* Web einzeichnen */
    				$weekday          = date('w', strtotime($day));
    				$col_total_height = ($idata['total'] * $h_cscale);

    				if ($weekday == 1) {
    					$pos_y_start = ($baseline + 27);
    					imagettftext($image, 8, 0, ($pos_x + 3), ($baseline + 13), $colors['text'], $this->fontface, strftime($this->local_settings['short_date'], strtotime($day)));
    					$color       = $colors['days_first'];
    				} else {
    					$pos_y_start = $baseline;
    					$color       = $colors['days_other'];
    				}
    				imageline($image, $pos_x, $pos_y_start, $pos_x, ($baseline - $col_total_height), $color);

    				$pos_x = ($pos_x + $col_width);
    			}


    			/* Linien zeichen */
    			$topics = array('feed', 'web', 'play');
    			foreach ($topics AS $topic) {
    				$cnt_points = count($points[$topic]);
    				$pos_x      = ($sidemargin + 35);
    				for ($i = 0; $i < ($cnt_points - 1); $i++) {
    					imageline($image, $pos_x, $points[$topic][$i], ($pos_x + $col_width), $points[$topic][$i + 1], $colors[$topic]);
    					$pos_x = ($pos_x + $col_width);
    				}
    			}

    			imagejpeg($image, $file, 100);
    			$chronometry_end = getmicrotime();
    			$chronometry1    = ($chronometry_end - $chronometry1);
    			$chronometry2    = ($chronometry_end - $chronometry2);
    			imagedestroy($image);

    			/* Output */
    			echo '<div id="podPress_graph" style="width: '.$image_width.'px;">'."\n";
    			echo '    <p><img src="'.$this->tempFileURLPath.'/'.$filename.'" width="'.$image_width.'" height="'.$image_height.'" alt="podPress-Statistics" /></p>'."\n";
    			echo "</div>\n";
    			echo '<p>'.__('Time to generate the graph', 'podpress').': '.number_format($chronometry1, 3, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' '.__('seconds', 'podpress').' ('.__('image', 'podpress').': '.number_format($chronometry2, 3, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' '.__('seconds', 'podpress').").</p>\n";
			} else {
    			echo '<p>'.__('We\'re sorry. At the moment we don\'t have enough data collected to display the graph.', 'podpress')."</p>\n";
			}
		}
	}

	#############################################
	#############################################

	function graphByDateAlt($msg = '') {
		GLOBAL $wpdb;
		$sql      = 'SELECT DATE_FORMAT(FROM_UNIXTIME(dt), "%a, %Y-%m-%d") AS title, DAYOFWEEK(FROM_UNIXTIME(dt)) AS weekday, COUNT(id) AS value '
		          . 'FROM '.$wpdb->prefix.'podpress_stats GROUP BY title ORDER BY dt DESC LIMIT 0, 100;';
 		$data     = $wpdb->get_results($sql);
 		$data     = array_reverse($data);
 		$splitter = array('weekday', '2');

 		echo '	<div class="wrap">'."\n";
 		echo '		<fieldset class="options">'."\n";
 		echo '			<legend>'.__('Downloads by date', 'podpress').' ('.__('Last 100 days', 'podpress').')</legend>'."\n";
 		echo $this->podGraph($data, $splitter);
		if($msg != '') {
			echo '      <p>'.$msg."</p>\n";
		}
		echo '		</fieldset>'."\n";
		echo '	</div>';
	}

	#############################################
	#############################################

	function graphByPost() {
		if ($this->checkWritableTempFileDir() && $this->checkFontFile()) {
			$chronometry1 = getmicrotime();
			$start        = (isset($_GET['start'])) ? $this->podSafeDigit($_GET['start']): 0;
			$limit        = 13;
			$image_width  = 800;
			$image_height = 470;
			$col_width    = 25;
			$col_space    = 15;
			$filename     = 'graph-by-post.jpg';
			$file         = $this->tempFileSystemPath.'/'.$filename;
			$sidemargin   = 20;
			$baseline     = ($image_height - 90);
			$topmargin    = 60;
			$maxheight    = ($baseline - $topmargin);
			$timelimit    = (time() - ((5 * 7) * 86400));

			/* get data */
			$total        = $this->wpdb->get_var('SELECT COUNT(postID) FROM '.$this->wpdb->prefix."podpress_statcounts WHERE postID != 0;");

			$query        = 'SELECT post_title AS title, total, feed, web, play, UNIX_TIMESTAMP(post_date) AS post_date '
			              . 'FROM '.$this->wpdb->prefix.'podpress_statcounts, '.$this->wpdb->prefix.'posts '
			              . 'WHERE '.$this->wpdb->prefix.'posts.ID = '.$this->wpdb->prefix.'podpress_statcounts.postID '
			              . 'AND postID !=0 GROUP BY postID ORDER BY post_date DESC LIMIT '.$start.', '.$limit.';';
			$data         = $this->wpdb->get_results($query);
			$cnt_data     = count($data);

			$first_post   = $data[($cnt_data - 1)]->post_date;
			$last_post    = $data[0]->post_date;

			$stats        = array();
			foreach ($data AS $idata) {
				$stats[$idata->day][$idata->method]++;
				$stats[$idata->day]['total']++;
				$stats_total[$idata->method]++;
				$stats_total['total']++;
			}

			/* get min an max values */
			$value_min   = 0;
			$value_max   = 0;
			$stats_total = array();
			foreach ($data AS $idata) {
				if ($value_min == 0) {
					$value_min = $idata->total;
				} elseif ($idata->total < $value_min) {
					$value_min = $idata->total;
				}
				if ($idata->total > $value_max) {
					$value_max = $idata->total;
				}
				$stats_total['feed']  = ($stats_total['feed'] + $idata->feed);
				$stats_total['web']   = ($stats_total['web'] + $idata->web);
				$stats_total['play']  = ($stats_total['play'] + $idata->play);
				$stats_total['total'] = ($stats_total['total'] + $idata->total);
			}

			/* Do we have enough data? */
			if ($value_max > 0) {
    			$h_cscale    = ($maxheight / $value_max);
    			$h_vscale    = ($maxheight / $value_min / 4);
    			$w_scale     = intval($image_width - (2 * $sidemargin) + intval(($image_width - (2 * $sidemargin)) / ($limit) / 4)) / ($limit);

    			/* create image */
    			$chronometry2 = getmicrotime();
    			$image        = imagecreatetruecolor($image_width, $image_height);
    			if (function_exists('imageantialias')) {
        			imageantialias($image, 1);
    			}

    			$colors = array(
    			                'background' => imagecolorallocate($image, 51, 51, 51),
    			                'line'       => imagecolorallocate($image, 79, 79, 79),
    			                'text'       => imagecolorallocate($image, 255, 255, 255),
    			                'copytext'   => imagecolorallocate($image, 79, 79, 79),
    			                'total'      => imagecolorallocate($image, 0, 0, 0),
    			                'feed'       => imagecolorallocate($image, 143, 53, 53),
    			                'web'        => imagecolorallocate($image, 71, 143, 88),
    			                'play'       => imagecolorallocate($image, 142, 143, 71),
    			              );

    			imagefill($image, 0, 0, $colors['background']);


    			/* draw background-lines and scale-text */
    			$step = 0;
    			$h    = (($baseline - $topmargin) / 10);
    			while ($step <=  10) {
    				$pos_y = ($topmargin + ($h * $step));
    				imageline($image, 0, ($topmargin + ($h * $step)), $image_width, ($topmargin + ($h * $step)), $colors['line']);
    				imagettftext($image, 8, 0, ($sidemargin), ($pos_y + 13), $colors['line'], $this->fontface, number_format((($baseline - $pos_y) / $h_cscale), 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]));
    				$step++;
    			}

    			/* create the legend */
    			imagettftext($image, 14, 0, $sidemargin, 25, $colors['text'], $this->fontface, get_option('blogname'));
    			imagettftext($image, 8, 0, $sidemargin, 42, $colors['text'], $this->fontface, __('Posts', 'podpress').' &#187; '.strftime('%d.%m.%Y', $first_post).' - '.strftime('%d.%m.%Y', $last_post));

    			$text_total = number_format($stats_total['total'], 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]);
    			$text_feed  = number_format($stats_total['feed'], 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' ('.number_format(($stats_total['feed'] * 100 / $stats_total['total']), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).'%)';
    			$text_web   = number_format($stats_total['web'], 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' ('.number_format(($stats_total['web'] * 100 / $stats_total['total']), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).'%)';
    			$text_play  = number_format($stats_total['play'], 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' ('.number_format(($stats_total['play'] * 100 / $stats_total['total']), 1, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).'%)';
    			$text_total = __('Total stats from displayed posts', 'podpress').':  '.$text_total.'  /  '.__('Feed', 'podpress').': '.$text_feed.'  /  '.__('Download', 'podpress').': '.$text_web.'  /  '.__('Play', 'podpress').': '.$text_play.' ';
    			imagettftext($image, 8, 0, $sidemargin, ($image_height - 15), $colors['text'], $this->fontface, $text_total);

    			$pos_y = ($image_height - 32);

    			imagefilledrectangle($image, ($sidemargin + 0), ($pos_y - 10), ($sidemargin + 10), $pos_y, $colors['feed']);
    			imagettftext($image, 8, 0, ($sidemargin + 15), $pos_y, $colors['text'], $this->fontface, __('Feed', 'podpress'));

    			imagefilledrectangle($image, ($sidemargin + 50), ($pos_y - 10), ($sidemargin + 60), $pos_y, $colors['web']);
    			imagettftext($image, 8, 0, ($sidemargin + 65), $pos_y, $colors['text'], $this->fontface, __('Download', 'podpress'));

    			imagefilledrectangle($image, ($sidemargin + 125), ($pos_y - 10), ($sidemargin + 135), $pos_y, $colors['play']);
    			imagettftext($image, 8, 0, ($sidemargin + 140), $pos_y, $colors['text'], $this->fontface, __('Play', 'podpress'));

    			imagettftext($image, 23, 0, ($image_width - 128), 30, $colors['copytext'], $this->fontface, 'podPress');
    			imagettftext($image, 8, 0, ($image_width - 115), 43, $colors['copytext'], $this->fontface, __('Plugin for WordPress', 'podpress'));

    			imagettftext($image, 6, 90, ($image_width - 15), ($image_height - 10), $colors['copytext'], $this->fontface, strftime($this->local_settings['creation_date'], time()).' ('.PODPRESS_VERSION.')');

    			$pos_x = ($image_width - $sidemargin - 15);

    			/* draw the posts */
    			foreach ($data AS $idata) {
    				/* Total stats */
    				$col_total_height = ($idata->total * $h_cscale);

    				imageline($image, ($pos_x - 2), $baseline, ($pos_x - 2), ($baseline - $col_total_height), $colors['total']);
    				imagettftext($image, 8, 0, ($pos_x - $col_width - 3), ($baseline - 3 - ($idata->total * $h_cscale)), $colors['text'], $this->fontface, $idata->total);

    				/* Feeds */
    				$perc_feed       = number_format(($idata->feed * 100 / $idata->total), 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]);
    				$col_feed_height = ($idata->feed * $h_cscale);
    				if ($col_feed_height < 0) {
        				imagefilledrectangle($image, ($pos_x - $col_width - 3), $baseline, ($pos_x - 3), ($baseline - $col_feed_height), $colors['feed']);
    				} else {
        				imagefilledrectangle($image, ($pos_x - $col_width - 3), ($baseline - $col_feed_height), ($pos_x - 3), $baseline, $colors['feed']);
    			    }
    				if ($col_feed_height > 11) {
    					imagettftext($image, 8, 0, ($pos_x - $col_width - 2), (($baseline - ($idata->feed * $h_cscale)) + 11), $colors['text'], $this->fontface, $perc_feed.'%');
    				}

    				/* Web */
    				$perc_web        = number_format(($idata->web * 100 / $idata->total), 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]);
    				$col_web_height = ($idata->web * $h_cscale);
    				if ($col_web_height < 0) {
        				imagefilledrectangle($image, ($pos_x - $col_width - 3), ($baseline - $col_feed_height), ($pos_x - 3), ($baseline - $col_web_height - $col_feed_height), $colors['web']);
    				} else {
        				imagefilledrectangle($image, ($pos_x - $col_width - 3), ($baseline - $col_web_height - $col_feed_height), ($pos_x - 3), ($baseline - $col_feed_height), $colors['web']);
    			    }
    				if ($col_web_height > 11) {
    					imagettftext($image, 8, 0, ($pos_x - $col_width - 2), (($baseline - $col_web_height - $col_feed_height) + 11), $colors['text'], $this->fontface, $perc_web.'%');
    				}

    				/* Play */
    				$perc_play       = number_format(($idata->play * 100 / $idata->total), 0, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]);
    				$col_play_height = ($idata->play * $h_cscale);
    				if ($col_play_height < 0) {
        				imagefilledrectangle($image, ($pos_x - $col_width - 3), ($baseline - $col_feed_height - $col_web_height), ($pos_x - 3), ($baseline - $col_play_height - $col_web_height - $col_feed_height), $colors['play']);
    				} else {
        				imagefilledrectangle($image, ($pos_x - $col_width - 3), ($baseline - $col_play_height - $col_web_height - $col_feed_height), ($pos_x - 3), ($baseline - $col_feed_height - $col_web_height), $colors['play']);
    			    }
    				if ($col_play_height > 11) {
    					imagettftext($image, 8, 0, ($pos_x - $col_width - 2), (($baseline - $col_play_height - $col_web_height - $col_feed_height) + 11), $colors['text'], $this->fontface, $perc_play.'%');
    				}

    				/* Set Date and Title */
    				$title = (strlen($idata->title) > 70) ? substr($idata->title, 0, 70).'...': $idata->title;
    				imagettftext($image, 8, 90, ($pos_x + 10), $baseline, $colors['text'], $this->fontface, $title);
    				imagettftext($image, 8, 0, ($pos_x - $col_width - 3), ($baseline + 14), $colors['text'], $this->fontface, strftime($this->local_settings['short_date'], $idata->post_date));

    				$pos_x = ($pos_x - $col_width - $col_space - 15);
    			}

    			imagejpeg($image, $file, 100);
    			$chronometry_end = getmicrotime();
    			$chronometry1    = ($chronometry_end - $chronometry1);
    			$chronometry2    = ($chronometry_end - $chronometry2);
    			imagedestroy($image);

    			echo '<div id="podPress_graph" style="width: '.$image_width.'px;">'."\n";
    			echo '    <p style="padding-top: 0;"><img src="'.$this->tempFileURLPath.'/'.$filename.'" width="'.$image_width.'" height="'.$image_height.'" alt="podPress-Statistics" /></p>'."\n";
    			echo $this->paging($start, $limit, $total, 'Posts');
    			echo '    <div class="clear"></div>'."\n";
    			echo "</div>\n";
    			echo '<p>'.__('Time to generate the graph', 'podpress').': '.number_format($chronometry1, 3, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1]).' seconds (image: '.number_format($chronometry2, 3, $this->local_settings['numbers'][0], $this->local_settings['numbers'][1])." seconds).</p>\n";
			} else {
    			echo '<p>'.__('We\'re sorry. At the moment we don\'t have enough data collected to display the graph.', 'podpress')."</p>\n";
			}
		}
	}

	#############################################
	#############################################

	function graphByPostAlt($msg = '') {
		global $wpdb;

		$sql  = 'SELECT post_title AS title, SUM(total) AS value '
		      . 'FROM '.$wpdb->prefix.'podpress_statcounts, '.$wpdb->prefix.'posts '
		      . 'WHERE '.$wpdb->prefix.'posts.ID = '.$wpdb->prefix.'podpress_statcounts.postID '
		      . 'AND postID !=0 GROUP BY postID ORDER BY post_date DESC LIMIT 0, 100;';
		$data = $wpdb->get_results($sql);
		$data = array_reverse($data);

		echo '	<div class="wrap">'."\n";
 		echo '		<fieldset class="options">'."\n";
 		echo '			<legend>'.__('Downloads by Post', 'podpress').' ('.__('Last 100 posts', 'podpress').')</legend>'."\n";
 		echo $this->podGraph($data);
 		echo '		</fieldset>'."\n";

 		if ($msg != '') {
			echo '      <p>'.$msg."</p>\n";
		}
		echo '	</div>';
	}

	#############################################
	#############################################

}

?>
