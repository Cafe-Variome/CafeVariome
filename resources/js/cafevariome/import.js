$("#importFiles").submit(function(event) {  
    event.preventDefault();
});

function lookupDir() {

    var lookup_dir = $('#lookupPath').val();
    var csrf_token = $('#csrf_token').val();
    var csrf_token_name = $('#csrf_token').prop('name');

    if (lookup_dir == null || lookup_dir == '') {
        $('#lookupPath').addClass('is-invalid');
        return;
    }
    else{
        $('#lookupPath').removeClass('is-invalid');
    }

    var fileData = new FormData();
    fileData.append('lookup_dir', $('#lookupPath').val());
    fileData.append(csrf_token_name, csrf_token);

    $.ajax({
        type: 'POST',
        url: baseurl+'AjaxApi/lookupDirectory',
        data: fileData,
        dataType: "json", 
        contentType: false,
        processData: false,   
        beforeSend: function (jqXHR, settings) {
            showLoader();
            clearError();
            disableLookup();
            hideImportBtn();
        },
        success: function(response)  {
            count = JSON.parse(response);
            $('#lookupCount').text(count + ' file(s) were found.');
            count > 0 ? showImportBtn() : hideImportBtn();
        },
        error: function(jqXHR, textStatus, errorThrown) {
            hideImportBtn();
            showError(textStatus, errorThrown)
        },
        complete: function (jqXHR, settings) {
            hideLoader();
            enableLookup();
        }
    });
}

function importDir() {
    var source_id = $('#source_id').val();
    var pipeline_id = $('#pipeline').val();
    var csrf_token = $('#csrf_token').val();
    var csrf_token_name = $('#csrf_token').prop('name');

    if (pipeline_id == null || pipeline_id == '' || pipeline_id == -1) {
        $('#pipeline').addClass('is-invalid');
        return;
    }
    else{
        $('#pipeline').removeClass('is-invalid');
    }

    var fileData = new FormData();
    fileData.append('lookup_dir', $('#lookupPath').val());
    fileData.append('source_id', $('#source_id').val());
    fileData.append('pipeline_id', pipeline_id);
    fileData.append('user_id', $('#user_id').val());
    fileData.append(csrf_token_name, csrf_token);

    $.ajax({
        type: 'POST',
        url: baseurl+'AjaxApi/importFromDirectory',
        data: fileData,
        dataType: "json", 
        contentType: false,
        processData: false,  
        beforeSend:  function (jqXHR, settings) {
            showLoader();
            clearError();
            disableLookup();
            disableImport();
        },
        success: function(response)  {
            textStatus = response.saved_count + ' file(s) were imported successfully.';
            if (response.unsaved_count > 0) {
                textStatus += response.unsaved_count + ' file(s) failed to get imported.'
            }

            $('#lookupCount').text(textStatus);
            reloadTable(source_id,false, true);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            showError(textStatus, errorThrown)
        },
        complete: function (jqXHR, settings){
            hideLoader();
            enableLookup();
            enableImport();
        }
    });
}

function showLoader() {
    $('#spinner').show();
}

function hideLoader() {
    $('#spinner').hide();
}

function showLookupBtn() {
    $('#lookupBtn').show();
}

function hideLookupBtn() {
    $('#lookupBtn').hide();
}

function showImportBtn() {
    $('#importBtn').show();
}

function hideImportBtn() {
    $('#importBtn').hide();
}

function disableImport() {
    $('#importBtn').prop('disabled', true);
    $('#pipeline').prop('disabled', true);
}

function enableImport() {
    $('#importBtn').prop('disabled', false);
    $('#pipeline').prop('disabled', false);
}

function showError(textStatus, errorThrown) {
    $('#lookupCount').text(textStatus + ': ' + errorThrown);
    $('#lookupCount').addClass('text-danger');
}

function clearError() {
    $('#lookupCount').text('');
    $('#lookupCount').removeClass('text-danger');
}

function disableLookup() {
    $('#lookupPath').prop('disabled', true);
    $('#lookupBtn').prop('disabled', true);
}

function enableLookup() {
    $('#lookupPath').prop('disabled', false);
    $('#lookupBtn').prop('disabled', false);
}