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
 * This function returns the URL used in the browser.
 *
 * @return string URL in the browser.
 */
function uls_get_browser_url(){
  $url =(isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"]=="on") ? "https://" : "http://";
  $url .= $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
  return $url;
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
function uls_get_link($post_id = null, $language = null, $label = null, $class='uls-link' ){
  // instance the atribute
  $translation_url = "#";

  if ($post_id == null) {
    if (is_home()) {
      $url = get_home_url();
      $translation_url = uls_get_url_translated($url, $language);
    }
    else if (is_archive() || is_search() || is_author() || is_category() || is_tag() || is_date()) {
      $url = uls_get_browser_url();
      $translation_url = uls_get_url_translated($url, $language);
    }
  }
  else {
    $translation_id = uls_get_post_translation_id($post_id, $language);
    if(empty($translation_id))
      $translation_id = $post_id;

    //set conversion of permalinks to true
    global $uls_permalink_convertion;
    $uls_permalink_convertion = true;

    $translation_url = uls_get_url_translated(get_permalink($translation_id), $language);

    //reset conversion of permalinks
    $uls_permalink_convertion = false;

    $title = get_the_title($translation_id);
  }

  if(null == $label)
    return '<a class="' . $class . '" href="' . $translation_url . '" >' . $title . '</a>';
  else
    return '<a class="' . $class . '" href="' . $translation_url . '" >' . $label . '</a>';
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
 * This function creates a set of links with available languages.
 *
 * @param $url string base URL to convert.
 * @param $url_type string type of language flag to add in the URL (query_var, prefix, subdomain)
 * @param $type string type of links to generate (links, select) (TO-DO: select isn't available yet.)
 * @param $only_lang_name if it is true, then the links don't contain the country name.
 * @param $class string additional CSS class to add in the div of links generated.
 *
 * @return string returns the HTML code with links to translated versions.
 */
function uls_language_link_switch($url = null, $url_type = 'prefix', $type = 'links', $only_lang_name = true, $class = null){
  //if URL is null, then it uses the current URL
  if(null == $url)
    $url = uls_get_browser_url();

  //get the available languages
  $available_languages = uls_get_available_languages();

  $class = (null == $class) ? 'uls-language-link-switch' : 'uls-language-link-switch ' . $class;

  //get the current language
  $current_language = uls_get_user_language();
  if('' == $current_language)
    $current_language = uls_get_site_language();

  //set conversion of permalinks to true
  global $uls_permalink_convertion;
  $uls_permalink_convertion = true;
  ob_start();
  ?>
  <div class="<?php echo $class; ?>">
  <?php
    include 'uls-languages.php';
    foreach($available_languages as $code):
     if(isset($country_languages[$code])){
       if($only_lang_name)
         $label = substr($country_languages[$code], 0, strpos($country_languages[$code], ' '));
       else
         $label = $country_languages[$code];
     }
     else{
       $label = $code;
     }
     $displayed = false;
      if($code == $current_language):
        $displayed = true;
      ?>
         <span class="<?php echo 'selected-language'?>"><?php echo __($label, 'user-language-switch'); ?></span>
      <?php else:
      //$current_post_id = empty($post->ID) ? url_to_postid($url) : $post->ID;
      $current_post_id = url_to_postid($url);
      if(0 == $current_post_id && !is_home()){
         wp_reset_query();
         the_post();
         $current_post_id = get_the_ID();
      }
      //if the current page has a post related
      if(0 != $current_post_id):
         $translation_id = uls_get_post_translation_id($current_post_id, $code);
         if(false !== $translation_id):
          $displayed = true;
          ?>
            <a href="<?php echo uls_get_url_translated(get_permalink($translation_id), $code); ?>"><?php echo __($label, 'user-language-switch'); ?></a>
        <?php endif; //translation url?>
      <?php endif; //current_post_id?>
      <?php endif;

    //if the translation isn't displayed yet
    if(!$displayed):
      //get the translation based on the URL
      $translation_url = uls_get_url_map_translation($url);
      if(false !== $translation_url): ?>
        <a href="<?php echo uls_get_url_translated($translation_url, $code); ?>"><?php echo __($label, 'user-language-switch'); ?></a>
      <?php else: ?>
        <a href="<?php echo uls_get_url_translated($url, $code); ?>"><?php echo __($label, 'user-language-switch'); ?></a>
      <?php endif; ?>
   <?php endif; //displayed ?>

   <?php endforeach; ?>
   </div>
   <?php
   $res = ob_get_contents();
   ob_end_clean();

   //reset conversion of permalinks
   $uls_permalink_convertion = false;

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
    'url_type' => 'prefix',
    'only_language' => true,
    'class' => null
  ), $atts ) );

  //if URL is null, then it uses the current URL
  if(null == $url)
    $url = uls_get_browser_url();

  if("false" == $only_language)
    $only_language = false;

  return uls_language_link_switch($url, $url_type, $type, $only_language, $class);
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
   $available_languages = uls_get_available_languages();
   ob_start();

   //include some JS libraries
   wp_enqueue_script('jquery-form');
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

/**
 * This function get the URL of the translation of an URL. It retrieve the URL from the mapping saved in the options page.
 *
 * @param $url string URL of the page to get the translation.
 * @param $language string language of translation. If it is null or invalid, current language loaded in the page is used.
 *
 * @return string it returns the URL of the translation or false if the URL isn't contained in the mapping.
 */
function uls_get_url_map_translation($url, $language = null){
  //get language
  if(!uls_valid_language($language))
    $language = uls_get_user_language();
  //get the mappging
  $options = get_option('uls_settings');
  if(isset($options['translation_mapping'][$language][$url]))
    return $options['translation_mapping'][$language][$url];

  return false;
}

add_action('wp_footer', 'tap_user_language_switch');
// uls-tab-user-language-switch include the template to show flags
function tap_user_language_switch() {
  $options = get_option('uls_settings');

  if($options['activate_tab_language_switch']){

    $languages = uls_get_available_languages();
    $position = $options['tab_position_language_switch']; 

    if ( is_home() || is_archive() || is_search() || is_category() || is_tag() || is_author() || is_date() )
      $postId = null;
    else
      $postId = get_post()->ID;

    include_once('uls-tab-template.php');
  }
} 

// this function is for create the styles conditions
add_action( 'wp_enqueue_scripts', 'uls_tab_background_color_picker' );
function uls_tab_background_color_picker() {

  $options = get_option('uls_settings');
  $position = $options['tab_position_language_switch']; 

  $TabStyle = "";
  $TabBackground = "background-color: ".$options['tab_color_picker_language_switch'].";"; 
  $TabFixed = ($options['fixed_position_language_switch']) ? "position: fixed;" : "position: absolute;";
  $bodyRelative = ($options['fixed_position_language_switch']) ? "" : "position: relative;";
  switch($position) {
    case 'TL':
      $TabStyle = "#tab_background_color_picker{
          top: 0;
          left: 0;
          width: auto; ".
          $TabFixed.
          $TabBackground."
          padding: 5px 5px 5px 10px;
          border-radius: 0 0 15px 15px;
          z-index: 10000000000;
        }
      .tab_flag {
        display: inline;
      }";
    break;
    case 'TC':
      $TabStyle = "#tab_background_color_picker{
          top: 0;
          left: 50%;
          width: auto;".
          $TabFixed.
          $TabBackground."
          padding: 5px 5px 5px 10px;
          border-radius: 0 0 15px 15px;
          z-index: 10000000000;
        }
        .tab_flag {
          display: inline;
        }";
    break;
    case 'TR':
      $TabStyle = "#tab_background_color_picker{
          top: 0;
          right: 0;
          width: auto;".
          $TabFixed.
          $TabBackground."
          padding: 5px 5px 5px 10px;
          border-radius: 0 0 15px 15px;
          z-index: 10000000000;
        }
        .tab_flag {
          display: inline;
        }";
    break;
    case 'BL':
      $TabStyle = "#tab_background_color_picker{
        bottom: 0;
        left: 0;
        width: auto;".
        $TabFixed.
        $TabBackground."
        padding: 5px 5px 5px 10px;
        border-radius: 15px 15px 0 0;
        z-index: 10000000000;
      }
      .tab_flag {
        display: inline;
      }
      body {
        $bodyRelative
      }"; 
    break;
    case 'BC':
      $TabStyle = "#tab_background_color_picker{
        bottom: 0;
        left: 50%;
        width: auto;".
        $TabFixed.
        $TabBackground."
        padding: 5px 5px 5px 10px;
        border-radius: 15px 15px 0 0;
        z-index: 10000000000;
      }
      .tab_flag {
        display: inline;
      }
      body {
        $bodyRelative
      }";
    break;
    case 'BR':
      $TabStyle = "#tab_background_color_picker{
        bottom: 0;
        right: 0;
        width: auto;".
        $TabFixed.
        $TabBackground."
        padding: 5px 5px 5px 10px;
        border-radius: 15px 15px 0 0;
        z-index: 10000000000;
      }
      .tab_flag {
        display: inline;
      }
      body {
        $bodyRelative
      }";
    break;
    case 'LT':
      $TabStyle = "#tab_background_color_picker{
        top: 0;
        left: 0;
        height: auto;".
        $TabFixed.
        $TabBackground."
        padding: 5px 5px 5px 10px;
        border-radius: 0 15px 15px 0;
        z-index: 10000000000;
      }";
    break;
    case 'LM':
      $TabStyle = "#tab_background_color_picker{
        top: 50%;
        left: 0;
        height: auto;".
        $TabFixed.
        $TabBackground."
        padding: 5px 5px 5px 10px;
        border-radius: 0 15px 15px 0;
        z-index: 10000000000;
      }";
    break;
    case 'LB':
      $TabStyle = "#tab_background_color_picker{
        bottom: 0;
        left: 0;
        height: auto;".
        $TabFixed.
        $TabBackground."
        padding: 5px 5px 5px 10px;
        border-radius: 0 15px 15px 0;
        z-index: 10000000000;
      }";
    break;
    case 'RT':
      $TabStyle = "#tab_background_color_picker{
        top: 0;
        right: 0;
        height: auto;".
        $TabFixed.
        $TabBackground."
        padding: 5px 4px 5px 10px;
        border-radius: 15px 0 0 15px;
        z-index: 10000000000;
      }";
    break;
    case 'RM':
      $TabStyle = "#tab_background_color_picker{
        top: 50%;
        right: 0;
        height: auto;".
        $TabFixed.
        $TabBackground."
        padding: 5px 4px 5px 10px;
        border-radius: 15px 0 0 15px;
        z-index: 10000000000;
      }";
    break;
    case 'RB':
    $TabStyle = "#tab_background_color_picker{
      bottom: 0;
      right: 0;
      height: auto;".
      $TabFixed.
      $TabBackground."
      padding: 5px 4px 5px 10px;
      border-radius: 15px 0 0 15px;
      z-index: 10000000000;
    }";
    break;
  }
?>
  <style type="text/css"> 
    <?= $TabStyle; ?>
  </style> 
<?php
}

// this function is for automatic traduction menues
/*function uls_traduction_automatic_menu($object)
{      
  foreach ($object as $key ) {
    $post_id = get_post_meta($key->object_id, 'uls_translation_'.strtolower(uls_get_user_language()), true);
    if ( !empty($post_id) ) {
      $key->title = get_post($post_id)->post_title;
      $key->url = uls_get_url_translated($key->url);
      var_dump('yeah!');
    }
  }
  return $object;
}
//add_filter( 'wp_nav_menu_objects', 'uls_traduction_automatic_menu');*/

// this functin action is for register sidebar if the checkbox in backend is enable
function uls_register_sidebar_laguages() {
  global $wp_registered_sidebars;

  $languages = uls_get_available_languages(); // get the all languages available in the wp
  $options = get_option('uls_settings'); 

  // delete  language that is actually in the side, from available languages 
  $lang_code = uls_get_site_language(); 
  foreach ( $languages as $lang_name) 
    if ( ($lang_name = array_search($lang_code, $languages)) !== false )
        unset($languages[$lang_name]);
  
  // create the N_sidebar X available_languages, but fir ask if the enable checkbox is true 
  if ( !isset($options['enable_translation_sidebars_language_switch']) || $options['enable_translation_sidebars_language_switch'] ) {
    if ( function_exists('register_sidebar') ) {
      $temporal_sidebars = $wp_registered_sidebars;
      foreach ( $temporal_sidebars as $sidebar => $items) {
        foreach ( $languages as $lang_name => $lang_code ) { 
          register_sidebar(array(
            'name' =>  $items['name'] .' / '. $lang_name,
            'id' => strtolower("uls_".$items['id'].'_'.$lang_code),
            'description' => __($items['description']. ' / '.$lang_name, 'user-language-switch'),
            'before_widget' => $items['before_widget'],
            'after_widget' => $items['after_widget'],
            'before_title' => $items['before_title'],
            'after_title' => $items['after_title'],
          ));
        }
      } 
    }
  }  
} 
add_action( 'widgets_init', 'uls_register_sidebar_laguages', 999 );

function uls_organize_widgets_sidebars($sidebars_widgets) {
  $options = get_option('uls_settings'); 

  if (!is_admin()) {
    if ( !isset($options['enable_translation_sidebars_language_switch']) || $options['enable_translation_sidebars_language_switch'] ) {
      $lang_code = strtolower('_'.uls_get_user_language());
      $uls_code = 'uls_';
      foreach ($sidebars_widgets as $sidebar => $widgets) { 
        if ( substr($sidebar,0,3) != $uls_code ) {
          if ( !empty($sidebars_widgets[$uls_code.$sidebar.$lang_code]) ) {
            $uls_widgets =  $sidebars_widgets[$uls_code.$sidebar.$lang_code]; 
            $sidebars_widgets[$sidebar] = $uls_widgets; 
          }
        }
      }
    }
  }

  return $sidebars_widgets;
}
add_filter ( 'sidebars_widgets', 'uls_organize_widgets_sidebars', 10, 1);
?>
