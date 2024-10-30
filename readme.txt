=== Calendar Posts ===
Contributors: swedish boy
Donate link: http://www.swedishboy.dk/products/donate/
Tags: calendar, post, posts, events, calendar posts
Requires at least: 2.8
Tested up to: 3.1.2
Stable tag: 0.7.1

A powerful yet simple plugin for adding calendar functionality to posts.
Great for using posts as events and calendar inputs. 

== Description ==

Adds a 'calendar-box' to the edit post mode. Here you set up to 10 different dates for your post to be associated with. Through a sidebar widget you control how to display posts that have upcoming dates set to them. This plugin should work fine with other post plugins and the posts you add "calendar-post-dates" will still be displayed in your normal blog post flow.

Features:

* Adds Date Picker (jQuery) box to 'edit post mode'.
* Up to 10 different dates can be set for one post.
* Configurable widget to choose how your sidebar calendar will look.
* CSS customizable through your themes stylesheet. Developers can style the output as they like. (Non developers can pick some css-code in the FAQ's) 

== Installation ==

1. Upload `calendar-posts` to your plugins-directory
1. Activate the plugin through the 'Plugins' menu in WordPress

or

1. Download, install and activate `calendar-posts` through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= How do I use this plug-in? =

Once installed you'll find the calendar-post box on when you edit or create posts. Pick dates to make the post available in the calendar. Set up the Calendar Posts widget under Appearace > Widgets and posts will appear in a Calendar in your sidebar.

= Where is the widget? =

Under `wp-admin/widgets.php`. It's called Calendar Posts.

= Where is the settings panel? =

There's none. You configure the calendar on each widget directly. There are some configurable vars at the top inside the plugin's php-script file (for developers).

= Where is that CSS code mentioned in the description? =

It's here:
`
.cp-month {
	padding: 8px 0px 5px 0px;
	font-weight: bold;
	font-size: 1.2em;
}
.cp-date-div {
	margin: 0.4em 0px;
}
`


= The month names are shown in English even though WordPress is set to a different language? =

Yes. Since PHP is showing month abbrevations wrong (typographicly) you have to set custom month names on your own at the top of the script file. Sorry. This is something I had to do since PHP's abbrevations always appear with three letters which is wrong.

= Can I change how month names are shown? =

Yes. This can be acheived inside the plugins php file. You should know some PHP and you'll find how to add your own month abbrevations at the top of the script file. More details there.

== Screenshots ==

1. Screenshot showing the plugin in edit post mode.
1. Screenshot showing the widget configuration.

== Changelog ==

= Future versions =
* Time will be displayed
* Better localized

= 0.7.1 =
* Last version missed jQuery files. Misstake during upload of version (sorry)

= 0.7 =
* Defaults to english calendar names (months and days).

= 0.6 =
* First public 'beta' release

== Upgrade Notice ==

Minor upgrade from 0.6