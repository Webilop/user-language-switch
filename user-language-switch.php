<?php
/*
Plugin Name: User Language Switch
Description: Build a multilingual and SEO friendly website. Linking translations of content and allow visitors to browse your website in different languages.
Version: 1.5.1
Author: webilop
Author URI: http://www.webilop.com
License: GPL2
*/

/*  Copyright 2013  webilop  (email : admin@webilop.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php

define( 'ULS_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'ULS_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'ULS_PLUGIN_NAME', plugin_basename(__FILE__) );
define( 'ULS_FILE_PATH', __FILE__ );

require_once 'uls-options.php';
require_once 'uls-rewrite-rules.php';
include 'uls-functions.php';

/**
 * This function intis the plugin. It check the language in the URL, the language in the browser and the language in the user preferences to redirect the user to the correct page with translations.
 *
 * 1. This function first check the language configured in the user browser and redirects the user to the correct language version of the website. It is done only the first time that the user visits the website in a PHP session and if the user is visiting the home page.
 * 2. If the current page language is not the same of the user or site language, then add the language flag to the URL.
 * 3. If the URL contains the language and it is the same of the site langauge or to the user language saved, then remove the language from the URL
 */
add_action('init', 'uls_init_plugin');
function uls_init_plugin(){
  //load translation
  $plugin_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
  load_plugin_textdomain( 'user-language-switch', false, $plugin_dir );

  //init flag of permalink convertion to true. When this flag is false, then don't try to get translations when is generating permalinks
  global $uls_permalink_convertion;
  $uls_permalink_convertion = true;

  //init flat for uls link filter function. When this flag is true is because it is running a process to generate a link with translations, then it abort any try to get a translation over a translation, in this way it doesn't do an infinite loop.
  global $uls_link_filter_flag;
  $uls_link_filter_flag = true;

  //redirects the user based on the browser language. It detectes the browser language and redirect the user to the site in that language. It is done only the first time that the user visits the website in a PHP session and if the user is visiting the home page.
  uls_redirect_by_browser_language();

  //if the current page language is not the same of the user or site language, then add the language flag to the URL
  uls_redirect_by_page_language();

  //if the URL contains the language and it is the same of the site language or the user langauge saved, then remove the language from the URL.
  uls_redirect_by_languange_redundancy();

  //reset flags
  $uls_permalink_convertion = false;
  $uls_link_filter_flag = false;

  //init session to detect if you are in the home page by "first time"
  if(!session_id()) session_start();
}

/**
 * This function gets the language from the current URL.
 *
 * @param $only_lang boolean if it is true, then it returns the 2 letters of the language. It is the language code without location.
 *
 * @return mixed it returns a string containing a language code or false if there isn't any language detected.
 */
function uls_get_user_language_from_url($only_lang = false){
  //get language from URL
  $language = null;
  //get the language form query vars
  if(!empty($_SERVER['QUERY_STRING'])){
    parse_str($_SERVER['QUERY_STRING']);
    if(!empty($lang))
      $language = $lang;
  }
  if(is_null($language)){
    //activate flag to avoid translations and get the real URL of the blog
    global $uls_permalink_convertion;
    $uls_permalink_convertion = true;

    //get the langauge from the URL
    $url = str_replace(get_bloginfo('url'), '', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    if( isset($url[0]) && $url[0] == '/') $url = substr($url, 1);
    $parts = explode('/', $url);
    if(count($parts) > 0)
      $language = $parts[0];

    //reset the flag
    $uls_permalink_convertion = true;
  }

  return uls_valid_language($language) ? $language : false;
}


/**
 * This function retrieves the user language selected in the admin side.
 *
 * @param $only_lang boolean if it is true, then it returns the 2 letters of the language. It is the language code without location.
 * @param $type string (backend|frontend) specify which language it will return.
 *
 * @return mixed it returns a string containing a language code. If user don't have permissions to change languages or user hasn't configured a language, then the default language of the website is returned. If user isn't logged in, then the default language of the website is returned.
 */
function uls_get_user_saved_language($only_lang = false, $type = null){
  //get the options of the plugin
  $options = uls_get_options();
  $language = false;

  //detect if the user is in backend or frontend
  if($type == null){
    $type = 'frontend';
    if( is_admin() )
      $type = 'backend';
  }

  //if the user is logged in
  if( is_user_logged_in() ){
    //if the user can modify the language
    if($options["user_{$type}_configuration"])
      $language = get_user_meta(get_current_user_id(), "uls_{$type}_language", true);
  }

  //set the default language if the user doesn't have a preference
  if(empty($language))
    $language = $options["default_{$type}_language"];

  //remove the location
  if(false != $language && $only_lang){
    $pos = strpos($language, '_');
    if(false !== $pos)
      return substr($language, 0, $pos);
  }

  return $language;
}

/**
 * This function retrieves the user language from the browser. It reads the headers sent by the browser about language preferences.
 *
 * @return mixed it returns a string containing a language code or false if there isn't any language detected.
 */
function uls_get_user_language_from_browser(){
  if(isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])){
    //split the header languages
    $browserLanguages = explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);

    //parse each language
    $parsedLanguages = array();
    foreach($browserLanguages as $bLang){
      //check for q-value and create associative array. No q-value means 1 by rule
      if(preg_match("/(.*);q=([0-1]{0,1}\.\d{0,4})/i",$bLang,$matches)){
        $matches[1] = strtolower(str_replace('-', '_', $matches[1]));
        $parsedLanguages []= array(
          'code' => (false !== strpos($matches[1] , '_')) ? $matches[1] : false,
          'l' => $matches[1],
          'q' => (float)$matches[2],
        );
      }
      else{
        $bLang = strtolower(str_replace('-', '_', $bLang));
        $parsedLanguages []= array(
          'code' => (false !== strpos($bLang , '_')) ? $bLang : false,
          'l' => $bLang,
          'q' => 1.0,
        );
      }
    }

    //get the languages activated in the site
    $validLanguages = uls_get_available_languages();
    //validate the languages
    $max = 0.0;
    $maxLang = false;
    foreach($parsedLanguages as $k => &$v){
      if(false !== $v['code']){
        //search the language in the installed languages using the language and location
        foreach($validLanguages as $vLang){
          if(strtolower($vLang) == $v['code']){
            //replace the preferred language
            if($v['q'] > $max){
              $max = $v['q'];
              $maxLang = $vLang;
            }
          }
        }//check for the complete code
      }
    }

    //if language hasn't been detected
    if(false == $maxLang){
      foreach($parsedLanguages as $k => &$v){
        //search only for the language
        foreach($validLanguages as $vLang){
          if(substr($vLang, 0, 2) == substr($v['l'], 0, 2)){
            //replace the preferred language
            if($v['q'] > $max){
              $max = $v['q'];
              $maxLang = $vLang;
            }
          }
        }//search only for the language
      }
    }

    return $maxLang;
  }

  return false;
}

/**
 * This function gets the language from the URL, if there is no language in the URL, then it gets language from settings saved by the user in the back-end side. If there isn't a language in the URL or user hasn't set it, then default language of the website is used.
 *
 * @param $only_lang boolean if it is true, then it returns the 2 letters of the language. It is the language code without location.
 *
 * @return string language code. If there isn't a language in the URL or user hasn't set it, then default language is returned.
 */
function uls_get_user_language($only_lang = false){
  //get language from URL
  $language = uls_get_user_language_from_url($only_lang);

  //get the language from user preferences
  if( empty($language) ){
    $language = uls_get_user_saved_language();
  }

  //remove location
  if(!empty($language) && $only_lang){
    $pos = strpos($language, '_');
      if(false !== $pos)
        return substr($language, 0, $pos);
  }

  return $language;
}

/**
 * Get the default language of the website.
 *
 * @param $side string (frontend | backend) if it is frontend, then it returns the default language for the front-end side, otherwise it returns the language for the back-end side. If there is not languages configured, then it returns false.
 *
 * @return mixed it returns an string with language code or false if there is not languages configured.
 */
function uls_get_site_language($side = 'frontend'){
   $options = uls_get_options();
   return isset($options["default_{$side}_language"]) ? $options["default_{$side}_language"] : false;
}

/**
 * This function check if the redirection based on the browser language is enabled. If it is and the user is in the home page, then the user is redirected to the home page with the specified language.
 *
 * @return mixed it returns false if the redirection is not possible, due to some of the restriction mentioned above. Otherwise, it just redirects the user.
 */
function uls_redirect_by_browser_language(){
  $options = uls_get_options();
  if ( !isset($options['use_browser_language_to_redirect_visitors']) || !$options['use_browser_language_to_redirect_visitors'] )
    return false;

  $type = 'frontend';
  $url = uls_get_browser_url();
  $homeUrl = get_bloginfo('url') . '/';

  //if user is in the home page
  if($homeUrl == $url){
    $options = uls_get_options();
    //if the redirection is enabled
    if((!isset($options['user_browser_language_detection']) || $options['user_browser_language_detection']) && "no" != get_user_meta(get_current_user_id(), "uls_{$type}_browser_language", true)){
      $language = uls_get_user_language_from_browser();

      //if the browser language is different to the site language
      if("" != $language && $language != uls_get_site_language()){
        //redirect to the browser language
        $redirectUrl = uls_get_url_translated($homeUrl, $language);

        //check if it is the first redirection
        if(!session_id()) session_start();
        if(empty($_SESSION['uls_home_redirection']) && empty($_COOKIE['uls_home_redirection'])){
          //save temporal vars to avoid redirection in the home page again
          $time = date_format(new DateTime(), 'Y-m-d H:i');
          setcookie('uls_home_redirection', $time, time()+2*60*60); //set a cookie for 2 hour
          $_SESSION['uls_home_redirection'] = $time; //save temporal var in session
        }
        else
          return false;

        //redirect
        if($url != $redirectUrl){
          wp_redirect($redirectUrl);
          exit;
        }
      }//browser language different to site language
    }//redirection enabled
  }//is in home

  return false;
}

/**
 * if the URL contains the language and it is the same of the site language or the user langauge saved, then remove the language from the URL using a redirection to the page.
 */
function uls_redirect_by_languange_redundancy(){
  //if user is in an admin area, then don't redirect
  if(is_admin()) return;

  //get the language from URL
  $urlLanguage = uls_get_user_language_from_url();
  if(false == $urlLanguage) return;

  //get the language from the site
  $siteLanguage = uls_get_user_saved_language();
  if(empty($siteLanguage))
    $siteLanguage = uls_get_site_language();

  //if the language of the site is the same of the language in the URL
  if($siteLanguage == $urlLanguage){
    //get the id of the current page
    $url = uls_get_browser_url();

    //get the URL without the code language
    $redirectUrl = uls_get_url_translated($url, $siteLanguage);

    if($redirectUrl != $url){
      wp_redirect($redirectUrl);
      exit;
    }
  }
}

/**
 * If the language of the current page is not the same of the user language neither the site language, then the user is redirected to the URL containing the language flag. It is to avoid SEO problems(multiple URLs to the same content).
 */
function uls_redirect_by_page_language(){
  //if user is in an admin area, then don't redirect
  if(is_admin()) return;

  //get the id of the current page
  $url = uls_get_browser_url();
  $id = url_to_postid($url);

  //if the page has an id
  if(0 < $id){
    //get the language of the page
    $postLanguage = uls_get_post_language($id);

    //if the page has a language
    if("" != $postLanguage){
      //get the language from URL
      $urlLanguage = uls_get_user_language_from_url();
      //get the language from the site
      $siteLanguage = uls_get_user_saved_language();
      if(empty($siteLanguage))
        $siteLanguage = uls_get_site_language();

      //if the language(saved and default) of the site is different to the language of the page and there is no prefix in the site.
      if($siteLanguage != $postLanguage && empty($urlLanguage)){
        //redirect to the current page with the correct prefix
        $redirectUrl = uls_get_url_translated($url, $postLanguage);
        if($redirectUrl != $url){
          wp_redirect($redirectUrl);
          exit;
        }
      }
      //if the language of the site is the same of the language in the URL and different to the language of the post
      //or if the language of the site is the same of the language of the post but different to the language of the URL
      else if(($siteLanguage == $urlLanguage && $urlLanguage != $postLanguage)
        || ($siteLanguage == $postLanguage && $urlLanguage != $postLanguage)){
        //check the translation of the post using the language of the URL
        $translation_id = uls_get_post_translation_id($id, $urlLanguage);
        if(!empty($translation_id)){
          //redirect to the language of the URL
          $redirectUrl = get_permalink($translation_id);
          if($redirectUrl != $url){
            wp_redirect($redirectUrl);
            exit;
          }
        }
      }
    }//if the page has a language
  }//if the page has an id
}

/**
 * This function is attached to the WP hook "locale" and it sets the language to see the current page. The function get the language of the user, it uses the first language found in these options: URL, browser configuration, user settings, default language.
 */
function uls_language_loading($lang){
   global $uls_locale;
   //if this method is already called, then it remove the action to avoid recursion
   if($uls_locale)
    remove_filter('locale', 'uls_language_loading');
   else
     $uls_locale = true;

   $language = uls_get_user_language();

   //if it is only the code language
   if(!empty($language) && false === strpos($language, '_')){
      //get default location for the language, if the language isn't valid, then it returns null
      $language = uls_get_location_by_language($language);
   }

   $res = empty($language) ? $lang : $language;

   $uls_locale = false;
   return $res;
}
add_filter('locale', 'uls_language_loading');

/**
 * It returns the configured or default code language for a language abbreviation. The code language is the pair of language and country (i.e: en_US, es_ES)-
 *
 * @param $language code language.
 *
 * @return mixed it returns an string with the complete code or null if the language is not available.
 */
function uls_get_location_by_language($language){
  //get available languages activated in the website
  $available_languages = uls_get_available_languages();
  //for each code language, search for the language
  foreach($available_languages as $code)
    if(substr($language, 0, 2) == $language)
      return $code;

  return null;
}

/**
 * Validate if language is valid and active.
 *
 * @param $language string language to validate.
 *
 * @return boolean true if language is valid, otherwise it returns false.
 */
function uls_valid_language($language){
   //TO-DO: validate with registered languages in the site
   //get language names
   require 'uls-languages.php';
   return !empty($country_languages[$language]) || in_array($language, $language_codes);
}

/**
 * Return the id of the translation of a post.
 *
 * @param $post_id integer id of post to translate.
 * @param $language string language of translation. If it is null or invalid, current language loaded in the page is used.
 *
 * @return mixed it returns id of translation post as an integer or false if translation doesn't exist.
 */
function uls_get_post_translation_id($post_id, $language = null){
  //get language
  if(!uls_valid_language($language))
    $language = uls_get_user_language();

  //get the translation of the post
  $post_language = uls_get_post_language($post_id);

  //if the language of the post is the same language of the translation
  if($post_language == $language)
    return $post_id;

  //get the translation
  $translation = get_post_meta($post_id, 'uls_translation_' . $language, true);
  if("" == $translation)
    $translation = get_post_meta($post_id, 'uls_translation_' . strtolower($language), true);

  return empty($translation) ? false : $translation;
}

$uls_link_filters = array(
  'home_url',
  'site_url',
  'user_trailingslashit',
  'post_link',
  'page_link',

  /*'post_type_link',
  'year_link',
  'month_link',
  'day_link',
  'author_feed_link',
  'feed_link',
  'category_link',
  'tag_link',
  'taxonomy_link',
  'search_link',
  'search_feed_link',
  'post_type_archive_link',
  'post_type_archive_feed_link',
  'get_pagenum_link',
  */
);
foreach($uls_link_filters as $filter)
  add_filter($filter, 'uls_link_filter', 10, 2);
function uls_link_filter($post_url, $post = null){
   //check flag to avoid infinite recursion
   global $uls_link_filter_flag;
   if($uls_link_filter_flag)
      return $post_url;

   //if global change is enabled
   global $uls_permalink_convertion;
   if($uls_permalink_convertion)
      return $post_url;

   //if user is in backend
   if( is_admin() ) return $post_url;

   //init flag to avoid infinite recursion
   $uls_link_filter_flag = true;

   //get language from URL
   $url_language = uls_get_user_language_from_url();

   //get the general options
   $options = uls_get_options();

   //check if page donesn't require a post to do translation, it only uses URL
   if(null == $post || (is_object($post) && empty($post->ID))){
      $post_url = uls_get_url_translated($post_url, $url_language, $options["url_type"]);

      //clean flag to control infinite recursion
      $uls_link_filter_flag = false;

      return $post_url;
   }

   //check if the URL is an special URL of WordPress and doesn't require changes
   /*$query_string_start = strpos($post_url, '?');
   if(false !== $query_string_start){
      //check special folders of WordPress
      $exclude_urls = array('wp-content', 'wp-includes', 'wp-admin');
      $start_url = substr($post_url, 0, $query_string_start);
      foreach($exclude_urls as $special_folder)
         if(false !== strpos($start_url, $special_folder))
            return $post_url;
   }*/

   //check post ID
   $post_id = $post;
   if(is_object($post))
      $post_id = $post->ID;

   //get post language
   $post_language = uls_get_post_language($post_id);

   //if there is a language in the URL, then append the language in the link
   if(false !== $url_language){
      //get the translation of the post
      $translation_id = uls_get_post_translation_id($post_id, $url_language);
      if($translation_id == $post_id)
         $post_url = uls_get_url_translated($post_url, $url_language, $options["url_type"]);
      elseif(false !== $translation_id)
         $post_url = uls_get_url_translated(get_permalink($translation_id), $url_language);
      else
         $post_url = uls_get_url_translated($post_url, $url_language, $options["url_type"]);
   }
   //if there is no a language in the URL, get the correct URL for the post
   else{
     //check if language is the same to the user saved language
     $saved_language = uls_get_user_saved_language();
     if(false === $saved_language)
        $saved_language = uls_get_site_language();

     //if languages are not the same
     if($post_language != $saved_language){
        //get the translation of the post
        $translation_id = uls_get_post_translation_id($post_id, $url_language);
        if($translation_id != $post_id && false !== $translation_id)
           $post_url = uls_get_url_translated(get_permalink($translation_id), $url_language);
     }
   }

   //clean flag to control infinite recursion
   $uls_link_filter_flag = false;

   return $post_url;
}

/**
 * This function add the language flag in the url.
 */
function uls_get_url_translated($url, $language, $type = 'prefix', $remove_default_language = true){
   if(empty($url))
      return null;

   //activate flag to avoid translations and get the real URL of the blog
   global $uls_permalink_convertion;
   $uls_permalink_convertion = true;

   //if URL will omit default language
   if($remove_default_language){
      //if language is the same for the user
      if(is_user_logged_in() && $language == uls_get_user_saved_language())
         $language = '';
      //if language is the default language
      elseif(! is_user_logged_in() && $language == uls_get_site_language())
         $language = '';
   }

   //add language to the url
   switch($type){
      case 'query_var':
         $parts = parse_url($url);
         if(empty($parts['query'])){
            if(!empty($language))
               $parts['query'] = 'lang=' . $language;
            $url = $parts['scheme'] . '://' . $parts['host'] . (empty($parts['port']) ? '' : ':' . $parts['port']) . (empty($parts['path']) ? '' : $parts['path']) . (empty($parts['query']) ? '' : '?' . $parts['query']) . (empty($parts['fragment']) ? '' : '#' . $parts['fragment']);
            break;
         }
         $query_parts = explode('&', $parts['query']);
         $new_query_parts = array();
         foreach($query_parts as $var){
            $var_value = explode('=',$var);
            if($var_value[0] == 'lang'){
               if(!empty($language))
                  $new_query_parts []= 'lang=' . $language;
            }
            else
               $new_query_parts []= $var;
         }
         $parts['query'] = implode('&', $new_query_parts);
         $url = $parts['scheme'] . '://' . $parts['host'] . (empty($parts['port']) ? '' : ':' . $parts['port']) . (empty($parts['path']) ? '' : $parts['path']) . (empty($parts['query']) ? '' : '?' . $parts['query']) . (empty($parts['fragment']) ? '' : '#' . $parts['fragment']);
         break;

      case 'subdomain':
         break;

      default:
         $parts = parse_url($url);
         $blog_parts = parse_url(get_bloginfo('url'));
         if(empty($parts['path']) && !empty($language)){
            $parts['path'] = '/' . $language . '/';
         }
         else{
            //split path to detect if it contains a language flag already
            if(empty($blog_parts['path'])){
               $parts['path'] = isset($parts['path']) ? $parts['path'] : '/';
               $path_parts = explode('/', $parts['path']);
               $available_languages = uls_get_available_languages();

               if( in_array($path_parts[1], $available_languages) ){
                  unset($path_parts[1]);
                  $parts['path'] = implode('/', $path_parts);
               }
               if(!empty($language))
                  $parts['path'] = '/' . $language . $parts['path'];
            }
            else {
               $path_parts = explode('/', str_replace($blog_parts['path'], '', $parts['path']));
               $available_languages = uls_get_available_languages();
               if(!empty($path_parts) && count($path_parts) > 1 && in_array($path_parts[1], $available_languages)){
                  unset($path_parts[1]);
               }
               if(empty($language))
                  $parts['path'] = $blog_parts['path'] . implode('/', $path_parts);
               else
                  $parts['path'] = $blog_parts['path'] . '/' . $language . implode('/', $path_parts);
            }
         }
         //if the URL is a relative URL
         if(empty($parts['scheme']) && empty($parts['host'])){
           // TO-DO: How to handle relative URLs if the site is not hosted in the root folder of the domain
         }
         else{
           $url = $parts['scheme'] . '://' . $parts['host']
             . (empty($parts['port']) ? '' : ':' . $parts['port'])
             . (empty($parts['path']) ? '' : $parts['path'])
             . (empty($parts['query']) ? '' : '?' . $parts['query'])
             . (empty($parts['fragment']) ? '' : '#' . $parts['fragment']);
         }

         break;
   }
   //reset flag
   $uls_permalink_convertion = false;

   return $url;
}

/**
 * This function creates an HTML select input with the available languages for the site.
 * @param $id string id of the HTML element.
 * @param $name string name of the HTML element.
 * @param $default_value string value of the default selected option.
 * @param $class string CSS classes for the HTML element.
 * @param $available_language boolean "true" to return only the available lagunage "false" return all language in the wp.
 *
 * @return string HTML code of the language selector input.
 */
function uls_language_selector_input($id, $name, $default_value = '', $class = '', $available_languages = true){
   //get available languages
   $available_languages = uls_get_available_languages($available_languages);

   //get language names
   require 'uls-languages.php';

   //create HTML input
   ob_start();
   ?>
   <select id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo $class; ?>" >
      <?php foreach($available_languages as $lang):
      $language_name = $lang;
      if(!empty($country_languages[$lang]))
        $language_name = $country_languages[$lang];
      else{
        $aux_name = array_search($lang, $language_codes);
        if(false !== $aux_name)
          $language_name = $aux_name;
      } ?>
      <option value="<?php echo $lang; ?>" <?php selected($lang, $default_value); ?>><?php _e($language_name,'user-language-switch'); ?></option>
      <?php endforeach; ?>
   </select>
   <?php
   $res = ob_get_contents();
   ob_end_clean();
   return $res;
}


/**
 * Get the available languages on the system.
 *
 * @return array associative array with the available languages in the system. The keys are the language names and the values are the language codes.
 */
function uls_get_available_languages( $available_languages = true ){
  if ($available_languages) {
    $options = get_option('uls_settings'); // get information from DB
    // if the user does not have available the languages so the plugin avilable all languages
    $available_language = isset($options['available_language']) ? $options['available_language'] : uls_get_available_languages(false);
    return $available_language;
  }
   $theme_root = get_template_directory();
   $lang_array = get_available_languages( $theme_root.'/languages/' );
   $wp_lang = get_available_languages(WP_CONTENT_DIR.'/languages/');
   if(!empty($wp_lang)) $lang_array = array_merge((array)$lang_array, (array)$wp_lang);
   if (!in_array('en_US',$lang_array)) array_push($lang_array, 'en_US');
   $lang_array = array_unique($lang_array);
   require 'uls-languages.php';
   $final_array= array();
   foreach($lang_array as $lang):
     if(!empty($country_languages[$lang]))
       $final_array[$country_languages[$lang]] = $lang;
     else
       $final_array[$lang] = $lang;
   endforeach;
   return $final_array;

}

/**
 * Get the language of a post.
 *
 * @param $id integer id of the post.
 *
 * @return string the code of the language or an empty string if the post doesn't have a language.
 */
function uls_get_post_language($id){
  $postLanguage = get_post_meta($id, 'uls_language', true);
  if("" == $postLanguage) return "";

  //format the language code
  $p = strpos($postLanguage, "_");
  if($p !== false){
    $postLanguage = substr($postLanguage, 0, $p) . strtoupper(substr($postLanguage, $p));
  }

  //validate the language
  if (uls_valid_language($postLanguage)) {
    return $postLanguage;
  }

  return "";
}

add_action( 'init', 'uls_initialize_meta_boxes', 9999 );

/**
 * Initialize the metabox class.
 *
 * @return void
 */
function uls_initialize_meta_boxes() {
    if( ! class_exists( 'cmb_Meta_Box' ) )
        require_once(plugin_dir_path( __FILE__ ) . 'init.php');
}

/**
 * Add meta boxes to select the language an traductions of a post.
 *
 * @return array
 */
function uls_language_metaboxes( $meta_boxes ) {
   if(isset($_GET['post'])){
      $post_type = get_post_type($_GET['post']);
   }else{
      if(isset($_GET['post_type'])){
         $post_type = $_GET['post_type'];
      }else{
         $post_type = 'post';
      }
   }
   $prefix = 'uls_'; // Prefix for all fields
   $languages = uls_get_available_languages();
   $options = array(array('name'=>'Select one option', 'value'=>''));
   require 'uls-languages.php';
   $fields = array();
   foreach ( $languages as $lang ){
      $language_name = $lang;
      if(!empty($country_languages[$lang]))
        $language_name = $country_languages[$lang];
      else{
        $aux_name = array_search($lang, $language_codes);
        if(false !== $aux_name)
          $language_name = $aux_name;
      }

      $new = array('name' => $language_name, 'value' => $lang);
      array_push($options, $new);
      $t1 = get_posts(array(
         'post_type' => $post_type,
         'meta_query' => array(
            array (
                   'key' => 'uls_language',
                   'value'=>array($lang),
            )
         ),
         'posts_per_page' => -1,
      ));
      $t2 = get_posts(array(
         'post_type' => $post_type,
         'meta_query' => array(
            array (
                   'key' => 'uls_language',
                   'compare'=> 'NOT EXISTS',
            )
         ),
         'posts_per_page' => -1,
      ));
      $the_posts = array_merge( $t1, $t2 );

      $posts = array(array('name'=>'Select the translated post', 'value'=>''));
       foreach ($the_posts as $post):
           $post = array('name'=>$post->post_title, 'value'=>$post->ID);
           array_push($posts, $post);
       endforeach;
       wp_reset_query();
      $field = array(
         'name' => 'Select the version in '. $language_name,
         'id' => $prefix.'translation_'.strtolower($lang),
         'type' => 'select',
         'options' => $posts
      );
      array_push($fields, $field);
   }

    array_unshift($fields, array('name' => 'Select a language',
                                'id' => $prefix . 'language',
                                'type' => 'select',
                                'options' => $options));
//   $fields[] = array(
//             'name' => 'Select a language',
//             'id' => $prefix . 'language',
//             'type' => 'select',
//             'options' => $options,
//          );

   $args=array(
     'public'   => true,
     '_builtin' => false
   );
   $output = 'names'; // names or objects, note names is the default
   $operator = 'and'; // 'and' or 'or'
   $custom_post_types = get_post_types($args,$output,$operator);
   $add_to_posts = array('page','post');
   if(!empty($custom_post_types)):
   foreach ($custom_post_types as $custom):
   array_push($add_to_posts, $custom);
   endforeach;
   endif;
   $meta_boxes[] = array(
      'id' => 'language',
      'title' => 'Language',
      'pages' => $add_to_posts, // post type
      'context' => 'normal',
      'priority' => 'high',
      'show_names' => true, // Show field names on the left
      'fields' => $fields
   );
   return $meta_boxes;
}
add_filter( 'cmb_meta_boxes', 'uls_language_metaboxes' );

/**
 * Save language associations
 */
function uls_save_association( $post_id ) {
  //verify post is a revision
  $parent_id = wp_is_post_revision( $post_id );
  if($parent_id === false)
   $parent_id = $post_id;

  $languages = uls_get_available_languages();
  $selected_language = isset($_POST['uls_language']) ? $_POST['uls_language'] : null;

  // get array post metas because we need the uls_language and uls_translation
  $this_post_metas = get_post_meta( $parent_id );
  $this_uls_translation = !empty($this_post_metas) ?  isset($this_post_metas['uls_language']) ? 'uls_translation_'.strtolower($this_post_metas['uls_language'][0]) : '' : '';
  // if the language of this page change so change the all pages that have this like a traduction
  if ($selected_language != $this_uls_translation) {
    // get post that have this traduction
    $args =  array('post_type' => get_post_type($parent_id),
                   'meta_key' => $this_uls_translation,
                   'meta_value' => $parent_id,
                   'meta_compare' => '=');
    $query = new WP_Query($args);

    // if the query return the post that have assocciate the translation this page,
    // delete the old post_meta uls_translation_#_#
    if ( !empty($query->posts) ) {
      // we need only the IDs of the post query
      foreach ($query->posts as $key) {
        // delete the old post_meta uls_translation_#_#
        delete_post_meta ($key->ID, $this_uls_translation);
        // if selected_language is not empty so add the new traduction
        if (!empty($selected_language)) {
          // get the new post meta if this exits does update the uls_translation
          $page_post_meta = get_post_meta ($key->ID, 'uls_translation_'.strtolower($selected_language), true);
          // ask if the new post_meta uls_translation_#_# exits
          if ( empty($page_post_meta) )
            update_post_meta ( $key->ID, 'uls_translation_'.strtolower($selected_language), $parent_id );
        }
      }
    }
  }
  if (!empty($selected_language)) {
    // if the language change so change the traduction
    foreach ($languages as $lang) {
      $related_post = isset($_POST['uls_translation_'.strtolower($lang)]) ? $_POST['uls_translation_'.strtolower($lang)] : null;
      if( !empty( $related_post ) ) {
        // add traduction to the page that was selected like a translation
        $related_post_meta_translation = get_post_meta( $related_post, 'uls_translation_'.strtolower($selected_language), true );
        if ( empty ( $related_post_meta_translation ) )
          update_post_meta ( $related_post, 'uls_translation_'.strtolower($selected_language), $parent_id );
        // add language to the page that was selected like a tranlation. If the page doesn't has associated a languages
        $related_post_get_language = get_post_meta( $related_post, 'uls_language', true );
        if ( empty ( $related_post_get_language) )
          update_post_meta ( $related_post, 'uls_language', $lang );

      }
    }
  }

}
add_action( 'save_post', 'uls_save_association' );

/**
 * Remove associations
 */
function uls_text_ajax_process_request() {
   // first check if data is being sent and that it is the data we want
      $relation_id = $_POST['pid'];
      $lang = $_POST['lang'];
      $post_id = $_POST['post'];
      $meta = $_POST['meta'];
   if ( isset( $_POST["pid"] ) ) {
      // now set our response var equal to that of the POST varif(isset($relation_id)){
      delete_post_meta( $relation_id, 'uls_translation_'.$lang );
      delete_post_meta( $post_id, $meta );
      // send the response back to the front end
      echo $relation_id.'-'.$lang.'-'.$post_id.'-'.$meta;
      die();
   }
}
add_action('wp_ajax_test_response', 'uls_text_ajax_process_request');

/**
* Enqueue plugin style-file
*/
function uls_add_styles() {
  // Respects SSL, Style.css is relative to the current file
  wp_register_style( 'html-style', plugins_url('css/styles.css', __FILE__) );
  wp_enqueue_style( 'html-style' );
  wp_enqueue_style( 'webilop-flags_16x11-style', plugins_url('css/flags/flags_16x11.css', __FILE__) );
}
add_action( 'admin_enqueue_scripts', 'uls_add_styles' );

/**
 * Register javascript file
 */
function uls_add_scripts() {
    wp_register_script( 'add-bx-js',   WP_CONTENT_URL . '/plugins/user-language-switch/js/js_script.js', array('jquery') );
    wp_enqueue_script( 'add-bx-js' );
    wp_enqueue_script( 'add_alert_select_js',   WP_CONTENT_URL . '/plugins/user-language-switch/js/event_select.js', array('jquery') );
    // make the ajaxurl var available to the above script
    wp_localize_script( 'add-bx-js', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    wp_enqueue_style( 'webilop-flags_32x32-style', plugins_url('css/flags/flags_32x32.css', __FILE__) );
}
add_action( 'wp_enqueue_scripts', 'uls_add_scripts' );


add_filter('manage_posts_columns', 'webilop_add_posts_columns');
function webilop_add_posts_columns($columns) {
    unset($columns['date']);
    $columns['language'] = 'Language';
    $columns['translation'] = 'Translations';
    $columns['date'] = 'Date';
    return $columns;
}
add_action('manage_posts_custom_column',  'webilop_show_posts_columns');
function webilop_show_posts_columns($name) {
    global $post;
    $string = "";
    switch ($name) {
      case 'language':
        $views = strtolower(get_post_meta($post->ID, 'uls_language', true));
        echo '<img src="'.plugins_url("css/blank.gif", __FILE__).'" style="margin-right:5px;" class="flag_16x11 flag-'.substr($views, -2).'" alt="'.$views.'" title="'.$views.'" />';
      break;
        case 'translation':
            $views = get_post_meta($post->ID);
      foreach($views as $key => $value)
         if(strpos($key,'uls_translation_') !== false)
          $string[substr($key, -5)] =  $value[0];

            $views = get_post_meta($post->ID, 'uls_language', true);
      if($string != ""){
         unset($string[strtolower($views)]);
         foreach ($string as $key => $value)
      echo '<a href="'.get_edit_post_link($value).'">'.
         '<img src="'.plugins_url("css/blank.gif", __FILE__).'" style="margin-right:5px;" class="flag_16x11 flag-'.substr($key, -2).'" alt="'.$views.'" title="'.$value.'" />'.
           '</a>';
      }else
         echo $string;
      break;
    }
}
add_filter('manage_pages_columns', 'webilop_add_pages_columns');
function webilop_add_pages_columns($columns) {
    unset($columns['date']);
    $columns['language'] = 'Language';
    $columns['translation'] = 'Translations';
    $columns['date'] = 'Date';
    return $columns;
}

add_action('manage_pages_custom_column',  'webilop_show_pages_columns');
function webilop_show_pages_columns($name) {
    global $post;
    $string = "";
    switch ($name) {
        case 'language':
            $views = strtolower(get_post_meta($post->ID, 'uls_language', true));
      echo '<img src="'.plugins_url("css/blank.gif", __FILE__).'" style="margin-right:5px;" class="flag_16x11 flag-'.substr($views, -2).'" alt="'.$views.'" />';
      break;
        case 'translation':
            $views = get_post_meta($post->ID);
      foreach($views as $key => $value)
         if(strpos($key,'uls_translation_') === 0) {
      $string[substr($key, -5)] =  $value[0];
         }

            $views = get_post_meta($post->ID, 'uls_language', true);
      if($string != ""){
         unset($string[strtolower($views)]);
         foreach ($string as $key => $value)
      echo '<a href="'.get_edit_post_link($value).'">'.
         '<img src="'.plugins_url("css/blank.gif", __FILE__).'" style="margin-right:5px;" class="flag_16x11 flag-'.substr($key, -2).'" alt="'.$views.'" />'.
           '</a>';
      }else
         echo $string;
      break;
    }
}

/**
 * Add queries to filter posts by languages. If a post doesn't have language.
 *
 * @param $query object WordPress query object where language query will be added.
 */
function uls_add_language_meta_query(&$query){
  //set permalink convertion to true, to get real URLs
  global $uls_permalink_convertion;
  $uls_permalink_convertion = true;

  //get language displayed
  $language_displayed = uls_get_user_language();

  //get the default language of the website
  $default_website_language = uls_get_site_language();

  //if the language displayed is the same to the default language, then it includes posts without language
  $language_query = null;
  if($language_displayed == $default_website_language){
    //build query for languages
    $language_query = array(
      'relation' => 'OR',
      array(
        'key' => 'uls_language',
        'value' => 'bug #23268',
        'compare' => 'NOT EXISTS'
      ),
      array(
        'key' => 'uls_language',
        'value' => $language_displayed,
        'compare' => '='
      )
    );
  }
  //filter posts by language displayed
  else{
    $language_query = array(
      array(
        'key' => 'uls_language',
        'value' => $language_displayed,
        'compare' => '='
      ),
    );
  }

  //get current meta query
  $meta_query = $query->get('meta_query');

  //add language query to the meta query
  if(empty($meta_query))
    $meta_query = $language_query;
  else
    $meta_query = array(
      'relation' => 'AND',
      $language_query,
      $meta_query
    );

  //set the new meta query
  $query->set('meta_query', $meta_query);

  //reset flag
  $uls_permalink_convertion = false;
}

/**
 * Filter posts in archives by language.
 *
 * @param $query object WordPress query object used to create the archive of posts.
 */
add_action('pre_get_posts', 'uls_filter_archive_by_language', 1);
function uls_filter_archive_by_language($query){
  //check if it in the admin dashboard
  if(is_admin())
    return;

  // get values configuration uls_settings to applic filter translation to the post_types
  // if the information in languages_filter_disable are true apply filter
  $settings = get_option('uls_settings');

  // Check post type in query, if post type is empty , Wordpress uses 'post' by default
  $postType = 'post';
  if(property_exists($query, 'query') && array_key_exists('post_type', $query->query)) {
    $postType = $query->query['post_type'];
  }

  if(array_key_exists('languages_filter_enable', $settings) &&
     !isset($settings['languages_filter_enable'][$postType])) {
    return;
  }

  //this flag indicates if we should filter posts by language
  $modify_query = !$query->is_page() && !$query->is_single() && !$query->is_preview();

  //if it is displaying the home page and the home page is the list of posts
  //$modify_query = 'posts' == get_option( 'show_on_front' ) && is_front_page();

  //if it is an archive
  //$modify_query = $modify_query || $query->is_archive() || $query->is_post_type_archive();

  //if this is not a query for a menu(menus are handled by the plugin too)
  $modify_query = $modify_query && 'nav_menu_item' != $query->get('post_type');

  //filter posts by language loaded in the page
  if($modify_query){
    uls_add_language_meta_query($query);
  }
}

add_action('wp_head','head_reference_translation');
function head_reference_translation() {

  //get the id of the current page
  $url =(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https://" : "http://";
  $url .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
  $post_id = url_to_postid($url);

  // get all available languages
  $languages = uls_get_available_languages();
  $curren_code = uls_get_user_language(); // get current language
  // delete the current language site
  $code_value = array_search($curren_code, $languages);
  unset($languages[$code_value]);

  // build the url to be tranlation
  $url = '';
  // get url from where it's using
  if ( is_home() )
    $url = get_home_url(); // get home url
  else if ( is_archive() || is_search() || is_author() || is_category() || is_tag() || is_date() )
    $url = uls_get_browser_url(); // get browser url

  // if exits the url so, translate this
  if (!empty($url) ) {
    // use all available languages and get the url translation
    foreach ($languages as $language => $code) {
      $translation_url = uls_get_url_translated($url, $code);
      echo '<link rel="alternate" hreflang="'.substr($code, 0, 2).'" href="'.$translation_url.'" />';
    }
  }

  // build url to the home
  if ( !empty($post_id) && empty($url) ) {

    // change the filter
    global $uls_permalink_convertion;
    $uls_permalink_convertion = false;

    // use all available languages and get the url translation
    foreach ($languages as $language => $code) {
      // get the post_id translation if the current page has translation
      $translation_id = uls_get_post_translation_id($post_id, $code);
      if ( !empty($translation_id) ) {
        $translation_url = uls_get_url_translated(get_permalink($translation_id), $code);
        echo '<link rel="alternate" hreflang="'.substr($code, 0, 2).'" href="'.$translation_url.'" />';
      }
    }
    // leave the global car like it was before
    $uls_permalink_convertion = true;
  }
}


// desactivate the tab flags
function update_db_after_update() {

  $options = get_option('uls_settings');
  !isset( $options['activate_tab_language_switch'] ) ?  $options['activate_tab_language_switch'] = false : '' ;
  update_option('uls_settings',$options);
}
register_activation_hook( __FILE__, 'update_db_after_update' );


/*
  Moving configuration options to user profile page
*/
add_action( 'show_user_profile', 'extended_user_profil_fields' );
add_action( 'edit_user_profile', 'extended_user_profil_fields' );
 
function extended_user_profil_fields( $user ) { 
  ULS_Options::create_user_profile_language_options();
}

add_action( 'personal_options_update', 'save_language_options' );
add_action( 'edit_user_profile_update', 'save_language_options' );

//Función que guarda los cambios 
function save_language_options( $user_id ) {
  ULS_Options::save_user_profile_language_preferences();
}

?>
