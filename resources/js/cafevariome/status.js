var tooltipObj;

$(document).ready(function() {
    param = $('#source_id').val();
    reloadTable(param,true);

    let eventSource = new EventSource(baseurl + "ServiceApi/pollUploadedFiles");

    eventSource.onmessage = function(event) {
        id = event.lastEventId;
        $progress = event.data;

        if ($progress > -1) {
            if($('#progressbar-' + id.toString())){
                $('#fActionOverwrite').prop('disabled', true);

                $('#progressbar-' + id.toString()).text(event.data.toString() + '%');
                $('#progressbar-' + id.toString()).css( "width", event.data.toString() + "%" );
            }
        }
        else if(id == 0){
            $('#fActionOverwrite').prop('disabled', false);
        }

        if(event.data == 100)
        {
            $('#progressbar-' + id.toString()).addClass('bg-success');
            //reloadTable(param,false);
        }
    };

    eventSource.onerror = function(err) {
    };

})

function reloadTable(param,first) {
    //return;
    if ($('.dataTables_filter input').is(":focus")) {
        return;
    }
	$.ajax({
        type: "POST",
        url: baseurl+'AjaxApi/getSourceStatus/' + param,
        dataType: "json",
        success: function(response) {
            currentscroll = $(window).scrollTop();
        	if (!first) {
                $('#file_table').dataTable().fnDestroy();
        	}
        	$("#file_grid").empty();

            $('#fActionOverwrite').prop('disabled', response.Files.length == 0);

        	for (var i = 0; i < response.Files.length; i++) {
        		$("#file_grid").append("<tr id='file_"+ response.Files[i].ID + "'><td>" + response.Files[i].FileName + "</td><td>" + response.Files[i].email + "</td></tr>");

                if (response.Files[i].Status == 'Pending') {
                    $('#file_' + response.Files[i].ID).append("<td><div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + response.Files[i].ID + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>0%</div></div></td>");
                }
                else if(response.Files[i].Status == 'Success'){
                    $('#file_' + response.Files[i].ID).append("<td><a href='#' data-toggle='tooltip' data-placement='top' title='Data has been successfully processed for this file.'><i class='fa fa-check text-success'></i></a> <a href='#' data-toggle='tooltip' data-placement='top' data-html='true' title='Upload Start:" + response.Files[i].uploadStart + " <br/> Upload End: " + response.Files[i].uploadEnd + "'><i class='fa fa-info text-info'></i></a> </td>");
                }
                else if (response.Error.length > 0) {
                    ErrorString = "";

                    for (var t = 0; t < response.Error.length; t++) {
                        if (response.Files[i].ID == response.Error[t].error_id) {
                            ErrorString += response.Error[t].message + '<br/>'
                            response.Error.splice(t, 1); 
                        }
                    }
                    $('#file_' + response.Files[i].ID).append("<td><a href='#' data-toggle='tooltip' data-html='true' data-placement='top' title='" + ErrorString + "'><i class='fa fa-exclamation-triangle text-warning'></i></a> </td>");
                }
                $('#file_' + response.Files[i].ID).append("<td><a href='#' data-toggle='tooltip' data-placement='top' title='Remove Records and Re-process File' onclick='processFile(" + response.Files[i].ID + ", 1)'><i class='fa fa-redo-alt text-info'></i></a> <a href='#' data-toggle='tooltip' data-placement='top' title='Re-process File and Append Records' onclick='processFile(" + response.Files[i].ID + ", 0)'><i class='fa fa-sync-alt text-warning'></i></a></td>");
            }  
            filesDt();  
            $(window).scrollTop(currentscroll);  
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
}

function filesDt() {
    if ($('#file_table').length) {
        $('#file_table').dataTable( {
        "sDom": "<'row'<'col 'l><'col'f>r>t<'row'<'col 'i><'col'p>>",
        //"sPaginationType": "bootstrap",
        "oLanguage": {
            "sLengthMenu": "_MENU_ records per page"
        }//, 
        //"aLengthMenu": [[5, 10, 25, 50, 100, 200, -1], [5, 10, 25, 50, 100, 200, "All"]]
        } );        
    }

}
