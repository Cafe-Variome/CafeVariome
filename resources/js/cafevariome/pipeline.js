$(document).ready(function() {
  if ($('#pipelinestable').length) {
      $('#pipelinestable').DataTable();
  }
});

$(function() {
  $('#subject_id_location').on('change', function(e){
    var subjectIDLocation = $(e.currentTarget).val();
    if (subjectIDLocation == '0') {
      // SUBJECT_ID_WITHIN_FILE
      hideSubjectIdBatchSizeAndPrefix();
      showSubjectIdAttributeName();
      hideSubjectIdExpansionOnColumns();
    }

    if(
        subjectIDLocation == '1' ||
        subjectIDLocation == '3')
    {
      // SUBJECT_ID_IN_FILE_NAME OR SUBJECT_ID_PER_FILE
      hideSubjectIdAttributeName();
      hideSubjectIdBatchSizeAndPrefix();
      hideSubjectIdExpansionOnColumns();
    }

    if (subjectIDLocation == '2') {
      // SUBJECT_ID_PER_BATCH_OF_RECORDS
      hideSubjectIdAttributeName();
      showSubjectIdBatchSizeAndPrefix();
      hideSubjectIdExpansionOnColumns();
    }

    if(subjectIDLocation == '4'){
      //SUBJECT_ID_BY_EXPANSION_ONCOLUMNS
      hideSubjectIdBatchSizeAndPrefix();
      hideSubjectIdAttributeName();
      showSubjectIdExpansionOnColumns();
    }
  }).change();

  $('#grouping').on('change', function(e){
    if($(e.currentTarget).val() == '0'){
      $('#group_columns').val('');
      $('#group_columns').hide();
      $('#group_columns').parent().find('label').hide();
    }

    if ($(e.currentTarget).val() == '1') {
      $('#group_columns').show();
      $('#group_columns').parent().find('label').show();
    }
  }).change();
});

function hideSubjectIdAttributeName() {
  $('#subject_id_attribute_name').val('');
  $('#subject_id_attribute_name').hide();
  $('#subject_id_attribute_name').parent().find('label').hide();
}

function showSubjectIdAttributeName() {
  $('#subject_id_attribute_name').show();
  $('#subject_id_attribute_name').parent().find('label').show();
}

function hideSubjectIdBatchSizeAndPrefix() {
  $('#subject_id_prefix_batch').hide();
  $('#subject_id_prefix').val('');
  $('#subject_id_batch_size').val('1');
}

function showSubjectIdBatchSizeAndPrefix() {
  $('#subject_id_prefix_batch').show();
}

function hideSubjectIdExpansionOnColumns() {
  $('#subject_id_expansion_on_columns').hide();
  $('#subject_id_expansion_columns').val('');
  $('#subject_id_expansion_policy').val(null);
}

function showSubjectIdExpansionOnColumns() {
  $('#subject_id_expansion_on_columns').show();
}