<div id="tab_background_color_picker">
  <?php foreach ($languages as $key => $value) : ?>
    <div class="tab_flag">
      <?php $tagHtml = ' <img src="'. plugins_url("css/blank.gif", __FILE__). '" style="margin-right:5px;" class="flag_32x32 flag-'. strtolower(substr($value, -2)) .'" alt="'. $value .'" /> ';

          echo uls_get_link($postId, $value, $tagHtml);
?>
    </div>
  <?php endforeach; ?> 
</div>
