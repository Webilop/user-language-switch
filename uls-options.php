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
      'activate_tab_language_switch' => true, 
      'tab_color_picker_language_switch' => 'rgba(255, 255, 255, 0)', 
      'tab_position_language_switch' => 'RM',
      'fixed_position_language_switch' => true, 
      'enable_translation_sidebars_language_switch' => true, 
      'use_browser_language_to_redirect_visitors' => true, 
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
  static function init_settings() {
    //register settings
    register_setting('uls_settings',
      'uls_settings',
      'ULS_Options::validate_settings');

    //get options
    $options = get_option('uls_settings'); 

    //create about section 
    add_settings_section('uls_create_section_tabs',
      __('','user-language-switch'),
      'ULS_Options::ilc_admin_tabs',
      'uls-settings-page');


    // add configuration about the setting depent of the tab
    if( isset($_GET['tab']) && $_GET['tab'] == 'homepage' || !isset($_GET['tab'])  ) { 
      //create section for registration
      add_settings_section('uls_general_settings_section',
        __('General Settings','user-language-switch'),
        'ULS_Options::create_general_settings_section',
        'uls-settings-page');

      $options['input_name'] = 'activate_tab_language_switch';
      add_settings_field($options['input_name'],
        __('Activate Tab','user-language-switch'),
        'ULS_Options::create_checkbox_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options); 

      $options['input_name'] = 'fixed_position_language_switch';
      add_settings_field($options['input_name'],
        __('Tab Fixed Pisition ','user-language-switch'),
        'ULS_Options::create_checkbox_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options); 

      $options['input_name'] = 'enable_translation_sidebars_language_switch';
      add_settings_field($options['input_name'],
        __('Enable translations for sidebars','user-language-switch'),
        'ULS_Options::create_checkbox_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options); 

      $options['input_name'] = 'use_browser_language_to_redirect_visitors';
      add_settings_field($options['input_name'],
        __('Use browser language to redirect visitors','user-language-switch'),
        'ULS_Options::create_checkbox_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options); 

      $options['input_name'] = 'tab_position_language_switch'; 
      $options['select_options'] = array( 'TL' => 'Top-Left','TC' => 'Top-Center','TR' => 'Top-Right',
                                          'BL' => 'Bottom-Left','BC' => 'Bottom-Center','BR' => 'Bottom-Right',
                                          'LT' => 'Left-Top','LM' => 'Left-Middle','LB' => 'Left-Bottom', 
                                          'RT' => 'Ringht-Top','RM' => 'Ringht-Middle','RB' => 'Ringht-Bottom', ); 
      add_settings_field($options['input_name'],
        __('Tab Position','user-language-switch'),
        'ULS_Options::create_select_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options); 

      $options['input_name'] = 'tab_color_picker_language_switch'; 
      add_settings_field($options['input_name'],
        __('Tab Backgorund Color','user-language-switch'),
        'ULS_Options::create_text_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options); 

      $options['input_name'] = 'default_frontend_language';
      add_settings_field($options['input_name'],
        __('Default language','user-language-switch'),
        'ULS_Options::create_language_selector_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options); 

      $options['input_name'] = 'default_backend_language';
      add_settings_field($options['input_name'],
        __('Default language for admin side','user-language-switch'),
        'ULS_Options::create_language_selector_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options);

      $options['input_name'] = 'user_frontend_configuration';
      add_settings_field($options['input_name'],
        __('Allow registered users to change the language that user looks the website','user-language-switch'),
        'ULS_Options::create_checkbox_input',
        'uls-settings-page',
        'uls_general_settings_section',$options);

      $options['input_name'] = 'user_backend_configuration';
      add_settings_field($options['input_name'],
        __('Allow registered users to change the language that user looks the back-end side','user-language-switch'),
        'ULS_Options::create_checkbox_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options);
    }
    else if( isset($_GET['tab']) && $_GET['tab'] == 'menulanguage' ) { 
      //create section for registration
      add_settings_section('uls_general_settings_section',
        __('Menu Language Settings','user-language-switch'),
        'ULS_Options::create_general_settings_section',
        'uls-settings-page');

      // create menu table configuration
      add_settings_section('table_menu_language',
        __('','user-language-switch'),
        'ULS_Options::create_table_menu_language',
        'uls-settings-page',
        'uls_general_settings_section',
        $options);
    }
    else if( isset($_GET['tab']) && $_GET['tab'] == 'available_languages' ) { 
      //create section for registration
      add_settings_section('uls_general_settings_section',
        __('Menu Language Settings','user-language-switch'),
        'ULS_Options::create_general_settings_section',
        'uls-settings-page');

      // create table configuration
      add_settings_section('table_menu_language',
        __('','user-language-switch'),
        'ULS_Options::create_table_available_language',
        'uls-settings-page',
        'uls_general_settings_section',
        $options); 
    }
    //create section for collaboration
    add_settings_section('uls_collaboration_section',
      __('Collaborate','user-language-switch'),
      'ULS_Options::create_collaboration_section',
      'uls-settings-page');

    //create about section 
    add_settings_section('uls_about_section',
      __('About','user-language-switch'),
      'ULS_Options::create_about_section',
      'uls-settings-page');
  }

  /**
  * Validate setting input fields. 
  */
  static function validate_settings($input){

    $options = get_option('uls_settings');

    // if this tab send by post
    if( isset($_POST['menulanguage']) ) { 
      $options['position_menu_language'] = $_POST['uls_position_menu_language'];
    }
    else if ( isset($_POST['available_languages']) ) {
      $options['available_language'] = $_POST['uls_available_language'];
    }else{ 
      //create default options
      $ulsPostionMenuLanguage = $options['position_menu_language'];
      // if the user does not save any language the default value is that the all languages are available
      // if the user does not want to show the languages he has tow options
      // 1 - desactive the flags tab  or 2 - desactive the plugin
      $ulsAvailableLanguage = isset($options['available_language']) ? $options['available_language'] : uls_get_available_languages(false);
      $options = self::$default_options;

      foreach($options as $k => $v)
        if(isset($_POST[$k]) && !empty($_POST[$k]) && '' != trim($_POST[$k]))
          $options[$k] = trim($_POST[$k]);

      //get values of checkboxes
      $options['user_backend_configuration'] = isset($_POST['user_backend_configuration']);
      $options['user_frontend_configuration'] = isset($_POST['user_frontend_configuration']);
      $options['activate_tab_language_switch'] = isset($_POST['activate_tab_language_switch']); 
      $options['fixed_position_language_switch'] = isset($_POST['fixed_position_language_switch']); 
      $options['enable_translation_sidebars_language_switch'] = isset($_POST['enable_translation_sidebars_language_switch']); 
      $options['use_browser_language_to_redirect_visitors'] = isset($_POST['use_browser_language_to_redirect_visitors']); 
      $options['position_menu_language'] = $ulsPostionMenuLanguage;
      $options['available_language'] = $ulsAvailableLanguage;
    } 
    return $options;
  }

  /**
  * Add entries in menu sidebar in back end.
  */
  static function register_menu(){
    add_options_page( __('User Language Switch','user-language-switch'),
      __('User Language Switch','user-language-switch'),
      'manage_options', 'uls-settings-page',
      'ULS_Options::create_settings_page' ); 

    //if users can configurate their languages
    $options = get_option('uls_settings');
    if($options['user_backend_configuration'] || $options['user_frontend_configuration'])
      add_menu_page( __('User Language Preferences','user-language-switch'),
        __('User Language','user-language-switch'),
        'read',
        'uls-user-language-page',
        'ULS_Options::create_user_language_page' );
  }

   /**
    * Create the HTML of an input select field to choose a language.
    * @param $options array plugin options saved.
    */
   static function create_language_selector_input($options){
      $default_value = (isset($options[$options['input_name']])) ? $options[$options['input_name']] : self::$default_options[$options['input_name']];
      echo uls_language_selector_input($options['input_name'],$options['input_name'],$default_value);
   }

   /**
    * Create the HTML of an input checkbox field.
    * @param $options array plugin options saved.
    */
   static function create_checkbox_input($options){
      $default_value = (isset($options[$options['input_name']])) ? $options[$options['input_name']] : self::$default_options[$options['input_name']];
      ?>
      <input type="checkbox" name="<?php echo $options['input_name']; ?>" <?php checked($default_value); ?> />
      <?php
   }

   /**
    * Create the HTML of an input text field.
    * @param $options array plugin options saved.
    */
   static function create_text_input($options){
      $default_value = (isset($options[$options['input_name']])) ? $options[$options['input_name']] : self::$default_options[$options['input_name']];
      ?>
      <input type="text" name="<?php echo $options['input_name']; ?>" value="<?php echo $default_value; ?>" />
      <?php
   }

   static function create_select_input($options){
      $default_value = (isset($options[$options['input_name']])) ? $options[$options['input_name']] : self::$default_options[$options['input_name']]; ?>

      <select name="<?php echo $options['input_name']; ?>">
         <?php foreach($options['select_options'] as $key => $value): ?>
            <option value="<?= $key ?>" <?php selected($key,$default_value); ?> ><?= $value ?></option>
         <?php endforeach; ?>
      </select> 
      <?php
   }

    static function create_table_menu_language($option) {
      $languages = uls_get_available_languages(); // get the all languages available in the wp
      $themeLocation  = get_registered_nav_menus(); // get the all theme location
      $options = get_option('uls_settings'); // get information from DB
      $options = isset($options['position_menu_language']) ? $options['position_menu_language'] : false; // get the information that actually is in the DB 
    ?> 
            <table id="menu-locations-table" class="widefat fixed">
                  <thead>
                      <tr>
                          <th class="manage-column column-locations" scope="col">Theme Location</th>
                          <?php foreach ($languages as $language_name => $language_code) : // add all header, language available ?>
                              <th class="manage-column column-locations" scope="col"><?= $language_name ?></th>
                          <?php endforeach; ?>
                      </tr>
                  </thead>
                  <tbody class="menu-locations">
                      <?php 
                        $menus = get_terms( 'nav_menu', array( 'hide_empty' => true ) ); // get menues
                        foreach ($themeLocation as $theme => $location) : // iterative theme locations, add rows  ?>
                          <tr id="menu-locations-row">
                              <td class="menu-location-title"><strong><?=$location?></strong></td>
                              <?php foreach ($languages as $language_name => $language_code ):  // iterative languages available in the wp?>
                                <td class="menu-location-menus">
                                  <select id="" class="" name="uls_position_menu_language[<?= $theme; ?>][<?= $language_code; ?>]" >
                                            <option value="0">— Select a Menu —</option> 
                                    <?php 
                                          foreach ($menus as $menu ): // iterative menues, add cols ?>
                                            <option value="<?= $menu->slug; ?>"  <?php selected($menu->slug, ($options) ? $options[$theme][$language_code] : '' );  ?> ><?= $menu ->name; ?></option> 
                                    <?php endforeach; ?>
                                  </select>
                                </td><!-- .menu-location-menus -->
                              <?php endforeach; ?>
                          </tr><!-- #menu-locations-row -->
                      <?php endforeach; ?>
                  </tbody>
            </table>
            <input type="hidden" name="menulanguage" value="menulanguage" >
        <?php
           }

  public function create_table_available_language($options) {
    $languages = uls_get_available_languages(false); // get the all languages available in the wp
    $options = get_option('uls_settings'); // get information from DB
    $available_language = isset($options['available_language']) ? $options['available_language'] : uls_get_available_languages(false); // get the information that actually is in the DB 
  ?>
    <table id="menu-locations-table" class="">
      <thead>
        <tr>
          <th>Enable / Disable </th>
          <th>Language</th>
        </tr>
      </thead> 
      <tbody>
        <?foreach ($languages as $lang_name => $lang_code): ?>
          <tr>
            <?php $checked = isset($available_language[$lang_name]) ? 'checked' : ''; ?> 
            <td>
              <input type="checkbox" name="uls_available_language[<?=$lang_name?>]" value="<?=$lang_code?>" <?=$checked?> />
            </td>
            <td>
                <img src="<?= plugins_url("css/blank.gif", __FILE__) ?>" style="margin-right:5px;" class="flag_16x11 flag-<?= strtolower(substr($lang_code, -2))?>" alt="<?= $lang_name ?>" />
            </td> 
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table> 
    <input type="hidden" name="available_languages" value="available_languages" >
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
      <div class="section-inside"><p><?php _e('You can collaborate with the development of this plugin, please send us any suggestion or bug notification to '); ?><a href="mailto:support@webilop.com">support@webilop.com</a></p></div>
      <?php
   }

   /**
    * Create the section to collaborate with the development.
    */
   static function create_about_section(){
    ?>
    <div class="section-inside">
    <p><strong>User Language Switch </strong><?php _e('was developed by ', 'user-language-switch');?><a title="Webilop. web and mobile development" href="http://www.webilop.com">Webilop</a></p>
    <p><?php _e('Webilop is a company focused on web and mobile solutions. We develop custom mobile applications, templates and plugins for CMS platforms like Wordpress. We can help you with your website, contact us at ', 'user-language-switch');?><a href="mailto:contact@webilop.com">contact@webilop.com</a></p>
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
         <?php submit_button('Save'); ?>
      </form>
   </div>
   <?php
   }

   static function ilc_admin_tabs() { 
    // get the current tab or default tab
    $current = isset($_GET['tab']) ? $_GET['tab'] : 'homepage'; 
    // add the tabs that you want to use in the plugin 
    $tabs = array( 'homepage' => 'General', 'menulanguage' => 'Menu Languages', 'available_languages' => 'Available Languages');
    echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    // configurate the url with your personal_url and add the class for the activate tab
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?page=uls-settings-page&tab=$tab'>$name</a>"; 
    }
    echo '</h2>';
   }

   /**
    * Create the HTML page to manage user language preferences.
    */
   static function create_user_language_page(){
      if ( !current_user_can( 'read' ) )  {
         wp_die( __( 'You do not have sufficient permissions to access this page.', 'user-language-switch' ) );
      }

      $options = array_merge(ULS_Options::$default_options, get_option('uls_settings'));
      $default_backend_language = get_user_meta(get_current_user_id(), $options['backend_language_field_name'], true);

      if(empty($default_backend_language))
         $default_backend_language = $options['default_backend_language'];
      $default_frontend_language = get_user_meta(get_current_user_id(), $options['frontend_language_field_name'], true);

      if(empty($default_frontend_language))
         $default_frontend_language = $options['default_frontend_language'];

      $activate_tab_language_switch = get_user_meta(get_current_user_id(), $options['activate_tab_language_switch'], true);
      if(empty($activate_tab_language_switch))
         $activate_tab_language_switch = $options['activate_tab_language_switch'];
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
               <?php if($options['user_frontend_configuration']): ?>
               <tr valign="top">
                  <th scope="row"><?php _e('Language for the website','user-language-switch'); ?></th>
                  <td>
                     <?php echo uls_language_selector_input($options['frontend_language_field_name'],$options['frontend_language_field_name'],$default_frontend_language); ?>
                  </td>
               </tr>
               <?php endif; ?>
               <?php if($options['user_backend_configuration']): ?>
               <tr valign="top">
                  <th scope="row"><?php _e('Language for the back-end side','user-language-switch'); ?></th>
                  <td>
                     <?php echo uls_language_selector_input($options['backend_language_field_name'],$options['backend_language_field_name'],$default_backend_language); ?>
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
    <p><?php _e('Webilop is a company focused on web and mobile solutions. We develop custom mobile applications, templates and plugins for CMS platforms like Wordpress. We can help you with your website, contact us at ', 'user-language-switch');?><a href="mailto:contact@webilop.com">contact@webilop.com</a></p>
   </div>
   <?php 
   }

   /**
    * Process and save the user language prefernces.
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
    * Create the settings link in admi plugin page.
    * @param $links array Current links to display.
    */
   static function create_settings_link($links){
      $docs_link = '<a title="documentation" target="_blank" href="http://www.webilop.com/products/user-language-switch-wordpress-plugin/">Docs</a>';
      array_unshift($links, $docs_link);
      $settings_link = '<a href="options-general.php?page=uls-settings-page">Settings</a>'; 
      array_unshift($links, $settings_link);
      return $links; 
   }

   static function select_correct_menu_language($items, $args) {
     

    $options = get_option('uls_settings');
    $menu_name = $args->menu;
    $position_menu_language = $options['position_menu_language']; 

    // if the mena arrive ask which traduction should be show up
    if (!empty($args->menu)) {
      foreach ( $position_menu_language as $location => $array_translation ) {
         $key = array_search ( $args->menu , $array_translation);
           if ($key) { 
             $menu_name = $array_translation[uls_get_user_language()];
             if ($menu_name == $args->menu)
               return $items;
             else
               return wp_nav_menu( array( 'menu' => $menu_name, 'items_wrap' => '%3$s' , 'container' => false, 'echo' => 0) );
           }
       } 
    } 

    // if the theme_location arrive ask whitch traduction should be show up
    if (!empty($args->theme_location)) {
      $menu_location = $position_menu_language[$args->theme_location];
      $key_menu_name = isset($menu_location[uls_get_user_language()]) ? array_search($menu_location[uls_get_user_language()], $menu_location) : '' ;
      if ( $key_menu_name != uls_get_user_language()) 
       return $items;
      else
       return wp_nav_menu( array( 'menu' => $menu_location[uls_get_user_language()], 'items_wrap' => '%3$s' , 'container' => false, 'echo' => 0) );
    }
    return $items;
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
/**
 * Add ajax action to save user language preferences.
 */
add_filter('wp_nav_menu_items', 'ULS_Options::select_correct_menu_language', 10, 2);
?>
