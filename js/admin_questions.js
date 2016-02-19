
/* function to answere the question about contact a professional traductor */
jQuery(document).ready(function($) {

  $('.uls_answere_button').on('click', function(){
    var button_answere_value = $(this).attr('name');
    $.post(ajaxurl, {
      action: 'uls_answere_question_contact',
      button_answere_value: button_answere_value
    }, function(data) {
      $('#content_question_display').html(data);
    });
  });
});
