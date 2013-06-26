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
   if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
     xmlhttp=new XMLHttpRequest();
     }
   else{// code for IE6, IE5
     xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
     }
   xmlhttp.onreadystatechange=function(){
     if (xmlhttp.readyState==4 && xmlhttp.status==200){
       $("#remove_"+name).remove();
       $("#"+name).val('');
       }
     }
   var post_id = getUrlVars()["post"];
   xmlhttp.open("GET","../wp-content/plugins/user-language-switch/remove_assoc.php?pid="+str+"&lang="+lang+"&post="+post_id+"&meta="+name,true);
   xmlhttp.send();
}

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}
