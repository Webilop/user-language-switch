<?php
function _db2($var){
	echo "<pre>"; print_r($var); echo "</pre>";
}
/*
Plugin Name: User Language Switcher
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

/**
 * This function handle the language saved for the users. It is attached to the WP hook "locale".
 */
function uls_language_loading($lang){
	//load functios to detect logged user
	if( ! function_exists('is_user_logged_in'))
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
	//echo $lang . '-' . $language.'<br/>';
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
	
	//get language names
	require 'uls-languages.php';
	return !empty($default_code_by_abbreviation[$language]) ? $default_code_by_abbreviation[$language] : null;
}

/**
 * This function creates an HTML slect input with the available languages for the site.
 * @param $id string id of the HTML element.
 * @param $name string name of the HTML element.
 * @param $default_value string value of the default selected option.
 * @param $class string CSS classes for the HTML element.
 * 
 * @return string HTML code of the language selector input.
 */
function uls_language_selector_input($id, $name, $default_value = '', $class = ''){
	//get available languages
	$available_languages = array('en_US','es_ES');
	
	//get language names
	require 'uls-languages.php';
	
	//create HTML input
	ob_start();
	?>
	<select id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo $class; ?>" >
		<?php foreach($available_languages as $lang): ?>
		<option value="<?php echo $lang; ?>" <?php selected($lang, $default_value); ?>><?php _e($country_languages[$lang],'user-language-switcher'); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
	$res = ob_get_contents();
	ob_end_clean();
	return $res;
}
?>
