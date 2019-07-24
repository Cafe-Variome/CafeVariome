String.prototype.format = function (arguments) {
    var s = this, i = arguments.length;
    if (i === 0)
        return s;
    while (i--)
        s = s.replace(new RegExp('\\{' + i + '\\}', 'gm'), arguments[i]);
    return s;
};

function add_options(option, arguments) {
    $select = $.parseHTML(option);
    for (var arg in arguments) {
        if(arguments[arg] == "----------------------" || arguments[arg] == "not all values displayed")
            $($select).children('select').append("<option value='" + arguments[arg] + "' disabled>" + arguments[arg] + "</option>");
        else
            $("<option />", {value: arguments[arg], text: arguments[arg]}).appendTo($($select).children('select'));
    }
    return $select;
}

function add_options_group(option, arguments) {
    var $select = $.parseHTML(option);
    for (var key in arguments) {
        var group = $('<optgroup label="' + key + '" />');
        for (var value in arguments[key])
            $("<option />", {value: arguments[key][value], text: arguments[key][value]}).appendTo(group);
        group.appendTo($($select).children('select'));
    }

    return $select;
}

function isNumber(n) {
    return !isNaN(parseFloat(n)) && isFinite(n);
}

$and_or_logic = '<div class="btn-group btn-toggle logic_phenotype" id="" style="margin-bottom:20px">\n\
                        <a class="btn btn-medium {0}">AND</a>\n\
                        <a class="btn btn-medium {1}">OR</a>\n\
                     </div>';

$or_logic = '<div class="row"><div class="btn-group span3 offset5 pagination-centered logic" style="padding-bottom:10px">\n\
                        <a class="btn btn-medium btn-primary disabled">OR</a>\n\
                 </div></div>';

$option = '<div class="pagination-centered {0}">\n\
                <select class="{1}" data-type="operator" {2}></select>\n\
               {3}\n\
                </div>';

$option_select2 = '<div class="pagination-centered {0}">\n\
                            <select class="input-xlarge {1}" style="margin-bottom:10px">\n\
                            <option></option>\n\
                            </select>\n\
                        </div>';

$textbox = '<div class="{0} pagination-centered">\n\
                    <div class="input-append">\n\
                        <input class="input_field textValidate {1}" data-type="{2}" type="text" placeholder="{3}">\n\
                        <span class="add-on"><i class="icon-remove-circle"></i></span>\n\
                    </div>\n\
                </div>';

$options_container = '<div class="span7 pagination-centered"></div>';

$textbox_with_label = '<div class="input-prepend input-append text_with_label {0}" style="margin-bottom:10px">\n\
                            <span class="add-on">{1}</span>\n\
                                <input class="input_field query_term {2}" type="text" placeholder="{3}">\n\
                            <span class="add-on"><i class="icon-remove-circle"></i></span>\n\
                          </div>';

$add_remove_btn = '<div class="span2 {0} add_remove_btn" style="padding-top: 3px;">\n\
                        <button class="btn btn-mini btn-success add">\n\
                            <i class="icon-plus"></i>\n\
                        </button>\n\
                        <button class="btn btn-mini btn-danger remove hidden">\n\
                            <i class="icon-minus icon-white"></i>\n\
                        </button>\n\
                       </div>';

$type_sample = '<div id="{0}" class="row-fluid type_sample {1}">\n\
                    </div>';

$row = '<div class="row-fluid pagination-centered"></div>';

$phenotype_count = 1;
$phenotype_option_1 = ["IS", "IS LIKE", "IS NOT", "IS NOT LIKE", "---------------", "=", "≠", "<", ">", "<=", ">="];
$phenotype_option_2 = ["--Select a value--", "NULL", "[Input your own value]"];

add_symbol("phenotypeContainer");

function add_symbol($symbol) {
    switch ($symbol) {
        case "phenotypeContainer":

            if ($("#phenotypeContainer").children('.type_sample').length == 1)
                $("#phenotypeContainer").append($and_or_logic.format(["btn-primary active", "btn-default"]));
            else if ($("#phenotypeContainer").children('.type_sample').length > 1) {
                if ($("#phenotypeContainer").find('.logic_phenotype .active').html() === "AND")
                    $("#phenotypeContainer").append($and_or_logic.format(["btn-primary active", "btn-default"]));
                else
                    $("#phenotypeContainer").append($and_or_logic.format(["btn-default", "btn-primary active"]));
            }

            $("#phenotypeContainer").append($type_sample.format(["phenotype" + $phenotype_count, ""]));

            if ($phenotype_count == 1)
                $("#phenotype" + $phenotype_count).append($option_select2.format(["span4 offset1", "keys phenotype_keys" + $phenotype_count]));
            else {
                $options = add_options($option_select2.format(["span4 offset1", "keys phenotype_keys" + $phenotype_count]), phenotype_keys);
                $("#phenotype" + $phenotype_count).append($options);
            }

            $("." + ["phenotype_keys" + $phenotype_count]).select2({
                placeholder: "--Select an attribute--",
//                    allowClear: true
            });

            $options_1 = add_options($option.format(["span2", "input-small conditions", "", ""]), $phenotype_option_1);
            $("#phenotype" + $phenotype_count).append($options_1);

            $options_2 = add_options($option.format(["span2", "input-large phenotype_values\" disabled ", "", ""]), $phenotype_option_2);
            $("#phenotype" + $phenotype_count).append($options_2);

            $("#phenotype" + $phenotype_count).append($add_remove_btn.format(["offset1"]));

            $phenotype_count += 1;
            break;
    }
}

var phenotype_keys = new Array();
var phenotype_values = new Array();

$(document).ready(function () {
    // $("#loader").addClass('hide');
    $network_key = $("#network_key").val();
   $.ajax({url: baseurl + 'admin/get_phenotype_attributes_for_network/' + $network_key,
//        $.ajax({url: baseurl + 'admin/get_phenotype_attributes_for_network/5b7a1ae7ac7fa0a4a4c7cedac1982dba',
        // $.ajax({url: baseurl + 'admin/get_phenotype_attributes_for_network/f75ef233eb89ba76a4187912cd6f909d',
        dataType: 'json',
        delay: 200,
        type: 'POST',
        success: function (json) {
            console.log("1 "+json);
            $.each(json, function (attribute, value) {
                $('select.phenotype_keys1').append($('<option>').text(attribute).attr('value', attribute));
                phenotype_keys.push(attribute);
                var index = value.indexOf("not all values displayed");
                if(index != -1) {
                    value.splice(index, 1);
                    value.push("----------------------", "not all values might be displayed");
                }
                
                phenotype_values[attribute] = value;
                
            });
            console.log(phenotype_values);
            $("#loader").addClass('hide');
        }
    });

    // Phenotype
    $(document).on('change', '.keys', function () {
        $current_phenotype_values = $(this).parent().parent().find('.phenotype_values').prop('disabled', '').parent();
        $new_phenotype_values = add_options($option.format(["span2", "input-large phenotype_values", "", ""]), $phenotype_option_2.concat(phenotype_values[$(this).val()]));
        $current_phenotype_values.replaceWith($new_phenotype_values);
    });

    $(document).on('change', "select.phenotype_values", function () {
        if ($(this).val() === "[Input your own value]") {
            $(this).parent().append('<div class="input-append phenotype_custom_value">\n\
                                <input class="input-medium phenotype_values" data-type="phenotype" type="text" placeholder="Enter a value">\n\
                                <span class="add-on"><i class="icon-share-alt"></i></span>\n\
                            </div>');
            $phenotype_values = $(this).remove();
        }
    });

    $(document).on('click', '.phenotype_custom_value .icon-share-alt', function () {
        $(this).parent().parent().parent().append($phenotype_values)
                .find('option:contains("--Select a value--")').attr('selected', 'selected')
                .parent().prev().remove();
    });

    // Rest
    $(document).on('click', ".btn-collapse", function () {

        if ($(this).attr('id') === "isPhenotype")
            $parent = $(this).parent().parent().parent();
        else
            $parent = $(this).parent().parent();

        if ($(this).attr("data-collapseStatus") === "false") {
            $(this).removeClass("btn-info").addClass("btn-success");
            $(this).find('i').removeClass("icon-chevron-left").addClass("icon-chevron-down");
            $($(this).parent().parent().next().collapse('show')).addClass('container_border');
            $(this).attr("data-collapseStatus", "true");
            $parent.prev().children('a').removeClass('disabled');

        } else {
            $collapse = true;

            switch ($(this).parent().parent().next().attr('id')) {
                case "phenotypeContainer":
                    $collapse = validate_Phenotype("collapseEvent");
                    break;
            }
            if ($collapse) {
                $(this).removeClass("btn-success").addClass("btn-info");
                $(this).find('i').removeClass("icon-chevron-down").addClass("icon-chevron-left");
                $($(this).parent().parent().next().collapse('hide')).removeClass("container_border");
                $(this).attr("data-collapseStatus", "false");
                $parent.prev().children('a').addClass('disabled');
            }
        }
    });

    $(document).on('click', 'button.add', function () {

        parentId = $(this).closest("div[id$='Container']").attr('id');
        add_symbol(parentId);
        $("#" + parentId).find(".remove").removeClass('hidden');
        $add_btn = $(this).remove();
    });

    $(document).on('click', 'button.remove', function () {

        // parent = $(this).closest('.row-fluid');

        if ($(this).closest('.row-fluid').is(":first-child"))
            $(this).closest('.row-fluid').next().remove();
        else {
            $(this).closest('.row-fluid').prev().remove();
            if ($(this).closest('.row-fluid').is(":last-child"))
                $(this).closest('.row-fluid').prev().find(".add_remove_btn").prepend($add_btn);
        }

        if ($(this).closest('.row-fluid').siblings().length === 1)
            $(this).closest('.row-fluid').siblings('.row-fluid').find('.remove').addClass('hidden');

        $(this).closest('.row-fluid').remove();

    });

    // AND-OR Toggle Function
    $(document).on('click', ".btn-toggle", function () {
        if ($("a", this).hasClass("disabled"))
            return;
        if ($(this).find('.btn-primary').length > 0) {
            if ($(this).parent().attr('id') === "phenotypeContainer") {
                $(this).parent().find('.logic_phenotype .btn').toggleClass('active');
                $(this).parent().parent().find('.logic_phenotype .btn').toggleClass('btn-primary');
            } else {
                $(this).find('.btn').toggleClass('active');
                $(this).find('.btn').toggleClass('btn-primary');
            }
        }
    });

    $(document).on('click', '.icon-remove-circle', function () {
        $(this).parent().siblings('input').val('').focus();
    });

    $("#reset_phenotype").click(function(e) {
        e.preventDefault();
        $('input').val('');
        $('select').prop('selectedIndex',0);
        $(".keys").select2('val', 'All');
    });

    // $(document).on('click', ".clear_all_textbox", function () {
    //     $('input').val('');
    // });

//        $idCount = 1;
    $("#buildQuery").click(function () {
        $("#query_result").empty();
//        $('#waiting').show(500);

        $idCount = 1;

        phe = validate_Phenotype("buildQueryEvent");

        if (!phe) {
            console.log("Build Query: Not Validated");
            return false;
        }
        
        $genotype_phenotype = $('#logic_genotype_phenotype .active').html();
        $phen_phen = $('.logic_phenotype .active').html() ? $('.logic_phenotype .active').html() : "";

        $query = "";

        $arr = {
            "queryMetadata": {
                "queryId": "<identifier>",
                "queryType": "once|periodic",
                "queryStart": "<Date, Time>",
                "queryStop": "<Date, Time>",
                "queryLabel": "<identifier>",
                "queryResultLevel": "Exists|Counts|Records",
                "submitter": {
                    "id": "SubmitterPersonID",
                    "name": "First [Middle] Last",
                    "email": "email@domain.com",
                    "institution": "AffiliationOfSubmitterPerson",
                    "urls": ["SubmitterPersonalURL", "..."]
                },
                "contact": {
                    "id": "ContactPersonID",
                    "name": "First [Middle] Last",
                    "email": "email@domain.com",
                    "institution": "AffiliationOfContactPerson",
                    "urls": ["ContactPersonURL", "..."]
                }
            },
            "query": {
                "phenotypeFeature": getJSON_Phenotype()
            }
        };

        $.each($arr.query, function (key, value) {
            if (value.length === 0)
                delete $arr.query[key];
        });

        if (Object.keys($arr.query).length === 0) {
            alert("You have to select at least one type in order to proceed to a query!");
            return false;
        }
        
        $.extend($arr, {"queryStatement": $query, "network_to_search": $network_key});
        console.log(JSON.stringify($arr, null, '\t'));
        // $arr = JSON.stringify($arr, null, '\t');
//        alert("queryString -> " + JSON.stringify($arr));

        $('#waiting').show(500);
        $.ajax({url: baseurl + 'discover/executeQuery/' + $network_key,
            dataType: 'html',
            delay: 200,
            type: 'POST',
            data: {'jsonAPI': $arr},
            success: function (data) {
//                        alert('test -> ' + data);
                // console.log(data);
                $('#waiting').hide(500);
                $("#query_result").html(data);
            }
        });

    });
    
    function getJSON_Phenotype() {
        $parentId = $("#phenotypeContainer");
        $parentType = $parentId.attr('data-type');
        $query = "";
        $arr = [];
        $parentId.children('.type_sample').each(function () {
            if ($(this).find('select.keys').val().trim()) {
                
                $phenotype = {
                    "querySegmentID": $idCount,
                    "operator": $(this).find('.conditions').val().toString(),
                    "phenotypeConcept": {
                        "cursivePhenotypeConcept": {"term": $(this).find('select.keys').val().toString(), "source": ""}
                    },
                    "phenotypeFeature": {"value": $(this).find('.phenotype_values').val().toString(), "units": "", "source": ""}
                };

                $arr.push($phenotype);
                if($query == "")
                    $query = $idCount;
                else
                    $query = $query + " " + $phen_phen + " " + $idCount;
                $idCount++;
            }
        });

        $query = "(" + $query + ")";
        
//        console.log($phen);
//        console.log(JSON.stringify($arr, null, "\t"));
        
        return $arr;
    }

    function validate_Phenotype($for) {
        $parentId = $("#phenotypeContainer");
        $parentType = $parentId.attr('data-type');

        if ($for === "collapseEvent") {
            if ($parentId.children('.type_sample').length === 1) {
                if ($parentId.find('select.keys').val().trim().length > 0)
                {
                    $.notify({
                        // options
                        message: "Non-empty sections cannot be collapsed."
                      },{
                        // settings
                        timer: 200
                      });
                    return false;
                }
            } else {
                $error = false;
                $parentId.children('.type_sample').each(function () {
                    if ($(this).find('select.keys').val().trim().length > 0) {
                        $.notify({
                            // options
                            message: "Non-empty sections cannot be collapsed."
                          },{
                            // settings
                            timer: 200
                          });
                        $error = true;
                        return false;
                    }
                });

                if ($error)
                    return false;
            }
            return true;
        } else if ($for === "buildQueryEvent") {
            $error = false;
            $parentId.children('.type_sample').each(function () {
                if ($(this).find('select.keys').val().trim().length > 0) {
                    condition_value = $(this).find('.conditions').val();
                    field_value = $(this).find('.phenotype_values').val();
                    if (field_value === "--Select a value--") {
                         $.notify({
                            // options
                            message: "You have not entered a phenotype value(s)"
                          },{
                            // settings
                            timer: 200
                          });
                        $error = true;
                    } else if (!phenotype_validation(condition_value, field_value)) {
                        $error = true;
                    }
                }
            });
            return !$error;
        }
    }

    function phenotype_validation(condition_value, field_value) {
        if (condition_value === '>' || condition_value === '<' || condition_value === '>=' || condition_value === '<='
                || condition_value === '=' || condition_value === '≠') {
            if (!isNumber(field_value)) {
                $.notify({
                        // options
                        message: "A numeric comparison operator was specified but the entered value is not numeric, unable to proceed with the query."
                      },{
                        // settings
                        timer: 200
                      });
                return false;
            }
            return true;
        }
        // Throw error if NULL query entered with anything apart from IS or IS NOT
        else if (condition_value.toLowerCase() === 'is like' || condition_value.toLowerCase() === 'is'
                || condition_value.toLowerCase() === 'is not' || condition_value.toLowerCase() === 'is not like') {
            if (condition_value.toLowerCase() === 'is like' || condition_value.toLowerCase() === 'is not like') {
                if (field_value.toUpperCase() === 'NULL') {
                    $.notify({
                        // options
                        message: "NULL queries are only possible with 'IS' or 'IS NOT' operators, unable to proceed with the query."
                      },{
                        // settings
                        timer: 200
                      });
                    return false;
                } else if (isNumber(field_value)) {
                    $.notify({
                        // options
                        message: 'You have specified a string comparison operator but supplied a numeric value. Query may not return proper results.'
                      },{
                        // settings
                        timer: 200
                      });
                }
            } else {
                if (isNumber(field_value)) {
                    $.notify({
                        // options
                        message: 'You have specified a string comparison operator but supplied a numeric value. Query may not return proper results.'
                      },{
                        // settings
                        timer: 200
                      });
                    
                }
            }

            return true;
        }
    }

    $(document).on('focus', '.conditions', function () {
        $(this).find('option[value="---------------"]').attr('disabled', 'disabled');
    });
});