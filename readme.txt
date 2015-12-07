=== User Language Switch ===
Contributors: webilop
Tags: wordpress translation plugin, wordpress language, plugin, language switch, localization, i18n, content translation, multilanguage
Requires at least: 4.0
Tested up to: 4.3.1
Stable tag: 1.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

> #### Read before updating to 1.5
> Version 1.5 brings some major changes. Please create a backup of your database before updating!

> #### Collaboration
> [The plugin is available in Github](https://github.com/Webilop/user-language-switch). We receive patches to fix bugs and translation files.

User Language Switch is a WordPress translation plugin that allows admin users to set the default language for the website and also include translations of pages, posts, custom post types, menus and sidebars. Visitors and users registered can see the different translations available of the website.

Admin users can use this WordPress translation plugin to set the language for front-end and back-end individually. Registered users can set this languages too in their profile edition pages in the back-end of the website. When a registered user change languages, this change applies to the user's account only. Visitors and registered users that have not configured languages will see the website in the default language set the the admin user.

User Language Switch allows to create translations for each post, page or custom post type. When users are creating a post, page or custom post type, only need to set the language of the content and translations for that content. The plugin creates new sidebars for each language and sidebars registered in the website, then admin users can use different widgets for each language. Menus are translated automatically based on the translations linked to pages included in menus. Admin users can create new menus with other items and link them as translations of other existing menus.

**The plugin doesn't translate content automatically**, the plugin reads languages installed in the website to translate text in themes and other plugins. Admin and editor users should provide translations for all content created in the website. Webilop team is able to help you find professional translators to translate content of your website in different languages.

Other features of this wordpress translation plugin are: automatic translation of links, redirection of users to the website using the same language of the browser, display flags to switch languages, filter posts, pages and custom post types of different languages.

= Localization =

* English (default).
* Spanish
* Persian - Special thanks to Khalil Delavaran(khalil.delavaran[at]gmail.com) for this contribution.
* Serbo-Croatian - Special thanks to Borisa Djuraskovic(borisad[at]webhostinghub.com) from [Web Hosting Hub](http://www.webhostinghub.com) for this contribution.
* French - Special thanks to Jaillet, Christophe (C.JAILLET[at]meci.fr) for this contribution.

= Documentation =

Check [the manual of User Language Switch](http://www.webilop.com/products/user-language-switch-wordpress-plugin/) to get more details about installation and configuration of the plugin. [Documentation in Spanish is also available](http://www.webilop.com/es_ES/productos/plugin-wordpress-user-language-switch/).

== Installation ==

This section describes how to install the wordpress translation plugin and get it working.

1. Upload the `user-language-switch` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Once you have activated the plugin, select the option 'User language' in 'Settings' menu, select available and default languages for your website.
4. Go to your posts, pages and custom post types and select the language and their translations.

== Frequently Asked Questions ==

= What is User Language Switch? =

User Language Switch is a WordPress translation plugin that allows admin users to set the default language for the website and also include translations of pages, posts, custom post types, menus and sidebars. Visitors and users registered can see the website using any of its translations available.

= I only see English in the list of available languages =

Probably you don't have any other language installed in your WordPress installation nor in your theme. Take a look to the [instructions to install other languages to your WordPress website](https://codex.wordpress.org/Installing_WordPress_in_Your_Language).

= I don't see the option to set the language and translations of my posts and pages =

Go to the post or page edition page, scroll to the top of the page and click on tab "Screen Options", then check the option "Language".

= My posts and pages doesn't appear on the option to add translations =

When you are creating or editing a post, page or custom post page, you can assign as translations posts, pages and custom post types that are already published and that have a language assigned.

= I click on a flag to see the translation of my page, but I don't see content translated =

First check if your translation have been linked to your post, page or custom post in the edition page. If there is no translation available, then the content of the default language is displayed though other sections of the page could appear in the correct language.

= I don't see my posts in the blog =

By default the plugin filter posts in languages different to the language you are looking in the website. You can deactivate this feature in the settings page of the plugin. If a post doesn't have a language assigned, then the plugin considers the post in the default language the website.

= Where do I obtain the content translated for my page? =

The plugin doesn't translate content automatically, we recommend professional translations for websites instead automatic translations provided by online tools. Webilop team can help you to find professional translators for your website.

= How can I collaborate with this plugin? =

You can submit your pull requests to the code of the plugin through the repository of [User Language Switch in Github](https://github.com/Webilop/user-language-switch). Additionally you can contact us at contact[at]webilop.com

== Screenshots ==

1. This screenshot illustrates the User Language options available in the settings page.
2. Assign menu translations to existing menus.
3. Enable or disable languages available for your website(it only applies to languages for the front-end).
4. Enable automatic filtering of posts, pages and custom post types.
5. Select the language and translations of a post.

== Changelog ==
= 1.0 =
* First general availability plug-in version
= 1.1 =
* Anonymous functions deleted in order to support php versions earlier than 5.3
= 1.2 =
* Persian language added to localization. Special thanks to Khalil Delavaran(khalil.delavaran[at]gmail.com) for this contribution.
= 1.3 =
* Serbo-Croatian language added. Special thanks to Borisa Djuraskovic(borisad@webhostinghub.com) from [Web Hosting Hub](http://www.webhostinghub.com) for this contribution.
= 1.4 =
* Fix of bug in redirections between translated pages and prefixes to avoid repeated pages with the same content.
* Fix of bug creating the links of the language switch. Some links used to not be created properly.
* Fix of bug in the selection of translations for a post. All the available translations used to not be loaded.
* Addition of Estonian language(et) as abbreviation for et_EE.
= 1.4.1 =
* Fix of bugs in localization of the plugin.
* Fix of typos in comments in code of the plugin.
* Thanks to Jaillet, Christophe for feedback provided.
= 1.5 =
* Fix of major bugs.
* Addition of language flags in all pages of the website automatically.
* Automatic translation of menus based on pages included in menus and their translations.
* Addition of translation menus to create different menus for each language.
* Addition ot translation sidebars to use different widgets for each language.
* Automatic filtering of posts, pages and custom post types according to their languages.
* Automatic filtering of search results according to the language displayed.
* Addition of references to other languages available in HTML head section of post, pages and custom post types.
* Creation of [Github repository](https://github.com/Webilop/user-language-switch) to receive collaboration easily.
