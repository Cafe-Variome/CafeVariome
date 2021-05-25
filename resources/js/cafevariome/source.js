$(document).ready(function() {
    // If the table exists, populate DataTable and make it stylish.
    if ($('#sourcestable').length) {
        $('#sourcestable').DataTable();
    }

    $('#ImportRecordsBtn').tooltip(); //Tooltip for import records added as the data-toggle attribute is used to trigger the modal
    
    if ($('#count_display').length) {
        $('#count_display').select2({width:'100%'});
    }

    if ($('#source_display').length) {
        $('#source_display').select2({width:'100%'});
    }
});

// Select groups on submission of form
function select_groups() {
    $(".groupsSelected").find('option').each(function () {
        $(this).attr('selected', 'selected');
    });
}

// Set proper hyperlinks to uploaders in the modal
$('#addVariantsModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); 
    var sourceId = button.data('id'); 
    var srcname = button.data('srcname'); 
    // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
    // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
    var modal = $(this);
    modal.find('.modal-title').text('Add Records To ' + srcname);
    modal.find('#bulkImport').attr('href', baseurl + "Upload/Spreadsheet/" + sourceId);
    modal.find('#phenoPacketsImport').attr('href', baseurl + "Upload/Phenopacket/" + sourceId);
    modal.find('#VCFImport').attr('href', baseurl + "Upload/VCF/" + sourceId);
    modal.find('#UniversalImport').attr('href', baseurl + "Upload/Universal/" + sourceId);
  })

