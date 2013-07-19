=== User Language Switch ===
Contributors: webilop
Tags: language, localization, switch language
Requires at least: 3.0.1
Tested up to: 3.5.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==
User Language Switch allows you to associate the original version and the translation of pages, posts and custom post types through custom fields
 in an easy and friendly way. The available languages are taken from the current theme and the wordpress installation.

= Localization =

*English (default).
*Spanish

= Documentation =

You can find the installation and configuration steps at: http://www.webilop.com/products/user-language-switch-wordpress-plugin/
Documentation in spanish is also available: http://www.webilop.com/productos/plugin-wordpress-user-language-switch/

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the `user-language-switch` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Once you have activated the plugin, select the option 'User language' in your wordpress menu and select the default langauges for the backend and the frontend.
4. Go to your post/page and select the post language, and the translated versions of the plugin using the custom fields under the 'Language' box.
5.Add the following php code to the template or theme file where you want the language links to appear:
<?php echo uls_language_link_switch(); ?>


== Frequently Asked Questions ==

= I only see English among the user language switch options =

If the only available option you see in the user language optons is English, it is because you don't have any other language available in your wordpress installation nor in your theme.
Make sure you create a 'languages' folder in your theme folder containing the .mo and .po files that correspond to the languages you will use in your blog.


== Screenshots ==

1. screenshot1.png illustrates the User Language options available in the backend. It allows you to select the languages that will be used in the backend and the frontend as default.
2. screenshot2.png shows the 'Language' meta box that will appear in every post, page and custom post type from where you will be able to select the post language and its translated version.