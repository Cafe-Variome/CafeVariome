function regenElastic(id,overwrite) {
    if (overwrite) {
        force = confirm('A forced Regeneration will completely rebuild your ElasticSearch Index. Do you wish to continue?');
        if (force) {
            callElastic(id,true);
        }
        else{
            return;
        }
    }
    else {
        callElastic(id,false);
    }
}

function callElastic(source_id, overwrite) {

    var postData = {'source_id': source_id, 'overwrite': overwrite};
    var csrfTokenObj = getCSRFToken('keyvaluepair');
    var csrfTokenName = Object.keys(csrfTokenObj)[0];
    postData[csrfTokenName] = csrfTokenObj[csrfTokenName];

    $.ajax({url  : baseurl + 'AjaxApi/IndexDataToElasticsearch',
        type: 'POST',
        data : postData,
        dataType: 'json',
        beforeSend:  function (jqXHR, settings) {
            enterLoading();
        },
        success: function (response) {
            switch (response.status) {
                case 0:
                    $('#statusMessage').addClass('text-success');
                    $('#statusMessage').html(response.message);

                    var taskId = response.task_id;
                    $('#lastTaskId').val(taskId);
                    $('#status-' + source_id).empty();
                    $('#status-' + source_id).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + taskId + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0'>0%</div></div><p id='statusmessage-" + taskId + "' style='font-size: 10px'></p>");
                    $('#action-' + source_id).children().prop('disabled', true);
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
}

function getCSRFToken(format = 'string'){
    csrf_token = $('#csrf_token').val();
    csrf_token_name = $('#csrf_token').prop('name');

    switch (format) {
        case 'string':
            return csrf_token_name + '=' + csrf_token;
        case 'keyvaluepair':
            var csrfObj = {};
            csrfObj[csrf_token_name] = csrf_token;
            return csrfObj;
    }
}

function enterLoading() {
    clearStatusMessage();
    $('#spinner').show();
    $('#processBtn').prop('disabled', true);
}

function exitLoading() {
    $('#spinner').hide();
    $('#processBtn').prop('disabled', false);
}

function clearStatusMessage()
{
    $('#statusMessage').html('');
    $('#statusMessage').attr('class', '');
}

$(document).ready(function() {
    var eventSource = new EventSource(baseurl + 'ServiceApi/PollTasks');

    eventSource.onmessage = function(event) {
        id = event.lastEventId;
        var edata = JSON.parse(event.data);
        var progress = edata.progress;
        var status = edata.status;
        var idString = id.toString();
        var sourceId = edata.source_id;
        var lastTaskId = $('#lastTaskId').val();

        if (progress > -1 && lastTaskId == idString) {
            if($('#status-' + sourceId).html() == '')
            {
                $('#status-' + sourceId).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + idString + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>0%</div></div><p id='statusmessage-" + idString + "' style='font-size: 10px'></p>");
            }
            $('#progressbar-' + idString).text(progress.toString() + '%');
            $('#progressbar-' + idString).css( 'width', progress.toString() + '%' );
            $('#statusmessage-' + idString).html(status);
        }

        if(progress == 100 && status.toLowerCase() == 'finished')
        {
            $('#progressbar-' + idString).addClass('bg-success');
            $('#action-' + sourceId).children().prop('disabled', false);
            clearStatusMessage();
        }
    };

    eventSource.onerror = function(err) {
    };
});