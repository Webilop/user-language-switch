<?php
global $wp_query;

$languages = uls_get_available_languages();
$postId = null;
if (!is_home() && !is_archive() && !is_search() && !is_404()) {
  $postId = $wp_query->post->ID;
}
?>
<div id="tab_background_color_picker">
  <?php foreach ($languages as $key => $value) : ?>
    <?php
    $tagHtml = ' <img src="'. plugins_url('css/blank.gif', __FILE__) .
      '" style="margin-right:5px;" class="flag_32x32 flag-' .
      strtolower(substr($value, -2)) . '" alt="' . $value . '" title="' .
      $key . '" /> ';
    ?>
    <div class="tab_flag"><?= uls_get_link($postId, $value, $tagHtml); ?></div>
  <?php endforeach; ?>
</div>
