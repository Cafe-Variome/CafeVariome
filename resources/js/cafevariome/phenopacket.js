$("#phenoinfo").submit(function(e){
	e.preventDefault();
	var pipeline_id = $('#pipeline').val();
	if (pipeline_id == -1 || pipeline_id == null || pipeline_id == '') {
		$('#pipeline').addClass('is-invalid');
		return;
	}
	else{
		$('#pipeline').removeClass('is-invalid');
	}
	var size = 0;
	var file_names = [];
	for (i = 0; i < $('#jsonFile')[0].files.length; i++) {
	  size = size + $('#jsonFile')[0].files[i].size;
	  file_names.push($('#jsonFile')[0].files[i].name);
	}
	//file_names = JSON.stringify(file_names);
	user_id = $('#user_id').val();
	id = $('#source_id').val();
	csrf_token = $('#csrf_token').val();
	csrf_token_name = $('#csrf_token').prop('name');
	param = 'source_id=' + id + '&size=' + size + '&' + csrf_token_name + '=' + csrf_token;
	console.log(param);
	$.ajax({
		type: 'post',
		url: baseurl+'AjaxApi/validateUpload',
		data: param,
		dataType: 'json',
		success: function(response)  {
			if (response == 'Red') {
				alert('Illegal Source Target.');
			}
			else if (response == 'Locked') {
				alert('This source is currently locked as there is a database update operation already ongoing. Please wait till its complete.');
			}
			else if (response == 'Yellow') {
				alert('There is not enough space on the server to accept this file(s). Please get an Administrator to clear some space.');
			}
			else {
				var uploadData = new FormData();
				uploadData.append(csrf_token_name, csrf_token);
				uploadData.append('source_id', $('#source_id').val());
				uploadData.append('fileNames', file_names);

				for (i = 0; i < file_names.length; i++) {
					uploadData.append('fileNames[]', file_names[i]);
				}

				$.ajax({
					type: 'POST',
					url: baseurl+'AjaxApi/checkJsonPresence',
					data: uploadData,
					dataType: 'json',
					contentType: false,
					processData: false,
					success: function(response)  {
						if (Array.isArray(response)) {
							message = 'These files have been uploaded before:\n';
							message = message + 'Do you want to replace these files and all its data? Or cancel Upload?\n';
							for (var i = 0; i < response.length; i++) {
								message = message + response[i] + '\n';
							}
							flag = confirm(message);
						}
						else {
							flag = true;
						}
						if (flag) {
							counter = 0;
							id = $('#source_id').val();
							$('#uploadSpinner').show();
							for (i = 0; i < $('#jsonFile')[0].files.length; i++) {
								if (flag) {
									var formData = new FormData();
									formData.append(csrf_token_name, csrf_token);
									formData.append('source_id', id);
									formData.append('user_id', user_id);
									formData.append('pipeline_id', pipeline_id);
									flag = false;
								}
								formData.append('userfile[]', $('#jsonFile')[0].files[i]);
								counter++;
								if (counter % 20 == 0) {
									flag = true;
									$.ajax({
										type: 'POST',
										url: baseurl+'AjaxApi/jsonBatch',
										data: formData,
										contentType: false,
										dataType: 'json',
										processData: false,
										success: function(response)  {

										}
									});
								}
							}
							$.ajax({
								type: 'POST',
								url: baseurl+'AjaxApi/jsonBatch',
								data: formData,
								contentType: false,
								processData: false,
								success: function(response)  {
									var formData = new FormData();
									formData.append(csrf_token_name, csrf_token);
									formData.append('source_id', id);
									formData.append('user_id', user_id);
									$.ajax({
										type: 'POST',
										url: baseurl + 'AjaxApi/jsonStart',
										data: formData,
										dataType: 'json',
										contentType: false,
										processData: false,
										success: function(response)  {
											if (response == 'Green') {
												$('#uploadSpinner').hide();
												$.notify({
													// options
													message: 'Upload Complete. Now inserting into MySQL.'
												  },{
													// settings
													timer: 200
												});
												reloadTable($('#source_id').val(),false);
											}
										}
									});
								}
							});
						}
					}
				});
			}
		}
	});
});

$('#jsonFile').on('change',function(){
    var files = $(this).prop('files');
    $(this).next('.custom-file-label').html(files.length.toString() + ' file(s) selected.');

	var selectedFilesSize = 0;
	for (i = 0; i < $('#jsonFile')[0].files.length; i++){
		selectedFilesSize += $('#jsonFile')[0].files[i].size;
	}

	var maxUploadFileSize = $('#maxUploadSize').data('bytevalue');
	$('#selectedFileSize').html((selectedFilesSize/1048576).toFixed(2)+ ' MB');

	if (selectedFilesSize > maxUploadFileSize){
		$('#uploadWarningText').html('Selected file(s) size is larger than the maximum allowed file size for upload. Upload cannot proceed. Please contact the server administrator to increase the upload size or select other files.');
		$('#uploadWarningAlert').show();
		$('#uploadBtn').prop('disabled', 'disabled');
	}
	else
	{
		$('#uploadWarningAlert').hide();
		$('#uploadBtn').prop('disabled', false);
	}
});