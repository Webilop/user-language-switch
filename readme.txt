=== User Language Switch ===

Contributors: webilop
Tags: multilingual, multilanguage, multiple language, bilingual, translation, translate, internationalization, i18n, globalization
Donate link: http://www.webilop.com/products/user-language-switch-wordpress-multilingual-plugin/#donations
Author URI: http://www.webilop.com/
Plugin URI: http://www.webilop.com/products/user-language-switch-wordpress-multilingual-plugin/
Requires at least: 4.0
Tested up to: 4.4
Stable tag: 1.6.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

> #### Read before updating to 1.5
> Version 1.5 brings some major changes. Please create a backup of your database before updating!

> #### Collaboration
> [The plugin is available in Github](https://github.com/Webilop/user-language-switch). We receive patches to fix bugs, improvements and translation files.

User Language Switch is a WordPress multilingual plugin that you can use to build multilingual websites. It displays flags to visitors that your users can use to browse your website in different languages. If you prefer, you can deactivate flags and user shortcodes and functions to create your own language switch.

Admin users can set default language for your website and registered users can set language they want to see your website by default. User Language Switch allows to set language for back-end and front-end independently.

When content is linked, a language prefix is added in URLs of content in secondary languages. The plugin always redirect to URLs with language prefix for content in secondary language, in this way there are no different URLs pointing to the same content and hurting your SEO. This plugin is compatible with SEO plugins like Yoast SEO.

It requires [you install different languages in your website](https://codex.wordpress.org/Installing_WordPress_in_Your_Language) and provide translation of your content, **it doesn't translate content automatically**. User Language Switch requires nice permalinks and URL rewriting enabled.

User Language Switch was created by [Webilop team](http://webilop.com/) and it is **free**. If you need help, you can use [the plugin support forum](http://wordpress.org/support/plugin/user-language-switch) or don't hesitate to [contact us](http://webilop.com/contact-us/). We also offer services about WordPress development and customization.

= Features =

* Linking translations of pages in multiple languages
* Linking translations of blog posts in multiple languages
* Linking translations of custom post types in multiple languages
* Automatic detection of visitor’s browser language
* Language flags that allow visitors to change website language easily
* Registered users are able to select the default language for the website
* Automatic translation of menus and creation of menus for each language
* Creation of sidebars for each language
* Automatic filter of blog posts with different language to the website language
* Option to select a language for back-end and another one for front-end
* Search of pages and blog posts taking into account website language

= Coming Features =

We have dreams for User Language Switch and we would like you be part of them, please tell us which features you like most for the plugin:

* **Allow me edit multiple translations in same place**: At the moment, you need to edit pages independently for each translation, what if you can edit the content of all these pages in the same place? - [I like it!](http://www.webilop.com/user-language-switch-comming-features/?vote=translations-in-one-place)
* **Put me in contact with human translators**: We want to create a page available for admin users where they can contact human translators and send pages and content to be translated(translators will not translate for free). - [I like it!](http://www.webilop.com/user-language-switch-comming-features/?vote=contact-translators)
* **Reports of content translated**: Allow admin and editor users to see reports about how much content has been translated and how much is missing. - [I like it!](http://www.webilop.com/user-language-switch-comming-features/?vote=translation-reports)

= Localization =

* English (default)
* Spanish
* Persian - thanks to [Khalil Delavaran](mailto:khalil.delavaran@gmail.com)
* Serbo-Croatian - thanks to [Borisa Djuraskovic](borisad@webhostinghub.com) from [Web Hosting Hub](http://www.webhostinghub.com)
* French - thanks to [Jaillet, Christophe](c.jaillet@meci.fr)

If you want to translate this plugin to your language, please use file user-language-switch.pot and to create MO and PO files. You can use an editor like [Poedit](http://www.poedit.net/) to do the job easily.

= Documentation =

Check User Language Switch in our website to get more details about installation and configuration:

* [Documentation in English](http://www.webilop.com/products/user-language-switch-wordpress-multilingual-plugin/)
* [Documentation in Spanish](http://www.webilop.com/es_ES/productos/user-language-switch-plugin-de-wordpress-multiidioma/).

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

1. This screenshot illustrates User Language Switch options available in the settings page.
2. Assign menu translations to existing menus.
3. Enable or disable languages available for your website(it only applies to languages for front-end).
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
= 1.5.1 =
* Fix of bug enabling and disabling post types for automatic filtering based on languages.
= 1.6 =
* Allow install a new language from the settings page
* Compatibility with Buddypress and bbPress
* Move user options to profile page
* Fix of major bugs.
= 1.6.1 =
* Fix relation between language codes and country codes for solve problem with render correct flag
= 1.6.2 =
* Add Esperanto flag
* Fix home page translation bug
* Fix correct show flags in posts/pages index page

== Upgrade Notice ==

= 1.5 =
This upgrade contains several bug fixes. Backup your website first!
