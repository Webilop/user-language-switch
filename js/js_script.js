jQuery(document).ready(function() {
  jQuery("select#uls_language").change(
    function() {
      val = jQuery(this).val();
      if( val != '' ){
        jQuery("select[name^='uls_translation_']").removeAttr('disabled');
        jQuery("select#uls_translation_"+val.toLowerCase()).attr('disabled', 'disabled');
      }
    }
  );
  var selected_language = jQuery("select#uls_language").val();
  jQuery("select[name^='uls_translation_']").each(function(){
    var select_value = jQuery(this).val();
    if(select_value != ''){
      var delete_link = '<a href="#" onclick="uls_remove_association(\''+select_value+'\',\''+jQuery(this).attr('id')+'\',\''+selected_language+'\');return false;" id="remove_'+jQuery(this).attr('id')+'" title="Remove association">Remove association</a>';
      jQuery(this).closest('td').append(delete_link);
    }
    if(jQuery(this).attr('id')=='uls_translation_'+selected_language.toLowerCase()){
      jQuery(this).attr('disabled', 'disabled');
    }
  });
});

function uls_remove_association(str,name,lang){
  if (str==''){
    return;
  }
  var post_id = uls_getUrlVars()["post"];
  var data = {
    action: 'test_response',
    pid: str,
    lang: lang,
    post: post_id,
    meta: name
  };
  // the_ajax_script.ajaxurl is a variable that will contain the url to the ajax processing file
  jQuery.post(the_ajax_script.ajaxurl, data, function(response) {
    jQuery("#remove_"+name).remove();
    jQuery("#"+name).val('');
  });
  return false;
}

function uls_getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}
