<?php
/*	Copyright 2005 Johan Känngård, johan@kanngard.net, http://dev.kanngard.net

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
 * Handles uploads, edits, updates, deletes and views/gets of db files.
 *
 * @author Johan Känngård, johan@kanngard.net, http://johankanngard.net
 * @version 1.1
 */

include_once 'dbfile.inc';

if (isset($_POST['FileDescription'])) {
	// Handle post from database file form 
	$id = isset($_POST['id']) ? db_file::clean($_POST['id'], 4) : "";
	db_file::receiveFile($id);
	wp_redirect(get_option('siteurl').'/wp-admin/edit.php?page=dbfile.php');
} else {
	if (!isset($_GET['action'])) {
		die('No action');
	}

	if (!isset($_GET['id'])) {
		if (!isset($_GET['name'])) {
			die('No id nor name');
		}
		$name = db_file::clean($_GET['name'], 256);
		$id = db_file::getID($name);
		if (!is_numeric($id) || (is_numeric($id) && $id < 0)) {
			die('File '.$name.' does not exist');
		}
	} else {
		$id=db_file::clean($_GET['id'], 6);
	}
	
	$inline = true;
	if(isset($_GET['inline'])) {
		$inline = strtolower(db_file::clean($_GET['inline'], 5));
		if ($inline == 'false') {
			$inline = false;
		}
	}
	
	$action=db_file::clean($_GET['action'], 6);
	
	switch ($action) {
	case "add":
		db_file::editFile("");
		break;
	case "delete":
		db_file::deleteFile($id);
		wp_redirect(get_option('siteurl').'/wp-admin/edit.php?page=dbfile.php');
		break;
	case "edit": 
		require_once(ABSPATH.'wp-admin/admin.php');
		require_once(ABSPATH.'wp-admin/admin-header.php');
		$title = __('Edit Database File');
		$parent_file = 'edit.php';
		db_file::editFile($id);
		require_once(ABSPATH.'wp-admin/admin-footer.php');
		break;
	case "get":
		db_file::getFile($id, $inline);
		break;
	default:
		die("Invalid action");
		break;
	}
}
?>