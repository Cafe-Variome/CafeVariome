$(document).ready(function() {
  if ($('#pipelinestable').length) {
      $('#pipelinestable').DataTable();
  }
});

$(function() {
  $('#subject_id_location').on('change', function(e){
    if($(e.currentTarget).val() == "1"){
      $('#subject_id_attribute_name').val("");
      $('#subject_id_attribute_name').hide();
      $('#subject_id_attribute_name').parent().find('label').hide()
    }

    if ($(e.currentTarget).val() == "0") {
      $('#subject_id_attribute_name').show();
      $('#subject_id_attribute_name').parent().find('label').show()
    }
  });

  $('#grouping').on('change', function(e){
    if($(e.currentTarget).val() == "0"){
      $('#group_columns').val("");
      $('#group_columns').hide();
      $('#group_columns').parent().find('label').hide()
    }

    if ($(e.currentTarget).val() == "1") {
      $('#group_columns').show();
      $('#group_columns').parent().find('label').show()
    }
  }).change();
});