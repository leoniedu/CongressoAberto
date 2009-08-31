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
	if(!function_exists('podPress_webContent')) {
		function podPress_webContent($podPressTemplateData) {
		GLOBAL $podPress;
		$divider                    = ' | ';
		$podPressContent            = '';
		$podPressPlayBlockScripts   = '';
		foreach ($podPressTemplateData['files'] as $key=>$val) {
			$GLOBALS['podPressPlayer']++;
			if(empty($val['dimensionW'])) {
				$val['dimensionW'] = "''";
			}
			if(empty($val['dimensionH'])) {
				$val['dimensionH'] = "''";
			}
			$dividerNeeded = false;

			if($val['enablePlayer']) {
				if($podPressContent != '') {			
					$podPressContent .= "<br/>\n";
				}
				$podPressContent .= '<div id="podPressPlayerSpace_'.$GLOBALS['podPressPlayer'].'">&nbsp;</div>'."\n";
			}

			if(isset($val['image'])) {
				if($val['enableDownload'] && !empty($val['URI'])) {
					$podPressContent .= '<a href="'.$val['URI'].'" target="new">';
				}
				$podPressContent .= '<img src="'.podPress_url().'images/'.$val['image'].'" border="0" align="top" class="podPress_imgicon" alt="icon for podpress" />';
				if($val['enableDownload'] && !empty($val['URI'])) {
					$podPressContent .= '</a>';
				}
				if(!$podPressTemplateData['showDownloadText'] == 'enabled') {
					$val['enableDownload'] = false;
				}
			}
			if($val['enableTorrentDownload']) {
				$podPressContent .= '<a href="'.$val['URI_torrent'].'" target="new">';
				if(strstr($val['image'], '_button')) {
					$torrentimg = 'misc_torrent_button.png';
				} else {
					$torrentimg = 'misc_torrent_icon.png';
				}
				$podPressContent .= '<img src="'.podPress_url().'images/'.$torrentimg.'" border="0" align="top" class="podPress_imgicon" alt="icon for podpress" />';
				$podPressContent .= '</a>';
			}

			$podPressContent .= ' &nbsp;';
			$podPressContent .= __($val['title'], 'podpress');

			if($podPressTemplateData['showDuration'] == 'enabled' && !empty($val['duration']) && preg_match("/([0-9]):([0-9])/", $val['duration'])) {
				$podPressContent .= ' ['.$val['duration'].'m]';
			}

			if($val['enablePlayer'] || $val['enablePopup'] || $val['enableDownload'] || !$val['authorized']) {
				$podPressContent .= ': ';
			}

			if(!$val['authorized']) {
				$podPressContent .= ' <a href="'.get_settings('siteurl').'/wp-login.php">(Protected Content)</a><br/>'."\n";
			} else {
				if($val['enablePlayer']) {
					if($dividerNeeded) {
						$podPressContent .= $divider;
					}
					if($val['disablePreview'] == 'on') {
						$previewVal = 'nopreview';
					} else {
						$previewVal = 'false';
					}
					$podPressContent .= "<a href=\"#\" onclick=\"javascript: podPressShowHidePlayer('".$GLOBALS['podPressPlayer']."','".$val['URI_Player']."',".$val['dimensionW'].",".$val['dimensionH'].",'true'); return false;\"><span id=\"podPressPlayerSpace_".$GLOBALS['podPressPlayer']."_PlayLink\">".__('Play Now', 'podpress')."</span></a>";
					$dividerNeeded = true;
					if($podPress->settings['contentAutoDisplayPlayer']) {
						$podPressPlayBlockScripts .= "podPressShowHidePlayer('".$GLOBALS['podPressPlayer']."', '".$val['URI_Player']."',".$val['dimensionW'].",".$val['dimensionH'].", '".$previewVal."', '".$val['previewImage']."');\n";
					}
				}

				if($val['enablePopup']) {
					if($dividerNeeded) {
						$podPressContent .= $divider;
					}
					$podPressContent .= "<a href=\"#\" onclick=\"javascript: podPressPopupPlayer('".$GLOBALS['podPressPlayer']."', '".$val['URI_Player']."',".$val['dimensionW'].",".$val['dimensionH']."); return false;\">".__('Play in Popup', 'podpress')."</a>";
					$dividerNeeded = true;
				}

				if($val['enableDownload'] && $podPressTemplateData['showDownloadText'] == 'enabled') {
					if($dividerNeeded) {
						$podPressContent .= $divider;
					}
					$podPressContent .= '<a href="'.$val['URI'].'" target="new">'.__('Download', 'podpress').'</a>';
					if($val['stats'] && $podPressTemplateData['showDownloadStats'] == 'enabled') {
						$podPressContent .= ' ('.$val['stats']['total'].')';
						$val['stats'] = false;
					}
					$dividerNeeded = true;
				}

				if($val['stats'] && $podPressTemplateData['showDownloadStats'] == 'enabled') {
					if($dividerNeeded) {
						$podPressContent .= $divider;
					}
					$podPressContent .= ' '.__('Downloads', 'podpress').' '.$val['stats']['total'].'';
					$dividerNeeded = true;
				}

				$podPressContent .= "<br/>\n";
			}
		}

		if($podPress->settings['contentAutoDisplayPlayer']) {
			$podPressPlayBlockScripts = '<script type="text/javascript"><!--'."\n".$podPressPlayBlockScripts;
			$podPressPlayBlockScripts .= "\n-->\n</script>";
		}
		return '<div class="podPress_content">'.$podPressContent.'</div>'."\n".$podPressPlayBlockScripts;
	}
	}

	if(!function_exists('podPress_defaultTitles')) {
	function podPress_defaultTitles($filetype) {
		switch($filetype) {
			case 'audio_mp3':
				return 'Standard Podcast';
				break;
			case 'audio_m4a':
			case 'audio_mp4':
				return 'Enhanced Podcast';
				break;
			case 'audio_m3u':
				return 'Streaming Audio';
				break;
			case 'video_m4v':
				return 'Podcast Video';
				break;
			case 'video_mp4':
			case 'video_mov':
			case 'video_qt':
				return 'Podcast Video';
				break;
			case 'video_avi':
			case 'video_mpg':
			case 'video_asf':
			case 'video_wmv':
			case 'video_wma':
				return 'Online Video';
				break;
			case 'video_swf':
				return 'Flash Content';
				break;
			case 'video_flv':
				return 'Flash Video';
				break;
			case 'embed_youtube':
				return  'YouTube';
				break;
			case 'ebook_pdf':
				return  'Ebook';
				break;
			case 'misc_other':
			default:
				return 'Other Media';
		}
	}
	}

