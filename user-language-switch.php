<?php
/*
Plugin Name: User Language Switch
Description: Allows backend users to set the language displayed in the back-end and front-end of your site. It also allows to translate pages and posts.
Version: 1.4.1
Author: webilop
Author URI: www.webilop.com
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
/*function _db($var){
   echo "<pre>"; print_r($var); echo "</pre>";
}*/

define( 'ULS_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'ULS_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'ULS_PLUGIN_NAME', plugin_basename(__FILE__) );
define( 'ULS_FILE_PATH', __FILE__ );

require_once 'uls-options.php';
require_once 'uls-rewrite-rules.php';
include 'uls-functions.php';

/**
 * Init plugin
 */
add_action('init', 'uls_init_plugin');
function uls_init_plugin(){
  //load translation
  $plugin_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
  load_plugin_textdomain( 'user-language-switch', false, $plugin_dir );

  //init flag of permalink convertion to true
  global $uls_permalink_convertion;
  $uls_permalink_convertion = true;

  //redirects the user based on the browser language
  uls_redirect_by_browser_language();

  //if the current page language is not the same of the user or site language, then redirect the user to the correct language
  uls_redirect_by_page_language();

  //if the URL contains the language and it is the same of the site language or the user langauge saved, then remove the language from the URL.
  uls_redirect_by_languange_redundancy();

  //init session to detect if you are in the home page by "first time"
  if(!session_id()) session_start();
}

/**
 * This function load the language from the current URL.
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
    //get the langauge from the URL
    $url = str_replace(get_bloginfo('url'), '', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    if($url[0] == '/') $url = substr($url, 1);
    $parts = explode('/', $url);
    if(count($parts) > 0)
      $language = $parts[0];
  }

  return uls_valid_language($language) ? $language : false;
}


/**
 * This function retrieves the user language selected in the admin side.
 *
 * @param $only_lang boolean if it is true, then it returns the 2 letters of the language. It is the language code without location.
 * @param $type string (backend|frontend) specify which language it will return.
 *
 * @return mixed it returns a string containing a language code. If user don't have permissions to change languages or user hasn't configured a language, then the default language is returned. If user isn't logged in, then false is returned.
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
   if( is_user_logged_in()){
    //if the user can modify the language
      if($options["user_{$type}_configuration"])
         $language = get_user_meta(get_current_user_id(), "uls_{$type}_language", true);

      //set the default language if the user doesn't have a preference
      if(empty($language))
         $language = $options["default_{$type}_language"];
   }

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
    /*echo "<pre>B:";
    print_r($browserLanguages);
    echo "</pre><pre>P:";
    print_r($parsedLanguages);*/

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
 * This function load the language from the URL, session or from settings saved for the user. If there isn't a language in the URL or user hasn't set it, then default language is returned.
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
 * Get the default language of the site.
 *
 * @param $side string (frontend | backend)
 * @param $only_lang boolean if it is true, then it returns the 2 letters of the language. It is the language code without location.
 *
 * @return string language code.
 */
function uls_get_site_language($side = 'frontend'){
   $options = uls_get_options();
   return $options["default_{$side}_language"];
}

/**
 * This function check if the redirection based on the browser language is enabled. If it is and the user is in the home page, then is redirected to the home page with the specified language.
 *
 * @return mixed it returns false if the redirection is not possible, due to some of the restriction mentioned above. Otherwise, it just redirects the user.
 */
function uls_redirect_by_browser_language(){
  $type = 'frontend';
  $url =(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https://" : "http://";
  $url .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
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
    $url =(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https://" : "http://";
    $url .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

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

  //get the language from URL
  $urlLanguage = uls_get_user_language_from_url();
  //get the language from the site
  $siteLanguage = uls_get_user_saved_language();
  if(empty($siteLanguage))
    $siteLanguage = uls_get_site_language();

  //get the id of the current page
  $url =(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https://" : "http://";
  $url .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
  $id = url_to_postid($url);

  //if the page has an id
  if(0 < $id){
    //get the language of the page
    $postLanguage = uls_get_post_language($id);

    //if the page has a language
    if("" != $postLanguage){
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
 * This function handle the language saved for the users. It is attached to the WP hook "locale".
 */
function uls_language_loading($lang){
   global $uls_locale;
   //if this method is already called, then it remove action to avoid recursion
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
 */
function uls_get_location_by_language($language){

   //TO-DO: Configuration of default locations for languages in the admin side.
   //TO-DO: Get available languages in rewrite rules

   //get language names
   require 'uls-languages.php';
   return !empty($default_code_by_abbreviation[$language]) ? $default_code_by_abbreviation[$language] : null;
}

/**
 * Validate if language is valid.
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

add_filter('post_type_link', 'uls_link_filter', 10, 2);
add_filter('post_link', 'uls_link_filter', 10, 2);
add_filter('page_link', 'uls_link_filter', 10, 2);
//add_filter('the_permalink', 'uls_link_filter', 10, 2);
function uls_link_filter($post_url, $post = null){
   //if global change is enabled
   global $uls_permalink_convertion;
   if( ! $uls_permalink_convertion )
      return $post_url;

   //TO-DO: what happen if user is in backend? see next line
   //if user is in backend
   if( is_admin() ) return $post_url;

   //echo "enter: " . $post_url . "<br/>";
   if(null == $post)
      return $post_url;
   $post_id = $post;
   if(is_object($post))
      $post_id = $post->ID;
   //echo "post_id: " . $post_id . "<br/>";

   //get post language
   $post_language = uls_get_post_language($post_id);

   //get language from URL
   $language = uls_get_user_language_from_url();

  //get the general options
  $options = uls_get_options();

   //echo "lang: " . $language . "<br/>";
   //if there is a language in the URL, then append the language in the link
   if(false !== $language){
      //get the translation of the post
      $translation_id = uls_get_post_translation_id($post_id, $language);
      //echo "Trans2: $translation_id <br/>";
      if($translation_id == $post_id)
         return uls_get_url_translated($post_url, $language, $options["url_type"]);
      elseif(false !== $translation_id)
         return get_permalink($translation_id);
      else
         return uls_get_url_translated($post_url, $language, $options["url_type"]);
   }

   //if language is the same to the user saved language
   $saved_language = uls_get_user_saved_language();
   //echo "saved: $saved_language<br/>";
   if(false === $saved_language)
      $saved_language = uls_get_site_language();
   if($post_language == $saved_language)
      return $post_url;
   else{
      //get the translation of the post
      $translation_id = uls_get_post_translation_id($post_id, $language);
      //echo "Trans: $translation_id <br/>";
      if($translation_id == $post_id)
         return $post_url;
      elseif(false !== $translation_id)
         return get_permalink($translation_id);
   }

   //add language to the url
   return uls_get_url_translated($post_url, $saved_language, $options["url_type"]);
}

/**
 * This function add the language flag in the url.
 */
function uls_get_url_translated($url, $language, $type = 'prefix', $remove_default_language = true){

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
            return $url;
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
               $path_parts = explode('/', $parts['path']);
               $available_languages = uls_get_available_languages();
               if(!empty($path_parts) && in_array($path_parts[1], $available_languages)){
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
         $url = $parts['scheme'] . '://' . $parts['host'] . (empty($parts['port']) ? '' : ':' . $parts['port']) . (empty($parts['path']) ? '' : $parts['path']) . (empty($parts['query']) ? '' : '?' . $parts['query']) . (empty($parts['fragment']) ? '' : '#' . $parts['fragment']);
         break;
   }
   //echo "final: $url<br/>";
   return $url;
}

/**
 * This function creates an HTML select input with the available languages for the site.
 * @param $id string id of the HTML element.
 * @param $name string name of the HTML element.
 * @param $default_value string value of the default selected option.
 * @param $class string CSS classes for the HTML element.
 *
 * @return string HTML code of the language selector input.
 */
function uls_language_selector_input($id, $name, $default_value = '', $class = ''){
   //get available languages
   $available_languages = uls_get_available_languages();

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
function uls_get_available_languages(){
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
    if ( ! class_exists( 'cmb_Meta_Box' ) )
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
      $fields[] = array(
            'name' => 'Select a language',
            'id' => $prefix . 'language',
            'type' => 'select',
            'options' => $options,
         );
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
  if(!empty($selected_language))
    foreach ($languages as $lang){
      $related_post = isset($_POST['uls_translation_'.strtolower($lang)]) ? $_POST['uls_translation_'.strtolower($lang)] : null;
      if( !empty( $related_post ) )
        update_post_meta ( $related_post, 'uls_translation_'.strtolower($selected_language), $parent_id );
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
}
add_action( 'admin_enqueue_scripts', 'uls_add_styles' );

/**
 * Register javascript file
 */
function uls_add_scripts() {
    wp_register_script( 'add-bx-js',   WP_CONTENT_URL . '/plugins/user-language-switch/js/js_script.js', array('jquery') );
    wp_enqueue_script( 'add-bx-js' );
    // make the ajaxurl var available to the above script
    wp_localize_script( 'add-bx-js', 'the_ajax_script', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}

add_action( 'wp_print_scripts', 'uls_add_scripts' );

?>
