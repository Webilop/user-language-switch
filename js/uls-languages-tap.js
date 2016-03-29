
jQuery(document).ready(function($) {
  jQuery('#button-download-language').click(function () {
    jQuery("#div_message_download").html("Downloading language...");

    var language = $("#tblang").val();
    $.post(ajaxurl, {
      action: 'uls_download_language',
      info_language: language
    }, function(data) {
      window.location.href = window.location + "&success=" + data;
    });
  });

});
