<?php
/**
 * This file contains the URL management for pretty URLs. It uses some rewrite rules to do the job.
 */


/**
 * Creates the custom rewrite rules.
 * @return array $rules.
 */
function uls_create_custom_rewrite_rules() {
  global $wp_rewrite;

  //get available languages
  $languages = "(".implode("|", array_values(uls_get_available_languages())).")";

  $wp_rewrite->add_rewrite_tag( '%lang%', $languages, 'lang=' );

  //create prefixed rules
  $new_rules = array(
    $languages . '/?$' => 'index.php?lang=$matches[1]' //home page rule
  );
  foreach($wp_rewrite->rules as $left => $right){
      $new_rules[$languages . '/' . $left] = 
        preg_replace_callback('/matches\[(\d{1,2})\]/', 'uls_replace_matched_rule', $right)
        . '&lang=$matches[1]';
  }
  
  //add new rules
  $wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
  
  //add rules for special non WordPress rewrites
  $new_non_wp_rules = array();
  if(!empty($wp_rewrite->non_wp_rules))
    foreach($wp_rewrite->non_wp_rules as $left => $right){
      $new_non_wp_rules[$languages . '/' . $left] = $right;
    }
  $wp_rewrite->non_wp_rules = $new_non_wp_rules + $wp_rewrite->non_wp_rules;
  
  //add rules for WordPress PHP files
  $new_non_wp_rules = array(
    $languages . '/(.*\.php)' => '$2/$3'
  );
  $wp_rewrite->non_wp_rules = $new_non_wp_rules + $wp_rewrite->non_wp_rules;
  
  //add rules for special folders in WordPress
  $special_folders = array('wp-content', 'wp-includes', 'wp-admin');
  $new_non_wp_rules = array(
    $languages . '/(' . implode('|', $special_folders) . ')/(.*)' => '$2/$3'
  );
  $wp_rewrite->non_wp_rules = $new_non_wp_rules + $wp_rewrite->non_wp_rules;
}
function uls_replace_matched_rule($matches){
  return 'matches[' . ($matches[1] + 1) . ']';
}

/**
 * Add the custom token as an allowed query variable.
 * @param array $public_query_vars.
 * @return array $public_query_vars.
 */
function uls_add_custom_page_variables( $public_query_vars ) {
  $public_query_vars[] = 'lang';
  return $public_query_vars;
}

/**
 * Flush the rewrite rules, which forces the regeneration with new rules.
 */
function uls_flush_rewrite_rules() {
  global $wp_rewrite;
  $wp_rewrite->flush_rules();
}

/**
 * Link functions to hooks.
 */
register_activation_hook(ULS_FILE_PATH, 'uls_flush_rewrite_rules');
add_action( 'generate_rewrite_rules', 'uls_create_custom_rewrite_rules', PHP_INT_MAX ); //highest priority to be excuted at last position
add_filter( 'query_vars', 'uls_add_custom_page_variables' );

/**
 * Add rewrite rules for BuddyPress
 */
add_filter('bp_core_get_directory_pages', 'uls_buddypress_rewrite_rules');
function uls_buddypress_rewrite_rules($directory_pages){
  //get language from url
  $language = uls_get_user_language_from_url();
  if(null == $language)
    return $directory_pages;

  foreach($directory_pages as &$page)
    $page->slug = $language . '/' . $page->slug;

  return $directory_pages;
}

/**
 * Redirect user after successful login. It removes languages in the URL if it is an admin URL.
 *
 * @param string $redirect_to URL to redirect to.
 * @param string $request URL the user is coming from.
 * @param object $user Logged user's data.
 * @return string
 */
function uls_login_redirect( $redirect_to, $request, $user ) {
  //check if the URL is an admin URL
  if(false !== strpos($redirect_to, '/wp-admin')){
    //get languages available
    $languages = uls_get_available_languages();
    //add slash to languages to remove it in the URL
    array_walk($languages, function(&$item, $key){
      $item = "/$item";
    });
    //remove languages in the URL
    $redirect_to = str_replace($languages, '', $redirect_to);
  }
  
  return $redirect_to;
}
//add_filter( 'login_redirect', 'uls_login_redirect', 10, 3 );

?>
