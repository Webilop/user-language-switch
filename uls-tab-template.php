<?php
$languages = uls_get_available_languages();
$options = get_option('uls_settings');
$position = $options['tab_position_language_switch']; 


$TabStyle = "";
$TabBackgroud = ($options['tab_color_picker_language_switch']) ? "" : "";; 
$TabFixed = ($options['fixed_position_language_switch']) ? "position: fixed;" : "position: absolute;";
switch($position){
   case 'TL':
      $TabStyle = "#tab_background_color_picker{
			top: 0;
			left: 0;
			width: auto;
			margin-left: 4px;".
			$TabFixed."
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
			width: auto;
			margin-left: 4px;".
			$TabFixed."
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
			width: auto;
			margin-right: 4px;".
			$TabFixed."
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
			width: auto;
			margin-left: 4px;".
			$TabFixed."
			z-index: 10000000000;
		  }
		  .tab_flag {
		     	display: inline;
		  }";
      break;
   case 'BC':
      $TabStyle = "#tab_background_color_picker{
			bottom: 0;
			left: 50%;
			width: auto;
			margin-left: 4px;".
			$TabFixed."
			z-index: 10000000000;
		  }
		  .tab_flag {
		     	display: inline;
		  }";
      break;
   case 'BR':
      $TabStyle = "#tab_background_color_picker{
			bottom: 0;
			right: 0;
			width: auto;
			margin-right: 4px;".
			$TabFixed."
			z-index: 10000000000;
		  }
		  .tab_flag {
		     	display: inline;
		  }";
      break;
   case 'LT':
      $TabStyle = "#tab_background_color_picker{
			top: 0;
			left: 0;
			height: auto;
			margin-left: 4px;".
			$TabFixed."
			z-index: 10000000000;
		     }";
      break;
   case 'LM':
      $TabStyle = "#tab_background_color_picker{
			top: 50%;
			left: 0;
			height: auto;
			margin-left: 4px;".
			$TabFixed."
			z-index: 10000000000;
		     }";
      break;
   case 'LB':
      $TabStyle = "#tab_background_color_picker{
			bottom: 0;
			left: 0;
			height: auto;
			margin-left: 4px;".
			$TabFixed."
			z-index: 10000000000;
		     }";
      break;
   case 'RT':
      $TabStyle = "#tab_background_color_picker{
			top: 0;
			right: 0;
			height: auto;
			margin-right: 4px;".
			$TabFixed."
			z-index: 10000000000;
		     }";
      break;
   case 'RM':
      $TabStyle = "#tab_background_color_picker{
			top: 50%;
			right: 0;
			height: auto;
			margin-right: 4px;".
			$TabFixed."
			z-index: 10000000000;
		     }";
      break;
   case 'RB':
      $TabStyle = "#tab_background_color_picker{
			bottom: 0;
			right: 0;
			height: auto;
			margin-right: 4px;".
			$TabFixed."
			z-index: 10000000000;
		     }";
      break;
}
?>

<style type="text/css">
   <?= $TabStyle; ?>
</style>
<div id="tab_background_color_picker">
   <?php foreach ($languages as $key => $value) : ?>
   	<div class="tab_flag">
	<img src="<?= plugins_url("css/blank.gif", __FILE__); ?>" style="margin-right:5px;" class="flag_32x32 flag-<?= strtolower(substr($value, -2));?>" alt="<?= $value; ?>" />
	</div>
   <?php endforeach; ?> 
</div>
