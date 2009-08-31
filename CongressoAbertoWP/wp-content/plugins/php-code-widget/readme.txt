=== PHP Code Widget ===
Contributors: Otto
Donate link: http://zme.amazon.com/exec/varzea/pay/T1PW4BG07GXM79
Tags: php, widget, execphp
Requires at least: 2.5
Tested up to: 2.8
Stable tag: 1.2

Like the Text widget, but also allows working PHP code to be inserted.

== Description ==

The normal Text widget allows you to insert arbitrary Text and/or HTML code. 
This allows that too, but also parses any inserted PHP code and executes it. 
This makes it easier to migrate to a widget-based theme.

All PHP code must be enclosed in the standard <?php and ?> tags for it to be 
recognized.

== Installation ==

1. Upload `execphp.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Use the widget like any other widget.

== Frequently Asked Questions ==

= There's some kind of error on line 37! =

That error means that your PHP code is incorrect or otherwise broken. 

= No, my code is fine! =

No, it's not. 

Really. 

This widget has no bugs, it's about the simplest widget one can possibly 
make. Any errors coming out of the "execphp,php" file are errors in code you 
put into one of the widgets. The reason that it shows the error being in the
execphp.php file is because that is where your code is actually being run
from.

So, if it says that you have an error on line 27, I assure you, the problem 
is yours. Please don't email me about that error.

== Screenshots ==

1. The widgets screen showing two PHP code widgets in use.
