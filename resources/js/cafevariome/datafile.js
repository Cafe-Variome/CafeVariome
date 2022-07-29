$(document).ready(function() {
    if ($('#datafilestable').length) {
        $('#datafilestable').DataTable();
    }

    let eventSource = new EventSource(baseurl + "ServiceApi/PollTasks");

    eventSource.onmessage = function(event) {
        id = event.lastEventId;
        var edata = JSON.parse(event.data);
        var progress = edata.progress;
        var status = edata.status;
        var idString = id.toString();
        var fileId = edata.data_file_id;
        if (progress > -1) {
            if($('#progress-' + idString).length){
                $('#actionBtns-' + idString).children().hide();
                if($('#progress-' + idString).html() == '')
                {
                    $('#progress-' + idString).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + idString + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>0%</div></div><p id='statusmessage-" + idString + "' style='font-size: 10px'></p>");
                }
                $('#progressbar-' + idString).text(progress.toString() + '%');
                $('#progressbar-' + idString).css( 'width', progress.toString() + '%' );
                $('#statusmessage-' + idString).html(status);
            }
            else{
                $('#action-' + fileId).append("<div id='progress-" + idString.toString() + "'></div>");
            }
        }

        if(progress == 100 && status.toLowerCase() == 'finished')
        {
            $('#progressbar-' + id.toString()).addClass('bg-success');
            $('#actionBtns-' + id.toString()).children().show();
            $('.reprocess').show();
        }
    };

    eventSource.onerror = function(err) {
    };

});

$('#name').on('change',function(){
    var fullFileName = $(this).val();
    var fileName = fullFileName.split('\\')[fullFileName.split('\\').length - 1];
    $(this).next('.custom-file-label').html(fileName);

    var selectedFileSize = $('#name')[0].files[0].size;
    var maxUploadFileSize = $('#maxUploadSize').data('bytevalue');
    $('#selectedFileSize').html((selectedFileSize/1048576).toFixed(2)+ ' MB');

    if (selectedFileSize > maxUploadFileSize){
        $('#uploadWarningText').html('Selected file size is larger than the maximum allowed file size for upload. Upload cannot proceed. Please contact the server administrator to increase the upload size or select another file.');
        $('#uploadWarningAlert').show();
        $('#uploadBtn').prop('disabled', 'disabled');
    }
    else
    {
        $('#uploadWarningAlert').hide();
        $('#uploadBtn').prop('disabled', false);
    }
});

$('#taskModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget);
    var fileId = button.data('fileid');
    var fileName = button.data('filename');
    var modal = $(this);
    modal.find('#fileId').val(fileId);
    modal.find('#fileName').text(fileName);
});

$('#taskModal').on('hide.bs.modal', function (event) {
    var modal = $(this);
    modal.find('#fileId').val('-1');
    modal.find('#fileName').text('');
    modal.find('#pipeline').val(-1);
    modal.find('#statusMessage').html('');
    modal.find('#statusMessage').attr('class', '');
});

$('#processBtn').on('click',function(event) {
    event.preventDefault();
    var fileId = $('#fileId').val();
    var pipelineId = $('#pipeline').val();

    var csrfTokenObj = getCSRFToken('keyvaluepair');
    var formData = {'fileId': fileId, 'pipelineId': pipelineId};
    var csrfTokenName = Object.keys(csrfTokenObj)[0];
    formData[csrfTokenName] = csrfTokenObj[csrfTokenName];

    $.ajax({
        type: 'POST',
        url: baseurl + 'AjaxApi/ProcessFile',
        data: formData,
        dataType: 'json',
        beforeSend:  function (jqXHR, settings) {
            enterLoading();
        },
        success: function(response)  {

            switch (response.status){
                case 0:
                    $('#statusMessage').addClass('text-success');
                    $('#statusMessage').html(response.message);

                    // Close modal
                    $('#taskModal').modal('hide');

                    var taskId = response.task_id;
                    $('#action-' + fileId).append("<div id='progress-" + taskId.toString() + "'></div>");

                    //hide action div
                    $('#actionBtns-' + fileId).hide();
                    break;
                case 1:
                    $('#statusMessage').addClass('text-danger');
                    $('#statusMessage').html('There was an error while processing the request: <br> Error Message: ' + response.message);
                    break;
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $('#statusMessage').addClass('text-danger');
            $('#statusMessage').html('There was an error while processing the request: <br> Error Code: ' + jqXHR.status + '<br> Error Message: ' + errorThrown);

        },
        complete: function (jqXHR, settings) {
            exitLoading();
        }
    });
});

function enterLoading() {
    $('#statusMessage').html('');
    $('#statusMessage').attr('class', '');
    $('#spinner').show();
    $('#processBtn').prop('disabled', true);
    $('#cancelBtn').prop('disabled', true);
}

function exitLoading() {
    $('#spinner').hide();
    $('#processBtn').prop('disabled', false);
    $('#cancelBtn').prop('disabled', false);
}

function getCSRFToken(format = 'string'){
    csrf_token = $('input[type=hidden]').val();
    csrf_token_name = $('input[type=hidden]').prop('name');

    switch (format) {
        case "string":
            return csrf_token_name + '=' + csrf_token;
        case "keyvaluepair":
            var csrfObj = {};
            csrfObj[csrf_token_name] = csrf_token;
            return csrfObj;
    }
}
