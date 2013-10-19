$ = jQuery;
$(document).ready(function() {
  $("select#uls_language").change(
     function() {
         val = $(this).val();
         if( val != '' ){
            $("select[name^='uls_translation_']").removeAttr('disabled');
            $("select#uls_translation_"+val.toLowerCase()).attr('disabled', 'disabled');
         }
     }
  );
   var selected_language = $("select#uls_language").val();
  $("select[name^='uls_translation_']").each(function(){
      var select_value = $(this).val();
      if(select_value != ''){
         var delete_link = '<a href="#" onclick="remove_association(\''+select_value+'\',\''+$(this).attr('id')+'\',\''+selected_language+'\');return false;" id="remove_'+$(this).attr('id')+'" title="Remove association">Remove association</a>';
         $(this).closest('td').append(delete_link);
      }
      if($(this).attr('id')=='uls_translation_'+selected_language.toLowerCase()){
         $(this).attr('disabled', 'disabled');
      }
  });
});

function remove_association(str,name,lang){
   if (str==''){
     return;
   }
   var post_id = getUrlVars()["post"];
   var data = {
         action: 'test_response',
         pid: str,
         lang: lang,
         post: post_id,
         meta: name
   };
   // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
   $.post(the_ajax_script.ajaxurl, data, function(response) {
         $("#remove_"+name).remove();
         $("#"+name).val('');
   });
   return false;
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}
