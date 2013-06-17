<?php
function _db2($var){
	echo "<pre>"; print_r($var); echo "</pre>";
}
/*
Plugin Name: User Language Switch
Description: Allow to your registered users set the language displayed in the back-end and front-end of your site.
Version: 0.1
Author: Carlos Guzman
Author URI:
License: GPL2
*/

define( 'ULS_PLUGIN_URL', plugin_dir_url(__FILE__) );
define( 'ULS_PLUGIN_PATH', plugin_dir_path(__FILE__) );
define( 'ULS_PLUGIN_NAME', plugin_basename(__FILE__) );
define( 'ULS_FILE_PATH', __FILE__ );

require_once 'uls-options.php';
require_once 'uls-rewrite-rules.php';
include_once 'uls-functions.php';

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
}

/*add_action('get_header', 'uls_redirect_page_by_language');
function uls_redirect_page_by_language(){
	//if user isn't in admin side
	if( ! is_admin() ){
		//get the language
		$language = uls_get_user_language();
		if('' == $language)
			$language = uls_get_site_language();

		//get the translation page
		$translation_id = uls_get_post_translation_id(get_the_ID(), $language);
		if(false !== $translation_id){
			wp_redirect(get_permalink($translation_id));
			exit;
		}
	}
}*/

/**
 * This function load the language from the current URL.
 *
 * @param $only_lang boolean if it is true, then it returns the 2 letters of the language. It is the language code without location.
 *
 * @return mixed it returns a string containing a language code or false if there isn't any language detected.
 */
function uls_get_user_language_from_url($only_lang = false){
	//load functios to detect logged user
	if( ! function_exists('is_user_logged_in'))
		require_once realpath(__DIR__.'/../../..') . "/wp-includes/pluggable.php";

	//get language from URL
	global $wp_query;
	$language = !empty($wp_query->query_vars['lang']) ? $wp_query->query_vars['lang'] : null;
	if(null == $language){
		//get the language form query vars
		if(!empty($_SERVER['QUERY_STRING'])){
			parse_str($_SERVER['QUERY_STRING']);
			if(!empty($lang))
				$language = $lang;
		}
		if(null == $language){
			//get the langauge from the URL
			$url = str_replace(get_bloginfo('url'), '', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			if($url[0] == '/') $url = substr($url, 1);
			$parts = explode('/', $url);
			if(count($parts) > 0)
				$language = $parts[0];
		}
	}

	return uls_valid_language($language) ? $language : false;
}


/**
 * This function retrives the user language selected in the admin side.
 *
 * @param $only_lang boolean if it is true, then it returns the 2 letters of the language. It is the language code without location.
 * @param $type string (backend|frontend) specifiy which language it will return.
 *
 * @return mixed it returns a string containing a language code. If user don't have permissions to change languages or user hasn't configured a language, then the default language is returned. If user isn't logged in, then false is returned.
 */
function uls_get_user_saved_language($only_lang = false, $type = null){
	if( is_user_logged_in() ){
		//detect if the user is in backend or frontend
		if($type == null){
			$type = 'frontend';
			if( is_admin() )
				$type = 'backend';
		}

		//check if the user can change the language
		$options = uls_get_options();
		if($options["user_{$type}_configuration"])
			$language = get_user_meta(get_current_user_id(), "uls_{$type}_language", true);
		//set the default language if the user doesn't have a preference
		if(empty($language))
			$language = $options["default_{$type}_language"];

		//remove location
		if($only_lang){
			$pos = strpos($language, '_');
			if(false !== $pos)
				return substr($language, 0, $pos);
		}
		return $language;
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

	//change the language by user preferences
	if( empty($language) && is_user_logged_in() )
		$language = uls_get_user_saved_language();

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
function uls_get_site_language($side = 'frontend', $only_flag = false){
	$options = uls_get_options();
	return $options["default_{$side}_language"];
}

/**
 * This function handle the language saved for the users. It is attached to the WP hook "locale".
 */
function uls_language_loading($lang){
	//load functios to detect logged user
	/*if( ! function_exists('is_user_logged_in'))
		require_once realpath(__DIR__.'/../../..') . "/wp-includes/pluggable.php";

	//get language from URL
	global $wp_query;
	$language = !empty($wp_query->query_vars['lang']) ? $wp_query->query_vars['lang'] : null;
	if(null == $language){
		//get the language form query vars
		if(isset($_SERVER['argv']) && is_array($_SERVER['argv']))
			foreach($_SERVER['argv'] as $arg)
				if(false !== strpos($arg, 'lang='))
					$language = substr($arg, 5);
		if(null == $language){
			//get the langauge from the URL
			$url = str_replace(get_bloginfo('url'), '', 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
			if($url[0] == '/') $url = substr($url, 1);
			$parts = explode('/', $url);
			if(count($parts) > 0)
				$language = $parts[0];
		}
	}

	//if it is only the code language
	if(!empty($language) && false === strpos($language, '_')){
		//get default location for the language, if the language isn't valid, then it returns null
		$language = uls_get_location_by_language($language);
	}

	//change the language by user preferences
	if( empty($language) && is_user_logged_in() ){
		//detect if the user is in backend or frontend
		$type = 'frontend';
		if( is_admin() )
			$type = 'backend';

		//check if the user can change the language
		$options = get_option('uls_settings');
		if($options["user_{$type}_configuration"])
			$language = get_user_meta(get_current_user_id(), "uls_{$type}_language", true);
		//set the default language if the user doesn't have a preference
		if(empty($language))
			$language = $options["default_{$type}_language"];
	}
	//$language = 'es_ES';
	//echo $lang . '-' . $language.'<br/>';*/
	$language = uls_get_user_language();

	//if it is only the code language
	if(!empty($language) && false === strpos($language, '_')){
		//get default location for the language, if the language isn't valid, then it returns null
		$language = uls_get_location_by_language($language);
	}

	$res = empty($language) ? $lang : $language;
	//_db2($_SERVER);
	//echo "res: $res<br/>";
	return $res;
}
add_filter('locale', 'uls_language_loading');

/**
 * It returns the configured or default code language for a language abbreviatio. The code language is the pair of language and country (i.e: en_US, es_ES)-
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
 * Return the post id of translation post of a post.
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
	$post_language = get_post_meta($post_id, 'uls_language', true);

	if($post_language == $language)
		return $post_id;
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
	$post_language = get_post_meta($post_id, 'uls_language', true);

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
					if(!empty($path_parts) && in_array($path_parts[1], $available_languages)){
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
 * Get the available lanuages on the system.
 */
function uls_get_available_languages(){
	return array('English' => 'en_US', 'Spanish' => 'es_ES');
}

?>