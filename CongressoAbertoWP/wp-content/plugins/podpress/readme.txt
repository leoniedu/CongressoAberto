=== podPress ===
Tags: post, podcast, audio, video, admin, feed, widget, stats, statistics 
Contributors: seek3r, macx, iscifi
Donate link: http://www.mightyseek.com/podpress_donate.php
Requires at least: 1.5
Tested up to: 2.3
Stable Tag: 8.8

A dream plugin for Podcasters using WordPress..

== Description ==

podPress adds tons of features designed to make WordPress the ideal 
platform for hosting a podcast.
Features:

* Full featured and automatic feed generation (RSS2, iTunes and ATOM and BitTorrent RSS)
* Preview of what your Podcast will look like on iTunes
* Podcast Download stats, with cool graphs. See below.
* Support for Premium Content (Pay Only)
* Makes adding a Podcast to a Post very simple
* View MP3 Files ID3 tags when your Posting
* Control over where the player will display within your post and what it will look like.
* Support for various formats, including Video Podcasting
* Supports unlimited number of media files.
* Automatic Media player for MP3, RM, OGG, MP4, MOV, QT, FLV, ASF, WMV, AVI, and more, with inline and Popup Window support.
* Preview image for videos
* Support for seperate Category podcasts
* Audio Comments

For the latest information visit the website

http://www.mightyseek.com/podpress

A complete changelog can be found at

http://www.mightyseek.com/podpress/changelog/

== Installation ==

If you have ever installed a pluggin, then this 
will be pretty easy.

1. Extract the files. Copy the `podpress` directory into `/wp-content/plugins/`
1. If your using WordPress 1.5, then you will need to use the replacement wp-rss2.php file that is provided. Look in the `podpress/optional_files`  directory
1. Activate the plugin through the 'Plugins' menu in WordPress
2. To add a link to itunes on your website, set the FeedID in the PodPress options page, and then add this code in  your template `<?php podPress_iTunesLink(); ?>`

Details about all the optional_files are in optional_files/details.txt
Details about premium podcasting support is in optional_files/premiumcasting.txt

Upgrade - VERY IMPORTANT

Upgrading to 4.0:
The plugins/podpress.php file is no longer needed and MUST be deleted. The podpress.php file now lives in `plugins/podpress/`

About wp-rss2.php:
Only users that have not upgraded to a version of WordPress above 2.0.0 need the custom wp-rss2.php file. If you have upgraded past 2.0.0 then use the normal wp-rss2.php that came with Word Press. 

About wp-commentsrss2.php: 
No one should be using the custom version of this file anymore. It is not supported and may cause problems.

== Configuration ==

1. Go to the new podPress menu and start configuring podPress and your Feed settings.

== Frequently Asked Questions ==

For more FAQs see the official [podPress FAQ](http://podcasterswiki.com/index.php?title=PodPress_FAQ "Official podPress FAQ")

= Blank screen after activating podPress =

Some PHP5 users end up with a blank screen after activating the podPress plugin. For reasons yet fully understood some PHP5 installations consume double the memory compared to a PHP4 install when dealing with WordPress. Some notes I have seen blame it on a bug with caching objects in session data, but I have not debugged it to that level yet.
The solution is to increase the memory_limit in your php.ini from 8MB to at last 12MB 

= How do I upgrade PodPress?? =

In general this just requires that you replace the existing files with the new ones. Sometimes its a good idea to delete all the files in wp-content/plugins/podpress/ and re-upload them fresh.

== Screenshots ==

1. Write a page and at the end of your Post add your mp3 filename or full URL.
2. Players automatically added to your blog
3. Edit config settings and preview what your podcast will look like in various podcasting directories including iTunes.
4. Stats graph by podcast
5. Stats graph by date
