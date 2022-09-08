/**
 * networkgroup.js
 * 
 * Created: 20/08/2019
 * 
 * @author Mehdi Mehtarizadeh
 */

$(document).ready(function() {

    if ($('#discoverygroupstable').length) {
        $('#discoverygroupstable').DataTable();
    }

    if ($('#users').length) {
        $('#users').select2({closeOnSelect: false, placeholder: 'Select users', width:'100%', dropdownAutoWidth: true});
    }

    if ($('#sources').length) {
        $('#sources').select2({ closeOnSelect: false, placeholder: 'Select sources', width:'100%', dropdownAutoWidth: true});
    }
} );
