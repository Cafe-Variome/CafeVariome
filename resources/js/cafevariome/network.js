
/**
 * network.js
 * Created 19/07/2019
 * 
 * @author Dhiwagaran Thangavelu
 * @author Mehdi Mehtarizadeh
 * 
 * This file contains JavaScript code for network controller and its views.
 */

$(document).ready(function() {

    //Check if networkstable exists in the document, then call datatable for it.
    if ($('#networkstable').length) {
        $('#networkstable').DataTable();
    }
    if ($('#users').length) {
        $('#users').select2({width:'100%'});
    }
    if ($('#sources').length) {
        $('#sources').select2({width:'100%'});
    }
} );



function join_network() {
    $callAjax = true;
    $('form[name="joinNetwork"]').submit(function (e) {
        e.preventDefault();
        $.ajax({url: baseurl + 'network/process_network_join_request',
            data: $(this).serialize(),
            dataType: 'json',
            delay: 200,
            type: 'POST',
            success: function (data) {
                if (data.error) {
				alert(data.error);
                    window.location.reload(true);
                }
                else if (data.success) {
				    alert("Successfully joined network");
                    alert("Successfully requested to join network");
                    window.location = baseurl + "network/networks";
                }
                else {
                    window.location.reload(true);
                }
            }
        });
    });
}

function addRemoteUser() {
    param = "rUser="+$('#remote_user_email').val();
    console.log(param);
    $.ajax({
        type: "post",  
        url: baseurl+'network/create_remote_user',
        data: param,
        dataType: "json", 
        success: function (data) {
            console.log(data);
            if (data.status == 'success') {
                $("#mng_right").append($("<option></option>")
                .attr("value",data.data.id)
                .text(data.data.username+' (R)')); 
            }
            else if (data.status == 'exists') {
                alert("This user already exists within this local installation.");
            }
            else {
                alert('User was not created successfully');
            }
        }
    });
}

function edit_user_network_groups_sources() {

    // Select all transferred items in the right hand side select element
    // so that they appear in the post request
    $(".groupsSelected").find('option').each(function () {
        $(this).prop("selected", true);
    });

    // Select all transferred items in the right hand side select element
    // so that they appear in the post request
    $(".sourcesSelected").find('option').each(function () {
         $(this).prop("selected", true);
     });

    $('form[name="editUser"]').submit();

}

/**
 * 
 * @deprecated
 */
function saveThreshold(event) { 
    event.preventDefault();

    if(isNaN($("#threshold").val()) || $("#threshold").val() < 0) {
        alert("Invalid threshold value");
        return;
    }

    $.ajax({
        url: authurl + '/network/set_network_threshold',
        type: 'POST',
        dataType: 'JSON',
        data: {network_key: $("#threshold_network_key").val(), network_threshold: $("#threshold").val()},
        error: function (jqXhr, textStatus, errorMessage) { // error callback 
        }
    }).done(function(data) {
        window.location = baseurl + "network";
    });

    //Temporary until proper response generated from auth server. (21/08/2019, Mehdi Mehtarizadeh)
    window.location = baseurl + "network";

}

