<?php
/**
 * This class control plugin settings.
 */
class ULS_Options{
   static private $default_options = array(
      'user_backend_configuration' => true,
      'user_frontend_configuration' => true,
      'default_backend_language' => 'en_US',
      'default_frontend_language' => 'en_US',
      'backend_language_field_name' => 'uls_backend_language',
      'frontend_language_field_name' => 'uls_frontend_language',
   );

   /**
    * Save default settings for the plugin.
    */
   static function init_plugin(){
      //update to default settings, if there isn't any settings stored
      $settings = get_option('uls_settings');
      $default_settings = self::$default_options;
      //if settings isn't stored yet
      if(false === $settings){
         //save default settings
         update_option('uls_settings', self::$default_options);
      }
   }

   /**
    * Register setting fields.
    */
   static function init_settings(){
      //register settings
      register_setting('uls_settings', 'uls_settings', 'ULS_Options::validate_settings');

      //get options
      $options = get_option('uls_settings');

      //create section for registration
      add_settings_section('uls_general_settings_section', __('General Settings','user-language-switch'), 'ULS_Options::create_general_settings_section', 'uls-settings-page');
      $options['input_name'] = 'default_backend_language';
      add_settings_field($options['input_name'], __('Default language for admin side','user-language-switch'), 'ULS_Options::create_language_selector_input', 'uls-settings-page','uls_general_settings_section',$options);

      $options['input_name'] = 'backend_language_field_name';
      add_settings_field($options['input_name'], __('User meta field to store the admin side language','user-language-switch'), 'ULS_Options::create_text_input', 'uls-settings-page','uls_general_settings_section',$options);

      $options['input_name'] = 'default_frontend_language';
      add_settings_field($options['input_name'], __('Default language for website','user-language-switch'), 'ULS_Options::create_language_selector_input', 'uls-settings-page','uls_general_settings_section',$options);

      $options['input_name'] = 'frontend_language_field_name';
      add_settings_field($options['input_name'], __('User meta field to store the website language','user-language-switch'), 'ULS_Options::create_text_input', 'uls-settings-page','uls_general_settings_section',$options);

      $options['input_name'] = 'user_backend_configuration';
      add_settings_field($options['input_name'], __('Allow users change their admin side language','user-language-switch'), 'ULS_Options::create_checkbox_input', 'uls-settings-page','uls_general_settings_section',$options);

      $options['input_name'] = 'user_frontend_configuration';
      add_settings_field($options['input_name'], __('Allow users change their website language','user-language-switch'), 'ULS_Options::create_checkbox_input', 'uls-settings-page','uls_general_settings_section',$options);

      //create section for collaboration
      add_settings_section('uls_collaboration_section', __('Collaborate','user-language-switch'), 'ULS_Options::create_collaboration_section', 'uls-settings-page');
      //create about section
      add_settings_section('uls_about_section', __('About','user-language-switch'), 'ULS_Options::create_about_section', 'uls-settings-page');
   }

   /**
    * Validate setting input fields.
    */
   static function validate_settings($input){
      //create default options
      $options = self::$default_options;

      foreach($options as $k => $v)
         if(isset($_POST[$k]) && !empty($_POST[$k]) && '' != trim($_POST[$k]))
            $options[$k] = trim($_POST[$k]);

      //get values of checkboxes
      $options['user_backend_configuration'] = isset($_POST['user_backend_configuration']);
      $options['user_frontend_configuration'] = isset($_POST['user_frontend_configuration']);

      return $options;
   }

   /**
    * Add entries in menu sidebar in back end.
    */
   static function register_menu(){
      add_options_page( __('User Language Switch','user-language-switch'), __('User Language Switch','user-language-switch'), 'manage_options', 'uls-settings-page', 'ULS_Options::create_settings_page' );

      //if users can configurate their languages
      $options = get_option('uls_settings');
      if($options['user_backend_configuration'] || $options['user_frontend_configuration'])
         add_menu_page( __('User Language Preferences','user-language-switch'), __('User Language','user-language-switch'), 'read', 'uls-user-language-page', 'ULS_Options::create_user_language_page' );
   }

   /**
    * Create the HTML of an input select field to choose a language.
    * @param $options array plugin options saved.
    */
   static function create_language_selector_input($options){
      $default_value = (isset($options[$options['input_name']])) ? $options[$options['input_name']] : self::$default_options['input_name'];
      echo uls_language_selector_input($options['input_name'],$options['input_name'],$default_value);
   }

   /**
    * Create the HTML of an input checkbox field.
    * @param $options array plugin options saved.
    */
   static function create_checkbox_input($options){
      $default_value = (isset($options[$options['input_name']])) ? $options[$options['input_name']] : self::$default_options['input_name'];
      ?>
      <input type="checkbox" name="<?php echo $options['input_name']; ?>" <?php checked($default_value); ?> />
      <?php
   }

   /**
    * Create the HTML of an input text field.
    * @param $options array plugin options saved.
    */
   static function create_text_input($options){
      $default_value = (isset($options[$options['input_name']])) ? $options[$options['input_name']] : self::$default_options['input_name'];
      ?>
      <input type="text" name="<?php echo $options['input_name']; ?>" value="<?php echo $default_value; ?>" />
      <?php
   }

   /**
    * Create register form displayed on back end.
    */
   static function create_general_settings_section(){
      ?>
      <p><?php _e('You can install more languages in your site following the instructions in ','user-language-switch'); ?><a href="http://codex.wordpress.org/WordPress_in_Your_Language" target="_blank"><?php _e('WordPress in Your Language','user-language-switch'); ?></a>.</p>
      <?php
   }

   /**
    * Create the section to collaborate with the development.
    */
   static function create_collaboration_section(){
      ?>
      <div class="section-inside"><p><?php _e('You can collaborate with the development of this plugin, please send us any suggestion or bug notification to support[at]webilop.com', 'user-language-switch'); ?></p></div>
      <?php
   }

   /**
    * Create the section to collaborate with the development.
    */
   static function create_about_section(){
    ?>
    <div class="section-inside">
    <p><strong>User Language Switch </strong><?php _e('was developed by ', 'user-language-switch');?><a title="Webilop. web and mobile development" href="http://www.webilop.com">Webilop</a></p>
    <p><?php _e('Webilop is a company focused on web and mobile solutions. We develop custom mobile applications and templates and plugins for CMSs such as Wordpress and Joomla!.', 'user-language-switch');?></p>
    <div><h4><?php _e('Follow us', 'user-language-switch')?></h4><a title="Facebook" href="https://www.facebook.com/webilop" target="_blank"><img src="<?php echo WP_PLUGIN_URL;?>/user-language-switch/images/facebook.png"></a>
    <a title="LinkedIn" href="http://www.linkedin.com/company/webilop" target="_blank"><img src="<?php echo WP_PLUGIN_URL;?>/user-language-switch/images/linkedin.png"></a>
    <a title="Twitter" href="https://twitter.com/webilop" target="_blank"><img src="<?php echo WP_PLUGIN_URL;?>/user-language-switch/images/twitter.png"></a>
    <a title="Google Plus" href="https://plus.google.com/104606011635671696803" target="_blank" rel="publisher"><img src="<?php echo WP_PLUGIN_URL;?>/user-language-switch/images/gplus.png"></a></div>
    </div>
    <?php
   }

   /**
    * Create settings page in back end.
    */
   static function create_settings_page(){
      if ( !current_user_can( 'manage_options' ) )  {
         wp_die( __( 'You do not have sufficient permissions to access this page.', 'user-language-switch' ) );
      }
   ?>
   <div class="wrap">
      <h2><?php _e('User Language Switch','user-language-switch'); ?></h2>
      <form method="post" action="options.php">
         <?php settings_fields( 'uls_settings' ); ?>
         <?php do_settings_sections( 'uls-settings-page' ); ?>
         <?php submit_button(__('Save', 'user-language-switch')); ?>
      </form>
   </div>
   <?php
   }

   /**
    * Create the HTML page to manage user language preferences.
    */
   static function create_user_language_page(){
      if ( !current_user_can( 'read' ) )  {
         wp_die( __( 'You do not have sufficient permissions to access this page.', 'user-language-switch' ) );
      }
      $options = get_option('uls_settings');
      $default_backend_language = get_user_meta(get_current_user_id(), $options['backend_language_field_name'], true);

      if(empty($default_backend_language))
         $default_backend_language = $options['default_backend_language'];
      $default_frontend_language = get_user_meta(get_current_user_id(), $options['frontend_language_field_name'], true);
      if(empty($default_frontend_language))
         $default_frontend_language = $options['default_frontend_language'];
   ?>
   <div class="wrap">
      <h2><?php _e('User Language Preferences','user-language-switch'); ?></h2>
      <?php if(isset($_GET['message'])): ?>
         <?php if('saved' == $_GET['message']): ?>
            <div class="uls-notice updated">
               <p><strong><?php _e('Preferences saved.', 'user-language-switch'); ?></strong></p>
            </div>
         <?php elseif('error' == $_GET['message']): ?>
            <div class="uls-error error">
               <p><strong><?php _e('Error saving preferences.', 'user-language-switch'); ?></strong></p>
            </div>
         <?php endif; ?>
      <?php endif; ?>
      <form id="uls_configuration_form" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>">
         <?php if(function_exists("wp_nonce_field")): ?>
            <?php wp_nonce_field('uls_user_language_preferences','uls_wpnonce'); ?>
         <?php endif; ?>
         <input type="hidden" name="action" value="uls_user_language_preferences" />
         <table class="form-table">
            <tbody>
               <?php if($options['user_backend_configuration']): ?>
               <tr valign="top">
                  <th scope="row"><?php _e('Displayed language in the admin side','user-language-switch'); ?></th>
                  <td>
                     <?php echo uls_language_selector_input($options['backend_language_field_name'],$options['backend_language_field_name'],$default_backend_language); ?>
                  </td>
               </tr>
               <?php endif; ?>
               <?php if($options['user_frontend_configuration']): ?>
               <tr valign="top">
                  <th scope="row"><?php _e('Displayed language in the front-end side','user-language-switch'); ?></th>
                  <td>
                     <?php echo uls_language_selector_input($options['frontend_language_field_name'],$options['frontend_language_field_name'],$default_frontend_language); ?>
                  </td>
               </tr>
               <?php endif; ?>
            </tbody>
         </table>
         <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save','user-language-switch'); ?>" />
         </p>
      </form>
          <div class="about-webilop">
    <h3 class="hndle"><?php _e('About','user-language-switch');?></h3>
    <div class="inside">
    <p><strong>User Language Switch </strong><?php _e('was developed by ', 'user-language-switch');?><a title="Webilop. web and mobile development" href="http://www.webilop.com">Webilop</a></p>
    <p><?php _e('Webilop is a company focused on web and mobile solutions. We develop custom mobile applications and templates and plugins for CMSs such as Wordpress and Joomla!.', 'user-language-switch');?></p>
   <div><h4><?php _e('Follow us', 'user-language-switch')?></h4><a title="Facebook" href="https://www.facebook.com/webilop" target="_blank"><img src="<?php echo WP_PLUGIN_URL;?>/user-language-switch/images/facebook.png"></a>
<a title="LinkedIn" href="http://www.linkedin.com/company/webilop" target="_blank"><img src="<?php echo WP_PLUGIN_URL;?>/user-language-switch/images/linkedin.png"></a>
<a title="Twitter" href="https://twitter.com/webilop" target="_blank"><img src="<?php echo WP_PLUGIN_URL;?>/user-language-switch/images/twitter.png"></a>
<a title="Google Plus" href="https://plus.google.com/104606011635671696803" target="_blank" rel="publisher"><img src="<?php echo WP_PLUGIN_URL;?>/user-language-switch/images/gplus.png"></a></div>
   </div>
   <?php
   }

   /**
    * Process and save the user language preferences.
    */
   static function save_user_language_preferences(){
      //check parameters
      if(empty($_POST) || ( function_exists('wp_verify_nonce') && !wp_verify_nonce($_POST['uls_wpnonce'],'uls_user_language_preferences') )){
         wp_redirect($_SERVER['HTTP_REFERER'] . "&message=error");
         exit;
      }

      //save settings for the user
      $options = get_option('uls_settings');
      if(!empty($_POST[$options['backend_language_field_name']]))
         update_user_meta(get_current_user_id(), $options['backend_language_field_name'], $_POST[$options['backend_language_field_name']]);
      if(!empty($_POST[$options['frontend_language_field_name']]))
         update_user_meta(get_current_user_id(), $options['frontend_language_field_name'], $_POST[$options['frontend_language_field_name']]);

      wp_redirect($_SERVER['HTTP_REFERER'] . "&message=saved");
      exit;
   }

   /**
    * Create the settings link in admin plugin page.
    * @param $links array Current links to display.
    */
   static function create_settings_link($links){
      $docs_link = '<a title="documentation" target="_blank" href="http://www.webilop.com/products/user-language-switch-wordpress-plugin/">Docs</a>';
      array_unshift($links, $docs_link);
      $settings_link = '<a href="options-general.php?page=uls-settings-page">Settings</a>';
      array_unshift($links, $settings_link);
      return $links;
   }
}

/**
 * Add the menu in the hook
 */
add_action( 'admin_menu', 'ULS_Options::register_menu' );
/**
 * Add setting registration
 */
add_action( 'admin_init', 'ULS_Options::init_settings' );
/**
 * Add Settings link to plugin admin page
 */
add_filter('plugin_action_links_' . ULS_PLUGIN_NAME, 'ULS_Options::create_settings_link');
/**
 * Add default settings for the plugin.
 */
register_activation_hook(ULS_FILE_PATH, 'ULS_Options::init_plugin');

/**
 * Add ajax action to save user language preferences.
 */
add_action('wp_ajax_uls_user_language_preferences', 'ULS_Options::save_user_language_preferences');
?>
