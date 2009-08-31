<?php
/*  Copyright 2005 Johan Känngård, johan@kanngard.net, http://dev.kanngard.net

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
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

/*
Plugin Name: dbfiles
Plugin URI: http://johankanngard.net/
Description: A plugin for uploading/downloading/viewing files via a MySQL database.
Version: 1.2
Author: Johan Känngård
Author URI: http://johankanngard.net
*/
include_once 'dbfile.inc';

add_action('admin_menu', 'db_file_add_admin_menu_pages');

function db_file_add_admin_menu_pages() {
	add_management_page('Database Files', 'Database Files', 8, '', array('db_file', 'showAdminList'));
	
	if (get_option('dbfile_use_fileupload') == '1') {
		add_management_page('Add Database File', 'Add Database File', 8, $pluginPath.'dbfile.php', array('db_file', 'editFile'));
	}
	add_options_page('Database Files', 'Database Files', 8, basename(__FILE__), array('db_file', 'showOptions'));
	
	if (isset($_GET['activate']) && $_GET['activate'] == 'true') {
		db_file::install();
	}
}
?>
