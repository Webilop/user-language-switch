<?php
global $wp_query;
$shortcode_class = isset($shortcode_class) ? $shortcode_class : 'tab_background_color_picker';

$options = get_option('uls_settings'); // get information from DB
$new_img_file =  isset($options['uls_available_language_new_flags']) ? $options['uls_available_language_new_flags'] : '' ;

$languages = uls_get_available_languages();
$postId = null;
if (!is_home() && !is_archive() && !is_search() && !is_404()) {
  $postId = $wp_query->post->ID;
}
?>
  <div id="<?= $shortcode_class;?>">
  <?php foreach ($languages as $key => $value) : ?>
    <?php
      $content_style = '';
      $tagHtml = ' <img src="'. plugins_url('css/blank.gif', __FILE__) .
      '" style="margin-right:5px;" class="flag_32x32 flag-' .
      Codes::languageCode2CountryCode($value). '" alt="' . $value . '" title="' .
      $key . '" /> ';
      $grayClass = '';
      if (!is_null($postId)) {
        $hasTranslation = uls_get_post_translation_id($postId, $value) !== false;
        $grayClass = $hasTranslation ? '' : 'uls-grayscale';
      }

      // if the user load a specific img for a language load it and add its configuration
      if ( isset($new_img_file[$key]) && !empty($new_img_file[$key]) ) {
        // get width and height from image loaded
        list($width, $height) = getimagesize($new_img_file[$key]['file']);
        // this rule is for div content "tab_flag"
        $content_style = 'width:'. $width .'px; height:'. $height.'px;';
        // create css rules
        $new_style = '';
        $new_style .= 'margin-right:5px;';
        $new_style .= 'background-image: none;';
        $new_style .= $content_style;
        // create html tag for show this image
        $tagHtml = ' <img src="'. $new_img_file[$key]['url'] .
          '" style="'.$new_style.'" class="flag_32x32 flag-' .
            Codes::languageCode2CountryCode($value). '" alt="' . $value . '" title="' .
            $key . '" /> ';
      }
    ?>
    <div class="tab_flag <?=$grayClass?>" style="<?=$content_style;?>"><?= uls_get_link($postId, $value, $tagHtml); ?></div>
  <?php endforeach; ?>
</div>
