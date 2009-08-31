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
	if(!defined('DB_NAME')) { // everything is normal
		define('WP_USE_THEMES', false);
		require_once('../../../wp-config.php');
		require_once(ABSPATH.PLUGINDIR.'/podpress/podpress_admin_functions.php');

		$customThemeFile = ABSPATH.'/wp-content/themes/'.get_option('template').'/podpress_theme.php';
		if(!file_exists($customThemeFile)) {
			$customThemeFile = ABSPATH.PLUGINDIR.'/podpress/podpress_theme.php';
		}

		switch(strtolower($_GET['action']))
		{
			case 'id3image':
				podPress_isAuthorized();
				podPress_getCoverArt(urldecode($_GET['filename']));
	 			break;
			case 'getduration':
				podPress_isAuthorized();
				echo podPress_getDuration(urldecode($_GET['filename']));
	 			break;
			case 'getfilesize':
				podPress_isAuthorized();
				echo podPress_getFileSize(urldecode($_GET['filename']));
	 			break;
			case 'showid3contents':
				podPress_isAuthorized();
				echo podPress_showID3tags(urldecode($_GET['filename']));
	 			break;
			case 'streamfile':
				//podPress_isAuthorized();
				//Header("Content-Type: ".$podPress->contentType."; charset=".$podPress->encoding."; filename=".basename($filename));
				//Header("Content-Disposition: inline; filename=".basename($filename));
				break;
			default:
		}
	}
