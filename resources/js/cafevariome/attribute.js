$(document).ready(function() {
    // If the table exists, populate DataTable and make it stylish.
    if ($('#attributestable').length) {
        $('#attributestable').DataTable();
    }

    if ($('#attributeontologiestable').length) {
        $('#attributeontologiestable').DataTable();
    }

    $('#ontology').change(function() {
        setCreateBtnState();

        var csrfTokenObj = getCSRFToken('keyvaluepair');
        var formData = {'attribute_id': $('#attribute_id').val(), 'ontology_id': $('#ontology').val()};
        var csrfTokenName = Object.keys(csrfTokenObj)[0];
        formData[csrfTokenName] = csrfTokenObj[csrfTokenName];

        $.ajax({
            type: 'POST',
            url: baseurl + 'AjaxApi/getOntologyPrefixesAndRelationships',
            data: formData,
            dataType: 'json',
            beforeSend: function (jqXHR, settings) {
                disableCreateBtn();
                showLoader();
                disableForm();
            },
            success: function(response)  {
                $('#prefix').empty();
                $('#prefix').append($('<option></option>').attr('value', '0').text('Please select a prefix.'));

                $('#relationship').empty();
                $('#relationship').append($('<option></option>').attr('value', '0').text('Please select a relationship.'));

                $.each(response.prefixes, function(key,value){
                    $('#prefix').append($('<option></option>').attr('value', key).text(value));
                });

                $.each(response.relationships, function(key,value){
                    $('#relationship').append($('<option></option>').attr('value', key).text(value));
                });
            },
            error: function(jqXHR, textStatus, errorThrown) {

            },
            complete: function (jqXHR, settings) {
                enableForm();
                hideLoader();
            }
        });

    }).change();

    $('#prefix').change(function() {
        setCreateBtnState();
    }).change();

    $('#relationship').change(function() {
        setCreateBtnState();
    }).change();

});

function setCreateBtnState(){
    if ($('#ontology').val() == "0" || $('#prefix').val() == "0" || $('#relationship').val() == "0"){
        disableCreateBtn();
    }
    else{
        enableCreateBtn();
    }
}

function disableForm() {
    $('#ontology').prop('disabled', true);
    $('#prefix').prop('disabled', true);
    $('#relationship').prop('disabled', true);
}

function enableForm() {
    $('#ontology').prop('disabled', false);
    $('#prefix').prop('disabled', false);
    $('#relationship').prop('disabled', false);
}

function showLoader() {
    $('#spinner').show();
}

function hideLoader() {
    $('#spinner').hide();
}

function disableCreateBtn(){
    $('#ontologyassociation_btn').prop('disabled', true);
}

function enableCreateBtn(){
    $('#ontologyassociation_btn').prop('disabled', false);
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