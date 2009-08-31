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

include dirname(__FILE__).'/../../'.'wp-config.php';

//include dirname(__FILE__).'/../../../'.'wp-config.php';
// If Wordpress v.1.5, uncomment this, if 2.0, let it be commented:
//$pluginPath = get_option('siteurl').'/wp-content/plugins/dbfile/';
//If Wordpress v.1.5, comment this, if 2.0 let it be uncommented:
//print('<!--siteurl:'.bloginfo('siteurl').'-->');
$pluginPath = get_bloginfo('siteurl').'/wp-content/plugins/dbfile/';


/**
 * Class for uploading and downloading files/blobs to/from a MySQL database.
 *
 * @author Johan Känngård, johan@kanngard.net, http://dev.kanngard.net
 * @version 1.2
 */
class db_file {

	/**
	 * Set this to true to get tracing on errors. Set to false to disable those and also disable debug logging.
	 */
	public static $trace = true;
	
	function getConnection() {

		if (!($connection = mysql_pconnect(DB_HOST, DB_USER, DB_PASSWORD)) || !mysql_select_db(get_option('dbfile_database'), $connection)) {
			showerror();
		}

		return $connection;
	}

	/**
	 * Edits the specified file.
	 * @param id the file id. If not a numerical value is supplied (i.e. null or ""),
	 * a new file is expected, and the upload form is shown.
	 */
	function editFile($id) {
		db_file::checkAccess();
		global $pluginPath;
		$isNew = true;

		if (!empty($id) && is_numeric($id)) {
			db_file::checkId($id);
			$isNew = false;
			$connection = db_file::getConnection();
			$sql = "SELECT name, description FROM ".get_option('dbfile_table')." WHERE id = $id";

			if (!($result = mysql_query ($sql, $connection))) {
				db_file::showerror();
			}
			$row = mysql_fetch_array($result);
			$description = $row['description'];
			$name = $row['name'];
			mysql_free_result($result);
			// Get post<->file relation
			$sql = "SELECT postID, sortNo FROM ".get_option('dbfile_post2file_table')." WHERE dbfileID = $id";
			if (!($result = mysql_query ($sql, $connection))) {
				showerror();
			}
			$row = mysql_fetch_array($result);
			$postID = $row['postID'];
			$sortNo = $row['sortNo'];
			mysql_free_result($result);
		}
		
?>
	<form method="post" action="<?php print($pluginPath);?>dbfile.php" enctype="multipart/form-data">
		<div class="wrap">
			<h2>Database File</h2>
			<table>
				<col span="1">
				<?php if (!$isNew) {?>
					<tr>
						<td>Filename:</td>
						<td><?php print($name);?></td>
					</tr>
				<?php
				}
				?>
					<tr>
						<td>Short description:</td>
						<td><input type="text" name="FileDescription" value="<?php print($description);?>" maxlength="50" title="Enter a short description for the internal listing" /></td>
					</tr>
					<tr>
						<td>Post ID:</td>
						<td><input type="text" name="FilePostID" value="<?php print($postID);?>" maxlength="6" title="Enter the post ID that you want to connect this file with. Enter 0 (zero) to remove any relation" onchange="if(this.value!=''){this.form.FileSortNo.disabled=false;}else{this.form.FileSortNo.disabled=true;}" /></td>
					</tr>
					<tr>
						<td>Sort number:</td>
						<td><input type="text" name="FileSortNo" value="<?php print($sortNo);?>"<?php print($postID != '' ? '' : ' disabled="disabled"');?> maxlength="6" title="Enter the sorting number, used in posts to sort the files. Enabled only if a post ID has been entered above " /></td>
					</tr>
					<tr>
						<td>File:</td>
						<td><input type="file" name="FileData" /></td>
					</tr>
				</table>
			<input type="hidden" name="id" value="<?php print($id); ?>" />
			<p class="submit">
				<input type="submit" name="submit" value="Submit" title="Submit" />
			</p>
<?php
		if (!$isNew&&db_file::isImage($name)) {
?>
			<label>Preview</label><br />
			<iframe style="width:100%;height:100%;border:0px;" src="<?php print($pluginPath."dbfile.php?id=$id&amp;action=get&amp;name=$name");?>">
			</iframe>
<?php
		}
?>
		</div>
	</form>
<?php
	}

	/**
	 * Stores the file specified from the HTTP POST in the MySQL database.
	 */
	function receiveFile($id) {
		db_file::checkAccess();
		global $table_prefix;
		
		$fileTypes = explode(' ', get_option('dbfile_upload_allowedtypes'));
		$short = db_file::clean($_POST["FileDescription"], 50);
		$postID = db_file::clean($_POST["FilePostID"], 11);
		$sortNo = db_file::clean($_POST["FileSortNo"], 11);
		$userfile = db_file::clean($_FILES['FileData']['name'], 256);

		if ($userfile != "") {
			$fileParts = explode('.', $userfile);
			$extension = strpos($userfile, '.') > 1 ? $fileParts[count($fileParts) - 1] : '';
	
			if (!in_array(strtolower($extension), $fileTypes, true)) {
				die('Invalid file type');
			}
			$maxFileSize = get_option('dbfile_upload_maxk');
			$fileSize = $_FILES['FileData']['size'] / 1024;
	
			if ($fileSize > $maxFileSize) {
				die('Maximum upload size is '.$maxFileSize.'Kb, you tried '.$fileSize.'Kb');
			}
			
			$userfile_type = db_file::clean($_FILES['FileData']['type'], 256);   // This can be faked by the browser...
			$fileContents = addslashes(file_get_contents($_FILES['FileData']['tmp_name']));
			$name = db_file::clean($_FILES['FileData']['name'], 256);
			$fileSql = ", name=\"{$name}\", mimeType=\"{$userfile_type}\", content=\"{$fileContents}\"";
		}
		$now=date("Y-m-d H:i:s");
		
		$id = db_file::clean($_POST["id"], 6);

		if (is_numeric($id)) {
			// FIXME check if any update is needed?
			// FIXME check that this post actually exists!
			$sql = "UPDATE ".get_option('dbfile_table')." SET description=\"{$short}\", modified=\"$now\"".$fileSql." WHERE id=$id";
		} else {
			$sql = "INSERT INTO ".get_option('dbfile_table')." SET description=\"{$short}\", created=\"$now\"".$fileSql;
		}
		$connection = db_file::getConnection();

		if (($result = mysql_query ($sql, $connection)) 
			&& mysql_affected_rows() == 1) {
			if (!is_numeric($id)) {
				$id = mysql_insert_id();
			}
		} else {
			db_file::showerror();
		}

		// Check if there is any relation between post and dbfile
		// FIXME make the UI accept multi values for postID. How to remove a relation? For now, this handles only 1 to 1 relations
		$sql = "SELECT relID, postID, dbfileID from ".get_option("dbfile_post2file_table")." where dbfileID = \"".$id."\"";

		if (!($result = mysql_query($sql, $connection))) {
			db_file::debug('$sql: '.$sql);
			db_file::showerror();
		}

		$sql = "";

		if (mysql_num_rows($result) < 1) {
			// No existing relationship found
			
			if (is_numeric($postID)) {
				// Create a new releation
				$sql = "INSERT INTO ".get_option("dbfile_post2file_table")." SET postID=\"$postID\", dbfileID=\"$id\", sortNo=$sortNo";
			}
			
		} else {
			// Relationship found

			$data = @mysql_fetch_array($result);
			// Check if an update is needed = faster than always updating
			if ($postID == 0) {
				// Remove relation
				$sql = "DELETE FROM ".get_option("dbfile_post2file_table")." WHERE relID=\"".$data["relID"]."\"";
			} else if ($postID <> $data["postID"]) {
				// Update the relationsship
				$sql = "UPDATE ".get_option("dbfile_post2file_table")." SET postID=\"$postID\", sortNo=$sortNo WHERE relID=\"".$data["relID"]."\"";
			} else {
				$sql = "";
			}
		}
		
		mysql_free_result($result);
		
		if ($sql != "") {
			print("Non-empty SQL [".$sql."]");
			if (!mysql_query($sql, $connection)) {
				db_file::showerror();
			}
			
		} else {
			db_file::debug("SQL is empty [".$sql."]");
		}
	}

	/**
	 * Sends the specified file to the browser.
	 *
	 * @param id the file id to get.
	 * @param inline set to true to get inline files (i.e. an image in the middle of an HTML page) or false
	 * to send the file as an attachment (the download prompt is shown in the web browser).
	 */
	function getFile($id, $inline) {
		db_file::checkId($id);
		$connection = db_file::getConnection();
		$hitQuery = get_option('dbfile_use_hitcounter') == '1' ? ', hits' : '';
		$sql = 'SELECT mimeType, name, content'.$hitQuery.' FROM '.get_option('dbfile_table')." WHERE id=$id";
	
		if (!($result = mysql_query($sql, $connection))) {
			db_file::showerror();
		}
	
		$data = mysql_fetch_array($result);
	
		if (empty($data['content'])) {
			die("File $file does not exist");
		}
		db_file::sendFile($data['content'], $data['name'], $data['mimeType'], $inline == TRUE);
		mysql_free_result($result);
		
		if (get_option('dbfile_use_hitcounter') == '1') {
			$sql = 'UPDATE '.get_option('dbfile_table').' set hits='.($data['hits']+1)." WHERE id=$id";

			if (!($result = mysql_query($sql, $connection))) {
				db_file::showerror();
			}
			mysql_free_result($result);
		}
	}

	/**
	 * Returns the id of the specified file.
	 *
	 * @param $name the file name of the dbfile to get the ID of.
	 * @return numeric id of the specified file
	 */
	function getID($name) {
		$connection = db_file::getConnection();
		$sql = "SELECT id FROM ".get_option('dbfile_table')." WHERE name ='$name'";

		if (!($result = mysql_query($sql, $connection))) {
			db_file::showerror();
		}
		
		$row = mysql_fetch_array($result);
		return $row['id'];
	}
	
	/**
	 * Sends the specified file content to the web client.
	 *
	 * @author Johan Känngård, http://dev.kanngard.net
	 * @version 1.0
	 * @param content the file contents
	 * @param name the file name that the web client should see.
	 * @param mimeType the mime type of the file, that is set via the Content-Type response header.
	 * @param inline if set to true, no Content-Disposition/attachment are sent.
	 */
	function sendFile($content, $name, $mimeType, $inline) {
		header("Content-Type: ".$mimeType);
	
		if (!$inline) {
			header('Content-Disposition: attachment; filename="'.$name.'"');
		}
		print($content);
	}

	/**
	 * Deletes the specified file from the database.
	 *
	 * @param $id the id of the file entry to remove
	 */
	function deleteFile($id) {
		db_file::checkAccess();
		db_file::checkId($id);
		$connection = db_file::getConnection();
		$sql = "DELETE from ".get_option('dbfile_table')." WHERE id = ".db_file::clean($id, 6)." LIMIT 1";
		
		if (!((mysql_query ($sql, $connection)) 
			&& mysql_affected_rows() == 1)) {
			die('Delete failed.'.mysql_error($connection));
		}
		
		// Remove relations
		$sql = "DELETE from ".get_option("dbfile_post2file_table")." WHERE dbfileID = ".db_file::clean($id, 6);
		if (!mysql_query ($sql, $connection)) {
			die('Delete failed. '.mysql_error($connection));
		}
	}

	/**
	 * Show the options for db files. Handled via the plugin architecture in dbfile.php as an add_action.
	 */
    function showOptions() {
		db_file::checkAccess();
        ?>
<div class="wrap">
	<style type="text/css">
		TABLE.editform TH {vertical-align:top;}
	</style>
	<h2>Database Files Options</h2>
	<form name="dbfileoptions" method="post" action="options.php">
		<input type="hidden" name="action" value="update" />
		<input type="hidden" name="page_options" value="'dbfile_use_fileupload', 'dbfile_database', 'dbfile_table', 'dbfile_create_db_and_table', 'dbfile_upload_maxk', 'dbfile_upload_allowedtypes', 'dbfile_upload_minlevel', 'dbfile_preview_types', 'dbfile_fileentry', 'dbfile_use_hitcounter'" />
		<fieldset class="options">
			<legend>
				<input type="checkbox" name="dbfile_use_fileupload" id="dbfile_use_fileupload" <?php print(get_option('dbfile_use_fileupload') == '1' ? 'checked="checked" ' : '');?>value="1" />
				<label for="dbfile_use_fileupload">Allow Database File Uploads</label>
			</legend>
			<table class="editform">
				<tr>
					<th scope="row">Destination database:</th> 
					<td>
						<input name="dbfile_database" type="text" id="dbfile_database" value="<?php print(get_option('dbfile_database'));?>" size="50" /><br />
						Recommended: <code>wordpress</code>	
					</td>
				</tr>
				<tr>
					<th scope="row">File table:</th>
					<td>
						<input name="dbfile_table" type="text" id="dbfile_table" value="<?php print(get_option('dbfile_table'));?>" size="50" /><br />
						Recommended: <code>wp_files</code>
					</td>
				</tr>
				<tr>
					<th scope="row">Post to file table:</th>
					<td>
						<input name="dbfile_post2file_table" type="text" id="dbfile_post2file_table" value="<?php print(get_option('dbfile_post2file_table'));?>" size="50" /><br />
						Recommended: <code>wp_post2file</code>
					</td>
				</tr>
				<tr>
					<th scope="row">Maximum size:</th>
					<td>
						<input name="dbfile_upload_maxk" type="text" id="dbfile_upload_maxk" value="<?php print(get_option('dbfile_upload_maxk'));?>" size="4" /> Kilobytes (KB)
					</td>
				</tr>
				<tr>
					<th scope="row">Allowed file types:</th>
					<td>
						<input name="dbfile_upload_allowedtypes" type="text" id="dbfile_upload_allowedtypes" value="<?php print(get_option('dbfile_upload_allowedtypes'));?>" size="50" /><br />
						Recommended: <code>jpg jpeg png gif</code>
					</td>
				</tr>
				<tr>
					<th scope="row">File types that should be previewed when edited:</th>
					<td>
						<input name="dbfile_preview_types" type="text" id="dbfile_preview_types" value="<?php print(get_option('dbfile_preview_types'));?>" size="50" /><br />
						Recommended: <code>jpg jpeg png gif</code>
					</td>
				</tr>
				<tr>
					<th scope="row">Minimum <a href="http://codex.wordpress.org/User_levels" title="User Levels documentation">level</a> to upload:</th>
					<td>
						<select name="dbfile_upload_minlevel" id="dbfile_upload_minlevel">
							<?php
							for ($i = 1; $i < 11; $i++) {
								$selected=get_option('dbfile_upload_minlevel') == $i ? ' selected="selected"':'';
								print('<option value="'.$i.'"'.$selected.'>'.$i.'</option>\n');
							}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row">File entry in posts:</th>
					<td>
						<input name="dbfile_fileentry" type="text" id="dbfile_fileentry" value="<?php print(htmlentities(get_option('dbfile_fileentry')));?>" size="50" /><br />
						Recommended: <code>&lt;li&gt;&lt;a href="%link"&gt;%name&lt;/a&gt;&lt;/li&gt;</code>
					</td>
				</tr>
				<tr>
					<th scope="row"></th>
					<td>
						<input type="checkbox" name="dbfile_use_hitcounter" value="1"<?php print(get_option('dbfile_use_hitcounter') == '1' ? ' checked' : '');?> />Use hit counter
					</td>
				</tr>
			</table> 
		</fieldset>
		<p class="submit">
			<input type="submit" name ="submit" value="Update Options &raquo;" />
		</p>
	</form>
</div>
<?php
	}

	/**
	 * Shows the management page for the db files.
	 */
	function showAdminList() {
		global $pluginPath;
		db_file::checkAccess();

		$connection = db_file::getConnection();
	?>
	<div class="wrap">
	<h2>Database Files</h2>
	<?php
		$sql = 'SELECT COUNT(id) FROM '.get_option('dbfile_table');
		if (!($result = mysql_query($sql, $connection))) {
			showerror();
		}
		if (mysql_result($result, 0, 0) < 1) {
			print('No files yet.');
		} else {
	?>
			<table>
				<thead>
					<tr><td>ID</td><td>Name</td><td>Description</td><td>MIME type</td><td>Size (bytes)</td><td>Post(s)</td><td>Created</td><td>Modified</td><td>Hits</td><td></td><td></td><td></td><td></td></tr>
				</thead>
				<tbody>
		<?php
			$sql = 'SELECT id, description, mimeType, content, name, created, modified, hits FROM '.get_option('dbfile_table').' ORDER BY id DESC';
			
			if (!($result = mysql_query($sql, $connection))) {
				db_file::showerror();
			}
			
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				$isEven = ($rowCount % 2) == 0;
				$rowClass = $isEven ? ' class="alternate"' : '';
				print("<tr$rowClass>");
				print('<th scope="row">'.$row['id'].'</td>');
				print('<td>'.$row['name'].'</td>');
				print('<td>'.$row['description'].'</td>');
				print('<td>'.$row['mimeType'].'</td>');
				print('<td>'.strlen($row['content']).'</td>');
				
				$sql = 'SELECT postID FROM '.get_option('dbfile_post2file_table').' WHERE dbfileID='.$row['id'];
				
				if (!($idResult = mysql_query($sql, $connection))) {
					db_file::showerror();
				}
				
				while ($postID = mysql_fetch_array($idResult, MYSQL_ASSOC)) {
					$postIDs[] = '<a href="'.get_option('home').'/?p='.$postID['postID'].'">'.$postID['postID'].'</a>'; 
				}
				
				print('<td>'.(empty($postIDs) ? '' : implode(', ', $postIDs)).'</td>');
				
				print('<td>'.$row['created'].'</td>');
				print('<td>'.$row['modified'].'</td>');
				print('<td>'.$row['hits'].'</td>');
				print('<td><a href="'.$pluginPath.'dbfile.php?action=get&amp;id='.$row['id'].'&amp;name='.$row['name'].'">View</a></td>');
				print('<td><a href="'.$pluginPath.'dbfile.php?action=get&amp;id='.$row['id'].'&amp;inline=false&amp;name='.$row['name'].'">Download</a></td>');
				print('<td><a href="'.$pluginPath.'dbfile.php?action=edit&amp;id='.$row['id'].'">Edit</a></td>');
				print('<td><a href="'.$pluginPath.'dbfile.php?action=delete&amp;id='.$row['id'].'" onclick="return confirm(\'Really delete \\\''.$row['name'].'\\\', id '.$row['id'].'?\');">Delete</a></td>');
				print("</td>\n");
				$rowCount++;
			}

			mysql_free_result($result);
		?>
				</tbody>
			</table>
	<?php
		}
	?>
	</div>
	<?php
	}

	/**
	 * Lists the files connected to the current post.
	 * Use it in page.php and/or index.php like this after the post content:
	 * <code>
	 * <ul id="filesList">
	 *     <?php db_file::listPostFileLinks(); ?>
	 * </ul>
	 * </code>
	 */
	 function listPostFileLinks($pre = '<li>', $post = '</li>', $none = '') {
		global $id;
		$hideDbFiles = get_post_meta($id, 'hideDbFiles', true) == 'true';
		
		if ($hideDbFiles == true) {
			return;
		}
		
		global $pluginPath;
		$blockStart = '<ul class="dbfiles">';	// FIXME make an option
		$blockEnd = "</ul>";	// FIXME make an option
		
		$connection = db_file::getConnection();
		$sql = 'SELECT '.get_option('dbfile_post2file_table').'.dbfileID, '.get_option('dbfile_post2file_table').'.sortNo, '.get_option('dbfile_table').'.name FROM '.get_option('dbfile_post2file_table').', '.get_option('dbfile_table').' WHERE '.get_option('dbfile_post2file_table').".postID = $id AND ".get_option('dbfile_table').'.id = '.get_option('dbfile_post2file_table').'.dbfileID ORDER BY sortNo';
				
		if (!($result = mysql_query($sql, $connection))) {
				db_file::showerror();
		}
	
		if (mysql_num_rows($result) < 1) {
			print($none);
			return;
		}
		print($blockStart);
		
		$entryPattern = get_option('dbfile_fileentry');

		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$url = $pluginPath.'dbfile.php?action=get&amp;id='.$row['dbfileID'].'&amp;name='.$row['name'];
			$entry = str_replace('%name', $row['name'], $entryPattern);
			$entry = str_replace('%url', $url, $entry);
			$entry = str_replace('%sizeMb', '%sizeMb not implemented yet', $entry);
			print($entry);
		}
		print($blockEnd);
	}
	
	/**
	 * Installs dbfile into Wordpress, by creating necessary tables and options.
	 * This method is invoked when the plugin is enabled in Wordpress. The actual call is made from dbfiles.php.
	 */
	function install() {
		db_file::checkAccess();
		global $user_level;
		
		get_currentuserinfo();
		if ($user_level < 10) {
			return;
		}
		
		global $table_prefix, $wpdb;
		$table_name = $table_prefix."files";
		$post2file_table_name = $table_prefix."post2file";
		add_option('dbfile_use_fileupload', '0', 'Turn on uploads by setting this to 1', 'no');
		add_option('dbfile_use_hitcounter', '0', 'Turn on hit counter by setting this to 1', 'no');
		add_option('dbfile_database', DB_NAME, 'The target database where files will be uploaded to', 'no');
		add_option('dbfile_table', $table_name, 'The target table where the files will be uploaded to', 'no');
		add_option('dbfile_post2file_table', $post2file_table_name, 'The target table where the files will be uploaded to', 'no');
		add_option('dbfile_upload_maxk', '300', 'The maximun number of kilobytes (KB) that will be acepted on upload', 'no');
		add_option('dbfile_upload_allowedtypes', 'jpg jpeg gif png', 'The allowed files types separated with space, excluding the extension dot', 'no');
		add_option('dbfile_preview_types', 'jpg jpeg gif png', 'The file types that should be previewed when editing a file, separated with space, excluding the extension dot', 'no');
		add_option('dbfile_upload_minlevel', '6', 'The minimum user level that are allowed to upload files to the database', 'no');
		add_option('dbfile_fileentry', '<li><a href="%link">%name</a></li>', 'The structure of a dbfile entry in a post. Use %name and %link inside the string', 'no');

		$result = mysql_list_tables(DB_NAME);
		$tables = array();
	
		while ($row = mysql_fetch_row($result)) { $tables[] = $row[0]; }

		if (in_array($table_name, $tables)) {
			// FIXME do an alter table instead?
			$sql = 'ALTER';
			//return;
		} else {
			$sql = 'CREATE';
		}


		$sql = $sql." TABLE `".$table_name."` (
  `id` int(11) NOT NULL auto_increment,
  `name` text,
  `mimeType` varchar(30) default NULL,
  `content` blob NOT NULL,
  `description` text,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NULL default NULL,
  `hits` int(11) NOT NULL default 0,
  PRIMARY KEY (`id`),
  FULLTEXT KEY `name` (`name`,`description`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;";

		require_once(ABSPATH.'wp-admin/upgrade-functions.php');
		dbDelta($sql);
		
		
		if (in_array($post2file_table_name, $tables)) {
			// FIXME do an alter table instead?
			$sql = 'ALTER';
			//return;
		} else {
			$sql = 'CREATE';
		}
		
		$sql = $sql." TABLE `".$post2file_table_name."` (
  `relID` int(11) NOT NULL auto_increment,
  `postID` int(11) NOT NULL,
  `dbfileID` int(11) NOT NULL,
  `sortNo` int(11) NOT NULL,
  PRIMARY KEY (`relID`)
) TYPE=MyISAM AUTO_INCREMENT=1;";
		require_once(ABSPATH.'wp-admin/upgrade-functions.php');
		dbDelta($sql);
	}

	/**
	 * Returns true if the specified file name is an image (jpg, jpeg, gif or png).
	 *
	 * Example:
	 * <code>
	 * $myFileName = 'myFile.jpg';
	 * if (db_file::isImage($myFileName) {
	 * 	print('The filename is an image!');
	 * }
	 * </code>
	 * Resulting in the output: The filename is an image!
	 *
	 * @param $fileName the filename to test
	 * @return true if the specified filename is an image, false otherwise.
	 */
	function isImage($fileName) {
		// FIXME rename to isPreview or something...
		return in_array(strtolower(db_file::getExtension($fileName)), explode(' ', get_option('dbfile_preview_types'))) != FALSE;
	}
	
	function getExtension($fileName) {
		return db_file::rightback($fileName, '.');
	}
	
	function right($haystack, $needle, $start = 0) {
		$position = strpos($haystack, $needle, $start);
		return $position == FALSE ? '' : substr($haystack, $position + 1);
	}
	
	function rightback($haystack, $needle, $start = 0) {
		$position = strrpos($haystack, $needle, $start);
		return $position == FALSE ? '' : substr($haystack, $position + 1);
	}
	
	/**
	 * Checks that the current user is authorized to handle dbfiles (upload/edit/remove).
	 * The level of the user must be at or above the option 'dbfile_upload_minlevel'.
	 */
	function checkAccess() {
		global $user_level;
		get_currentuserinfo();
		
		if ($user_level < get_option('dbfile_upload_minlevel')) {
			db_file::authorizationFailure();
		}
	}
	
	/**
	 * Show an authorization failure error message to the user.
	 */
	function authorizationFailure() {
		die('You are not authorized to perform this operation');
	}
	
	/**
	 * Shows an error and stops the script
	 */
	function showerror() {
		if (db_file::$trace) {
			$s = mysql_errno().' : '.mysql_error().' '.print_r(debug_backtrace(), true);
		}

		if (mysql_error()) {
			die('Database error '.$s);
		} else {
			die('Could not connect to the database '.$s);
		}
	}
	
	/**
	 * Secure the user data by escaping characters and shortening the input string
	 *
	 * @param $input the data to "clean"
	 * @param $maxlength maximum number of characters to return.
	 */
	function clean($input, $maxlength) {
		return EscapeShellCmd(substr($input, 0, $maxlength));
	}

	/**
	 * Checks that the specified dbfile ID is correctly "formatted". If not, makes the execution die. 
	 */
	function checkId($id) {
		if (!is_numeric($id)) {
			die('Invalid dbfile id');
		}
	}
	
	function debug($message) {
		if (db_file::$trace) {
			print($message."<br />");
		}
	}
}

?>
