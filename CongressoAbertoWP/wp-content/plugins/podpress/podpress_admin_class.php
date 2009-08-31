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

		function page_form() {
			return $this->post_form('page');
		}

		function post_form($entryType = 'post') {
			if(!is_object($GLOBALS['post']) && isset($GLOBALS['post_cache'][$GLOBALS['post']])) {
				$post = $GLOBALS['post_cache'][$GLOBALS['post']];
			} else {
				$post = $GLOBALS['post'];
			}
			$post = $this->addPostData($post, true);
			if(($_GET['action'] == 'edit')) {
				$post_id = $_GET['post'];

				if(!is_array($post->podPressMedia)) {
					$post->podPressMedia = array();
				}
			}

			$files = array();
			if(@is_dir($this->settings['mediaFilePath'])) {
				$dh  = opendir($this->settings['mediaFilePath']);
				while (false !== ($filename = readdir($dh))) {
					if($filename != '.' && $filename != '..' && !is_dir($this->settings['mediaFilePath'].'/'.$filename) && !in_array(podPress_getFileExt($filename), array('php', 'html'))) {
						$files[] = $filename;
					}
				}
				natcasesort($files);
			}

			if($this->settings['enablePodangoIntegration']) {
				if(!empty($post->podPressPostSpecific['PodangoEpisodeID'])) {
					if(empty($post->podPressPostSpecific['PodangoMediaFileID'])) {
						$x = $this->podangoAPI->GetEpisode($post->podPressPostSpecific['PodangoEpisodeID']);
						$post->podPressPostSpecific['PodangoMediaFileID'] = $x['MediaFileId'];
						unset($x);
					}
					$podangoMediaFiles = $this->podangoAPI->GetMediaFile($post->podPressPostSpecific['PodangoMediaFileID']);
				} else {
					$podangoMediaFiles = $this->podangoAPI->GetMediaFiles();
				}
			}

			echo '<script type="text/javascript" src="'.podPress_url().'podpress_admin_postedit.js"></script>'."\n";
			echo '<script type="text/javascript">'."\n";
			echo "var podPressMaxMediaFiles = ".$this->settings['maxMediaFiles'].";\n";
			$newMediaDefaults = array();
			echo "var newMediaDefaults = new Array();\n";
			$newMediaDefaults['URI'] = '';
			echo "newMediaDefaults['URI'] = '".$newMediaDefaults['URI']."';\n";
			$newMediaDefaults['title'] = '';
			echo "newMediaDefaults['title'] = '".$newMediaDefaults['title']."';\n";
			$newMediaDefaults['type'] = 'audio_mp3';
			echo "newMediaDefaults['type'] = '".$newMediaDefaults['type']."';\n";
			$newMediaDefaults['size'] = '';
			echo "newMediaDefaults['size'] = '".$newMediaDefaults['size']."';\n";
			$newMediaDefaults['duration'] = '';
			echo "newMediaDefaults['duration'] = '".$newMediaDefaults['duration']."';\n";
			$newMediaDefaults['dimensionW'] = '320';
			echo "newMediaDefaults['dimensionW'] = '".$newMediaDefaults['dimensionW']."';\n";
			$newMediaDefaults['dimensionH'] = '240';
			echo "newMediaDefaults['dimensionH'] = '".$newMediaDefaults['dimensionH']."';\n";
			$newMediaDefaults['previewImage'] = podPress_url().'images/vpreview_center.png';
			echo "newMediaDefaults['previewImage'] = '".$newMediaDefaults['previewImage']."';\n";
			$newMediaDefaults['rss'] = 'false';
			echo "newMediaDefaults['rss'] = ".$newMediaDefaults['rss'].";\n";
			$newMediaDefaults['atom'] = 'true';
			echo "newMediaDefaults['atom'] = ".$newMediaDefaults['atom'].";\n";
			$newMediaDefaults['feedonly'] = 'true';
			echo "newMediaDefaults['feedonly'] = ".$newMediaDefaults['feedonly'].";\n";
			$newMediaDefaults['disablePlayer'] = 'false';
			echo "newMediaDefaults['disablePlayer'] = ".$newMediaDefaults['disablePlayer'].";\n";
			$newMediaDefaults['disablePreview'] = 'false';
			echo "newMediaDefaults['disablePreview'] = ".$newMediaDefaults['disablePreview'].";\n";
			$newMediaDefaults['content_level'] = 'free';
			echo "newMediaDefaults['content_level'] = '".$newMediaDefaults['content_level']."';\n";
			$newMediaDefaults['showme'] = 'false';
			echo "newMediaDefaults['showme'] = ".$newMediaDefaults['showme'].";\n";

			if(empty($post->podPressMedia)) {
				$num = 0;
			} else {
				$num = count($post->podPressMedia);
			}

			while ($num < $this->settings['maxMediaFiles']) {
				$post->podPressMedia[$num] = $newMediaDefaults;
				$num++;
			}

			$num = 0;
			while ($num < $this->settings['maxMediaFiles']) {
				if(!isset($post->podPressMedia[$num]['showme'])) {
					$post->podPressMedia[$num]['showme'] = 'true';
				}
				if($post->podPressMedia[$num]['showme'] == 'false') {
					$num++;
					continue;
				}

				if($this->settings['enablePodangoIntegration']) {
					if($podangoMediaFiles[$post->podPressPostSpecific['PodangoMediaFileID']]['Filename'] == basename($post->podPressMedia[$num]['URI'])) {
						$post->podPressMedia[$num]['URI'] = 'Podango:'.$podangoMediaFiles[$post->podPressPostSpecific['PodangoMediaFileID']]['Podcast'].':'.$podangoMediaFiles[$post->podPressPostSpecific['PodangoMediaFileID']]['ID'].':'.$podangoMediaFiles[$post->podPressPostSpecific['PodangoMediaFileID']]['EpisodeID'].':'.$podangoMediaFiles[$post->podPressPostSpecific['PodangoMediaFileID']]['Filename'];
					}
				}

				if($post->podPressMedia[$num]['rss'] == 'on') {
					$post->podPressMedia[$num]['rss'] = 'true';
				} else {
					$post->podPressMedia[$num]['rss'] = 'false';
				}
				if($post->podPressMedia[$num]['atom'] == 'on') {
					$post->podPressMedia[$num]['atom'] = 'true';
				} else {
					$post->podPressMedia[$num]['atom'] = 'false';
				}
				if($post->podPressMedia[$num]['feedonly'] == 'on') {
					$post->podPressMedia[$num]['feedonly'] = 'true';
				} else {
					$post->podPressMedia[$num]['feedonly'] = 'false';
				}
				if(!isset($post->podPressMedia[$num]['disablePlayer']) || $post->podPressMedia[$num]['disablePlayer'] == false || $post->podPressMedia[$num]['disablePlayer'] == 'false') {
					$post->podPressMedia[$num]['disablePlayer'] = 'false';
				} else {
					$post->podPressMedia[$num]['disablePlayer'] = 'true';
				}

				if(!isset($post->podPressMedia[$num]['disablePreview']) || $post->podPressMedia[$num]['disablePreview'] == false || $post->podPressMedia[$num]['disablePreview'] == 'false') {
					$post->podPressMedia[$num]['disablePreview'] = 'false';
				} else {
					$post->podPressMedia[$num]['disablePreview'] = 'true';
				}

				if($post->podPressMedia[$num]['premium_only'] == 'on' || $post->podPressMedia[$num]['premium_only'] == true) {
					$post->podPressMedia[$num]['content_level'] = 'premium_content';
				}
				if(!isset($post->podPressMedia[$num]['content_level'])) {
					$post->podPressMedia[$num]['content_level'] = 'free';
				}
				echo "\n";
				echo "podPressAddMediaFile(".$post->podPressMedia[$num]['showme'].", '".$post->podPressMedia[$num]['URI']."', '".$post->podPressMedia[$num]['URI_torrent']."', '".$post->podPressMedia[$num]['title']."', '".$post->podPressMedia[$num]['type']."', '".$post->podPressMedia[$num]['size']."', '".$post->podPressMedia[$num]['duration']."', '".$post->podPressMedia[$num]['dimensionW']."', '".$post->podPressMedia[$num]['dimensionH']."', '".$post->podPressMedia[$num]['previewImage']."', ".$post->podPressMedia[$num]['rss'].", ".$post->podPressMedia[$num]['atom'].", ".$post->podPressMedia[$num]['feedonly'].", ".$post->podPressMedia[$num]['disablePlayer'].", '".$post->podPressMedia[$num]['content_level']."');\n";
				$num++;
			}
			echo '</script>'."\n";

			echo '<div id="podPressstuff" class="dbx-group">'."\n";
			echo '	<fieldset id="podpresscontent" class="dbx-box">'."\n";
			echo '		<h3 class="dbx-handle">'.__('Podcasting', 'podpress').'</h3> '."\n";
			echo '		<div class="dbx-content" id="podPress_mediaFileList">'."\n";
			echo '			<strong>'.__('Podcasting', 'podpress').' '.__('Files', 'podpress').'</strong><br/>'."\n";

			// debug start
			$num = 0;
			while ($num < $this->settings['maxMediaFiles']) {
				if(!isset($post->podPressMedia[$num])) {
					$num++;
					continue;
				}
				$thisMedia = $post->podPressMedia[$num];
				if($thisMedia['showme'] == 'true') {
					$display_text = 'block';
				}	else {
					$display_text = 'none';
				}
				echo '     	<div id="podPressMediaFileContainer_'.$num.'" class="wrap" style="visibility: visible; display: '.$display_text.';">'."\n";
				echo '				<table border="0" style="width: 100%;"><tr><td style="border-right-style: none; text-align: left;"><label><strong>'.__('Media File', 'podpress').':</strong> </label></td>';
				echo '				<td style="border-left-style: none; text-align: right;">';
				echo '						<input type="button" value="'.__('Move Up', 'podpress').'" onclick="podPressMoveFile('.$num.', \'up\'); podPressDisplayMediaFiles();"/>'."\n";
				echo '						<input type="button" value="'.__('Move Down', 'podpress').'" onclick="podPressMoveFile('.$num.', \'down\'); podPressDisplayMediaFiles();"/>'."\n";
				echo '						<input type="button" name="podPressAddAnother" value="'.__('Remove File', 'podpress').'" onclick="podPressRemoveFile('.$num.'); podPressDisplayMediaFiles();"/>';
				echo '				</td></tr></table>'."\n";
				echo '				<table border="0">'."\n";
				echo '					<tr>'."\n";
				echo '						<td>'."\n";
				echo '							<label for="podPressMedia_'.$num.'_URI">'.__('Location', 'podpress').'</label>: '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				if(!empty($files) || $this->settings['enablePodangoIntegration']) {
					$fileOptionList = '';
					echo '						<select name="podPressMedia['.$num.'][URI]" id="podPressMedia_'.$num.'_URI" width="35" onchange="javascript: if(this.value==\'!\') { podPress_customSelectVal(this, \'Specifiy URL.\'); } podPressMediaFiles['.$num.'][\'URI\'] = this.value; podPressDetectType('.$num.');">'."\n";
					echo '							<option value="!">'.__('Specify URL', 'podpress').'...</option>'."\n";
					$fileSelected = false;
					if($this->settings['enablePodangoIntegration']) {
						$podangoOptGroup = '';
						$podangoFirstOptGroup = true;
						foreach($podangoMediaFiles as $podangoMediaFile) {
							if(!empty($podangoMediaFile['EpisodeID']) && $podangoMediaFile['EpisodeID'] != $post->podPressPostSpecific['PodangoEpisodeID'] && $post->post_title != $podangoMediaFile['EpisodeTitle']) {
								continue;
							}
							if($podangoOptGroup != $podangoMediaFile['Podcast']) {
								$podangoOptGroup = $podangoMediaFile['Podcast'];
								if(!$podangoFirstOptGroup) {
									echo "							</optgroup>\n";
								}
								$x = $this->podangoAPI->GetPodcast($podangoMediaFile['Podcast'], true);
								echo '							<optgroup name="PodangoOptGroup'.$podangoMediaFile['Podcast'].'" label="Podango Podcast: '.$x['Title'].'">'."\n";
								unset($x);
							}
							$key = 'Podango:'.$podangoMediaFile['Podcast'].':'.$podangoMediaFile['ID'].':'.$podangoMediaFile['EpisodeID'].':'.$podangoMediaFile['Filename'];
							if($key == $thisMedia['URI']) {
								$xSelected = ' selected="selected"';
								$fileSelected = true;
							} else {
								$xSelected = '';
							}
							echo '								<option value="'.$key.'"'.$xSelected.'>'.$podangoMediaFile['Filename'].'</option>'."\n";
						}
						echo "							</optgroup>\n";
						echo '							<optgroup name="LocallyHosted" label="Locally Hosted">'."\n";

					}
					foreach ($files as $key=>$val) {
						if(is_numeric($key)) {
							$key = $val;
						}
						if($key == $thisMedia['URI']) {
							$xSelected = ' selected="selected"';
							$fileSelected = true;
						} else {
							$xSelected = '';
						}
						$fileOptionList .= '							<option value="'.$key.'"'.$xSelected.'>'.podPress_stringLimiter($val, 45, true).'</option>'."\n";
					}
					if(!$fileSelected) {
						echo '							<option value="'.$thisMedia['URI'].'" selected="selected">'.podPress_stringLimiter($thisMedia['URI'], 45, true).'</option>'."\n";
					}
					echo $fileOptionList;
					unset($fileOptionList);
					if($this->settings['enablePodangoIntegration']) {
						echo "							</optgroup>\n";
					}

					echo '						</select>'."\n";
				} else {
					echo '							<input type="text" id="podPressMedia_'.$num.'_URI" name="podPressMedia['.$num.'][URI]" size="40" value="'.$thisMedia['URI'].'" onchange="javascript: podPressMediaFiles['.$num.'][\'URI\'] = this.value; podPressDetectType('.$num.');" />'."\n";
				}
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				if($this->settings['enableTorrentCasting']) {
					echo '					<tr>'."\n";
					echo '						<td>'."\n";
					echo '							<label for="podPressMedia_'.$num.'_URItorrent">'.__('.torrent Location', 'podpress').'</label>: '."\n";
					echo '						</td>'."\n";
					echo '						<td>'."\n";
					echo '							<input type="text" id="podPressMedia_'.$num.'_URItorrent" name="podPressMedia['.$num.'][URI_torrent]" size="40" value="'.$thisMedia['URI_torrent'].'" onchange="javascript: podPressMediaFiles['.$num.'][\'URI_torrent\'] = this.value;" />'."\n";
					echo '						</td>'."\n";
					echo '					</tr>'."\n";
				}
				echo '					<tr>'."\n";
				echo '						<td>'."\n";
				echo '							<label for="podPressMedia_'.$num.'_title">'.__('Title', 'podpress').'</label> ('.__('optional', 'podpress').'): '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<input type="text" id="podPressMedia_'.$num.'_title" name="podPressMedia['.$num.'][title]" size="40" value="'.stripslashes($thisMedia['title']).'" onchange="javascript: podPressMediaFiles['.$num.'][\'title\'] = this.value;" />'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr>'."\n";
				echo '						<td>'."\n";
				echo '							<label for="podPressMedia_'.$num.'_type">'.__('Type', 'podpress').'</label>: '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<select id="podPressMedia_'.$num.'_type" name="podPressMedia['.$num.'][type]" onchange="javascript: podPressMediaFiles['.$num.'][\'type\'] = this.value; podPressAdjustMediaFieldsBasedOnType('.$num.');" >'."\n"; podPress_mediaOptions();	echo '</select>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr id="podPressMediaSizeWrapper_'.$num.'" >'."\n";
				echo '						<td>'."\n";
				echo '							<label for="podPressMedia_'.$num.'_size">'.__('Size', 'podpress').'</label>: '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<input type="text" id="podPressMedia_'.$num.'_size" name="podPressMedia['.$num.'][size]" size="10" value="'.$thisMedia['size'].'"  onchange="javascript: podPressMediaFiles['.$num.'][\'size\'] = this.value;"/> &nbsp;&nbsp; <input type="button" name="AutoDetect" value="'.__('Auto Detect', 'podpress').'" onclick="podPressDetectLength('.$num.', document.getElementById(\'podPressMedia_'.$num.'_URI\').value);"/>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr id="podPressMediaDurationWrapper_'.$num.'" style="display: none;">'."\n";
				echo '						<td>'."\n";
				echo '							<label for="podPressMedia_'.$num.'_duration">'.__('Duration', 'podpress').'</label>: '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<input type="text" id="podPressMedia_'.$num.'_duration" name="podPressMedia['.$num.'][duration]" size="10" value="'.$thisMedia['duration'].'"  onchange="javascript: podPressMediaFiles['.$num.'][\'duration\'] = this.value;"/> &nbsp;&nbsp; <input type="button" name="AutoDetect" value="'.__('Auto Detect', 'podpress').'" onclick="podPressDetectDuration('.$num.', document.getElementById(\'podPressMedia_'.$num.'_URI\').value);"/> ('.__('May take several seconds/minutes', 'podpress').')'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				if(empty($thisMedia['previewImage'])) {
					$thisMedia['previewImage'] = podPress_url().'images/vpreview_center.png';
				}
				echo '					<tr id="podPressMediaPreviewImageWrapper_'.$num.'" style="display: none;">'."\n";
				echo '						<td>'."\n";
				echo '							<label for="podPressMedia_'.$num.'_previewImage">'.__('Preview Image URL', 'podpress').'</label>: '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<input type="text" id="podPressMedia_'.$num.'_previewImage" name="podPressMedia['.$num.'][previewImage]" size="40" value="'.$thisMedia['previewImage'].'" onchange="javascript: podPressMediaFiles['.$num.'][\'previewImage\'] = this.value; podPressShowPreviewImage('.$num.');" />'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr id="podPressMediaPreviewImageDisplayWrapper_'.$num.'" style="display: none;">'."\n";
				echo '						<td valign="top">'."\n";
				echo '							'.__('Preview Image', 'podpress').': '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<div id="podPressPlayerSpace_'.$num.'"></div>'."\n";
				echo '<script type="text/javascript"><!--'."\n";
				echo "	document.getElementById('podPressPlayerSpace_".$num."').innerHTML = podPressGenerateVideoPreview (".$num.", '', '', '', document.getElementById('podPressMedia_".$num."_previewImage').value, true);\n";
				echo "--></script>\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";

				echo '					<tr id="podPressMediaDimensionWrapper_'.$num.'">'."\n";
				echo '						<td>'."\n";
				echo '							'.__('Dimensions', 'podpress').' (WxH): '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<input type="text" id="podPressMedia_'.$num.'_dimensionW" name="podPressMedia['.$num.'][dimensionW]" size="5" value="'.$thisMedia['dimensionW'].'" onchange="javascript: podPressMediaFiles['.$num.'][\'dimensionW\'] = this.value;" />x<input type="text" id="podPressMedia_'.$num.'_dimensionH" name="podPressMedia['.$num.'][dimensionH]" size="5" value="'.$thisMedia['dimensionH'].'" onchange="javascript: podPressMediaFiles['.$num.'][\'dimensionH\'] = this.value;" /> '."\n";
				echo '							<select onchange="javascript: podPressUpdateDimensions(\''.$num.'\', this.value);">'."\n"; podPress_videoDimensionOptions();	echo '</select>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";

				if($post->post_status == 'static') {
					echo '					<tr style="display: none;">'."\n";
				} else {
					echo '					<tr>'."\n";
				}
				echo '						<td nowrap="nowrap">'."\n";
				echo '							'.__('Included in', 'podpress').': '."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							&nbsp;<label for="podPressMedia_'.$num.'_rss">'.__('RSS2', 'podpress').'</label> <input type="checkbox" id="podPressMedia_'.$num.'_rss" name="podPressMedia['.$num.'][rss]" onchange="javascript: podPressMediaFiles['.$num.'][\'rss\'] = this.checked; podPressSetSingleRSS('.$num.');" />'."\n";
				echo '							&nbsp;<label for="podPressMedia_'.$num.'_atom">'.__('ATOM', 'podpress').'</label> <input type="checkbox" id="podPressMedia_'.$num.'_atom" name="podPressMedia['.$num.'][atom]" onchange="javascript: podPressMediaFiles['.$num.'][\'atom\'] = this.checked;" />'."\n";
				echo '							&nbsp;<label for="podPressMedia_'.$num.'_feedonly">'.__('Feed Only', 'podpress').'</label> <input type="checkbox" id="podPressMedia_'.$num.'_feedonly" name="podPressMedia['.$num.'][feedonly]" onchange="javascript: podPressMediaFiles['.$num.'][\'feedonly\'] = this.checked;" />'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				if($this->settings['enablePremiumContent']) {
					echo '					<tr>'."\n";
					echo '						<td nowrap="nowrap">'."\n";
					echo '							<label for="podPressMedia_'.$num.'_content_level">'.__('Subscription', 'podpress').'</label>:';
					echo '						</td>'."\n";
					echo '						<td>'."\n";
					echo '							<select id="podPressMedia_'.$num.'_content_level" name="podPressMedia['.$num.'][content_level]" onchange="javascript: podPressMediaFiles['.$num.'][\'content_level\'] = this.value;">'."\n";
					echo '								<option value="free" '; if(empty($thisMedia['content_level']) || $thisMedia['content_level'] == 'free') { echo 'selected="selected"';	}	echo '>'.__('Free', 'podpress').'</option>'."\n";
					foreach (podPress_getCapList(true) as $cap) {
						if(substr($cap, -8, 8) == '_content') {
							echo '								<option value="'.$cap.'" '; if($thisMedia['content_level'] == $cap) { echo 'selected="selected"';	}	echo '>'.__(podPress_getCapName($cap), 'podpress').'</option>'."\n";
						}
					}
					echo '							</select>'."\n";
					echo '						</td>'."\n";
					echo '					</tr>'."\n";
				}
				echo '					<tr>'."\n";
				echo '						<td nowrap="nowrap">'."\n";
				echo '							<label for="podPressMedia_'.$num.'_disablePlayer">'.__('Disable Player', 'podpress').'</label>:'."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<input type="checkbox" id="podPressMedia_'.$num.'_disablePlayer" name="podPressMedia['.$num.'][disablePlayer]"'; if($thisMedia['disablePlayer'] != 'false') { echo 'checked="checked" '; } echo ' onchange="javascript: podPressMediaFiles['.$num.'][\'disablePlayer\'] = this.checked;" />'."\n";
				echo '							&nbsp;&nbsp; (Use if this media file is not compatible the included player.)'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr>'."\n";
				echo '						<td nowrap="nowrap">'."\n";
				echo '							<label for="podPressMedia_'.$num.'_disablePreview">'.__('Disable Preview Player', 'podpress').'</label>:'."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<input type="checkbox" id="podPressMedia_'.$num.'_disablePreview" name="podPressMedia['.$num.'][disablePreview]"'; if($thisMedia['disablePreview'] != 'false') { echo 'checked="checked" '; } echo ' onchange="javascript: podPressMediaFiles['.$num.'][\'disablePreview\'] = this.checked;" />'."\n";
				echo '							&nbsp;&nbsp; (Use this to disable the "Click to Player" preview player.)'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				$actionMedia = $thisMedia;
				$actionMedia['num'] = $num;
				do_action('podPress_customMediaData', array($actionMedia));
				echo '					<tr>'."\n";
				echo '						<td style="vertical-align: top;" nowrap="nowrap">'."\n";
				echo '							'.__('Tag (ID3) Info', 'podpress').":\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<input type="button" name="podPressMedia['.$num.'][mp3_details_button]" id="podPressMedia_'.$num.'_mp3_detailsbutton_" value="Show" onclick="javascript: podPressShowHideMP3Details('.$num.');"/>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr>'."\n";
				echo '						<td colspan="2">'."\n";
				echo '							<div id="podPressMedia_'.$num.'_mp3_details" style="display: none; vertical-align: top;">'."\n";
				echo '							</div>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '				</table>'."\n";
				echo '			</div>'."\n";

				$num++;
			}

			echo '	<h3>'.__('To control player location in your post, put', 'podpress').' '.$this->podcastTag.' '.__(' where you want it to appear', 'podpress').'.</h3> '."\n";
			echo '			<input type="button" name="podPressAddAnother" value="Add Media File" onclick="javascript: podPressAddMediaFile(true, \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'\', \'new\', true, false, false, \'free\'); podPressDisplayMediaFiles();"/>'."\n";
			if($entryType != 'page') {
				echo '			<br/>'."\n";
				echo '			<strong>'.__('Post specific settings for iTunes', 'podpress').': </strong>'."\n";
				echo '			<input type="button" name="iTunesSpecificSettings_button" id="iTunesSpecificSettings_button" value="Show" onclick="javascript: podPressShowHideDiv(\'iTunesSpecificSettings\');"/>'."\n";
				echo '     	<div class="wrap" id="iTunesSpecificSettings" style="display: none;">'."\n";
				echo '				<table border="0">'."\n";

				echo '					<tr>'."\n";
				echo '						<td width="1%" nowrap="nowrap">'."\n";
				echo '							<label for="iTunesSubtitleChoice">'.__('iTunes:Subtitle', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesSubtitleHelp\');">(?)</a>:'."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<select id="iTunesSubtitleChoice" name="iTunesSubtitleChoice" onchange="javascript: if(this.value == \'Custom\') { document.getElementById(\'iTunesSubtitleWrapper\').style.display=\'\'; } else { document.getElementById(\'iTunesSubtitleWrapper\').style.display=\'none\'; }">'."\n";
				echo '								<option value="PostExcerpt" '; if($post->podPressPostSpecific['itunes:subtitle'] == '##PostExcerpt##') { echo 'selected="selected"';	}	echo '>'.__('Use Post Excerpt', 'podpress').'</option>'."\n";
				echo '								<option value="Custom" '; if($post->podPressPostSpecific['itunes:subtitle'] != '##PostExcerpt##') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
				echo '							</select>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr id="iTunesSubtitleHelp" style="display: none;">'."\n";
				echo '						<td colspan="2">'.__('By default this is taken from the first 25 characters of the blog Post text.', 'podpress').'</td>'."\n";
				echo '					</tr>'."\n";
				if($post->podPressPostSpecific['itunes:subtitle'] == '##PostExcerpt##') { $tempShowMe = 'style="display: none;"';$post->podPressPostSpecific['itunes:subtitle'] = ''; } else { $tempShowMe = ''; }
				echo '					<tr id="iTunesSubtitleWrapper" '.$tempShowMe.'>'."\n";
				echo '						<td width="1%" nowrap="nowrap">&nbsp;</td>'."\n";
				echo '						<td>'."\n";
				echo '							<textarea name="iTunesSubtitle" rows="4" cols="40">'.stripslashes($post->podPressPostSpecific['itunes:subtitle']).'</textarea>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr>'."\n";
				echo '						<td width="1%" nowrap="nowrap">'."\n";
				echo '							<label for="iTunesSummaryChoice">'.__('iTunes:Summary', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesSummaryHelp\');">(?)</a>:'."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<select id="iTunesSummaryChoice" name="iTunesSummaryChoice" onchange="javascript: if(this.value == \'Custom\') { document.getElementById(\'iTunesSummaryWrapper\').style.display=\'\'; } else { document.getElementById(\'iTunesSummaryWrapper\').style.display=\'none\'; }">'."\n";
				echo '								<option value="PostExcerpt" '; if($post->podPressPostSpecific['itunes:summary'] == '##PostExcerpt##') { echo 'selected="selected"';	}	echo '>'.__('Use Post Excerpt', 'podpress').'</option>'."\n";
				echo '								<option value="Global" '; if($post->podPressPostSpecific['itunes:summary'] == '##Global##') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').'</option>'."\n";
				echo '								<option value="Custom" '; if($post->podPressPostSpecific['itunes:summary'] != '##Global##' && $post->podPressPostSpecific['itunes:summary'] != '##PostExcerpt##') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
				echo '							</select>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr id="iTunesSummaryHelp" style="display: none;">'."\n";
				echo '						<td colspan="2">'.__('By default this is taken from the blog Post text.', 'podpress').'</td>'."\n";
				echo '					</tr>'."\n";
				if($post->podPressPostSpecific['itunes:summary'] == '##Global##' || $post->podPressPostSpecific['itunes:summary'] == '##PostExcerpt##') { $tempShowMe = 'style="display: none;"';	$post->podPressPostSpecific['itunes:summary'] = ''; } else { $tempShowMe = ''; }
				echo '					<tr id="iTunesSummaryWrapper" '.$tempShowMe.'>'."\n";
				echo '						<td width="1%" nowrap="nowrap">&nbsp;</td>'."\n";
				echo '						<td>'."\n";
				echo '							<textarea name="iTunesSummary" rows="4" cols="40">'.stripslashes($post->podPressPostSpecific['itunes:summary']).'</textarea>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";

				echo '					<tr>'."\n";
				echo '						<td width="1%" nowrap="nowrap">'."\n";
				echo '							<label for="iTunesKeywordsChoice">'.__('iTunes:Keywords', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesKeywordsHelp\');">(?)</a>:'."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<select id="iTunesKeywordsChoice" name="iTunesKeywordsChoice" onchange="javascript: if(this.value == \'Custom\') { document.getElementById(\'iTunesKeywordsWrapper\').style.display=\'\'; } else { document.getElementById(\'iTunesKeywordsWrapper\').style.display=\'none\'; }">'."\n";
				echo '								<option value="WordPressCats" '; if($post->podPressPostSpecific['itunes:keywords'] == '##WordPressCats##') { echo 'selected="selected"';	}	echo '>'.__('Use WordPress Categories', 'podpress').'</option>'."\n";
				echo '								<option value="Global" '; if($post->podPressPostSpecific['itunes:keywords'] == '##Global##') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.podPress_stringLimiter(stripslashes($this->settings['iTunes']['keywords']), 40) .')</option>'."\n";
				echo '								<option value="Custom" '; if($post->podPressPostSpecific['itunes:keywords'] != '##Global##' && $post->podPressPostSpecific['itunes:keywords'] != '##WordPressCats##') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
				echo '							</select>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr id="iTunesKeywordsHelp" style="display: none;">'."\n";
				echo '						<td colspan="2">'.__('Not visible in iTunes, but used for searches.', 'podpress').'</td>'."\n";
				echo '					</tr>'."\n";
				if($post->podPressPostSpecific['itunes:keywords'] == '##Global##' || $post->podPressPostSpecific['itunes:keywords'] == '##WordPressCats##') { $tempShowMe = 'style="display: none;"';	$post->podPressPostSpecific['itunes:keywords'] = ''; } else { $tempShowMe = ''; }
				echo '					<tr id="iTunesKeywordsWrapper" '.$tempShowMe.'>'."\n";
				echo '						<td width="1%" nowrap="nowrap">&nbsp;</td>'."\n";
				echo '						<td>'."\n";
				echo '							'.__('Separate multiples with commas', 'podpress').', '.__('max 8', 'podpress').'<br/><textarea name="iTunesKeywords" rows="4" cols="40">'.stripslashes($post->podPressPostSpecific['itunes:keywords']).'</textarea>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";

				echo '					<tr>'."\n";
				echo '						<td width="1%" nowrap="nowrap">'."\n";
				echo '							<label for="iTunesAuthorChoice">'.__('iTunes:Author', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesAuthorHelp\');">(?)</a>:'."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<select id="iTunesAuthorChoice" name="iTunesAuthorChoice" onchange="javascript: if(this.value == \'Custom\') { document.getElementById(\'iTunesAuthorWrapper\').style.display=\'\'; } else { document.getElementById(\'iTunesAuthorWrapper\').style.display=\'none\'; }">'."\n";
				echo '								<option value="Global" '; if($post->podPressPostSpecific['itunes:author'] == '##Global##') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.podPress_stringLimiter(ucfirst(stripslashes($this->settings['iTunes']['author'])), 40).')</option>'."\n";
				echo '								<option value="Custom" '; if($post->podPressPostSpecific['itunes:author'] != '##Global##') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
				echo '							</select>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";

				echo '					<tr id="iTunesAuthorHelp" style="display: none;">'."\n";
				echo '						<td colspan="2">'.__('Used if this Author is different than the feeds author.', 'podpress').'</td>'."\n";
				echo '					</tr>'."\n";
				if($post->podPressPostSpecific['itunes:author'] == '##Global##') { $tempShowMe = 'style="display: none;"';	$post->podPressPostSpecific['itunes:author'] = ''; } else { $tempShowMe = ''; }
				echo '					<tr id="iTunesAuthorWrapper" '.$tempShowMe.'>'."\n";
				echo '						<td width="1%" nowrap="nowrap">&nbsp;</td>'."\n";
				echo '						<td><input type="text" name="iTunesAuthor" size="40" value="'.stripslashes($post->podPressPostSpecific['itunes:author']).'" /></td>'."\n";
				echo '					</tr>'."\n";

				echo '					<tr>'."\n";
				echo '						<td width="1%" nowrap="nowrap">'."\n";
				echo '							<label for="iTunesExplicit">'.__('iTunes:Explicit', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesExplicitHelp\');">(?)</a>:'."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<select id="iTunesExplicit" name="iTunesExplicit">'."\n";
				echo '								<option value="Default" '; if($post->podPressPostSpecific['itunes:explicit'] == 'Default') { echo 'selected="selected"';	}	echo '>'.__('Use Default', 'podpress').' ('.$this->settings['iTunes']['explicit'].')</option>'."\n";
				echo '								<option value="No" '; if($post->podPressPostSpecific['itunes:explicit'] == 'No') { echo 'selected="selected"';	}	echo '>'.__('No', 'podpress').'</option>'."\n";
				echo '								<option value="Yes" '; if($post->podPressPostSpecific['itunes:explicit'] == 'Yes') { echo 'selected="selected"';	}	echo '>'.__('Yes', 'podpress').'</option>'."\n";
				echo '								<option value="Clean" '; if($post->podPressPostSpecific['itunes:explicit'] == 'Clean') { echo 'selected="selected"';	}	echo '>'.__('Clean', 'podpress').'</option>'."\n";
				echo '							</select>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr id="iTunesExplicitHelp" style="display: none;">'."\n";
				echo '						<td colspan="2">'.__('Headphones Required? Sets the parental advisory graphic in name column.', 'podpress').'</td>'."\n";
				echo '					</tr>'."\n";

				echo '					<tr>'."\n";
				echo '						<td width="1%" nowrap="nowrap">'."\n";
				echo '							<label for="iTunesBlock">'.__('iTunes:Block', 'podpress').'</label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'itunesBlockHelp\');">(?)</a>:'."\n";
				echo '						</td>'."\n";
				echo '						<td>'."\n";
				echo '							<select id="iTunesBlock" name="iTunesBlock">'."\n";
				echo '								<option value="Default" '; if($post->podPressPostSpecific['itunes:block'] == 'Default') { echo 'selected="selected"';	}	echo '>'.__('Use Default', 'podpress').' ('.$this->settings['iTunes']['block'].')</option>'."\n";
				echo '								<option value="No" '; if($post->podPressPostSpecific['itunes:block'] == 'No') { echo 'selected="selected"';	}	echo '>'.__('No', 'podpress').'</option>'."\n";
				echo '								<option value="Yes" '; if($post->podPressPostSpecific['itunes:block'] == 'Yes') { echo 'selected="selected"';	}	echo '>'.__('Yes', 'podpress').'</option>'."\n";
				echo '							</select>'."\n";
				echo '						</td>'."\n";
				echo '					</tr>'."\n";
				echo '					<tr id="itunesBlockHelp" style="display: none;">'."\n";
				echo '						<td colspan="2">'.__('Prevent this episode or podcast from appearing in iTunes.', 'podpress').'</td>'."\n";
				echo '					</tr>'."\n";
				echo '				</table>'."\n";
				echo '			</div>'."\n";
			}
			if($this->settings['enablePodangoIntegration']) {
				echo "			<br/>\n";
				echo '			<strong>Podango File Uploader</strong>';
				if($this->settings['podangoDefaultPodcast'] == '##ALL##') {
					$podangoPodcastList = $this->podangoAPI->GetPodcasts(true);
					echo ' <strong>for: </strong><select name="podPressPodangoPodcastID" id="podPressPodangoPodcastID" onChange="javascript: document.getElementById(\'podangoUploadFrame\').src=\''.$this->podangoAPI->fileUploader.'?podcastId=\'+this.value">'."\n";
					foreach ($podangoPodcastList as $k=>$v) {
						if(!isset($podangoPodcastID)) {
							$podangoPodcastID = $k;
						}
						echo '						<option value="'.$k.'">'.$v['Title'].'</option>'."\n";
					}
					echo '					</select>'."\n";
				} else {
					$podangoPodcastID = $this->settings['podangoDefaultPodcast'];
				}
				echo '<br/>'."\n";
				echo '			<iframe src="'.$this->podangoAPI->fileUploader.'?podcastId='.$podangoPodcastID.'" id="podangoUploadFrame" title="Podango Upload" border="0" width="560" height="110"> </iframe>'."\n";
			}
			echo '		</div>'."\n";
			echo '	<h3>'.__('End of podPress. File Uploading support is not part of podPress', 'podpress').'</h3> '."\n";
			echo '	</fieldset>'."\n";
			echo '</div><br/>'."\n";
			echo '<script type="text/javascript">podPressDisplayMediaFiles(); </script>'."\n";
		}

		function post_edit($post_id) {
			GLOBAL $post;
			if($this->justposted) {
				return;
			}
			$this->justposted = true;
			if($this->checkWritableTempFileDir(false)) {
				if(!empty($this->tempFileSystemPath)) {
					$dh  = opendir($this->tempFileSystemPath);
					while (false !== ($filename = readdir($dh))) {
						if(substr($filename, 0, 10) == 'feedcache_' && is_file($this->tempFileSystemPath.'/'.$filename)) {
							unlink($this->tempFileSystemPath.'/'.$filename);
						}
					}
				}
			}
			if(isset($_POST['podPressMedia'])) {
				foreach ($_POST['podPressMedia'] as $val) {
					if($val['URI'] != '') {
						$verifiedMedia[] = $val;
					}
				}
				delete_post_meta($post_id, 'podPressMedia');
				if(!empty($verifiedMedia)) {
					if($this->settings['enablePodangoIntegration']) {
						foreach ($verifiedMedia as $key=>$val) {
							if(substr($val['URI'], 0, strlen('Podango:')) == 'Podango:') {
								$fileNameParts = explode(':', $val['URI']);
								$podPressPostSpecific['PodangoPodcastID'] = $fileNameParts[1];
								$podPressPostSpecific['PodangoMediaFileID'] = $fileNameParts[2];
								$podPressPostSpecific['PodangoEpisodeID'] = $fileNameParts[3];

								if(empty($podPressPostSpecific['PodangoMediaFileID'])) {
									// need to add the mediafileID lookup
								}

								if(empty($podPressPostSpecific['PodangoEpisodeID'])) {
									if($this->settings['podangoDefaultPodcast'] == '##ALL##') {
										$podangoPodcastID = (int)$_POST['podPressPodangoPodcastID'];
									} else {
										$podangoPodcastID = $this->settings['podangoDefaultPodcast'];
									}
									$podPressPostSpecific['PodangoEpisodeID'] = $this->podangoAPI->CreateEpisode($podangoPodcastID, $_POST['post_title'], $_POST['content'], '', $podPressPostSpecific['PodangoMediaFileID']);
								} else {
									$this->podangoAPI->UpdateEpisode($podPressPostSpecific['PodangoEpisodeID'], $_POST['post_title'], $_POST['content'], '', $podPressPostSpecific['PodangoMediaFileID']);
								}
								$val['URI'] = $this->podangoAPI->mediaTrackerURL.'555/'.$podPressPostSpecific['PodangoEpisodeID'].'/'.$fileNameParts[4];
								$verifiedMedia[$key] = $val;
								unset($fileNameParts);
							}
						}
					}
					podPress_add_post_meta($post_id, 'podPressMedia', $verifiedMedia, true) ;
				}
			}
			if($_POST['iTunesSubtitleChoice'] == 'Custom' && !empty($_POST['iTunesSubtitle'])) {
				$podPressPostSpecific['itunes:subtitle'] = $_POST['iTunesSubtitle'];
			} else {
				$podPressPostSpecific['itunes:subtitle'] = '##PostExcerpt##';
			}

			if($_POST['iTunesSummaryChoice'] == 'Custom' && !empty($_POST['iTunesSummary'])) {
				$podPressPostSpecific['itunes:summary'] = $_POST['iTunesSummary'];
			} elseif($_POST['iTunesSummaryChoice'] == 'Global') {
				$podPressPostSpecific['itunes:summary'] = '##Global##';
			} else {
				$podPressPostSpecific['itunes:summary'] = '##PostExcerpt##';
			}

			if($_POST['iTunesKeywordsChoice'] == 'Custom' && !empty($_POST['iTunesKeywords'])) {
				$podPressPostSpecific['itunes:keywords'] = $_POST['iTunesKeywords'];
			} elseif($_POST['iTunesKeywordsChoice'] == 'Global') {
				$podPressPostSpecific['itunes:keywords'] = '##Global##';
			} else {
				$podPressPostSpecific['itunes:keywords'] = '##WordPressCats##';
			}

			if($_POST['iTunesAuthorChoice'] == 'Custom' && !empty($_POST['iTunesAuthor'])) {
				$podPressPostSpecific['itunes:author'] = $_POST['iTunesAuthor'];
			} else {
				$podPressPostSpecific['itunes:author'] = '##Global##';
			}

			if($_POST['iTunesExplicit']) {
				$podPressPostSpecific['itunes:explicit'] = $_POST['iTunesExplicit'];
			} else {
				$podPressPostSpecific['itunes:explicit'] = 'No';
			}

			if($_POST['iTunesBlock']) {
				$podPressPostSpecific['itunes:block'] = $_POST['iTunesBlock'];
			} else {
				$podPressPostSpecific['itunes:block'] = 'No';
			}

			delete_post_meta($post_id, 'podPressPostSpecific');
			podPress_add_post_meta($post_id, 'podPressPostSpecific', $podPressPostSpecific, true) ;
			$post->podPressPostSpecific = $podPressPostSpecific;


			/*
			if(class_exists(snoopy)) {
				if(!empty($this->settings['iTunes']['FeedID'])) {
					$client = new Snoopy();
					$client->_fp_timeout = 10;
					$x = $client->fetch('https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id='.$this->settings['iTunes']['FeedID']);
				} elseif(!empty($this->settings['iTunes']['feedURL'])) {
					$client = new Snoopy();
					$client->_fp_timeout = 10;
					$x = $client->fetch('https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?feedURL='.$this->settings['iTunes']['feedURL']);
				}
			}
			 */
		}

		function edit_category_form($input) {
			$data = podPress_get_option('podPress_category_'.$input->cat_ID);

			if(empty($data['podcastFeedURL'])) {
				$data['podcastFeedURL'] = get_settings('siteurl').'/?feed=rss2&cat='.$input->cat_ID;
			}
			echo '<div class="wrap">'."\n";
			echo '	<h2>'.__('podPress CategoryCasting', 'podpress').'</h2>'."\n";
			echo '					<label for="categoryCasting"><strong>'.__('Enable Category Casting', 'podpress').'</strong></label>  <a href="javascript:void(null);" onclick="javascript: podPressShowHideDiv(\'categoryCastingHelp\');">(?)</a>:';
			echo '					<input type="checkbox" name="categoryCasting" id="categoryCasting" '; if($data['categoryCasting'] == 'true') { echo 'checked="checked"'; } echo ' onclick="javascript: podPress_updateCategoryCasting();"/>'."\n";
			echo '					<div id="categoryCastingHelp" style="display: none;">'."\n";
			echo '						Only use CategoryCasting if you understand what this feature is and you need it.<br/>This feature requires the custom wp-rss2.php that is in the podpress/optional_files directory<br/> (TODO: explain this feature better).'."\n";
			echo '					</div>'."\n";

			echo '  <div class="wrap" id="iTunesSpecificSettings" style="display: none; border: 0;">'."\n";
			podPress_DirectoriesPreview('edit_category_form');
			echo '		<fieldset class="options">'."\n";
			echo '			<legend>'.__('Podcast Feed Options', 'podpress').'</legend>'."\n";

			echo '		<table width="100%" cellspacing="2" cellpadding="5" class="editform" >'."\n";
			echo '			<tr>'."\n";
			echo '				<td width="50%"><h3>'.__('iTunes Settings', 'podpress').'</h3></td>'."\n";
			echo '				<td width="50%"><h3>'.__('Standard Settings', 'podpress').'</h3></td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesFeedID"><strong>'.__('iTunes:FeedID', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input name="iTunesFeedID" id="iTunesFeedID" type="text" value="'.htmlentities($data['iTunesFeedID'], ENT_QUOTES, get_settings('blog_charset')).'" size="10" />';
			echo '					<input type="button" name="Ping_iTunes_update" value="Ping iTunes Update" onclick="javascript: if(document.getElementById(\'iTunesFeedID\').value != \'\') { window.open(\'https://phobos.apple.com/WebObjects/MZFinance.woa/wa/pingPodcast?id=\'+document.getElementById(\'iTunesFeedID\').value); }"/>'."\n";
			echo '				</td>'."\n";

			echo '				<td width="50%">';
			echo '					<label for="podcastFeedURL"><strong>'.__('Podcast Feed URL', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<input type="text" id="podcastFeedURL" name="podcastFeedURL" size="40" value="'.htmlentities(stripslashes($data['podcastFeedURL']), ENT_QUOTES, get_settings('blog_charset')).'" /><br />'.__('The RSS Feed URL to your podcast.', 'podpress');
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesNewFeedURL"><strong>'.__('iTunes:New-Feed-Url', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesNewFeedURLHelp\');">(?)</a>';
			echo '					<br/>';
			echo '					<select name="iTunesNewFeedURL" id="iTunesNewFeedURL">'."\n";
			echo '						<option value="#Global##" '; if($data['iTunesNewFeedURL'] == '#Global##' || empty($data['iTunesNewFeedURL'])) { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').'</option>'."\n";
			echo '						<option value="Disable" '; if($data['iTunesNewFeedURL'] == 'Disable') { echo 'selected="selected"'; } echo '>'.__('Disable', 'podpress').'</option>'."\n";
			echo '						<option value="Enable" '; if($data['iTunesNewFeedURL'] == 'Enable') { echo 'selected="selected"'; } echo '>'.__('Enable', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<br/>'.__('Enabling will set the tag to the "Podcast Feed URL".  <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesNewFeedURLHelp\');"><font color="red">(Warning)</font></a>', 'podpress')."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<input type="button" value="Validate your Feed" onclick="javascript: if(document.getElementById(\'add_option\').value != \'\') { window.open(\'http://www.feedvalidator.org/check.cgi?url=\'+document.getElementById(\'podcastFeedURL\').value); }"/>'."\n";
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
			echo '					<label for="blognameChoice"><strong>'.__('Blog/Podcast title', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select id="blognameChoice" name="blognameChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['blognameChoice'] == 'Global' || empty($data['blognameChoice'])) { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').'</option>'."\n";
			echo '						<option value="Append" '; if($data['blognameChoice'] == 'Append') { echo 'selected="selected"';	}	echo '>'.__('Append Category Name', 'podpress').'</option>'."\n";
			echo '						<option value="CategoryName" '; if($data['blognameChoice'] == 'CategoryName') { echo 'selected="selected"';	}	echo '>'.__('Use Category Name', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<input type="hidden" id="global_blogname" value="'.htmlentities(stripslashes(get_option('blogname')), ENT_QUOTES, get_settings('blog_charset')).'" /></td>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesSummaryChoice"><strong>'.__('iTunes:Summary', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesSummaryHelp\');">(?)</a>:'."\n";
			echo '					<br/>';
			echo '					<select id="iTunesSummaryChoice" name="iTunesSummaryChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['iTunesSummaryChoice'] != 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').'</option>'."\n";
			echo '						<option value="Custom" '; if($data['iTunesSummaryChoice'] == 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<input type="hidden" id="global_iTunesSummary" value="'.htmlentities(stripslashes($this->settings['iTunes']['summary']), ENT_QUOTES, get_settings('blog_charset')).'" />'."\n";
			echo '					<div id="iTunesSummaryWrapper" style="display: none;">'."\n";
			echo '						<textarea name="iTunesSummary" id="iTunesSummary" rows="4" cols="40" onchange="javascript: podPress_updateCategoryCasting();">'.htmlentities(stripslashes($data['iTunesSummary']), ENT_QUOTES, get_settings('blog_charset')).'</textarea>'."\n";
			echo '					</div>'."\n";
			echo '					<div id="iTunesSummaryHelp" style="display: none;">'."\n";
			echo '						'.__('By default this is taken from the blog Post text.', 'podpress')."\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="blogdescriptionChoice"><strong>'.__('Description', 'podpress').'</strong></label>:'."\n";
			echo '					<br/>';
			echo '					<select id="blogdescriptionChoice" name="blogdescriptionChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['blogdescriptionChoice'] != 'CategoryDescription') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').'</option>'."\n";
			echo '						<option value="CategoryDescription" '; if($data['blogdescriptionChoice'] == 'CategoryDescription') { echo 'selected="selected"';	}	echo '>'.__('Use Category Description', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<input type="hidden" id="global_blogdescription" value="'.htmlentities(stripslashes(get_option('blogdescription')), ENT_QUOTES, get_settings('blog_charset')).'" />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesImageChoice"><strong>'.__('iTunes:Image', 'podpress').' (300*300 pixels)</strong></label>'."\n";
			echo '					<br/>';
			echo '					<select id="iTunesImageChoice" name="iTunesImageChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['iTunesImageChoice'] != 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').'</option>'."\n";
			echo '						<option value="Custom" '; if($data['iTunesImageChoice'] == 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="iTunesImageWrapper" style="display: none;">'."\n";
			echo '						<br/>';
			echo '						<input id="iTunesImage" type="text" name="iTunesImage" value="'.$data['iTunesImage'].'" size="40" onchange="podPress_updateCategoryCasting();"/>'."\n";
			echo '						<input id="global_iTunesImage" type="hidden" value="'.$this->settings['iTunes']['image'].'"/>'."\n";
			echo '					</div>'."\n";
			echo '					<br/>';
			echo '					<img id="itunes_image_display" width="300" height="300" alt="Podcast Image - Big" src="" />'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="rss_imageChoice"><strong>'.__('Blog/RSS Image', 'podpress').' (144*144 pixels)</strong></label>'."\n";
			echo '					<br/>';
			echo '					<select id="rss_imageChoice" name="rss_imageChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['rss_imageChoice'] != 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').'</option>'."\n";
			echo '						<option value="Custom" '; if($data['rss_imageChoice'] == 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="rss_imageWrapper" style="display: none;">'."\n";
			echo '						<br/>';
			echo '						<input id="rss_image" type="text" name="rss_image" value="'.$data['rss_image'].'" size="40" onchange="javascript: podPress_updateCategoryCasting();"/>'."\n";
			echo '						<input id="global_rss_image" type="hidden" value="'.get_option('rss_image').'"/>'."\n";
			echo '					</div>'."\n";
			echo '					<br/>';
			echo '					<img id="rss_image_Display" width="144" height="144" alt="Podcast Image - Small" src="" />'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesAuthorChoice"><strong>'.__('iTunes:Author/Owner', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesAuthorHelp\');">(?)</a>:'."\n";
			echo '					<br/>';
			echo '					<select id="iTunesAuthorChoice" name="iTunesAuthorChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['iTunesAuthorChoice'] != 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.podPress_stringLimiter(ucfirst(stripslashes($this->settings['iTunes']['author'])), 40).')</option>'."\n";
			echo '						<option value="Custom" '; if($data['iTunesAuthorChoice'] == 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="iTunesAuthorWrapper" style="display: none;">'."\n";
			echo '						<input type="text" name="iTunesAuthor" size="40" id="iTunesAuthor" value="'.htmlentities(stripslashes($data['iTunesAuthor']), ENT_QUOTES, get_settings('blog_charset')).'" onchange="javascript: podPress_updateCategoryCasting();"/>';
			echo '						<input type="hidden" id="global_iTunesAuthor" value="'.htmlentities(stripslashes($this->settings['iTunes']['author']), ENT_QUOTES, get_settings('blog_charset')).'" />'."\n";
			echo '					</div>'."\n";
			echo '					<div id="iTunesAuthorHelp" style="display: none;">'."\n";
			echo '						'.__('Used if this Author is different than the feeds author.', 'podpress')."\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesAuthorEmailChoice"><strong>'.__('Owner E-mail address', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesAuthorEmailHelp\');">(?)</a>:'."\n";
			echo '					<br/>';
			echo '					<select id="iTunesAuthorEmailChoice" name="iTunesAuthorEmailChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['iTunesAuthorEmailChoice'] != 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.podPress_stringLimiter(ucfirst(stripslashes(get_option('admin_email'))), 40).')</option>'."\n";
			echo '						<option value="Custom" '; if($data['iTunesAuthorEmailChoice'] == 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="iTunesAuthorEmailWrapper" style="display: none;">'."\n";
			echo '						<input type="text" name="iTunesAuthorEmail" size="40" id="iTunesAuthorEmail" value="'.htmlentities(stripslashes($data['iTunesAuthorEmail']), ENT_QUOTES, get_settings('blog_charset')).'" onchange="javascript: podPress_updateCategoryCasting();"/>';
			echo '						<input type="hidden" id="global_iTunesAuthorEmail" value="'.htmlentities(stripslashes(get_option('admin_email')), ENT_QUOTES, get_settings('blog_charset')).'" />'."\n";
			echo '					</div>'."\n";
			echo '					<div id="iTunesAuthorEmailHelp" style="display: none;">'."\n";
			echo '						'.__('Used if this owner of this category is different than the feeds owner.', 'podpress')."\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesSubtitleChoice"><strong>'.__('iTunes:Subtitle', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesSubtitleHelp\');">(?)</a>:'."\n";
			echo '					<br/>';
			echo '					<select id="iTunesSubtitleChoice" name="iTunesSubtitleChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['iTunesSubtitleChoice'] != 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').'</option>'."\n";
			echo '						<option value="Custom" '; if($data['iTunesSubtitleChoice'] == 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="iTunesSubtitleWrapper" style="display: none;">'."\n";
			echo '						<textarea name="iTunesSubtitle" rows="4" cols="40">'.htmlentities(stripslashes($data['iTunesSubtitle']), ENT_QUOTES, get_settings('blog_charset')).'</textarea>'."\n";
			echo '					</div>'."\n";
			echo '					<div id="iTunesSubtitleHelp" style="display: none;">'."\n";
			echo '						'.__('By default this is taken from the first 25 characters of the blog Post text.', 'podpress')."\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			$langs = podPress_itunesLanguageArray();
			echo '				<td width="50%">';
			echo '					<label for="rss_language"><strong>'.__('Language', 'podpress').'</strong></label>:'."\n";
			echo '					<br/>';
			echo '					<select id="rss_language" name="rss_language" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="##Global##" '; if($data['rss_language'] == '##Global##' || empty($data['rss_language'])) { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' - '.$langs[get_option('rss_language')].' ['.get_option('rss_language').']</option>'."\n";
			podPress_itunesLanguageOptions($data['rss_language']);
			echo '					</select>'."\n";
			echo '					<input type="hidden" id="global_rss_language" value="'.$langs[get_option('rss_language')].'['.stripslashes(get_option('rss_language')).']" /></td>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td colspan="2">';
			echo '					<label for="iTunesKeywordsChoice"><strong>'.__('iTunes:Keywords', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesKeywordsHelp\');">(?)</a>:'."\n";
			echo '					<br/>';
			echo '					<select id="iTunesKeywordsChoice" name="iTunesKeywordsChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['iTunesKeywordsChoice'] != 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.podPress_stringLimiter(stripslashes($this->settings['iTunes']['keywords']), 40).')</option>'."\n";
			echo '						<option value="Custom" '; if($data['iTunesKeywordsChoice'] == 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="iTunesKeywordsWrapper" style="display: none;">'."\n";
			echo '						'.__('Separate multiples with commas', 'podpress').', '.__('max 8', 'podpress').'<br/><textarea name="iTunesKeywords" rows="4" cols="40">'.htmlentities(stripslashes($data['iTunesKeywords']), ENT_QUOTES, get_settings('blog_charset')).'</textarea>'."\n";
			echo '					</div>'."\n";
			echo '					<div id="iTunesKeywordsHelp" style="display: none;">'."\n";
			echo '						'.__('Not visible in iTunes, but used for searches.', 'podpress')."\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top"> '."\n";
			echo '				<td colspan="2">';
			echo '					<label for="iTunesCategory_0"><strong>'.__('iTunes:Categories', 'podpress').'</strong></label>';
			echo '					<br/>';
			echo '					<select id="iTunesCategory_0" name="iTunesCategory[0]" onchange="podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="##Global##" '; if($data['iTunesCategory'][0] == '##Global##' || empty($data['iTunesCategory'][0])) { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.$this->settings['iTunes']['category'][0].')</option>'."\n";
			podPress_itunesCategoryOptions(htmlentities($data['iTunesCategory'][0]), ENT_QUOTES, get_settings('blog_charset'));
			echo '					</select><br/>'."\n";
			echo '					<input type="hidden" id="global_iTunesCategory" value="'.htmlentities(stripslashes($this->settings['iTunes']['category'][0]), ENT_QUOTES, get_settings('blog_charset')).'" />'."\n";
			echo '					<select name="iTunesCategory[1]">'."\n";
			echo '						<option value="##Global##" '; if($data['iTunesCategory'][1] == '##Global##' || empty($data['iTunesCategory'][1])) { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.$this->settings['iTunes']['category'][1].')</option>'."\n";
			podPress_itunesCategoryOptions(htmlentities($data['iTunesCategory'][1]), ENT_QUOTES, get_settings('blog_charset'));
			echo '					</select><br/>'."\n";
			echo '					<select name="iTunesCategory[2]">'."\n";
			echo '						<option value="##Global##" '; if($data['iTunesCategory'][2] == '##Global##' || empty($data['iTunesCategory'][2])) { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.$this->settings['iTunes']['category'][2].')</option>'."\n";
			podPress_itunesCategoryOptions(htmlentities($data['iTunesCategory'][2]), ENT_QUOTES, get_settings('blog_charset'));
			echo '					</select>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesExplicit"><strong>'.__('iTunes:Explicit', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'iTunesExplicitHelp\');">(?)</a>:'."\n";
			echo '					<br/>';
			echo '					<select id="iTunesExplicit" name="iTunesExplicit" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="##Global##" '; if($data['iTunesExplicit'] == '##Global##' || empty($data['iTunesExplicit'])) { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.$this->settings['iTunes']['explicit'].')</option>'."\n";
			echo '						<option value="No" '; if($data['iTunesExplicit'] == 'No') { echo 'selected="selected"';	}	echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '						<option value="Yes" '; if($data['iTunesExplicit'] == 'Yes') { echo 'selected="selected"';	}	echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '						<option value="Clean" '; if($data['iTunesExplicit'] == 'Clean') { echo 'selected="selected"';	}	echo '>'.__('Clean', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="iTunesExplicitHelp" style="display: none;">'."\n";
			echo '						'.__('Headphones Required? Sets the parental advisory graphic in name column.', 'podpress')."\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">'."\n";

			echo '					<label for="rss_copyrightChoice"><strong>'.__('RSS Copyright', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'rss_copyrightHelp\');">(?)</a>:'."\n";
			echo '					<br/>';
			echo '					<select id="rss_copyrightChoice" name="rss_copyrightChoice" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="Global" '; if($data['rss_copyrightChoice'] != 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.podPress_stringLimiter(ucfirst(stripslashes($this->settings['rss_copyright'])), 40).')</option>'."\n";
			echo '						<option value="Custom" '; if($data['rss_copyrightChoice'] == 'Custom') { echo 'selected="selected"';	}	echo '>'.__('Custom', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="rss_copyrightWrapper" style="display: none;">'."\n";
			echo '						<input type="text" name="rss_copyright" size="40" id="rss_copyright" value="'.htmlentities(stripslashes($data['rss_copyright']), ENT_QUOTES, get_settings('blog_charset')).'" onchange="javascript: podPress_updateCategoryCasting();"/>';
			echo '						<br />The copyright dates. For example: 2006-2007'."\n";
			echo '						<input type="hidden" id="global_rss_copyright" value="'.htmlentities(stripslashes($this->settings['rss_copyright']), ENT_QUOTES, get_settings('blog_charset')).'" />'."\n";
			echo '					</div>'."\n";
			echo '					<div id="rss_copyrightHelp" style="display: none;">'."\n";
			echo '						'.__('Used if this dates are different than the global copyright dates.', 'podpress')."\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			echo '			</tr>'."\n";

			echo '			<tr valign="top">'."\n";
			echo '				<td width="50%">';
			echo '					<label for="iTunesBlock"><strong>'.__('iTunes:Block', 'podpress').'</strong></label> <a href="javascript:void(null);" onclick="javascript: podPressShowHideRow(\'itunesBlockHelp\');">(?)</a>:'."\n";
			echo '					<br/>';
			echo '					<select id="iTunesBlock" name="iTunesBlock" onchange="javascript: podPress_updateCategoryCasting();">'."\n";
			echo '						<option value="##Global##" '; if($data['iTunesBlock'] != '##Global##' || empty($data['itunesBlock'])) { echo 'selected="selected"';	}	echo '>'.__('Use Global', 'podpress').' ('.$this->settings['iTunes']['block'].')</option>'."\n";
			echo '						<option value="No" '; if($data['iTunesBlock'] == 'No') { echo 'selected="selected"';	}	echo '>'.__('No', 'podpress').'</option>'."\n";
			echo '						<option value="Yes" '; if($data['iTunesBlock'] == 'Yes') { echo 'selected="selected"';	}	echo '>'.__('Yes', 'podpress').'</option>'."\n";
			echo '					</select>'."\n";
			echo '					<div id="itunesBlockHelp" style="display: none;">'."\n";
			echo '						'.__('Prevent this episode or podcast from appearing in iTunes.', 'podpress')."\n";
			echo '					</div>'."\n";
			echo '				</td>'."\n";
			echo '				<td width="50%">&nbsp;</td>'."\n";
			echo '			</tr>'."\n";
			echo '		</table>'."\n";

			echo '		</fieldset>'."\n";
			echo '	</div>'."\n";

			echo '<script type="text/javascript">podPress_updateCategoryCasting(); </script>';
			echo '</div>'."\n";

		}

		function edit_category($cat_ID) {
			if(!isset($_POST['iTunesFeedID'])){
				return;
			}
			$data = array();
			if($_POST['categoryCasting'] == 'on') {
				$data['categoryCasting'] = 'true';
			} else {
				$data['categoryCasting'] = 'false';
			}

			$data['podcastFeedURL'] = $_POST['podcastFeedURL'];
			$data['iTunesFeedID'] = $_POST['iTunesFeedID'];
			$data['iTunesNewFeedURL'] = $_POST['iTunesNewFeedURL'];
			$data['blognameChoice'] = $_POST['blognameChoice'];
			$data['blogname'] = $_POST['blogname'];
			$data['blogdescriptionChoice'] = $_POST['blogdescriptionChoice'];
			$data['iTunesSubtitleChoice'] = $_POST['iTunesSubtitleChoice'];
			$data['iTunesSubtitle'] = $_POST['iTunesSubtitle'];
			$data['iTunesSummaryChoice'] = $_POST['iTunesSummaryChoice'];
			$data['iTunesSummary'] = $_POST['iTunesSummary'];
			$data['iTunesKeywordsChoice'] = $_POST['iTunesKeywordsChoice'];
			$data['iTunesKeywords'] = $_POST['iTunesKeywords'];
			$data['iTunesAuthorChoice'] = $_POST['iTunesAuthorChoice'];
			$data['iTunesAuthor'] = $_POST['iTunesAuthor'];
			$data['iTunesAuthorEmailChoice'] = $_POST['iTunesAuthorEmailChoice'];
			$data['iTunesAuthorEmail'] = $_POST['iTunesAuthorEmail'];
			$data['rss_language'] = $_POST['rss_language'];
			$data['iTunesExplicit'] = $_POST['iTunesExplicit'];
			$data['iTunesBlock'] = $_POST['iTunesBlock'];
			$data['iTunesImageChoice'] = $_POST['iTunesImageChoice'];
			$data['iTunesImage'] = $_POST['iTunesImage'];
			$data['rss_imageChoice'] = $_POST['rss_imageChoice'];
			$data['rss_image'] = $_POST['rss_image'];
			$data['rss_copyrightChoice'] = $_POST['rss_copyrightChoice'];
			$data['rss_copyright'] = $_POST['rss_copyright'];
			$data['iTunesCategory'] = $_POST['iTunesCategory'];

			delete_option('podPress_category_'.$cat_ID);
			podPress_add_option('podPress_category_'.$cat_ID, $data);
		}

		function delete_category($cat_ID) {
			echo 'told to delete';
			//delete_option('podPress_category_'.$cat_ID);
		}

	}
