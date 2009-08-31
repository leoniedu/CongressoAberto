=== dbfile ===
Tags: file, database, image, store, retrieve, upload, download, mysql
Contributors: kanngard

dbfiles is a plugin that enables storage of files to the MySQL database. Why store files in a database? Short answer: it is easier to handle file meta data. Some say that storing files in a db will degrade performance... They are right of course :-) The plugin is not intended to be used for ALL images etc on a site, only those downloadable (small) files that can belong to posts - example codelistings, images, sounds etc.



== History ==
    - 2005-10-19: * added the possibility to hide dbfiles list in a post, by setting the custom field hideDbFiles to true
                  * $wpURL is now replaced in dbfile.inc and dbfile.php with get_options('siteurl')
                  * the post(s) of a file is now shown (with link) in "Manage Database Files"
                  * install now requires user level 10.
                  * install now checks if the post2file table exists before creation
                  * install now checks if ALTER TABLE or CREATE TABLE should be used on files and post2file tables.
    - 2005-10-18: * a file can now be fetched using only the name. If more than one file exists with the same name, the first encountered is returned.
    - 2005-10-13: * renamed all options from db_file* to dbfile_*
                  * renamed db_post2file_table to dbfile_post2file_table
                  * checking of id is done in dbfile.inc instead of in dbfile.php
                  * sortNo is disabled if no postID has been entered when editing a file
                  * added option dbfile_preview_types that controls what file types to preview when editing
                  * added option dbfile_fileentry that controls the structure of a file list entry in a post with listPostLinks
                  * added hits to files table
                  * added download counter + option to turn it on globally (default is off)
    - 2005-10-12: * only images are previewed
    - 2005-10-07: * added $trace flag to minimize debug/trace output if disabled.
                  * sortNo moved from files to post2file table
    - 2005-09-27: * added option for post2file table
                  * added post2file table in install
                  * added relation post2file in receiveFile
                  * requirements changed, added my production environment
                  * moved all functions into dbfile class to avoid name clashes
    - 2005-09-23: * added Requirements in readme.txt
                  * install/enabling the plugin now checks if the files table exists before trying to create it
                  * SQL changed to be MySQL 4.0x compatible
                  * more user friendly maximum upload size error message
                  * added postID and sortNo to table
                  * added FilePostID and FileSortNo to upload page
1.0 - 2005-09-20: initial release

== Requirements ==
This is the setup I have tested with. Please let me and the community know if you have got it working on other conjfigurations!

Test environment:
Windows XP SP2 (http://www.microsoft.com/windowsxp)
Apache httpd 2.0.54 (http://httpd.apache.org)
PHP 5.0.4 (http://www.php.net)
MySQL 4.1.13-nt (http://www.mysql.org)
Wordpress 1.5.2 (http://www.wordpress.org)

Production environment:
FreeBSD or OpenBSD (my hosting company wont give away exactly which of them I use)
Apache httpd 1.3.33 (http://httpd.apache.org)
PHP 4.x (http://www.php.net)
MySQL 4.0.18 (http://www.mysql.org)
Wordpress 1.5.2 (http://www.wordpress.org)


== Installation ==

* Extract the files in the archive
* Upload the extracted folder (dbfile) to your plugins folder, usually `wp-content/plugins/`
* Optionally: if you rename the dbfile folder, change the $pluginPath variable in dbfile.inc.
* Activate the plugin at the plugin screen. The default table and options are set up automatically.
* Optional: modify the settings in the 'Options', 'Database File Options' pane
* Finito! You are now ready to upload files!



== Upload a file ==
* Go to "Manage", "Add Database File".
* Optional: fill in a short description
* Optional: fill in post id, to associate the file with a post
* Optional: fill in sort number to sort on when displaying files in a post (only enabled if you filled in a post id)
* Click "Browse" and find the file you want to upload (select it and choose OK)
* Hit the "Submit" button
* You will be redirected to the "Database Files" page


== Manage files ==
* Go to "Manage", "Database Files"
* If you haven't uploaded any files, there will be no useful information here. Upload a file by following the instructions above.
* If you want to view a file, click "View"
* If you want to download a file, press "Download"
* If you want to delete a file, click "Delete" and confirm with "OK"
* If you want to edit a file, click "Edit", and you will be presented with the upload page again.



== Frequently Asked Questions ==

Question:
 - Do I really need to use this plugin?
Answer:
 - No. You could use the built-in file upload method, but your files will be put in your filesystem, instead of in a database.

Question:
 - How can I tell if it's working?
Answer:
 - If you have enabled it, you should see the new tab "Database Files" in the Options page.
   You should also get two new tabs in the Manage page, "Database Files" and "Add Database File".

Question:
 - I have found some bugs or have some feedback, where do I report it?
Answer:
 - http://dev.wp-plugins.org/report/1



== Screenshots ==
Can be found at http://dev.kanngard.net/Permalinks/ID_20050719162813.html



== Uninstall ==
* Check what database and table the plugin is using, under Options, Database Files
* Turn the plugin off in the Plugin Management page.
* Delete the dbfiles directory in the Wordpress plugin directory
* Optional: delete the files table mentioned in Options, Database Files, Destination database / Destination table
* Optional: delete the options added in wp_options (see "Options added and used in wp_options" below)



== Files ==
This is how the structure should look when installed correctly:

/wordpress/plugins/dbfile/dbfile.inc		- the db_file class with all functionality to access database files
                          dbfile.php		- all actions (edit, view, get, delete) are handled via this file
                          dbfiles.php		- the plugin main activation file
                          LICENSE.TXT		- GPL license
                          readme.txt		- this file



== Tables ==
The files table (defaults to wp_files in the Wordpress database) consists of these fields:
 id		- the id of the database file
 name		- the name of the file
 mimeType	- the mime type of the file, i.e. image/png for a .png file
 content	- the actual file, stored as a blob - binary large object
 description	- a short description of the file
 created	- date and time when the file was uploaded (first time)
 modified	- date and time when the file was modified/uploaded (after first time)
 hits		- the number of times the file has been downloaded/viewed. This only increments if hit counting is turned on with the dbfile_use_hitcounter option. 

The post2file table (defaults to wp_post2file in the Wordpress database) consists of these fields:
 relID		- the relational id
 postID		- the id of the post record
 dbfileID	- the id of the dbfile record
 sortNo		- the sorting number used when displaying dbfiles in a post, to sort the entries
For now, the dbfile plugin handles only one to one relation between a post and a dbfile. This is soon to be changed.


== Options added and used in wp_options ==
 dbfile_use_fileupload		- "1" to enable uploads, "0" to disable. Default is "0"
 dbfile_database		- The name of the database to use for upload/download of files.
 				  Defaults to "wordpress" (or whatever you have named it).
 dbfile_table			- The name of the table to use for upload/download of files.
 				  Defaults to "wp_files" (actually the wp_ part is dynamically fetched from your installation).
 dbfile_post2file_table		- The name of the table to use for relations between posts and db files.
 				  Defaults to "wp_post2file" (the wp_ part is dynamically fetched from your installation).
 dbfile_upload_maxk		- The maximum number of kilobytes that will be received when uploading.
 				  Defaults to 300
 dbfile_upload_allowedtypes	- The file extensions that are allowed to be uploaded, separated by spaces.
 				  Defaults to "jpg jpeg gif png"
 dbfile_upload_minlevel		- The minimum user level required to upload files.
 				  Defaults to 6.
 dbfile_preview_types		- The file types that should be previewed when editing a file, separated with space, excluding the extension dot
 				  Defaults to jpg jpeg gif png
 dbfile_entry			- The structure of a dbfile entry in a post. Use %name and %link inside the string
				  Defaults to <li><a href="%link">%name</a></li> 
 dbfile_use_hitcounter		- Turn on hit counter by setting this to 1
 				  Defaults to 0
 

== Todo ==
As this is a new project, there are several items/bugs that could be fixed in the future:
* Localization
* Check FIXMEs
* Security/SQL-injection?
* Compression
* Use the ORIGINAL created and modified of the file? Add a field "uploaded" to wp_files then.
* Follow Wordpress coding standards
* Different files for the different actions? adddbfile.php, editdbfile.php, admindbfiles.php?
* Add "read" and "edit" rights to files
* Separate content from presentation better
* Remove hard coded links/paths
* More documentation
* Sortable table in Database File Management (see http://www.kryogenix.org/code/browser/sorttable/ for an example)
* Nicer error messages for users (XHTML compliant)
* Integrate with the image/file browser in ChenPress/FCKeditor
* Caching (on/off) (cache files on filesystem?)
* Add referrer checking, so external sites can not link directly (on/off)
* Change the content field in the files table to LONGBLOB
* Export files from the database to the file system
* Rename files
