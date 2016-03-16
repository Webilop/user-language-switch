<?php
/**
 * This class control plugin settings.
 */
class ULS_Options{
   static private $default_options = array(
      'uls_plugin_version' => '1.5',
      'uls_plugin_question' => false,
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
      'languages_filter_enable' => array('post' => 'post', 'page' => 'page'),
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
 //   echo "<pre>"; print_r($options ); echo "</pre>";
    if ( empty(get_option('uls_settings_question'))  )
      add_action( 'admin_notices', 'ULS_Options::uls_admin_notice_question' );

    //create about section
    add_settings_section('uls_create_section_tabs',
      '',
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
        __('Enable flags tab','user-language-switch'),
        'ULS_Options::create_checkbox_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options);

      $options['input_name'] = 'fixed_position_language_switch';
      add_settings_field($options['input_name'],
        __('The tab is always visible in the browser window','user-language-switch'),
        'ULS_Options::create_checkbox_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options);

      $options['input_name'] = 'tab_position_language_switch';
      $options['select_options'] = array('TL' => 'Top-Left',
                                         'TC' => 'Top-Center',
                                         'TR' => 'Top-Right',
                                         'BL' => 'Bottom-Left',
                                         'BC' => 'Bottom-Center',
                                         'BR' => 'Bottom-Right',
                                         'LT' => 'Left-Top',
                                         'LM' => 'Left-Middle',
                                         'LB' => 'Left-Bottom',
                                         'RT' => 'Right-Top',
                                         'RM' => 'Right-Middle',
                                         'RB' => 'Right-Bottom'
                                        );
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
      $options['available_language'] = true;
      add_settings_field($options['input_name'],
        __('Default language','user-language-switch'),
        'ULS_Options::create_language_selector_input',
        'uls-settings-page',
        'uls_general_settings_section',
        $options);

      $options['input_name'] = 'default_backend_language';
      $options['available_language'] = false;
      add_settings_field($options['input_name'],
        __('Default language for admin side','user-language-switch'),
        'ULS_Options::create_language_selector_input',
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

      $options['input_name'] = 'enable_translation_sidebars_language_switch';
      add_settings_field($options['input_name'],
        __('Enable translations for sidebars','user-language-switch'),
        'ULS_Options::create_checkbox_input',
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
      //create section for tabs description
      $options['input_name'] = 'uls_tabs_menu_language';
      add_settings_section($options['input_name'],
        __('Information','user-language-switch'),
        'ULS_Options::create_tabs_information_section',
        'uls-settings-page');

      // create menu table configuration
      add_settings_section('table_menu_language',
        '',
        'ULS_Options::create_table_menu_language',
        'uls-settings-page',
        'uls_tabs_menu_language',
        $options);
    }
    else if( isset($_GET['tab']) && $_GET['tab'] == 'available_languages' ) {
      //create section for registration
      add_settings_section('uls_tabs_available_language',
        __('Information','user-language-switch'),
        'ULS_Options::create_tabs_information_section',
        'uls-settings-page');

      // create table configuration
      add_settings_section('table_available_language',
        '',
        'ULS_Options::create_table_available_language',
        'uls-settings-page',
        'uls_tabs_available_language',
        $options);
    }
    else if( isset($_GET['tab']) && $_GET['tab'] == 'languages_filter_enable' ) {
      //create section for tabs description
      $options['input_name'] = 'uls_tabs_language_filter';
      add_settings_section($options['input_name'],
        __('Information','user-language-switch'),
        'ULS_Options::create_tabs_information_section',
        'uls-settings-page');

      // create menu table configuration
      $options['input_name'] = 'languages_filter_enable';
      add_settings_section('table_language_filter',
        '',
        'ULS_Options::create_table_language_filter',
        'uls-settings-page',
        'uls_tabs_language_filter',
        $options);
    }
    //create calification section
    add_settings_section('uls_calification_section',
      __('Rate it!','user-language-switch'),
      'ULS_Options::create_calification_section',
      'uls-settings-page');

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
    }
    else if ( isset($_POST['languages_filter_enable']) ) {
      $options['languages_filter_enable'] = $_POST['uls_language_filter'];
    }else{
      //create default options
      $ulsPostionMenuLanguage = $options['position_menu_language'];
      // if the user does not save any language the default value is that the all languages are available
      // if the user does not want to show the languages he has tow options
      // 1 - desactive the flags tab  or 2 - desactive the plugin
      $ulsAvailableLanguage = isset($options['available_language']) ? $options['available_language'] : uls_get_available_languages(false);
      // disable all post type filter
      $ulsLanguageFilter = isset($options['languages_filter_enable']) ? $options['languages_filter_enable'] : self::$default_options['languages_filter_enable'];

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
      $options['languages_filter_enable'] = $ulsLanguageFilter;
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

  }
  /**
   * function uls_admin_notice_question
   * this function is to show the message in the admin panel
   */
  static function uls_admin_notice_question() {
  ?>
    <div class="notice notice-success is-dismissible">
        <div id="content_question_display">
          <p><?php _e( 'Would you like to help you to contact a professional translator for your site.', 'user-language-switch' ); ?></p>
          <button id="uls_answere_traductor_yes" type="button" name="answere_yes" class="button button-primary uls_answere_button">YES</button>
          <button id="uls_answere_traductor_not" type="button" name="answere_not" class="button button-primary uls_answere_button">NO</button>
        </div>
    </div>
  <?php
  }
  /**
   * function: uls_answere_question_contact
   * this is an ajax function to change value of the uls_plugin_question to not show
   * the question again and send the information to this url:
   * http://dev.webilop.com/webilop-3.0/wp-admin/admin-ajax.php?action=store_answer&answer=yes&domain=awesome-site.com&email=aritoma@gmail.com
  */
  static function uls_answere_question_contact() {
    $answere_question = isset($_POST['button_answere_value']) ? $_POST['button_answere_value'] : '';
    if (!empty($answere_question)) {

      if ($answere_question == 'answere_yes' )
        $answere = "yes";
      else
        $answere = "no";

      $user = wp_get_current_user();
      $user_email = $user->user_email;
      $site_url = get_site_url();
      $site_url = preg_replace('#^https?://#', '', $site_url);
      $url = "http://webilop.com/wp-admin/admin-ajax.php?action=store_answer&answer=$answere&domain=$site_url&email=$user_email";

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true );
      $response = curl_exec($ch);
      curl_close($ch);

      $uls_options = add_option('uls_settings_question',  $answere);

      $message = " <br/> The information was save <br/>";
      echo $message ; exit;
    }
    echo "0"; exit;
  }


   /**
    * Create the HTML of an input select field to choose a language.
    * @param $options array plugin options saved.
    */
   static function create_language_selector_input($options) {
      $default_value = (isset($options[$options['input_name']])) ? $options[$options['input_name']] : self::$default_options[$options['input_name']];
      $available_language = (isset($options['available_language'])) ? $options['available_language'] : true;
      $class = '';
      // $id, $name, $default_value = '', $class = '', $available_languages = true
      echo uls_language_selector_input($options['input_name'], $options['input_name'], $default_value, $class, $available_language);
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
                                            <?php
                                            $selectedHTML = '';
                                            if(isset($options[$theme]) && isset($options[$theme][$language_code])) {
                                              $selectedHTML = selected($menu->slug, ($options) ? $options[$theme][$language_code] : '' );
                                            }
                                            ?>
                                            <option value="<?= $menu->slug; ?>"  <?= $selectedHTML;?> ><?= $menu ->name; ?></option>
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

  static function sort_translations_callback($a, $b) {
    return strnatcasecmp($a['english_name'], $b['english_name']);
  }

   /*
    * this function dowload automaticaly the langue from the https: official page
    * this funcion check if the class ZipArchive is available and check the openss available too
    * this class needs the last requirements to can download and unzip the language
    */
  static function download_language() {
    $data = explode(";", $_POST['info_language']);
    $remoteFile = $data[1];


    $localPath = WP_CONTENT_DIR . '/languages/';
    $localFile = $localPath."package.zip";

    $flag = file_put_contents($localFile, fopen($remoteFile, 'r'));

    if($flag === FALSE){
      echo "0";
    }
    else{
      if (class_exists('ZipArchive')){
        $zip = new ZipArchive;
        if ($zip->open($localFile) === TRUE) {
          $zip->extractTo($localPath, array($data[0].".mo", $data[0].".po"));
          $zip->close();
        }
        echo "1";
      }
      else{
        echo "2";
      }

      unlink($localFile);
    }

    wp_die();
  }

  static function create_table_available_language($options) {
    $languages = uls_get_available_languages(false); // get the all languages available in the wp
    $options = get_option('uls_settings'); // get information from DB
    $available_language = isset($options['available_language']) ? $options['available_language'] : uls_get_available_languages(false); // get the information that actually is in the DB
  ?>
    <script type="text/javascript">
      jQuery(function($){
        jQuery('#button-download-language').click(function () {
          jQuery("#div_message_download").html("<?php echo _("Downloading language...") ?>");

          var language = $("#tblang").val();
          $.post(ajaxurl, {
            action: 'uls_download_language',
            info_language: language
          }, function(data) {
            window.location.href = window.location + "&success=" + data;
          });
        });
      });
    </script>
    <table id="menu-locations-table" class="">
      <thead>
        <tr>
          <th>Enable / Disable </th>
          <th>Language</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($languages as $lang_name => $lang_code): ?>
          <tr>
            <?php $checked = isset($available_language[$lang_name]) ? 'checked' : ''; ?>
            <td>
              <input type="checkbox" name="uls_available_language[<?=$lang_name?>]" value="<?=$lang_code?>" <?=$checked?> />
            </td>
            <td>
                <!--img src="<?= plugins_url("css/blank.gif", __FILE__) ?>" style="margin-right:5px;" class="flag_16x11 flag-<?= strtolower(substr($lang_code, -2))?>" alt="<?= $lang_name ?>" title="<?= $lang_name ?>" /-->
                <img src="<?= plugins_url("css/blank.gif", __FILE__) ?>" style="margin-right:5px;" class="flag_16x11 flag-<?= Codes::languageCode2CountryCode($lang_code); ?>" alt="<?= $lang_name ?>" title="<?= $lang_name ?>" />
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <input type="hidden" name="available_languages" value="available_languages" >

    <br/>

    <table id="menu-locations-table" class="">
      <thead>
        <tr>
          <th>Install Additional Languages</th>
        </tr>
      </thead>
      <tbody>
        <tr>
        <?php
          require_once ABSPATH . '/wp-admin/includes/translation-install.php';
          $translations = wp_get_available_translations();
          uasort($translations, array( __CLASS__, 'sort_translations_callback'));


          // check the requirement to can download language
          $execute_languages = true;
          $zip_message = ''; // meessage information
          $ssl_message = ''; // meessage information
          if ( !class_exists('ZipArchive')  ){
            $zip_message = '<p class="bg-warning">';
            $zip_message .= __("Missing class ZipArchive. Please install and retry later.");
            $zip_message .= '</p>';
            $execute_languages = false;
          }
          if ( !extension_loaded('openssl') ) {
            $ssl_message = '<p class="bg-warning">';
            $ssl_message .= __("Missing extension openssl. Please enable openss extension in your php.ini and retry later.");
            $ssl_message .= '</p>';
            $execute_languages = false;
          }

          echo "<td>".__('Select a language').": </td><td><select id='tblang'>";
          if ( $execute_languages ) {
            foreach($translations as $language){
              echo "<option value='".$language['language'].";".$language['package'].";".$language['english_name']."'>";
              echo $language['english_name']." - ".$language['native_name']."</option>";
            }
          }
          echo "</select>";
          if ( $execute_languages ) : ?>
            <input type="button"
                   class="button-primary"
                   id="button-download-language"
                   value="<?php echo __('Download','user-language-switch')?>" />
          <?php endif; ?>
        </td>
        </tr>
      </tbody>
    </table>
    <div id="div_message_download" class="div_message_download">
      <?php
        if(isset($_GET['success'])){
          if($_GET['success'] == 1) {
            $ok_message = '<p class="bg-success">';
            $ok_message .= __("Language successfully downloaded!!!");
            $ok_message .= '</p>';
            echo $ok_message;
          }
          else if($_GET['success'] == 0) {
            $error_message = '<p class="bg-warning">';
            $error_message .= __("File writing permission denied. Please fix permissions to directory wp-content/languages.");
            $error_message .= '</p>';
            echo $error_message;
          }
        }
        echo $zip_message;
        echo $ssl_message;
      ?>
    </div>
  <?php
  }

   /*
    * create table language filter this is for enable and disable post_type
     */
  static function create_table_language_filter($options) {
    $options = get_option('uls_settings'); // get information from DB
    // get the information that actually is in the DB
    $languages_filter = isset($options['languages_filter_enable']) ? $options['languages_filter_enable'] : '';

    $args = array( '_builtin' => false);// values for do the query
    $post_types = get_post_types($args); // get all custom post types
    $post_types['post'] = 'post'; // add default post type
    $post_types['page'] = 'page'; // add default post type
  ?>
    <table id="menu-locations-table" class="">
      <thead>
        <tr>
          <th>Enable / Disable </th>
          <th>Post types</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($post_types as $post_type => $name): ?>
          <tr>
            <?php $checked = isset($languages_filter[$post_type]) ? 'checked' : ''; ?>
            <td>
              <input type="checkbox" name="uls_language_filter[<?=$post_type?>]" value="<?=$name?>" <?=$checked?> />
            </td>
            <td>
              <label for="<?=$post_type?>_label"><?=$name?></label>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <input type="hidden" name="languages_filter_enable" value="languages_filter_enable" >
  <?php
  }
   /**
    * Create register form displayed on back end.
    */
   static function create_general_settings_section(){
    ?>
      <p><?php _e('Configure settings for the language tab that contains flags to change between languages in your website, set default languages for your website, create custom menu translations and activate translations of sidebars to create different sidebars for each language.', 'user-language-switch'); ?></p>
    <?php
   }

   /**
    * Create the section to collaborate with the development.
    */
   static function create_collaboration_section(){
      ?>
      <div class="section-inside"><p><?php printf(__('You can collaborate with the development of this plugin, please submit your collaboration to %s or send us any suggestion or bug notification to %s.', 'user-language-switch'), '<a target="_blank" href="https://github.com/Webilop/user-language-switch">' . __('the respository of the plugin in Github', 'user-language-switch') . '</a>', '<a href="mailto:support@webilop.com">support@webilop.com</a>'); ?></p></div>
      <?php
   }

   /**
    * Create the section to collaborate with the development.
    */
   static function create_calification_section(){
      ?>
      <div class="section-inside"><p><?php _e('If you like this plugin please'); ?> <a title="rate it" href="https://wordpress.org/support/view/plugin-reviews/user-language-switch" target="_blank">leave us a rating</a>. In advance, thanks from Webilop team! &nbsp;
        <span class="rating">
          <input type="radio" class="rating-input"
              id="rating-input-1-5" name="rating-input-1" onclick="window.open('https://wordpress.org/support/view/plugin-reviews/user-language-switch')">
          <label for="rating-input-1-5" class="rating-star"></label>
          <input type="radio" class="rating-input"
              id="rating-input-1-4" name="rating-input-1" onclick="window.open('https://wordpress.org/support/view/plugin-reviews/user-language-switch')">
          <label for="rating-input-1-4" class="rating-star"></label>
          <input type="radio" class="rating-input"
              id="rating-input-1-3" name="rating-input-1" onclick="window.open('https://wordpress.org/support/view/plugin-reviews/user-language-switch')">
          <label for="rating-input-1-3" class="rating-star"></label>
          <input type="radio" class="rating-input"
              id="rating-input-1-2" name="rating-input-1" onclick="window.open('https://wordpress.org/support/view/plugin-reviews/user-language-switch')">
          <label for="rating-input-1-2" class="rating-star"></label>
          <input type="radio" class="rating-input"
              id="rating-input-1-1" name="rating-input-1" onclick="window.open('https://wordpress.org/support/view/plugin-reviews/user-language-switch')">
          <label for="rating-input-1-1" class="rating-star"></label>
        </span>
      </p></div>
      <?php
   }

   /**
    * Create the section tabs information.
    */
   static function create_tabs_information_section($options){
     switch($options['id']){
       case 'uls_tabs_menu_language':
         $description = __("Assign menus as translations to other menus, first you need to create your menus in Appearance - Menus. If you don't assign a translation for a menu, then pages in the menu are translated individually if they have translations assigned.", 'user-language-switch');
         break;
       case 'uls_tabs_available_language':
         $description = '';//__('You can install more languages in your site following the instructions in <a href="http://codex.wordpress.org/WordPress_in_Your_Language" target="_blank">WordPress in Your Language</a>.', 'user-language-switch');
         break;
       case 'uls_tabs_language_filter':
         $description = __("Select which post types should be filtered automatically by language. If a post, page or custom post doesn't match the language you are looking in the website, then it is not displayed. If a post, page or custom post doesn't have language, then it is matched with the default language of the website.", 'user-language-switch');
         break;
     }
      ?>
      <div><p><?php echo $description; ?></p></div>
      <?php
   }

   /**
    * Create the section to collaborate with the development.
    */
   static function create_about_section(){
    ?>
    <div class="section-inside">
    <p><strong>User Language Switch </strong><?php _e('was developed by ', 'user-language-switch');?><a title="Webilop. web and mobile development" href="http://www.webilop.com" target="_blank">Webilop</a>.</p>
    <p><?php _e('Webilop is a company focused on web and mobile solutions. We are experts customizing themes and building plugins in WordPress. We can help you with your website, contact us at ', 'user-language-switch');?><a href="mailto:contact@webilop.com">contact@webilop.com</a>.</p>
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
         <?php submit_button( __( 'Save', 'user-language-switch') ); ?>
      </form>
   </div>
   <?php
   }

   static function ilc_admin_tabs() {
    // get the current tab or default tab
    $current = isset($_GET['tab']) ? $_GET['tab'] : 'homepage';
    // add the tabs that you want to use in the plugin
    $tabs = array('homepage' => __('General', 'user-language-switch'),
                  'menulanguage' => __('Menu Languages', 'user-language-switch'),
                  'available_languages' => __('Available Languages', 'user-language-switch'),
                  'languages_filter_enable' => __('Filter Post Types', 'user-language-switch') );

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
    * Create the HTML options to manage user language preferences in the user profile page.
    */
  static function create_user_profile_language_options(){
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
    <h3><?php _e('User Language Preferences','user-language-switch'); ?></h3>
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
            <?php
                  // $id, $name, $default_value = '', $class = '', $available_languages = true
                  $class = '';
                  $available_languages = false;
                  echo uls_language_selector_input($options['backend_language_field_name'], $options['backend_language_field_name'], $default_backend_language, $class, $available_languages);
              ?>
            </td>
          </tr>
          <?php endif; ?>
      </tbody>
    </table>
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

  static function save_user_profile_language_preferences(){
    //save settings for the user
    $options = get_option('uls_settings');
    if(!empty($_POST[$options['backend_language_field_name']]))
        update_user_meta(get_current_user_id(), $options['backend_language_field_name'], $_POST[$options['backend_language_field_name']]);
    if(!empty($_POST[$options['frontend_language_field_name']]))
        update_user_meta(get_current_user_id(), $options['frontend_language_field_name'], $_POST[$options['frontend_language_field_name']]);

    // save
    if ( !isset($_SESSION) )
     session_start();
    $_SESSION["ULS_USER_FRONTEND_LOCALE"] = $_POST[$options['frontend_language_field_name']];
    $_SESSION["ULS_USER_BACKEND_LOCALE"] = $_POST[$options['backend_language_field_name']];
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

   static function select_correct_menu_language($items, $args) {

    $options = get_option('uls_settings');
    $menu_name = $args->menu;
    $position_menu_language = isset($options['position_menu_language']) ? $options['position_menu_language'] : array();

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

/**
 * Add ajax action to download a specific language
 */
add_action('wp_ajax_uls_download_language', 'ULS_Options::download_language');
/**
 * Add ajax action to answere the question
 */
add_action('wp_ajax_uls_answere_question_contact', 'ULS_Options::uls_answere_question_contact');

?>
