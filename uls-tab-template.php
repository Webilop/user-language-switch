<?php
global $wp_query;
$shortcode_class = isset($shortcode_class) ? $shortcode_class : 'tab_background_color_picker';

$languages = uls_get_available_languages();
$postId = null;
if (!is_home() && !is_archive() && !is_search() && !is_404()) {
  $postId = $wp_query->post->ID;
}
?>
  <div id="<?= $shortcode_class;?>">
  <?php foreach ($languages as $key => $value) : ?>
    <?php
      $tagHtml = ' <img src="'. plugins_url('css/blank.gif', __FILE__) .
      '" style="margin-right:5px;" class="flag_32x32 flag-' .
      Codes::languageCode2CountryCode($value). '" alt="' . $value . '" title="' .
      $key . '" /> ';
      $grayClass = '';
      if (!is_null($postId)) {
        $hasTranslation = uls_get_post_translation_id($postId, $value) !== false;
        $grayClass = $hasTranslation ? '' : 'uls-grayscale';
      }
    ?>
    <div class="tab_flag <?=$grayClass?>"><?= uls_get_link($postId, $value, $tagHtml); ?></div>
  <?php endforeach; ?>
</div>
