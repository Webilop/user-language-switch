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
	// $languages = '(en|es|en_US|es_ES)';
	$languages = "(".implode("|", array_values(uls_get_available_languages())).")";

	$wp_rewrite->add_rewrite_tag( '%lang%', $languages, 'lang=' );

	//create prefixed rules
	$new_rules = array(
		$languages . '/?$' => 'index.php?lang=$matches[1]' //home page rule
	);
	foreach($wp_rewrite->rules as $left => $right){
		//ignore attachment rules
		//if(false === strpos($left, '/attachment/')){
			$new_rules[$languages . '/' . $left] = preg_replace_callback('/matches\[(\d{1,2})\]/', 'uls_replace_matched_rule', $right) . '&lang=$matches[1]';
		//}
	}

	//add new rules
	//_db2($wp_rewrite->rules);
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
	//_db2($wp_rewrite->rules);
	return $wp_rewrite->rules;
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
//add_action( 'init', 'uls_flush_rewrite_rules' );
add_action( 'generate_rewrite_rules', 'uls_create_custom_rewrite_rules' );
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
?>
