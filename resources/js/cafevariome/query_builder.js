$('#reset_query').click(function() {
    location.reload();
});
var result_data = {};
var source_data = {};
var attributesValues = {};
var attributesDisplayNames = {};
var valuesDisplayNames = {};

var queryXHR = null;

$( function() {
    $( "#age-range" ).slider({
        range: true,
        min: 0,
        max: 99,
        values: [ 0, 99 ],
        slide: function( event, ui ) {
            $("#age-value").val(ui.values[0] + " - " + ui.values[1]);
        }
    });

    $("#age-value").val($("#age-range").slider("values", 0) + " - " + $("#age-range").slider("values", 1));

    $( "#age-diagnosis-range" ).slider({
        range: true,
        min: 0,
        max: 99,
        values: [ 0, 99 ],
        slide: function( event, ui ) {
            $("#age-diagnosis-value").val(ui.values[0] + " - " + ui.values[1]);
        }
    });

    $("#age-diagnosis-value").val($("#age-diagnosis-range").slider("values", 0) + " - " + $("#age-diagnosis-range").slider("values", 1));

    $( "#age-first-symptoms-range" ).slider({
        range: true,
        min: 0,
        max: 99,
        values: [ 0, 99 ],
        slide: function( event, ui ) {
            $("#age-first-symptoms-value").val(ui.values[0] + " - " + ui.values[1]);
        }
    });

    $("#age-first-symptoms-value").val($("#age-first-symptoms-range").slider("values", 0) + " - " + $("#age-first-symptoms-range").slider("values", 1));

    $( "#similarity-rel-range" ).slider({
        range: "min",
        min: 0,
        max: 1,
        value: 1,
        step: 0.05
    });

    $( "#similarity-rel-range-ordo" ).slider({
        range: "min",
        min: 0,
        max: 1,
        value: 1,
        step: 0.05
    });

    $( "#match-scale-ordo" ).slider({
        range: "min",
        min: 0,
        max: 100,
        value: 100,
        step: 1
    });

    var handle = $( "#sr-handle" );

    $( "#similarity-range" ).slider({
        range: "min",
        min: 0,
        max: 0,
        value: 1,
        step: 1,
        create: function() {
            if ($(this).slider("value") != 0) {
                handle.text($(this).slider("value"));
            }
        },
        slide: function( event, ui ) {
            handle.text( ui.value );
        }
    });

    $( "#similarity-range" ).slider('disable');

    $( "[type=radio]" ).checkboxradio({
        icon: false
    });

});


$(function() {
    // urls object
    const urls = {'qb_config': baseurl + 'resources/js/config.json',
                  'qb_json': baseurl + 'resources/js/querybuilder.json',
                  'phen_json': baseurl + 'AjaxApi/getPhenotypeAttributes/' + $('#network_key').val()
                };
    // error object
    const error = {
        'load_config': 'Error: Unable to load query builder config file.',
        'load_json': 'Error: Unable to load query builder json file.',
        'load_phen_json': 'Error: Unable to load phenotype.json',
        'NaN': 'A numeric comparison operator was specified but the entered value is not numeric, unable to proceed with the query.',
        'null' : 'NULL queries are only possible with "IS" or "IS NOT" operators, unable to proceed with the query.',
        'str_cmp': 'You have specified a string comparison operator but supplied a numeric value. Query may not return proper results.'
    }

    String.prototype.isNumber = function(){ return !isNaN(parseFloat(this)) && isFinite(this) }
    String.prototype.isEmpty = function(){ return !this.trim().length > 0 }
    // Split the string by the delimiter specified, capitalized first character of each word & then joins each word by a space.
    String.prototype.titleCase = function(delimiter) {
        if(this === 'NULL') return this
        str = this.toLowerCase().split(delimiter).map((word)=> word.charAt(0).toUpperCase() + word.slice(1))
        return str.join(' ')
    }

    $('#reset_query').click(function() {
        location.reload();
    });

    //load_qb_config()
    load_phen_json();
    var template = {}
    var phen_data = {};
    // Load phenotype json and then load JSON API template if successful
    function load_phen_json() {
        var query_builder_post = getCSRFToken();
        $.ajax({
            type: 'post',
            url: urls['phen_json'],
            data:query_builder_post,
            dataType: 'json'
        })
        .done((jsonData)=> {
            attributesValues = jsonData['attributes_values'];
            attributesDisplayNames = jsonData['attributes_display_names'];
            valuesDisplayNames = jsonData['values_display_names'];

            for (const [attribute, values] of Object.entries(attributesValues)) {
                $('select.keys_pat').append($('<option></option>').attr('value', attribute).text(attributesDisplayNames[attribute][0]))
            }

            template['patient'] = $('.rule')[0].outerHTML
            template['genotype'] = $('.rule')[1].outerHTML

            //$('select#values_phen_left').filterByText($('#search_filter_phen_left'));

            initSelect2();
        })
        .fail(()=> alert(error['load_phen_json']))
    }

    $search_str = ''
    $('#search_filter_phen_left').keyup(function() {
        if($search_str == $(this).val()) return;
        $('select#values_phen_left').empty()
        var arrayToReduce = $(this).val().trim().split(' ').filter((term) => term.length != 1);
        str = (arrayToReduce.length > 0) ? arrayToReduce.reduce((v1, v2) => v1 + " " + v2) : '';
        $.getJSON('https://www185.lamp.le.ac.uk/EpadGreg/hpo/query/' + (str) + '/0/1', (data) => {
            $('select#values_phen_left').empty()
            data.forEach((term) => {
                $('select#values_phen_left').append($('<option></option>').attr('value', term).text(term))
            })
        })
            .fail(function() {  });
        $search_str = $(this).val()
    })


    // var hpo_json = {};
    // $.ajax({
    //     dataType: "json",
    //     url: baseurl + "AjaxApi/HPOQuery",
    //     data: null,
    //     success: function(data){
    //         hpo_json = data;
    //         init_hpotree(hpo_json);
    //     }
    // });

    // Load JSON API template and initialise query builder if successful
    function load_json_api_template(qb_config, phen_attrib) {
        $.ajax({ url: urls['qb_json'], dataType: 'json', })
        .done()
        .fail(()=> alert(error['load_json']))
    }

    function initSelect2() {
        $('select.keys').select2({ allowClear: true, placeholder: 'Select an attribute', dropdownAutoWidth: 'true' });
        $('select.conditions').select2({ allowClear: true, placeholder: 'Select operator', dropdownAutoWidth: 'true' });
        $('select.keys_altaf').select2({placeholder: 'Select an attribute', dropdownAutoWidth: 'true' });
        $('select.keys_pat').select2({placeholder: 'Select an attribute', dropdownAutoWidth: 'true' });
        $('select.values_altall').select2({allowClear: true, placeholder: 'ALT', dropdownAutoWidth: 'true' });
        $('select.values_refall').select2({allowClear: true, placeholder: 'REF', dropdownAutoWidth: 'true' });

        $('select.values').select2({ allowClear: true, placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
        $('select.values_pat').select2({ allowClear: true, placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
        $('select.values_pos').select2({ allowClear: true, placeholder: 'Select/Input position', dropdownAutoWidth: 'true' });
        $('select.values_altaf').select2({ allowClear: true, placeholder: 'Select/Input value'});
    }

    $(document).on('change', "select.keys_pat", function () {
        $(this).closest('.rule').find('select.values_pat').select2('destroy')
        $val = $(this).closest('.rule').find('select.values_pat');
        $val.empty()
        $val.append('<option></option>');

        attributesValues[$(this).val()].forEach(function(val) {
            $val.append($('<option>', {
                value: val,
                text: valuesDisplayNames[val][0]
            }));    
        })
        $val.select2({ allowClear: true, placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
    })

    $('button.btnRemove').click(() => {
        $("#similarity-range").slider('disable');
        var max_val = $("#similarity-range").slider( "option", "max" );
        var items_count = 0;
        $('#values_phen_right :selected').each((key, el) => {
            var txt = $(el).val();
            $("#values_phen_right option[value='" + txt + "']").remove();
            items_count++;
        });
        $('#values_phen_right').filterByText($('#search_filter_phen_right'));
        $("#similarity-range").slider( "option", "max", max_val - items_count );

        if (max_val - items_count > 0) {
            $("#similarity-range").slider('enable');
        }
        else{
            $("#similarity-range").slider( "option", "min", 0);
            $("#similarity-range").slider( "option", "max", 0);
            $("#similarity-range").slider( "option", "value", 1);
        }
        if ($("#similarity-range").slider('values', 0) != 0) {
            $("#sr-handle").text($("#similarity-range").slider('values', 0));
        }
        else{
            $("#sr-handle").text('');
        }
    });

    $('button.btnAdd').click(() => {
        $("#similarity-range").slider('disable');
        var max_val = $("#similarity-range").slider( "option", "max" ); // 1 at start
        var items_count = 0;
        $('#values_phen_left :selected').each((key, el) => {
            var txt = $(el).text();
            if ($("#values_phen_right option[value='" + txt + "']").length == 0) {
                $('#values_phen_right').append($("<option></option>").attr("value", txt).text(txt));    
                items_count++;
            }
        });

        $('#values_phen_right').filterByText($('#search_filter_phen_right'));

        if ((max_val + items_count) == 1) {
            $("#similarity-range").slider( "option", "min", 0);
            $("#similarity-range").slider( "option", "value", 1);
            $("#similarity-range").slider( "option", "max", items_count + max_val);
        }
        else if (max_val ==  $("#similarity-range").slider( "option", "value")) {
            $("#similarity-range").slider( "option", "max", items_count + max_val);
            $("#similarity-range").slider( "option", "min", 1);
            $("#similarity-range").slider( "option", "value", items_count + max_val);
            $("#similarity-range").slider('enable');
        }
        else{
            $("#similarity-range").slider( "option", "min", 1);
            $("#similarity-range").slider( "option", "max", items_count + max_val);
            $("#similarity-range").slider('enable');
        }
        $( "#sr-handle" ).text($("#similarity-range").slider('values', 0));
    });



    function logic_eav(rule, eav, logic) {
        if(typeof rule[1] !== 'undefined' && typeof rule[2] !== 'undefined' && rule[1] !== '' && rule[2] !== '') {
            eav.push({'attribute' : rule[0], 'operator': rule[1], 'value': rule[2]})
            logic['-AND'].push("/query/components/eav/" + (eav.length-1))
        }
    }

    $('#build_query').click(() => {
        $('#waiting').show();
        $('#build_query').addClass('disabled');
        $('#cancel_query').show();
        $('#reset_query').hide();
        $('#query_result tbody').html('')
        $.ajax({ url: urls['qb_json'], dataType: 'json'})
        .done((jsonAPI) => {

            var attributes = [] // Attributes that need to be extracted from sources after query go here. Do not add subject_id as it is included implicitly.
            var logic = {"-AND": []}
            var eav = []
            var phe = []
            var gen = []
            var mutation = []
            var ordo = []

            $('#pat_container .rule').each(function() {
                var attr = $('select.keys_pat', this).val()
                var opr = $('select.conditions', this).val()
                var val = $('select.values_pat', this).val()
                if(val != '') {
                    logic_eav([attr, opr, val], eav, logic);
                }
            })

            // Gender
                // logic_gender = []
                // eav.push({'attribute' : "Gender", 'operator': "is", 'value': "m"})
                // logic_gender.push("/query/components/eav/" + (eav.length-1))
                // eav.push({'attribute' : "Gender", 'operator': "is", 'value': "f"})
                // logic_gender.push("/query/components/eav/" + (eav.length-1))
                // logic['-AND'].push({'-OR': logic_gender})
            

            // logic_eav(['sex', 'is', $('#values_sex').val()], eav, logic);
            // logic_eav(['age', $('select.oprAge').val(), $('select.values_age').val()], eav, logic);
            // logic_eav(['tissue', 'is', $('#values_tissue').val()], eav, logic);

            // logic_haplo = [];
            // $("#values_haplo_right > option").each(function() {
            //     eav.push({'attribute' : "haplogroup", 'operator': "is", 'value': this.value})
            //     logic_haplo.push("/query/components/eav/" + (eav.length-1))
            // });
            // if(logic_haplo.length > 1) {logic['-AND'].push({'-OR': logic_haplo})}

            var phenLogic = 'SIM';
            logic_phen = [];

            sim = [];
            if(phenLogic === 'SIM' && $("#values_phen_right option").length > 0) {
                terms = [];
                $("#values_phen_right option").each(function() { terms.push($(this).val().split(' ')[0].replace(/[()]/g, ''))})

                    sim[0] = {
                        'r': $( "#similarity-rel-range" ).slider('values', 0),
                        's': $( "#similarity-range" ).slider('values', 0),
                        'ORPHA': $('#includeORPHA').prop( "checked"),
                        'ids': terms
                    }
                    logic['-AND'].push('/query/components/sim/0')
            } else {
                $("#values_phen_right option").each(function() { 
                    var term = $(this).val().split(' ')[0].replace(/[()]/g, '')

                    if(phenLogic === 'AND') {
                        phe.push({'attribute' : "phenotypes_id", 'operator': "is", 'value': term})
                        logic['-AND'].push("/query/components/phenotype/" + (phe.length-1))
                    } else {
                        phe.push({'attribute' : "phenotypes_id", 'operator': "is", 'value': term})
                        logic_phen.push("/query/components/phenotype/" + (phe.length-1))
                    }
                });
                if(logic_phen.length > 1 && phenLogic === 'OR') {logic['-AND'].push({'-OR': logic_phen})}    
            }
            
            
            var genLogic = 'AND'; //$('#gen_logic a.active').html();
            var logic_gen = [];
            $('#gen_container .rule').each(function() {
                v = {
                        'chr' : $('#values_chr', this).val(),
                        'start' : $('input.values_start', this).val(),
                        'end' : $('input.values_end', this).val(),
                        'referencebases' : $('select.values_refall', this).val(),
                        'alternatebases' : $('select.values_altall', this).val()
                    };
                if(v['chr'] !== '' && v['referencebases'] !== '' && v['alternatebases'] !== '') {
                    gen.push(v);
                    if(genLogic === 'AND') {
                        logic['-AND'].push("/query/components/subjectVariant/" + (gen.length-1))
                    } else {
                        logic_gen.push("/query/components/subjectVariant/" + (gen.length-1))    
                    }
                }
            });

            if($("#ordoSelect").val().length == 1){
                ordo[0] = {
                    'r': $("#similarity-rel-range-ordo").slider('values', 0),
                    's': $("#match-scale-ordo").slider('values', 0),
                    'id': [$("#ordoSelect").val()[0].split(' ')[0]],
                    'HPO': $('#includeHPO').prop( "checked")
                }
                logic['-AND'].push("/query/components/ordo/" + (ordo.length-1))
            }

            if(logic_gen.length > 1 && genLogic === 'OR') {logic['-AND'].push({'-OR': logic_gen})}

            jsonAPI['requires']['response']['components']['attributes'] = attributes;
            jsonAPI['query']['components']['eav'] = eav;
            jsonAPI['query']['components']['subjectVariant'] = gen;
            jsonAPI['query']['components']['phenotype'] = phe;
            jsonAPI['query']['components']['sim'] = sim;
            jsonAPI['query']['components']['ordo'] = ordo;

            jsonAPI['logic'] = logic;
            var csrfTokenObj = getCSRFToken('keyvaluepair');
            var queryData = {'jsonAPI': jsonAPI, 'network_key': $('#network_key').val()};
            var csrfTokenName = Object.keys(csrfTokenObj)[0];
            queryData[csrfTokenName] = csrfTokenObj[csrfTokenName];

            queryXHR = $.ajax({url: baseurl + 'AjaxApi/query',
                type: 'POST',
                data: queryData,
                dataType: 'json',
                success: function (data) {
                    result_data = {};
                    source_data = {};
                    $.each(data, function(key, val) {
                        if(key == 'error'){
                            trow = "<tr><td>Error</td><td>" + val + "</td><td></td></tr>";
                            $('#query_result tbody').append(trow);
                        }
                        else if (key == 'timeout') {
                            $('#timeoutalert').show();
                            window.scrollTo(0, 0); 
                        }
                        else if(val.length > 0) {
                            resp = $.parseJSON(val)
                            $.each(resp, function(key, val1) {
                                //$('#resTbl tbody').empty();
                                trow = "<tr id = " + key + "><td>" + key + "</a></td>";
                                if (val1['records']['subjects'] != "Access Denied") {
                                    var records = val1['records']['subjects'];
                                    var source_display = val1['source_display'];
                                    source_data[key] = val1['details'];
                                    result_data[key] = records;
                                    trow += '<td>';
                                    if (records.length > 0 || Object.keys(records).length > 0) {
                                        if (source_display){
                                            trow += '<a type="button" class="btn btn-primary active" data-toggle="modal" data-target="#resultModal" data-sourcename="' + key + '">' + Object.keys(records).length + '</a>';
                                        }
                                        else{
                                            trow +=  Object.keys(records).length;
                                        }
                                    }
                                    else{
                                        trow += '0';
                                    }
                                    trow += '</td><td>';

                                    if (source_display){
                                        trow += '<a type="button" class="btn btn-info active" data-toggle="modal" data-target="#sourceModal" data-sourcename="' + key + '"><i class="fa fa-database"></i></a>';
                                    }
                                    else{
                                        trow += 'Not Available';
                                    }
                                    trow += '</td>';
                                }
                                else{
                                    trow += '<td>Access Denied</td><td>-</td>';
                                }
                                trow += "</tr>";
                                    $('#query_result tbody').append(trow);
                                //}
                            })    
                        }
                    })
                },
                'complete': function(data) {
                    $('#query_result').show();
                    $('#build_query').removeClass('disabled');
                    $('#waiting').hide();
                    $('#cancel_query').hide();
                    $('#reset_query').show();
                },
            })

            $('#cancel_query').click(()=> {
                queryXHR.abort();
                $('#cancel_query').hide();
            });

        }).fail(()=> alert(error['load_json']));
    })

    // Bootstrap notify plugin
    function notify(title, msg, type) { 
        $.notify({
            title: '<strong>' + title + ' </strong>', 
            message: msg, 
            icon: 'glyphicon glyphicon-' + (type === 'danger' ? 'remove' : 'info') + '-sign'
        }, 
        {type: type, delay: 5000}) 
    }

    $(document).on('click', ".btn-collapse", function () {
        $parent = $(this).parent().parent().parent();
        if ($(this).attr("data-collapseStatus") === "false") {
            $(this).removeClass("btn-info").addClass("btn-success");
            $(this).find('i').removeClass("icon-chevron-left").addClass("icon-chevron-down");
            $(this).parent().parent().next().collapse('show').addClass('container_border');
            $(this).attr("data-collapseStatus", "true");
            $parent.prev().children('a').removeClass('disabled');
        } else {
            $collapse = true;
            // $collapse = validate_Phenotype("collapseEvent")
            if ($collapse) {
                $(this).removeClass("btn-success").addClass("btn-info");
                $(this).find('i').removeClass("icon-chevron-down").addClass("icon-chevron-left");
                $($(this).parent().parent().next().collapse('hide')).removeClass("container_border");
                $(this).attr("data-collapseStatus", "false");
                $parent.prev().children('a').addClass('disabled');
            }
        }
    });


    $(document).on('click', ".btn-add", function () {
        var $rule = $(template[$(this).attr('data-rule')]);
        $rule.find('.btn-remove').show();
        $(this).closest('.rule').find('.btn-add').hide();
        $(this).closest('.rule').find('.btn-remove').show();
        $('select.attribute').select2('destroy');
        $('select.operator').select2('destroy');
        $('select.value').select2('destroy');
        if($(this).attr('data-rule') === 'patient') {
            $('#pat_container').append($rule);
        } else if($(this).attr('data-rule') === 'genotype') {
            $('#gen_container').append($rule);
        }
        initSelect2();
    });

    $(document).on('click', ".btn-remove", function () {
        var $rule = $(this).closest('.rule')

        if($rule.is(':first-child')) {} 
        else { 
            if($rule.is(':last-child')) { 
                $rule.prev().find('.btn-add').show() ;
            } 
        }
        if($rule.siblings().length === 1) { 
            $rule.siblings().find('.btn-remove').hide();
        }
        $rule.remove()
    });

    $('#resultModal').on('shown.bs.modal', function (e) {
        $('#resTbl').hide();
        $('#loader').show();

        var src = $(e.relatedTarget).data('sourcename');
        var source_results = result_data[src];
        var ic = 1;
        var resRow = '';
        $.each(source_results, function (rkey, rval) {
            resRow += '<tr><td>' + ic + '</td><td>' + rval + '</td></tr>'
            ic++;
        })

        $('#resTbl tbody').append(resRow);

        if ($('#resTbl').length) {
            $('#resTbl').DataTable();
        }

        $('#loader').hide();
        $('#resTbl').show();

    });

    $('#resultModal').on('hidden.bs.modal', function (e) {
        $('#resTbl').DataTable().destroy();
        $('#resTbl tbody').empty();
    });

    $('#sourceModal').on('show.bs.modal', function (e) {

        var src = $(e.relatedTarget).data('sourcename');
        var source_results = source_data[src];
        $('#source_name').html('<p class="ml-1">' + source_results['name'] + '</p>');
        $('#source_owner').html('<p class="ml-1">' + source_results['owner_name'] + '</p>');
        $('#source_owner_email').html('<p class="ml-1">' + source_results['email'] + '</p>');
        $('#source_uri').html('<p class="ml-1">' + source_results['uri'] + '</p>');
        $('#source_description').html('<p class="ml-1">' + source_results['description'] + '</p>');
        $('#source_long_description').html('<p class="ml-1">' + source_results['long_description'] + '</p>');
    });

    $('#sourceModal').on('hidden.bs.modal', function (e) {
        $('#source_name').html('');
        $('#source_owner').html('');
        $('#source_owner_email').html('');
        $('#source_uri').html('');
        $('#source_description').html('');
        $('#source_long_description').html('');
    });

        // https://stackoverflow.com/a/6647367/5510713
    jQuery.fn.filterByText = function(textbox) {
      return this.each(function() {
        var select = this;
        var options = [];
        $(select).find('option').each(function() {
          options.push({value: $(this).val(), text: $(this).text()});
        });
        $(select).data('options', options);

        $(textbox).bind('change keyup', function() {
          var options = $(select).empty().data('options');
          var search = $.trim($(this).val());
          var regex = new RegExp(search, "gi");
          $.each(options, (i) => {
            var option = options[i];
            if (option.text.match(regex) !== null) {
              $(select).append($('<option>').text(option.text).val(option.value));
            }
          });
        });
      });
    };

    $(document).on('click', ".btn-logic", function () {
        if($(this).hasClass('btn-secondary')) {
            $(this).addClass('active').addClass('btn-primary').removeClass('btn-secondary')
            $(this).siblings().removeClass('active').addClass('btn-secondary').removeClass('btn-primary')
        }
    });

});

$(document).ready(function() {
    var optionData = [];
    load_Ordo();
    function load_Ordo() {
        $.ajax({ url: baseurl + 'ContentAPI/loadOrpha'  })
        .done((d)=>{ 
            d.forEach( (item)=>{
                optionData.push(item);
            })
             $('#ordoSelect').select2({
                placeholder: 'Choose Ordo term',
                allowClear: false,
                width: '100%',
                maximumSelectionLength: 1,
                minimumInputLength: 2 ,
                data:optionData
            });
        })
    }
});

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