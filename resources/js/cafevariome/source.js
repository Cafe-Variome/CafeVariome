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
$('#uploadModal').on('show.bs.modal',function(event){
    var button = $(event.relatedTarget); 
    var sourceId = button.data('id'); 
    var modal = $(this);
    modal.find('#bulkImport').attr('href', baseurl + "Upload/Spreadsheet/" + sourceId);
    modal.find('#phenoPacketsImport').attr('href', baseurl + "Upload/Phenopacket/" + sourceId);
    modal.find('#VCFImport').attr('href', baseurl + "Upload/VCF/" + sourceId);
    modal.find('#importModal').attr('href', baseurl + "Upload/Import/" + sourceId);
})

$('#indicesModal').on('show.bs.modal',function(event){
    var button = $(event.relatedTarget); 
    var sourceId = button.data('id'); 
    var modal = $(this);
    modal.find('#ESIndex').attr('href', baseurl + "Source/Elasticsearch/" + sourceId);
    modal.find('#NeoIndex').attr('href', baseurl + "Source/Neo4J/" + sourceId);
    modal.find('#UIIndex').attr('href', baseurl + "Source/UserInterface/" + sourceId);
})

$('#sourcesModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); 
    var sourceId = button.data('id');   
    var modal = $(this);
    modal.find('#srcValues').attr('href', baseurl + "Attribute/List/" + sourceId);
    modal.find('#srcEdit').attr('href', baseurl + "Source/Update/" + sourceId);
    modal.find('#srcDelete').attr('href', baseurl + "Source/Delete/" + sourceId);
})