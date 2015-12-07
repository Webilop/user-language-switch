jQuery(document).ready(function($) { 
  var actual_value = jQuery("select#uls_language").val(); 
  jQuery("select#uls_language").change(
    function() {
      val = jQuery(this).val();
      if( val != actual_value && actual_value != '' ){
        alert("If you change the current language, then it will be changed in other pages that use it as translation");
      }
    }
  );
});
