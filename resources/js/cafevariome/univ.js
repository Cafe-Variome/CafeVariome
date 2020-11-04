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
        validateParams = 'source_id=' + id + '&size=' + size;
        $('#uploadBtn').prop('disabled', 'disabled');
        $('#uploadSpinner').show()

        $.ajax({
            type: "post",  
            url: baseurl+'AjaxApi/validateUpload',
            data: validateParams,
            dataType: "json", 
            success: function(response)  {
                //get the data put onto the form
                if (response != "Green") {
                    alert("Illegal Source Target.")
                }
                else if (response == "Locked") {
                    alert("This source is currently locked as there is a database update operation already ongoing. Please wait till its complete.");
                }
                else if (response == "Yellow") {
                    alert("There is not enough space on the server to accept this file/s. Please get an Administrator to clear some space.");
                }
                else {
                    var uploadData = new FormData();
                    uploadData.append('source_id', $('#source_id').val());
                    uploadData.append('user_id', $('#user_id').val());
                    uploadData.append('files', $('#dataFile')[0].files[0]);
                    uploadData.append('fAction', selected);
                    uploadData.append('config', $('#config').val());
                    $.ajax({
                        //Send the form through to do_upload
                        type: "POST",  
                        url: baseurl+'AjaxApi/univ_upload',
                        contentType: 'multipart/form-data',
                        data: uploadData,
                        cache: false,
                        contentType: false,
                        processData: false,       
                        success: function(response)  {
                            $('#uploadSpinner').hide();
                            $('#uploadBtn').prop('disabled', false);
                            $("#uploadBulk")[0].reset();
                            console.log(response);
                            data = $.parseJSON(response);
                            //if the data has the wrong headers warn the user
                            if (data.status == "Header") {
                                alert(data.message);
                            }
                            //check if the user wants to delete prior file or leave it
                            else if (data.status == "Duplicate") {
                                if (confirm("This file has been uploaded before. Do you want to replace the file and all associated data?")) {
                                    $.ajax({
                                        type: "POST",  
                                        url: baseurl+'AjaxApi/univ_upload/true',
                                        contentType: 'multipart/form-data',
                                        data: uploadData,
                                        cache: false,
                                        contentType: false,
                                        processData: false,
                                        success: function(response)  {
                                        	console.log(response);
                                        	  $.notify({
										        message: 'Upload Complete. We are currently processing your data.'
										      },{
										        // settings
										        timer: 200
										    });
                                            fileUploadInterval();                      
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            alert("loading error data " + errorThrown);
                                        }
                                    });
                                }
                            }
                            else {
                            	  $.notify({
								        // options
								        message: 'Upload Complete. We are currently processing your data.'
								      },{
								        // settings
								        timer: 200
								    });
                            	fileUploadInterval();                           
                            }
                        },
                        error: function(jqXHR, textStatus, errorThrown) {

                        }
                    });
                }

            },
            error: function(jqXHR, textStatus, errorThrown) {
            }
        }); 


});

$('#dataFile').on('change',function(){
    var fullFileName = $(this).val();
    var fileName = fullFileName.split('\\')[fullFileName.split('\\').length - 1];
    $(this).next('.custom-file-label').html(fileName);
})