$(function() {
    // urls object
    const urls = {'qb_config': baseurl + 'resources/js/config.json',
                  'qb_json': baseurl + 'resources/js/querybuilder.json',
                  'phen_json': baseurl + 'AjaxApi/getPhenotypeAttributes/' + $('#network_key').val()
                }
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
        var phen_attrib = []
        $.ajax({ url: urls['phen_json'], dataType: 'json'})
        .done((jsonData)=> { 
            phen_data = jsonData[0];

            $.each( jsonData[0].chr, function( key, value ) {
                $('select.values_chr').append($('<option></option>').attr('value', value.toLowerCase()).text('Chr:' + value));
            });

            $.each( jsonData[0].alternatebases,function( key, value ){
                $('select.values_altall').append($('<option></option>').attr('value', value.toLowerCase()).text(value))
            });

            $.each(jsonData[0].referencebases,function( key, value ) {
                $('select.values_refall').append($('<option></option>').attr('value', key.toLowerCase()).text(value))
            });

            $.each(JSON.parse(jsonData[1]), (hpo, ancestry) => {
                $('select#values_phen_left').append($('<option></option>').attr('value', ancestry).text(hpo))
            })

            // $.each(attributes, function(k, v) {
            //     $('select.keys_pat').append($('<option></option>').attr('value', v.toLowerCase()).text(k))
            // })
            
            $.each(phen_data, function(k, v) {
                $('select.keys_pat').append($('<option></option>').attr('value', k).text(k))
            })

            template['patient'] = $('.rule')[0].outerHTML
            template['genotype'] = $('.rule')[1].outerHTML

            $('select#values_phen_left').filterByText($('#search_filter_phen_left'));

            initSelect2();
        })
        .fail(()=> alert(error['load_phen_json']))
    }

    var hpo_json = {};

    $.ajax({
        dataType: "json",
        url: baseurl + "AjaxApi/HPOQuery",
        data: null,
        success: function(data){
            hpo_json = data;
            init_hpotree(hpo_json);
        }
    });

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

        phen_data[$(this).val()].forEach(function(val) {
            $val.append($('<option>', {
                value: val,
                text: val.toUpperCase()
            }));    
        })
        $val.select2({ allowClear: true, placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
    })


    function logic_eav(rule, eav, logic) {
        if(typeof rule[1] !== 'undefined' && typeof rule[2] !== 'undefined' && rule[1] !== '' && rule[2] !== '') {
            eav.push({'attribute' : rule[0], 'operator': rule[1], 'value': rule[2]})
            logic['-AND'].push("/query/components/eav/" + (eav.length-1))
        }
    }

    var modal_data = {};

    $('#build_query').click(() => {
        $('#waiting').show();
        $('#build_query').prop('disabled', 'true');
        $('#query_result tbody').html('')
        $.ajax({ url: urls['qb_json'], dataType: 'json', })
        .done((jsonAPI) => {

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

            var phenLogic = $('#phen_logic a.active').html();
            logic_phen = [];

            sim = [];
            if(phenLogic === 'SIM' && $("#values_phen_right option").length > 0) {
                terms = [];
                $("#values_phen_right option").each(function() { terms.push($(this).val().split(' ')[0].replace(/[()]/g, ''))})

                    if($('#rel').is(':checked')) {
                        sim[0] = {
                            'r': $('#r').val(),
                            's': $('#s').val(),
                            'ids': terms
                        }
                    }

                    if($('#jc').is(':checked')) {
                        sim[0] = {
                            'j': $('#j').val(),
                            'ids': terms
                        }
                    }
                    logic['-AND'].push('/query/components/sim/0')
            } else {
                // $("#jstree_hpo").jstree("get_selected").forEach(function(term) {
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
            if(logic_gen.length > 1 && genLogic === 'OR') {logic['-AND'].push({'-OR': logic_gen})}

            jsonAPI['query']['components']['eav'] = eav;
            jsonAPI['query']['components']['subjectVariant'] = gen;
            jsonAPI['query']['components']['phenotype'] = phe;
            jsonAPI['query']['components']['sim'] = sim;
            jsonAPI['logic'] = logic;

            $.ajax({url: baseurl + 'AjaxApi/query/' + $('#network_key').val(),
                dataType: 'html',
                delay: 200,
                type: 'POST',
                data: {'jsonAPI': jsonAPI, 'user_id': $('#user_id').val()},
                dataType: 'json',
                success: function (data) {
                    $.each(data, function(key, val) {
                        if(key == 'error'){
                            trow = "<tr><td>Error</td><td>" + val + "</td></tr>";
                            $('#query_result tbody').append(trow);
                        }
                        else if (key == 'timeout') {
                            $('#timeoutalert').show();
                            window.scrollTo(0, 0); 
                        }
                        else if(val.length > 0) {
                            resp = $.parseJSON(val)
                            $.each(resp, function(key, val1) {
                                $('#resTbl tbody').empty();
                                trow = "<tr id = " + key + "><td>" + key + "</a></td>";
                                if (val1 != "Access Denied") {
                                        if (val1.length > 0) {
                                            var ic = 1;
                                            $.each(val1, function (rkey, rval) {
                                                $('#resTbl tbody').append('<tr><td>' + ic + '</td><td>' + rval + '</td></tr>');
                                                ic++;
                                            })
                                            trow += '<td><a type="button" class="btn btn-primary active" data-toggle="modal" data-target="#resultModal">' + Object.keys(val1).length + '</a></td>';
                                        }
                                        else if( Object.keys(val1).length > 0){
                                            var ic = 1;
                                            $.each(val1, function (rkey, rval) {
                                                $('#resTbl tbody').append('<tr><td>' + ic + '</td><td>' + rval + '</td></tr>');
                                                ic++;
                                            })
                                            trow += '<td><a type="button" class="btn btn-primary active" data-toggle="modal" data-target="#resultModal">' + Object.keys(val1).length + '</a></td>';
                                        }
                                        else{
                                            trow += '<td>0</td>';
                                        }
                                    }
                                    else{
                                        trow += '<td>Access Denied</td>';
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
                },
            })
        }).fail(()=> alert(error['load_json']));
    })

    setTimeout(() => {$('#isPhenotype').trigger('click')}, 200)
    setTimeout(() => {$('#isGenotype').trigger('click')}, 200)
    setTimeout(() => {$('#isDemographic').trigger('click')}, 200)
    setTimeout(() => {$('#ishaplogroup').trigger('click')}, 200)
    setTimeout(() => {$('#istissue').trigger('click')}, 200)
    setTimeout(() => {$('#isPatient').trigger('click')}, 200)

    $(document).on('click', ".hover_test", function (e) {
        e.preventDefault()
        
    });

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
    })

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