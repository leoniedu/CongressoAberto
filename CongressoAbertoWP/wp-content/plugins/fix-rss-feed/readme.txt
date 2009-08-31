=== fix-rss-feed ===
Contributors:flyaga li
Donate link: http://www.flyaga.info/go/fix-rss-feed-donations.php
Tags: rss feed error,burn feedburner rss feed error,firefox rss feed error,opera rss feed error
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 1.01
Fix rss feed error "Error on line 2: The processing instruction target matching "[xX][mM][lL]" is not allowed." while burn feed from feedburner.com

== Description ==
Fix wordpress rss feed error "Error on line 2: The processing instruction target matching "[xX][mM][lL]" is not allowed." while you burn wordpress rss feed from www.feedburner.com, also fix error "XML or text declaration not at start of entity" in firefox, and fix error "XML declaration not at beginning of document" in opera.

Relative Posts: Wordpress how to correct rss feed in the Error on line 2: The processing instruction target matching "[xX] [mM] [lL]" is not allowed error?
http://www.flyaga.info/en/wordpress/fix-wordpress-rss-feed-error.htm

version: 1.03

release date: 2009-05-24

website: http://www.flyaga.info

plugin url: http://www.flyaga.info/en/wordpress/plugins/fix-rss-feed-error-wordpress-plugins.htm

            http://wordpress.org/extend/plugins/fix-rss-feed/

download url: http://downloads.wordpress.org/plugin/fix-rss-feed.zip

              http://www.flyaga.info/blog/download/fix-rss-feed.rar

email:flyaga@163.com

change log:
2008-12-30 release v1.0
2009-02-04 release v1.01, fixed some errors, add create backup files before change php files, thanks for Willem Kossen's advice.
2009-02-16 release v1.02, fixed some errors
2009-05-24 release v1.03, add "check wordpress rss feed error" button, thanks for Wanda's advice.

install steps:

1. Upload to your plugins folder, usually `wp-content/plugins/` and unzip the file, it will create a `wp-content/plugins/fix-rss-feed/` directory.

2. Activate the plugin on the plugin screen.

3. done

usage steps:

1. Into the Dashboard admin-> options -> fix rss feed

2. Click on the "fix wordpress rss feed" button, it will check all folders (except wp-admin and wp-includes catalog)'s php file whether include head and tail blank lines, It would delete the blank lines (Do not worry, it will not delete central blank lines in files, delete only head and tail blank lines, so it will not affect your php program).

3. After fix job completed, the result will be listed, if your document is readyonly, it prompted an error; after you change file permission to writable, please click the "fix wordpress rss feed " button and try again.

4. All fix are completed, you will find your wordpress rss feed no mistake,^_^

Uninstallation steps:

1. go into admin->plugins ,disable fix-rss-feed

2. done.

More Info: please visit http://www.flyaga.info/en/wordpress/plugins/fix-rss-feed-error-wordpress-plugins.htm

== Installation ==
1. Upload to your plugins folder, usually `wp-content/plugins/` and unzip the file, it will create a `wp-content/plugins/fix-rss-feed/` directory.
2. Activate the plugin on the plugin screen.
3. done

== Frequently Asked Questions == 
= This plug-in will damage php files? =
Do not worry, this plug-php files to delete only the beginning and end of the blank lines, it will not delete files Central blank lines, and it will not affect your php process running, if you still do not trust, you can first do file back up, and then run this plug-in to fix feed error. If it affects your php code, you can rename .bak to .php, then your original php files.

= if php file is not writable, how to do? =
If your os is the windows, please check your file whether is read-only, so, in the Explorer right-click the file, choose Properties, uncheck read-only attribute, and then click "ok" button, final your file can be written..

If your os is linux, using ftp or WinSCP into your server, find the file, right-click the file, choose Properties, please set file permissions to 777, and then click "ok" button, final your file can be written..

If it is linux of ssh login, then enter the server, to find documents, use chmod command to set permissions, for example:.

     chmod 777 test.php.

final you can write documents.

== Screenshots ==
http://www.flyaga.info/image/fix-rss-feed-screenshot.jpg