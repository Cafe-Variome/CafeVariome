attributeValueObj = null;

$(document).ready(function() {
    source_id = $('#source_id').val();

    $.ajax({
        type: "post",  
        data:{"source_id": source_id},
        url: baseurl+'AjaxApi/getAttributeValueFromFile',
        dataType: "json", 
        beforeSend: function (jqXHR, settings) {
            $('#attributestable').hide();
            $('#loader').show();
        },
        success: function(data, status, jqXHR)  {
            $('#attributestable').show();
            attributeValueObj = data;
            $.each(data, function (fileName, attributeValueString) {
                $.each(JSON.parse(attributeValueString), function(attribute, valueCountObj){
                    vcounter = 0;
                    $.each(valueCountObj, function (value, count) {
                        vcounter++;
                    });

                    trow = "<tr>";
                    trow += "<td>" + attribute + "</td>";
                    trow += "<td>" + fileName + "</td>";
                    trow += "<td>" + vcounter + "</td>";
                    trow += "<td><button type='button' class='btn btn-info' data-toggle='modal' data-target='#valueModal' data-attribute='" + attribute + "' data-filename='" + fileName + "'><i class='fa fa-list'></i></button></td>";
                    trow += "</tr>";

                    $('#attributestable tbody').append(trow)
                })
            })

            // If the table exists, populate DataTable and make it stylish.
            if ($('#attributestable').length) {
                $('#attributestable').DataTable();
            }
        },
        error: function (jqXHR, status, errorText) {
            $('#erroralert').show();
            $('#errorinfo').text("More information on the error: " + errorText + "");
        },
        complete: function (jqXHR, settings) {
            $('#loader').hide();
        }
    })

    $('#valueModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget) 
        var attribute = button.data('attribute') 
        var fileName = button.data('filename') 

        values = loadValues(attribute, fileName);

        var trow = "";
        $.each(values, function (val, count) {
            trow += "<tr>";
            trow += "<td>" + val + "</td>"
            trow += "<td>" + count + "</td>"
            trow += "</tr>"
        })

        $('#valueModalLabel').text('List of Unique Values for: ' + attribute)

        $('#valuestable tbody').append(trow);
        if ($('#valuestable').length) {
            $('#valuestable').DataTable();
        }
    });

    $('#valueModal').on('hidden.bs.modal', function (e) {
        $('#valueModalLabel').text('')
        $('#valuestable').DataTable().destroy();
        $('#valuestable tbody').empty()

    })
})

function loadValues(attribute, file_name) {
    values = null;

    if (attributeValueObj != null && attributeValueObj.hasOwnProperty(file_name)) {
        fileAttributeValueList = JSON.parse(attributeValueObj[file_name]);
        values = fileAttributeValueList[attribute]
    }

    return values;
}