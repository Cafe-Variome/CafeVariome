var fileIntervalActive = false;

$(document).ready(function() { 
   $('.norm').on('click', function(){
   	// console.log($(this).prev('input').prop('checked'));
   	if ($(this).prev('input').prop('checked')) {
   		$(this).prev('input').prop('checked', false);
   	}
   	else {
   		$(this).prev('input').prop('checked', true);
   		$('input.target').not($(this).prev('input')).prop('checked', false);  
   	} 	
   });
})
$('input.target').on('change', function() {
    $('input.target').not(this).prop('checked', false);  
});

$(document).ready(function() {
	//form to upload file to system
	//allow the user to upload files to the server to be inserted into MySQL
	//first perform checks to ensure sanity of file
	$("#vcfinfo").submit(function(e){
		e.preventDefault();
		var ajaxData = new FormData(this);
		size = 0;
		file_names = [];
		for (i = 0; i < $('#vcfFile')[0].files.length; i++) {
		  size = size + $('#vcfFile')[0].files[i].size;
		  file_names.push($('#vcfFile')[0].files[i].name);
		} 
		id = $('#source_id').val();
		param = "source_id="+id+"&size="+size;
		$cfile = $("#config")[0].files[0];
		$.ajax({
      		type: "post",  
      		url: baseurl+'AjaxApi/validateUpload',
      		data: param,
      		dataType: "json", 
      		success: function(response)  {
				if (response == "Red") {
					alert("Illegal Source Target.")
				}
				else if (response == "Locked") {
					alert("This source is currently locked as there is a database update operation already ongoing. Please wait till its complete.");
				}
				else if (response == "Yellow") {
					alert("There is not enough space on the server to accept this file. Please get an Administrator to clear some space.");
				}
				else {
					// var ajaxData = new FormData(this);
					$("#load").append('<div class="loading">Loading&#8230;</div>');
					var formData = new FormData();
					formData.append("source_id", id);
					formData.append("files", file_names);
					formData.append("config", $("#config")[0].files[0])					
					$.ajax({
						//Send the form through to do_upload
			      		type: "POST",  
			      		url: baseurl+'AjaxApi/vcf_upload',
			      		contentType: 'multipart/form-data',
			      		data: formData,
			      		cache: false,
						contentType: false,
						processData: false,       
			      		success: function(response)  {
			      			data = $.parseJSON(response);
			      			$("#load").empty();
			      			if (data.status == "Overload") {
			      				alert(data.message);
			      			}
			      			else if (data.status == "Cancel") {
			      				// alert(data.message);
			      				$('#vcf_errors').empty();			      
			      				for (var i = 0; i < data.message.length; i++) {
			      					if (data.message[i].match("^dup_")) {
			      						data.message[i] = data.message[i].substring(4);
			      						$('#vcf_errors').append('<p style="background-color:lightpink; text-align:center;">'+data.message[i]+' has already been uploaded before.</p>');
			      					}
			      					else {
			      						$('#vcf_errors').append('<p style="background-color:lightpink; text-align:center;">'+data.message[i]+'</p>');
			      					}
			                        
			                    }
			      			}
			      			else if (data.status == "Duplicate") {
			      				confirmvcf(data);		
			      			}
			      			else if (data.status == "Green") {
			      				counter = 0;
								$("#load").append('<div class="loading">Loading&#8230;</div>');
								flag = true;
								for (i = 0; i < $('#vcfFile')[0].files.length; i++) {
									// console.log("Success");
									if (flag) {
										var formData = new FormData();
										formData.append("source_id", id);
										formData.append("uid", data.uid);
										flag = false;
									}
									formData.append("userfile[]", $('#vcfFile')[0].files[i]);
									counter++;
									if (counter%20 == 0) {
										flag = true;
										$.ajax({
											type: "POST",  
											url: baseurl+'AjaxApi/vcfBatch',
											contentType: 'multipart/form-data',
											data: formData,
											cache: false,
											contentType: false,
											dataType: "application/json", 
											processData: false,
											success: function(response)  {

											}
										});
									}
								}
								$.ajax({
									type: "POST",  
									url: baseurl+'AjaxApi/vcfBatch',
									contentType: 'multipart/form-data',
									data: formData,
									cache: false,
									contentType: false,
									processData: false,
									success: function(response)  {
										param = "source_id="+id+"&uid="+data.uid;
										$.ajax({
											type: "POST",   
								      		url: baseurl+'AjaxApi/vcfStart',
								      		data: param,
								      		dataType: "json", 
											success: function(response)  {
												if (response == "Green") {
													$("#load").empty();
								      				$.notify({
								                        // options
								                        message: 'Upload Complete. Now inserting into ElasticSearch.'
								                      },{
								                        // settings
								                        timer: 200
							                      	});
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
})



function confirmvcf(data) {
	counter = 0;
	done = [];
	$("#applyAll").prop('checked', false);
	if (sessionStorage) {
		sessionStorage.clear();
		for (var i = 0; i < data.types.length; i++) {
			target = data.types[i];
			sessionStorage.setItem(data.types[i], JSON.stringify(data[target]));
		}
		sessionStorage.setItem("uid", data.uid);
		sessionStorage.setItem("check", 1);
		sessionStorage.setItem("done", JSON.stringify(done));
		sessionStorage.setItem("count", data.types.length);
        $("#vcfGrid").empty();
        if (data.types[0] == "both") {
			remakeModal("both");
		}
		else if (data.types[0] == "elastic") {
			remakeModal("elastic");
		}
		else if (data.types[0] == "files") {
			remakeModal("files");
		}       
		$('#confirmVcf').modal('show');
	}
}



function proceedVcf() {
	// console.log("proceeding");
	var $boxes = $('input[name="chk[]"]:checked');
	done = JSON.parse(sessionStorage.getItem('done'));
	for (var i = 0; i < $boxes.length; i++) {
		console.log("value: "+$boxes[i].value);
		done.push($boxes[i].value);
	}
	sessionStorage.removeItem("done");
	sessionStorage.setItem('done', JSON.stringify(done));
	if (sessionStorage.getItem('both') !== null) {
		$('#vcfTable').dataTable().fnDestroy();
	   	remakeModal("both");
	  	return;
	}
	if (sessionStorage.getItem('elastic') !== null) {
		$('#vcfTable').dataTable().fnDestroy();
	  	remakeModal("elastic");
	  	return;
	}
	if (sessionStorage.getItem('files') !== null) {
		$('#vcfTable').dataTable().fnDestroy();
	  	remakeModal("files");
	  return;
	}
	if (sessionStorage.getItem('done') != "[]") {
	  	batchVcf();
	  	return;
	}
	else {
		$('#confirmVcf').modal('hide');
		$('#vcfTable').dataTable().fnDestroy();
		$.notify({
            // options
            message: 'No Files were selected. This upload was cancelled.'
          },{
            // settings
            timer: 200
      	});
	}
}

function remakeModal(target) {
	$("#variableIssue").empty();
	if (target == "both") {
		$("#variableIssue").append('<p>These files have both been uploaded before and the Patient/Tissue combo is already stored in our database.</p>');
		$("#variableIssue").append('<p>By Uploading these files you will replace the files and data currently stored in the database.</p>');
	}
	else if (target == "elastic") {
		$("#variableIssue").append('<p>The Patient/Tissue Combo linked to these files in your config file means there is already a database present with these settings.</p>');
		$("#variableIssue").append('<p>By uploading these files you will overwrite the data stored in these databases.</p>');
	}
	else if (target == "files") {
		$("#variableIssue").append('<p>These files with these names been uploaded before.</p>');
		$("#variableIssue").append('<p>By uploading them you will replace the files but no data in databases will be overwritten.</p>');
	}
	count = sessionStorage.getItem('count')-1;
	if (count == 1) {
		$("#variableIssue").append('<p>There is '+count+' more item to resolve after this.</p>');
	}
	else {
		$("#variableIssue").append('<p>There are '+count+' more items to resolve after this.</p>');
	}
	sessionStorage.setItem('count',count);
	$("#vcfGrid").empty();
    arr = JSON.parse(sessionStorage.getItem(target));
    if (arr instanceof Array) {
		for (var i = 0; i < arr.length; i++) {
			$("#vcfGrid").append('<tr id="row_'+i+'"></tr>');
			$("#row_"+i).append('<td>'+arr[i]+'</td>');
			$("#row_"+i).append('<td><label id="child"><input type="checkbox" class="select-checkbox" value="'+arr[i]+'" name="chk[]" id="file_'+i+'"> Tick to upload and replace.</label></td>');
		}
	}
	else {
		$("#vcfGrid").append('<tr id="row_0"></tr>');
		$("#row_0").append('<td>'+arr+'</td>');
		$("#row_0").append('<td><label id="child"><input type="checkbox" class="select-checkbox" value="'+sessionStorage.both+'" name="chk[]" id="file_'+0+'"> Tick to upload and replace.</label></td>');
	}
	$('#vcfTable').css('width', '');
	$('#vcfTable').dataTable( {
	    columnDefs: [ {
	        orderable: false,
	        className: 'select-checkbox',
	        targets:   1
	    } ],
	    select: {
	        style:    'os',
	        selector: 'td:first-child'
	    },
	    order: [[ 1, 'asc' ]]
	} );
	sessionStorage.removeItem(target);
}

function batchVcf() {
	$('#confirmVcf').modal('hide');
	$('#vcfTable').dataTable().fnDestroy();
	id = $('#source_id').val();
	$("#load").append('<div class="loading">Loading&#8230;</div>');
	flag = true;
	uid = sessionStorage.getItem('uid');
	done = JSON.parse(sessionStorage.getItem('done'));
	sessionStorage.clear();
	for (i = 0; i < $('#vcfFile')[0].files.length; i++) {
		// console.log("Success");
		file = $('#vcfFile')[0].files[i];
		if (flag) {
			var formData = new FormData();
			formData.append("source_id", id);
			formData.append("uid", uid);
			flag = false;
		}
		if (done.includes(file.name)) {
			formData.append("userfile[]", $('#vcfFile')[0].files[i]);
			counter++;
		}		
		if (counter%20 == 0) {
			flag = true;
			$.ajax({
				type: "POST",  
				url: baseurl+'Upload/vcfBatch',
				contentType: 'multipart/form-data',
				data: formData,
				cache: false,
				contentType: false,
				dataType: "application/json", 
				processData: false,
				success: function(response)  {

				}
			});
		}
	}
	$.ajax({
		type: "POST",  
		url: baseurl+'upload/vcfBatch',
		contentType: 'multipart/form-data',
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		success: function(response)  {
			param = "source_id="+id+"&uid="+uid;
			$.ajax({
				type: "POST",   
	      		url: baseurl+'upload/vcfStart',
	      		data: param,
	      		dataType: "json", 
				success: function(response)  {
					if (response == "Green") {
						$("#load").empty();
	      				$.notify({
	                        // options
	                        message: 'Upload Complete. Now inserting into ElasticSearch.'
	                      },{
	                        // settings
	                        timer: 200
                      	});
					}	
				}
		  	});
	  	}
  	});	
}

$("#headerInfo").click(function(){
	$('#uploadInfoModal').modal('show');
}); 



function checkAllToggle() {
	var rowCount = $('#vcfTable >tbody >tr').length;
	for (var i = 0; i < rowCount; i++) {	
		if (sessionStorage.check == 1) {
			$("#file_"+i).prop('checked', true);
		}
		else {
			$("#file_"+i).prop('checked', false);
		}
	}
	if (sessionStorage.check == 1) {
		sessionStorage.check = 0;
	}
	else {
		sessionStorage.check = 1;
	}
}

$('#confirmVcf').on('hidden', function () {
	$.notify({
            // options
            message: 'The upload has been cancelled due to the resolution modal being closed.'
          },{
            // settings
            timer: 200
      	});
    $('#vcfTable').dataTable().fnDestroy();
})



function fileUploadInterval() {
    if (!fileIntervalActive) {
        fileIntervalActive = true;
        fileInterval = setInterval(function() {
            $.ajax({url: baseurl + 'upload/checkUploadJobs',
                type: 'POST',
                success: function (data) {
					param = $('#source_id').val();

					reloadTable(param,false);
                    data = $.parseJSON(data);
                    // console.log(data);
                    if (!data.Status) {
                        fileIntervalActive = false;  
                        clearInterval(fileInterval);  
                        return; 
                    }
                    if (data.Message.length > 0) {
                        for (var i = 0; i < data.Message.length; i++) {
                            $.notify({
                                message: 'The Source: '+data.Message[i]+' has finished with its database operation (Upload Or Regenerate). Please refer to the status page for that source to see more details.'
                              },{
                                // settings
                                timer: 2000000
                            });
                        }
                    }                
                }
            });
        }, 5000)
    }
}

