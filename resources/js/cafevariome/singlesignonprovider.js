$(document).ready(function() {
    if ($('#singlesignonproviderstable').length) {
        $('#singlesignonproviderstable').DataTable();
    }
});

$('#icon').on('change',function(){
    var fullFileName = $(this).val();
    var fileName = fullFileName.split('\\')[fullFileName.split('\\').length - 1];
    $(this).next('.custom-file-label').html(fileName);

    var selectedFileSize = $('#icon')[0].files[0].size;
    $('#selectedFileSize').html((selectedFileSize/1048576).toFixed(2)+ ' MB');

    var maxUploadFileSize = $('#maxUploadSize').data('bytevalue');
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