<?php
/*
Plugin Name: UTF-8 Database Converter
Plugin URI: http://g30rg3x.com/utf8-database-converter
Description: Easily Converts the WordPress database to UTF-8 character set.
Version: 2.0.1
Author: g30rg3_x
Author URI: http://g30rg3x.com/
*/

/*
	Copyright 2007  g30rg3_x  (email : g30rg3x@gmail.com)

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

// Dont access directly
if ( !defined('ABSPATH') )
	die();

function UTF8_DB_Converter_menu_add() {
	if ( function_exists('add_submenu_page') )
		add_submenu_page('plugins.php', 'UTF-8 Database Converter', 'UTF-8 Database Converter', 'level_10', 'utf8-db-converter', 'UTF8_DB_Converter_menu');
}

function UTF8_DB_Converter_menu() {
	$version = false;
	$success = false;

	if ( isset($_POST['submit']) ) {
		if ( function_exists('current_user_can') && !current_user_can('level_10') )
			die('Only the blog owner has the access capabilities to this resource.');

		if ( preg_match('/Final/', $_POST['submit']) )
			$success = UTF8_DB_Converter_DoIt();
		else
			$version = true;	
	}

	if ( preg_match('/\\A(?:2\\.(1|2)\\.*.*)\\z/', get_bloginfo('version')) )
		$version = true;

	?>
	<?php if ( $success ) : ?>
		<div id="message" class="updated fade"><p><strong>The database has been succesfully converted to UTF-8</strong>. <a href="<?php bloginfo('url'); ?>/">View site &raquo;</a></p></div>
	<?php endif; ?>
	<div class="wrap">
		<h2>UTF-8 Database Converter</h2>
		<div class="narrow">
			<p style="padding: .2em; background-color: #DD2222; color: #FFFFFF; font-weight: bold; font-size: large; text-align: center;">
				WARNING<br/>
				<?php if ( $version ) : ?>
					DATA MAY BE LOST
				<?php else : ?>
					VERSION NOT SUPPORTED
				<?php endif; ?>
			</p>
			<p>
				<?php if ( $version ) : ?>
					Before proceed with the final step please make a complete backup of your database.<br/>
					Also is very recommendable that you close the public access of your WordPress based blog/website.<br/>
					<br/>
					The next procedure may take some several time, so do not close your navigator or your internet connection
					during the execution of this plugin.<br/>
					<br/>
					Proceed with the final step.
				<?php else : ?>
					This plugin has been developed and successfully tested under WordPress 2.2.x and 2.1.x<br/>
					We cannot assure that it will work in any other minor or major release.<br/>
					If you still want to use it after this warning proceed with the next and final step.<br/>
					<br/>
					Also this warning may show if you change the version value "wp_version" inside the file "/wp-includes/version.php",
					so if you have changed this value and you know that you have supported version of WordPress...<br/>
					Ignore this warning and continue to the next and final step.
				<?php endif; ?>
			</p>
			<form action="" method="post" id="utf8-db-converter">
				<p class="submit"><input type="submit" name="submit" value="<?php echo $version ? 'Final' : 'Next'; ?> Step &raquo;"/></p>
			</form>
		</div>
	</div>
	<?php
}

function UTF8_DB_Converter_DoIt() {
	$tables = array();
	$tables_with_fields = array();

	// Since we cannot use the WordPress Database Abstraction Class (wp-db.php),
	// we have to make an a stand-alone/direct connection to the database.
	$link_id = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD) or die('Error establishing a database connection');
	mysql_select_db(DB_NAME, $link_id);

	// Gathering information about tables and all the text/string fields that can be affected
	// during the conversion to UTF-8.
	$resource = mysql_query("SHOW TABLES", $link_id);
	while ( $result = mysql_fetch_row($resource) )
		$tables[] = $result[0];

	if ( !empty($tables) ) {
		foreach ( (array) $tables as $table ) {
			$resource = mysql_query("EXPLAIN $table", $link_id);
			while ( $result = mysql_fetch_assoc($resource) ) {
				if ( preg_match('/(char)|(text)|(enum)|(set)/', $result['Type']) )
					$tables_with_fields[$table][$result['Field']] = $result['Type'] . " " . ( "YES" == $result['Null'] ? "" : "NOT " ) . "NULL " .  ( !is_null($result['Default']) ? "DEFAULT '". $result['Default'] ."'" : "" );
			}
		}

		// Change all text/string fields of the tables to their corresponding binary text/string representations.
		foreach ( (array) $tables as $table )
			mysql_query("ALTER TABLE $table CONVERT TO CHARACTER SET binary", $link_id);

		// Change database and tables to UTF-8 Character set.
		mysql_query("ALTER DATABASE " . DB_NAME . " CHARACTER SET utf8", $link_id);
		foreach ( (array) $tables as $table )
			mysql_query("ALTER TABLE $table CONVERT TO CHARACTER SET utf8", $link_id);

		// Return all binary text/string fields previously changed to their original representations.
		foreach ( (array) $tables_with_fields as $table => $fields ) {
			foreach ( (array) $fields as $field_type => $field_options ) {
				mysql_query("ALTER TABLE $table MODIFY $field_type $field_options", $link_id);
			}
		}

		// Optimize tables and finally close the mysql link.
		foreach ( (array) $tables as $table )
			mysql_query("OPTIMIZE TABLE $table", $link_id);
		mysql_close($link_id);
	} else {
		die('<strong>There are no tables?</strong>');
	}

	return true;
}

add_action('admin_menu', 'UTF8_DB_Converter_menu_add');
?>