function regenElastic(id,append) {
    if (append) {
        callElastic(id,true);
    }
    else {
        force = confirm('A forced Regeneration will completely rebuild your ElasticSearch Index. Do you wish to continue?');
        if (force) {
            callElastic(id,false);
        }
        else{
            return;
        }
    }
}

function callElastic(id, append) {

    var postData = {'source_id': id, 'append': append};
    var csrfTokenObj = getCSRFToken('keyvaluepair');
    var csrfTokenName = Object.keys(csrfTokenObj)[0];
    postData[csrfTokenName] = csrfTokenObj[csrfTokenName];

    $.ajax({url  : baseurl + 'AjaxApi/elasticStart',
        type: 'POST',
        data : postData,
        dataType: 'json',
        success: function (data) {
            $.notify({
                // options
                message: 'ElasticSearch is now regenerating.'},{
                // settings
                timer: 200
            });
        }
    });

    $('#status-' + id.toString()).empty();
    $('#status-' + id.toString()).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + id.toString() + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0'>0%</div></div><p id='statusmessage-" + id.toString() + "' style='font-size: 10px'></p>");

    $('#action-' + id.toString()).children().prop('disabled', true);

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

$(document).ready(function() {
    var eventSource = new EventSource(baseurl + 'ServiceApi/pollElasticSearch');

    eventSource.onmessage = function(event) {
        id = event.lastEventId;
        edata = JSON.parse(event.data);
        progress = edata.progress;
        status = edata.status;

        if (progress > -1) {
            if($('#progressbar-' + id.toString()).length){
                $('#progressbar-' + id.toString()).removeClass('bg-success');

                $('#progressbar-' + id.toString()).text(progress.toString() + '%');
                $('#progressbar-' + id.toString()).css( "width", progress.toString() + '%');
                $('#statusmessage-' + id.toString()).html(status);

            }
            else{
                $('#status-' + id.toString()).empty();
                $('#status-' + id.toString()).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + id.toString() + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0'>0%</div></div><p id='statusmessage-" + id.toString() + "' style='font-size: 10px'>" + status + "</p>")
                $('#action-' + id.toString()).children().prop('disabled', true);

                $('#progressbar-' + id.toString()).text(progress.toString() + '%');
                $('#progressbar-' + id.toString()).css('width', progress.toString() + '%');
            }
        }

        if(progress == 100 && status.toLowerCase() == 'finished')
        {
            $('#progressbar-' + id.toString()).addClass('bg-success');
            $('#action-' + id.toString()).children().prop('disabled', false);
        }
    };

    eventSource.onerror = function(err) {
    };
});