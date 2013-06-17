<?php
 /**
 * This file containg general functions to use in themes or other plugins.
 */

/**
 * Get the general options saved for the plugin.
 *
 * @return array associative array with the options for the plugin.
 */
function uls_get_options(){
  //get the general options
  $options = get_option('uls_settings');

  //default values
  $defaults = array(
    'default_backend_language' => null,
    'default_frontend_language' => null,
    'user_backend_configuration' => true,
    'user_frontend_configuration' => true,
    'backend_language_field_name' => 'uls_backend_language',
    'frontend_language_field_name' => 'uls_frontend_language',
    'url_type' => 'prefix',
  );

  //merge with default values
  return array_merge($defaults, $options);
}

/**
 * Return the permalink of the translation link of a post.
 *
 * @param $post_id integer id of post.
 * @param $language string language of translation. If it is null or invalid, current language loaded in the page is used.
 *
 * @return string the permalink of the translation link of a post.
 */
function uls_get_permalink($post_id, $language = null){
	$translation_id = uls_get_post_translation_id($post_id, $language);
	return empty($translation_id) ? get_permalink($post_id) : get_permalink($translation_id);
}

/**
 * Return the HTML link of the translation of a post.
 *
 * @param $post_id integer id of post.
 * @param $language string language of translation. If it is null or invalid, current language loaded in the page is used.
 * @param $label string inner text of the link.
 * @param $class string text to include as class parameter in the link
 *
 * @return string the HTML link of the translation link of a post.
 */
function uls_get_link($post_id, $language = null, $label = null, $class='uls-link' ){
	$translation_id = uls_get_post_translation_id($post_id, $language);
	if(empty($translation_id))
		$translation_id = $post_id;
	if(null == $label)
		return '<a class="' . $class . '" href="' . get_permalink($post_id) . '" >' . get_the_title($translation_id) . '</a>';
	else
		return '<a class="' . $class . '" href="' . get_permalink($post_id) . '" >' . $label . '</a>';
}
/**
 * Add shortcode to get link.
 */
add_shortcode('uls-link', 'uls_link_shortcode');
function uls_link_shortcode($atts){
	extract( shortcode_atts( array(
		'id' => null,
		'lang' => uls_get_user_language(),
		'post_type' => 'page',
		'title' => null,
		'label' => null
	), $atts ) );

	//get post
	if(null == $id && null != $title){
		$post = get_page_by_title($title, 'OBJECT', $post_type);
		$id = $post->ID;
	}

	if(null != $id)
		return uls_get_link($id, $lang, $label);
	return null;
}

/**
 * This function creates a set of links with available languages
 *
 * @param $url string base URL to convert.
 * @param $url_type string type of language flag to add in the URL (query_var, prefix, subdomain)
 * @param $type string type of links to generate (links, select) (TO-SO: select isn't available yet.)
 * @param $class string additional CSS class to add in the div of links generated.
 *
 * @return string returns the HTML code with links to translated versions.
 */
function uls_language_link_switch($url, $url_type = 'prefix', $type = 'links', $class = null){
	$available_languages = uls_get_available_languages();

	$class = (null == $class) ? 'uls-language-link-switch' : 'uls-language-link-switch ' . $class;

	//get the current language
	$current_language = uls_get_user_language();
	if('' == $current_language)
		$current_language = uls_get_site_language();

	//set conversion of permalinks to false
	global $uls_permalink_convertion;
	$uls_permalink_convertion = false;

	//add styles
	wp_enqueue_style('uls-user-language-form', plugins_url('/css/styles.css', __FILE__));
	ob_start();
	?>
	<div class="<?php echo $class; ?>">
	<?php foreach($available_languages as $label => $code): ?>
		<?php if($code == $current_language): ?>
			<span class="<?php echo 'selected-language'?>"><?php echo __($label, 'user-language-switch'); ?></span>
		<?php else:
			$translation_id = uls_get_post_translation_id(get_the_ID(), $code);
			if(false !== $translation_id): ?>
				<a href="<?php echo uls_get_url_translated(get_permalink($translation_id), $code); ?>"><?php echo __($label, 'user-language-switch'); ?></a>
			<?php else: ?>
				<a href="<?php echo uls_get_url_translated($url, $code); ?>"><?php echo __($label, 'user-language-switch'); ?></a>
			<?php endif; ?>
		<?php endif; ?>
	<?php endforeach; ?>
	</div>
	<?
	$res = ob_get_contents();
	ob_end_clean();

	//set conversion of permalinks to true again
	$uls_permalink_convertion = true;

	return $res;
}
/**
 * Add showrtcode to add language versions
 */
add_shortcode('uls-language-selector', 'uls_language_selector_shortcode');
function uls_language_selector_shortcode($atts){
	extract( shortcode_atts( array(
		'url' => null,
		'type' => 'links',
		'url_type' => 'query_var',
		'class' => null
	), $atts ) );

	//if URL is null, then it uses current URL
	if(null == $url)
		$url = get_permalink();

	return uls_language_link_switch($url, $url_type, $type, $class);
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
	$available_languages = uls_get_available_languages();

	//get language names
	require 'uls-languages.php';

	//create HTML input
	ob_start();
	?>
	<select id="<?php echo $id; ?>" name="<?php echo $name; ?>" class="<?php echo $class; ?>" >
		<?php foreach($available_languages as $lang): ?>
		<option value="<?php echo $lang; ?>" <?php selected($lang, $default_value); ?>><?php _e($country_languages[$lang],'user-language-switch'); ?></option>
		<?php endforeach; ?>
	</select>
	<?php
	$res = ob_get_contents();
	ob_end_clean();
	return $res;
}


/**
 * This function creates a form to update language selection of an user to display in the front end side. If user isn't logged in or can't change the language, then it returns null.
 *
 * @param $default_language string language code used as default value of the input selector. If it is null, then the language saved by the user is selected.
 * @param $label string label to use for the language field.
 * @param $submit_label string label to use in the button to submit the form.
 * @param $usccess_message string Message to display if language is saved successfully.
 * @param $error_message string Message to display if language isn't saved.
 *
 * @return mixed HTML code of the form as a string. If user isn't logged in or user can't choose a language(settings of the plugin) then null is returned.
 */
function uls_create_user_language_switch_form($default_language = null, $label = null, $submit_label = null, $success_message = null, $error_message = null){
	//check if user is logged in
	if( ! is_user_logged_in() )
		return null;

	//check if the user can change the language
	$options = get_option('uls_settings');
	$type = 'frontend';
	if( ! $options["user_{$type}_configuration"])
		return null;

	//get default values
	$label = empty($label) ? __('Language', 'user-language-switch') : $label;
	$submit_label = empty($submit_label) ? __('Save', 'user-language-switch') : $submit_label;
	$success_message = empty($success_message) ? __('Language saved', 'user-language-switch') : $success_message;
	$error_message = empty($error_message) ? __('Error saving language', 'user-language-switch') : $error_message;

	//get user's language
	$language = get_user_meta(get_current_user_id(), "uls_{$type}_language", true);
	//set the default language if the user doesn't have a preference
	if(empty($language))
		$language = $options["default_{$type}_language"];

	//available languages
	$available_languages = array('English' => 'en_US', 'Spanish' => 'es_ES');
	ob_start();

	//include some JS libraries
	wp_enqueue_script('jquery-form');
	wp_enqueue_style('uls-user-language-form', plugins_url('/css/styles.css', __FILE__));
	?>
	<div class="uls-user-language-form-div">
		<form id="uls_user_language_form" action="<?php echo admin_url('admin-ajax.php'); ?>" method="POST">
			<input type="hidden" name="action" value="uls_user_language_switch" />
			<?php if(function_exists("wp_nonce_field")): ?>
				<?php wp_nonce_field('uls_user_language_switch','uls_wpnonce'); ?>
			<?php endif; ?>
			<label for="uls_language"><?php echo $label; ?></label>
			<select id="uls_language" name="<?php echo $options['frontend_language_field_name']; ?>">
			<?php foreach($available_languages as $langName => $langCode): ?>
				<?php if($langCode == $language): ?>
					<option value="<?php echo $langCode; ?>" selected="selected"><?php echo $langName; ?></option>
				<?php else: ?>
					<option value="<?php echo $langCode; ?>"><?php echo $langName; ?></option>
				<?php endif; ?>
			<?php endforeach; ?>
			</select>
			<div class="uls_submit_div">
				<input type="submit" class="btn" value="<?php echo $submit_label; ?>" />
			</div>
		</form>
		<div id="uls_user_language_message" class="uls_user_language_message"></div>
		<script>
		jQuery(document).ready(function(){
			jQuery("form#uls_user_language_form").ajaxForm({
				beforeSubmit: function(arr, $form, options) {
					jQuery("div#uls_user_language_message").html("");
				},
				success: function (responseText, statusText, xhr, $form){
					var $response = jQuery.parseJSON(responseText);
					if($response.success)
						jQuery("div#uls_user_language_message").html("<p class='success'><?php echo $success_message; ?></p>");
					else
						jQuery("div#uls_user_language_message").html("<p class='error'><?php echo $error_message; ?></p>");
				},
				error: function(){
					jQuery("div#uls_user_language_message").html("<p class='error'><?php echo $error_message; ?></p>");
				}
			});
		});
		</script>
	</div>
	<?php
	$res = ob_get_contents();
	ob_end_clean();
	return $res;
}

/**
 * This function save the selection of a language by an user. It gets parameter values from POST variables.
 */
add_action('wp_ajax_uls_user_language_switch', 'uls_save_user_language');
function uls_save_user_language(){
	//check parameters
	if(empty($_POST) || ( function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['uls_wpnonce'],'uls_user_language_switch') )){
		echo json_encode(array('success' => false));
		exit;
	}

	//save settings for the user
	$options = get_option('uls_settings');

	//if user can save settings and there is a value
	if($options["user_frontend_configuration"] && !empty($_POST[$options['frontend_language_field_name']]) && uls_valid_language($_POST[$options['frontend_language_field_name']]))
		update_user_meta(get_current_user_id(), $options['frontend_language_field_name'], $_POST[$options['frontend_language_field_name']]);

	echo json_encode(array('success' => true));
	exit;
}
?>