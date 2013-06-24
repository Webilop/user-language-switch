$ = jQuery;
$(document).ready(function() {
  $("select#uls_language").change(
     function() {
         val = $(this).val();
         if( val != '' ){
            $("select[name^='uls_translation_']").removeAttr('disabled');
            $("select#uls_translation_"+val).attr('disabled', 'disabled');
         }
     }
  );
});

