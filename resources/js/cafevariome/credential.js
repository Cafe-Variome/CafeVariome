$(document).ready(function() {
    if ($('#credentialstable').length) {
        $('#credentialstable').DataTable();
    }
});

function enableUsernameChange() {
    $('#changeuserlnk').hide();
    $('#username').attr('disabled', false);
    $('#username').attr('placeholder', '');
}

function enablePasswordChange() {
    $('#changepasslnk').hide();
    $('#password').attr('disabled', false);
    $('#password').attr('placeholder', '');
}

function usernameChange(){
    $('#username_changed').val('true');
}

function passwordChange(){
    $('#password_changed').val('true');
}
