=== Plugin Name ===
Contributors: frasten
Donate link: http://polpoinodroidi.com/wordpress-plugins/wordpresscom-popular-posts/#donations
Tags: posts, widget, statistics, popular posts
Requires at least: 2.8.0
Tested up to: 2.8.4
Stable tag: 2.1.0

This plugin can show the most popular articles in your sidebar, using data collected by Wordpress.com Stats plugin.

== Description ==


Wordpress.com Popular Posts lists the most popular posts on a wordpress powered weblog.
This list can be used in the sidebar to show an indication of which are the most visited pages.

For further info visit [plugin homepage](http://polpoinodroidi.com/wordpress-plugins/wordpresscom-popular-posts/).

**Requires [Wordpress.com Stats](http://wordpress.org/extend/plugins/stats/) plugin, at least v1.2**

**From v2.0.0, it requires WordPress 2.8 or greater.**

== Installation ==

Wordpress.com Popular Posts can be installed easily:

1. Download and install [Wordpress.com Stats](http://wordpress.org/extend/plugins/stats/) plugin.
1. Download Wordpress.com Popular Posts .zip archive
1. Extract the files in the .zip archive, and upload them (including subfolders) to your /wp-content/plugins/ directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Inside the WordPress admin, go to Design > Widgets, and add the 'Popular Posts' widget where you want, then save the changes.
1. If you want, you can customize some settings for the widget, in that page.

== Frequently Asked Questions ==

= I added the widget, but nothing shows up =

Check whether the Wordpress.com Stats plugin is installed and active.
You must have at least version 1.2 of WP Stats.

= How can I integrate this plugin in my non-widget-ready theme? =
If your theme supports widgets, you can place the widget named 'Popular Posts' where you want.

If it doesn't, put this code inside the file sidebar.php, in your theme files:

`<?php if (function_exists('WPPP_show_popular_posts')) WPPP_show_popular_posts(); ?>`

Optionally you can add some parameters to the function, in this format:

`name=value&name=value etc.`

Possible names are:

* `title` (title of the widget, you can add tags (e.g. `<h3>Popular Posts</h3>`) default: Popular Posts)
* `number` (number of links shown, default: 5)
* `days` (length of the time frame of the stats, default 0, i.e. infinite)
* `show` (can be: both, posts, pages; default both)
* `format` (the format of the links shown, default: `<a href='%post_permalink%' title='%post_title%'>%post_title%</a>`)
* `excerpt_length` (the length of the excerpt, if `%post_excerpt%` is used in the format)
* `title_length` (the length of the title links, default 0, i.e. unlimited)
* `exclude` (the list of post/page IDs to exclude, separated by commas. Read the following FAQ for instructions)
* `cutoff` (don't show posts/pages with a view count under this number, default 0, i.e. unlimited)
* `list_tag` (can be: ul, ol, default ul)
* `category` (the ID of the category, see FAQ below for info. Default 0, i.e. all categories)
* `cachename` it is used to enable the cache. Please see the FAQ below.

Example: if you want to show the widget without any title, the 3 most
viewed articles, in the last week, and in this format:
*My Article (123 views)* you will use this:

 `<?php WPPP_show_popular_posts( "title=&number=3&days=7&format=<a href='%post_permalink%' title='%post_title_attribute%'>%post_title% (%post_views% views)</a>" );?>`

You don't have to fill every field, you can insert only the values you
want to change from default values.

You can use these special markers in the `format` value:

* `%post_permalink%` the link to the post
* `%post_title%` the title the post
* `%post_title_attribute%` the title of the post; use this in attributes, e.g. `<a title='%post_title_attribute%'...`
* `%post_views%` number of views
* `%post_excerpt%` the first n characters of the content. Set n with *excerpt_length*.
* `%post_category%` the category of the post

= How can I discover the ID of a post/page? =
Log into your admin page, go to **Posts** or **Pages**; go with your mouse
on your post's title, and in your status bar you should see something like
this: http://YOURSITE.com/wp-admin/post.php?action=edit&post=14
Then **14** is the number you are looking for.

= How can I enable the caching system when using WPPP_show_popular_posts()? =

When you're using `WPPP_show_popular_posts()`, the caching system is not
enabled.
However you can easily enable it adding a `cachename` parameter to your
settings string. You will have something like this:

 `<?php WPPP_show_popular_posts( "title=&number=3&cachename=topposts1" );?>`

where *topposts1* is a name you must choose for your list. This allows you
to have different lists with different caches.


= I have some other issues. How can I get a debug log? =

1. Download [this zip file](http://polpoinodroidi.com/download/plugins/wppp_debug.zip)
1. extract it and overwrite the current `/wp-content/plugins/wordpresscom-popular-posts/wppp.php` file

Now, if you are using WPPP as a widget:

1. Log in to your admin page.
1. Go to Settings -> Popular Posts *DEBUG*
1. you’ll see a long text. Copy & mail it to: frasten AATT gmail DOOTT com

If you are instead using `WPPP_show_popular_posts()` function for non-widget-ready
themes:

1. edit your theme files, replacing your old `WPPP_show_popular_posts` with `WPPP_show_popular_posts_debug` .
1. you'll see the debug text in place of your usual top-posts list.
1. Copy & mail it to: frasten AATT gmail DOOTT com

**IMPORTANT**: Then, for both cases (widget and function) reinstall the
original version of wppp.php.


= I want to translate the plugin in my language! =

Make a copy of the file `language/wordpresscom-popular-posts.pot` of the
plugin, edit it adding your translations, and then send it to:
frasten AATT gmail DOOTT com.

You can take a look to the it_IT translation file if you want.

I'll add your translations to the plugin!


== Changelog ==

= 2.1.0 =
* New Feature: implemented a cache system. This will improve the speed
  of the plugin. If you are using the function WPPP_show_popular_posts()
  for non-widget-ready themes, please read the FAQ.
* Fixed an issue with %post_category%. Thanks to Isaac | GoBlogger.
* Better compatibility with plugins like qTranslate. Thanks to Blutarsky
  for this.

= 2.0.2 =
* Regression: you couldn't set an empty title anymore.
* Regression: You couldn't add HTML tags to the widget title.
* Fix: more robust security checks.

= 2.0.1 =
* New feature: now you can use %post_category% in your format, to show
  the post's category.
* New feature: now you can show posts from a specific category.

= 2.0.0 =
* New complete rewrite, using WP 2.8 Widget API. Note: from now on, this
  plugin will require at least WP 2.8
* New feature: now you can add multiple widgets with their own different
  settings!
* New feature: now you can exclude specific posts/pages by IDs.
* New feature: don't show posts with a view count under x
* New feature: now you can choose between unordered (<ul>) or ordered
  (<ol>) list.
* Fix: now private posts are excluded from the list.
* Fix: now deleted posts shouldn't appear anymore.
* Fix: W3C Validation fix, thanks to Jonathan M. Hollin
* Fix: fixed an issue with titles containing special characters/quotes.
* Fix: removed the shortcodes from the excerpt, thanks to Peter.

= 1.3.5 =
* Added a workaround for a cache issue in stats plugin.

= 1.3.4 =
* Hopefully fixed a problem on some blogs, when displaying only posts or
  only pages.

= 1.3.3 =
* Updated compatibility with WP 2.6

= 1.3.2 =
* New option: now you can limit the length of the links.

= 1.3.1 =
* Fixed an incompatibily with PHP < 5.0.

= 1.3 =
* Now the titles & permalinks are taken from the database, so they should
  be updated.
* Arabic/Greek etc. language issues should be fixed now.

= 1.2 =
* New feature: Added multilanguage support.
* Added italian language
* New feature: now you can show the first *n* characters of the content
  of each post, through the %post_excerpt% parameter. Just add it to your
  format.

= 1.1.2 =
* Added a fix for a bug in WordPress Stats.

= 1.1.1 =
* Fixed some unclosed tags, which caused issues in IE7 configuration of
  the widget.
* Removed (actually it's only skipped, not fixed yet) a nasty MySQL error
  if there aren't any "top posts".

= 1.1 =
* New feature: now you can choose to show only posts, only pages, or both (default).

= 1.0.1 =
* Added CSS class "wppp_list", so that you can now customize the styling in your stylesheet.

= 1.0 =
* New feature: now you can customize the format of the links (see FAQ)
* New Feature: it's now possible to show the number of views of the post (see FAQ)
* Fix: fixed utf-8 encoding issue with WP >= 2.5
* Fix: now the widget should better integrate with your theme
* Change: **Note**: the way to call WPPP_show_popular_posts() to integrate
  the plugin in non-widget-ready themes has changed. See FAQ.

= 0.4.0 =
* Added arguments to the function WPPP_show_popular_posts(). Now you can
  customize several parameters even if your theme doesn't support widgets.

= 0.3.0 =
* New feature: now you can set the length (in days) of the desired time frame.
* Fixed a bug with titles containing foreign characters.

= 0.2.2 =
* Added support for non-widgets-ready themes.

= 0.2.1 =
* Fixed a bug with quotes.

= 0.2.0 =
* Added widget configuration.

= 0.1.0 =
* First working release with the widget.
