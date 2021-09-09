var tooltipObj;
var isImport = false;
var filesDtPage = 0;

selectd_fileids = [];

$(document).ready(function() {
    isImport = window.location.href.split('/').indexOf('Import') > 0;
    param = $('#source_id').val();
    reloadTable(param,true, isImport);

    let eventSource = new EventSource(baseurl + "ServiceApi/pollUploadedFiles");

    eventSource.onmessage = function(event) {
        id = event.lastEventId;
        edata = JSON.parse(event.data);
        progress = edata.progress;
        status = edata.status;

        if (progress > -1) {
            if($('#progressbar-' + id.toString()).length){
                $('#fActionOverwrite').prop('disabled', true);
                $('#action-' + id.toString()).children().hide();
                $('.reprocess').hide();

                $('#progressbar-' + id.toString()).text(progress.toString() + '%');
                $('#progressbar-' + id.toString()).css( "width", progress.toString() + "%" );
                $('#statusmessage-' + id.toString()).html(status);
            }
        }
        else if(id == 0){
            $('#fActionOverwrite').prop('disabled', false);
        }

        if(progress == 100 && status.toLowerCase() == 'finished')
        {
            $('#progressbar-' + id.toString()).addClass('bg-success');
            $('#action-' + id.toString()).children().show();
            $('.reprocess').show();
        }
    };

    eventSource.onerror = function(err) {
    };

});

function reloadTable(param,first, chkBox = false) {
    if ($('.dataTables_filter input').is(":focus")) {
        return;
    }
    csrfTokenObj = getCSRFToken('keyvaluepair');
    postData = {'source_id': param};
    var csrfTokenName = Object.keys(csrfTokenObj)[0];
    postData[csrfTokenName] = csrfTokenObj[csrfTokenName];

	$.ajax({
        type: "POST",
        url: baseurl+'AjaxApi/getSourceStatus/',
        data: postData,
        dataType: "json",
        success: function(response) {
            currentscroll = $(window).scrollTop();
        	if (!first) {
                $('#file_table').dataTable().fnDestroy();
        	}
        	$("#file_grid").empty();

            $('#fActionOverwrite').prop('disabled', response.Files.length == 0);
            var pendingFilesCount = 0;
        	for (var i = 0; i < response.Files.length; i++) {

                if (response.Files[i].Status == 'Pending') {
                    pendingFilesCount++;
                }
                var checkBoxStr = "";
                if (chkBox) {
                    checkBoxStr = "<td>";
                    checkBoxStr += "<input type='checkbox' class='fileChkBx' data-fileid='" + response.Files[i].ID + "' onclick='updateFileIDs(event)'></input>";
                    checkBoxStr += "</td>";
                }

        		$("#file_grid").append("<tr id='file_"+ response.Files[i].ID + "'>" + checkBoxStr + "<td>" + response.Files[i].FileName + "</td><td>" + response.Files[i].email + "</td></tr>");

                if (response.Files[i].Status == 'Pending') {
                    $('#file_' + response.Files[i].ID).append("<td><div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + response.Files[i].ID + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='100'>0%</div></div><p id='statusmessage-" + response.Files[i].ID + "' style='font-size: 10px'></p></td>");
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

                actionStr = "<td id='action-" + response.Files[i].ID + "'><a href='#' data-toggle='tooltip' data-placement='top' title='Remove Records and Re-process File' class='reprocess' onclick='processFile(" + response.Files[i].ID + ", 1)'><i class='fa fa-redo-alt text-info'></i></a>";
                actionStr += "</td>";

                $('#file_' + response.Files[i].ID).append(actionStr);

            }

            $('#batchProcessPendingBtn span').html(pendingFilesCount.toString());
        	if (pendingFilesCount == 0){
                $('#batchProcessPendingBtn').prop('disabled', true);
            }
        	else{
                $('#batchProcessPendingBtn').prop('disabled', false);
            }

            filesDt();  
            $(window).scrollTop(currentscroll);  
            $('[data-toggle="tooltip"]').tooltip();
        }
    });
}

function filesDt() {
    if ($('#file_table').length) {
        var table = $('#file_table').DataTable( {
            "sDom": "<'row'<'col 'l><'col'f>r>t<'row'<'col 'i><'col'p>>",
            "columnDefs": [{ targets: 0, orderable: false }],
            "oLanguage": {
                "sLengthMenu": "_MENU_ records per page"
            }, 
            "lengthMenu": [[25, 50, 100], [25, 50, 100]],
        } );  
        
        $('#file_table').DataTable().page(filesDtPage).draw('page');

        $('#file_table').on( 'page.dt', function () {
            $('#file_table thead .chkBxMaster').prop('checked', false);
            var rowsPerPage = $('#file_table tbody tr').length;
            var currentPage = $('#file_table').DataTable().page();
            var startingPoint = currentPage * rowsPerPage;
            var allRowsChecked = true;

            for (var rc = currentPage * rowsPerPage; rc < startingPoint + rowsPerPage; rc++){
                var rowChkBox = $($('#file_table').DataTable().cell({row:rc, column:0}).data());
                if(!rowChkBox.prop('checked')){
                    allRowsChecked = false;
                }
            }
            
            $('#file_table thead .chkBxMaster').prop('checked', allRowsChecked);
        });
        
        $('#file_table thead .chkBxMaster').change(function (){
            checkOrUncheck = $(this).prop('checked');
            fileCount = (parseInt($('#batchProcessBtn span').html()) < 0 && checkOrUncheck) ? 0 : parseInt($('#batchProcessBtn span').html());
            chkBxCount = fileCount;
    
            var rowsPerPage = $('#file_table tbody tr').length;
            var currentPage = $('#file_table').DataTable().page();
            var startingPoint = currentPage * rowsPerPage;
            for (var rc = currentPage * rowsPerPage; rc < startingPoint + rowsPerPage; rc++){

                var rowChkBox = $($('#file_table').DataTable().cell({row:rc, column:0}).data());

                if (rowChkBox.length > 0) {
                    var rowChkBoxHtml;
                    var alreadyChecked = (rowChkBox.prop('checked') && checkOrUncheck) || (!rowChkBox.prop('checked') && !checkOrUncheck);
                    if (rowChkBox.prop('checked') == false && checkOrUncheck){
                        rowChkBoxHtml = rowChkBox.attr('checked', checkOrUncheck).prop('outerHTML');
                    }
                    else if (rowChkBox.prop('checked') && !checkOrUncheck){
                        rowChkBoxHtml = rowChkBox.attr('checked', checkOrUncheck).prop('outerHTML');
                    }
                    else{
                        rowChkBoxHtml = rowChkBox.prop('outerHTML');
                    }
        
                    $('#file_table').DataTable().cell({row:rc, column:0}).data(rowChkBoxHtml);
                    if (!alreadyChecked){
                        updateFileIDs($($('#file_table').DataTable().cell({row:rc, column:0}).data()));
                        checkOrUncheck ? chkBxCount++ : chkBxCount--;
                    }
                }
            }

            fileCount = chkBxCount < 0 ? 0 : chkBxCount;

        });
    }
}

function updateFileIDs(elem){

	var chkBoxElem;
	var rowIndex;

	if ('target' in elem){
		chkBoxElem = $(elem.target);
		rowIndex = $(elem.target.parentElement.parentElement).index();
	}
	else if(elem.length > 0 && 'type' in elem[0] && elem[0].type == 'checkbox'){
		chkBoxElem = elem;
		rowIndex = -1;
	}

    if (chkBoxElem == undefined) {
        return;
    }

    var fileCount = parseInt($('#batchProcessBtn span').html());
    var checkedOrUnchecked = chkBoxElem.prop('checked');
	if(rowIndex != -1){
        var rowsPerPage = $('#file_table tbody tr').length;
        var currentPage = $('#file_table').DataTable().page();
		var elemRow = (rowsPerPage * currentPage) + rowIndex;

		var rowChkBox = $($('#file_table').DataTable().cell({row:elemRow, column:0}).data());
		var rowChkBoxHtml = rowChkBox.attr('checked', checkedOrUnchecked).prop('outerHTML');
		$('#file_table').DataTable().cell({row:elemRow, column:0}).data(rowChkBoxHtml);
	}

    checkedOrUnchecked ? fileCount++ : fileCount--;
    $('#batchProcessBtn span').html(fileCount.toString());
    if (fileCount <= 0) {
        $('#batchProcessBtn').prop('disabled', true);
    }
    var selectedFId = chkBoxElem.data('fileid');

    if(checkedOrUnchecked){
        selectd_fileids.push(selectedFId);
    }
    else{
        var index = selectd_fileids.indexOf(selectedFId);
        if (index !== -1) {
            selectd_fileids.splice(index, 1);
        }
    }

    if(selectd_fileids.length > 0){
        $('#batchProcessBtn').prop('disabled', false);
    }
 }

function processFile(fileId, overwrite) {

    var fileData = new FormData();
    fileData.append('fileId', fileId.toString());
    fileData.append('overwrite', overwrite.toString());
    fileData.append('uploader', $('#uploader').val());

    $.ajax({
        type: "POST",  
        url: baseurl+'AjaxApi/processFile',
        data: fileData,
        dataType: "json", 
        contentType: false,
        processData: false,   
        success: function(response)  {
            $.notify({
                // options
                message: 'Task started.'
              },{
                // settings
                timer: 200
            });
            id = $('#source_id').val();
            filesDtPage = $('#file_table').DataTable().page()
            $('[data-toggle="tooltip"]').tooltip('hide');
            reloadTable(id,false, isImport);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.notify({
                // options
                message: 'There was an error.'
              },{
                // settings
                timer: 200
            });
        }
    });
}

function processSelectedFiles() {
    if (selectd_fileids.length == 0) {
        return;
    }
    var fileData = new FormData();
    fileData.append('fileIds', selectd_fileids);

    $.ajax({
        type: "POST",  
        url: baseurl+'AjaxApi/processFiles',
        data: fileData,
        dataType: "json", 
        contentType: false,
        processData: false,   
        success: function(response)  {
            $.notify({
                // options
                message: 'Tasks started.'
              },{
                // settings
                timer: 200
            });

            selectd_fileids = [];
			$('#file_table thead .chkBxMaster').prop('checked', false);
			$('#file_table thead .chkBxMaster').change();
            filesDtPage = $('#file_table').DataTable().page()
            id = $('#source_id').val();
            reloadTable(id,false, isImport);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.notify({
                // options
                message: 'There was an error.'
              },{
                // settings
                timer: 200
            });
        }
    });
}

function processPendingFiles() {

    var fileData = new FormData();
    fileData.append('source_id', $('#source_id').val());
    fileData.append('pending', true);
    fileData.append('overwrite', 2); // 2 indicates that existing data for the file must be deleted before insertion.

    $.ajax({
        type: "POST",
        url: baseurl+'AjaxApi/processFilesBySourceId',
        data: fileData,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(response)  {
            $.notify({
                // options
                message: 'Tasks started.'
            },{
                // settings
                timer: 200
            });

            filesDtPage = $('#file_table').DataTable().page()
            id = $('#source_id').val();
            $('#batchProcessPendingBtn').prop('disabled', true);
            reloadTable(id,false, isImport);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            $.notify({
                // options
                message: 'There was an error.'
            },{
                // settings
                timer: 200
            });
        }
    });
}

function getCSRFToken(format = 'string'){
    csrf_token = $('#csrf_token').val();
    csrf_token_name = $('#csrf_token').prop('name');

    switch (format) {
        case "string":
            return csrf_token_name + '=' + csrf_token;
        case "keyvaluepair":
            var csrfObj = {};
            csrfObj[csrf_token_name] = csrf_token;
            return csrfObj;
    }
}