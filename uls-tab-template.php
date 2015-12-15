<?php
$languages = uls_get_available_languages();
$options = get_option('uls_settings');
$position = $options['tab_position_language_switch'];

global $wp_query;
if (is_home() || is_archive() || is_search() )
  $postId = null;
else
  $postId = $wp_query->post->ID;

?>
<div id="tab_background_color_picker">
  <?php foreach ($languages as $key => $value) : ?>
    <div class="tab_flag">
      <?php
      $tagHtml = ' <img src="'. plugins_url("css/blank.gif", __FILE__). '" style="margin-right:5px;" class="flag_32x32 flag-'. strtolower(substr($value, -2)) .'" alt="'. $value .'" title="'.$key.'" /> ';
      echo uls_get_link($postId, $value, $tagHtml);
      ?>
    </div>
  <?php endforeach; ?>
</div>
