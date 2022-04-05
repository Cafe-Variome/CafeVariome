
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

