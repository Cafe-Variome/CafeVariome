$("#uploadBulk").submit(function(event) {  
    event.preventDefault();
   //form to upload file to system
    //allow the user to upload files to the server to be inserted into MySQL
    //first perform checks to ensure sanity of file
		selected = $('input[name="fAction[]"]:checked').val(); 
		var appendRadioBtn = $("#fActionAppend").prop('checked');
		var overwriteRadioBtn = $("#fActionOverwrite").prop('checked');
		if (!appendRadioBtn && !overwriteRadioBtn){
			alert("You need to select whether to append or replace.");
		}
        if (selected == 'append') {
        	if(!confirm("You have selected to append new data into this source without changing any other data. Continue?")) {
        		return;
        	}
        }
        else if (selected == 'overwrite') {
        	if(!confirm("WARNING! By selecting this option you will delete all data from the source. Are you sure you want to continue?")) {
        		return;
        	}
        }

        size = 0;
        for (i = 0; i < $('#dataFile')[0].files.length; i++) {
          size = size + $('#dataFile')[0].files[i].size;
        } 
        id = $('#source_id').val();
        name = $('#dataFile')[0].files[0].name;
        param = id;
        csrf_token = $('#csrf_token').val();
        csrf_token_name = $('#csrf_token').prop('name');
        validateParams = 'source_id=' + id + '&size=' + size + '&' + csrf_token_name + '=' + csrf_token;
        $('#uploadBtn').prop('disabled', 'disabled');
        $('#uploadSpinner').show();

        $.ajax({
            type: 'post',
            url: baseurl+'AjaxApi/validateUpload',
            data: validateParams,
            dataType: 'json',
            success: function(response)  {
                //get the data put onto the form
                if (response == 'Locked') {
                    alert('This source is currently locked as there is a database update operation already ongoing. Please wait till its complete.');
                }
                else if (response == 'Yellow') {
                    alert('There is not enough space on the server to accept this file/s. Please get an Administrator to clear some space.');
                }
                else if (response != 'Green') {
                    alert('Illegal Source Target.');
                }
                else {
                    var uploadData = new FormData();
                    uploadData.append(csrf_token_name, csrf_token);
                    uploadData.append('source_id', $('#source_id').val());
                    uploadData.append('user_id', $('#user_id').val());
                    uploadData.append('fAction', selected);
                    uploadData.append('pipeline_id', $('#pipeline').val());
                    uploadData.append('files', $('#dataFile')[0].files[0]);

                    $.ajax({
                        type: 'post',
                        url: baseurl+'AjaxApi/spreadsheetUpload/',
                        contentType: false,
                        data: uploadData,
                        cache: false,
                        processData: false,
                        dataType: 'json',
                        success: function(data)  {
                            $('#uploadBulk')[0].reset();
                            //data = $.parseJSON(response);
                            //if the data has the wrong headers warn the user
                            if (data.status == 'Header') {
                                alert(data.message);
                            }
                            //check if the user wants to delete prior file or leave it
                            else if (data.status == 'Duplicate') {
                                if (confirm('This file has been uploaded before. Do you want to replace the file and all associated data?')) {
                                    uploadData.append('force', true);

                                    $.ajax({
                                        type: 'POST',
                                        url: baseurl+'AjaxApi/spreadsheetUpload/',
                                        contentType: false,
                                        data: uploadData,
                                        cache: false,
                                        processData: false,
                                        success: function(response)  {
                                        	  $.notify({
                                                    message: 'Upload Complete. The file is being processed soon.',
                                                    type: 'info'
                                                  },{
                                                    timer: 200
                                              });
                                              reloadTable($('#source_id').val(),false);
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            $.notify({
                                                message: 'An error occurred while uploading the file: ' + errorThrown,
                                                type: 'danger'
                                            },{
                                                timer: 200
                                            });
                                        },
                                        complete: function (jqXHR, textStatus){
                                            $('#uploadSpinner').hide();
                                            $('#uploadBtn').prop('disabled', false);
                                        }
                                    });
                                }
                            }
                            else if(data.status == 'Red'){
                                $.notify({
                                    message: 'Unknown error.',
                                    type: 'danger'
                                  },{
                                    timer: 200
                                });
                            }
                            else if (data.status == 'InvalidFile') {
                                $.notify({
                                    message: 'File is not valid.',
                                    type: 'warning'
                                  },{
                                    timer: 200
                                });
                            }
                            else {
                                $.notify({
                                    message: 'Upload Complete. We are currently processing your data.',
                                    type: 'info'
                                    },{
                                    timer: 200
                                });
                                reloadTable($('#source_id').val(),false);
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {
                            $.notify({
                                message: 'An error occurred while uploading the file: ' + errorThrown,
                                type: 'danger'
                              },{
                                timer: 200
                            });
                        },
                        complete: function (jqXHR, textStatus){
                            $('#uploadSpinner').hide();
                            $('#uploadBtn').prop('disabled', false);
                        }
                    });
                }

            },
            error: function(jqXHR, textStatus, errorThrown) {
                $.notify({
                    message: 'An error occurred while validating file upload: ' + errorThrown,
                    type: 'danger'
                },{
                    timer: 200
                });
            },
            complete: function (jqXHR, textStatus){
                $('#uploadSpinner').hide();
                $('#uploadBtn').prop('disabled', false);
            }
        }); 


});

$('#dataFile').on('change',function(){
    var fullFileName = $(this).val();
    var fileName = fullFileName.split('\\')[fullFileName.split('\\').length - 1];
    $(this).next('.custom-file-label').html(fileName);
})

