$(function() {
  
    // console.log alias
    const log = console.log.bind(console)

    // urls object
    const urls = {'qb_config': baseurl + 'resources/js/config.json', 'qb_json': baseurl + 'resources/js/querybuilder.json', 'phen_json': baseurl + 'admin/get_phenotype_attributes_for_network/' + $('#network_key').val() }
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

    var attributes = {
                        'MedicationName' : 'medicationname'
                        //'DOB' : "DOB",
                        //'Gender': "Gender",
                        //"forename": "Forename",
                        //"postcode": "Postcode",
                        //"surname": "Surname",
                        //"snomed_term": "snomed_term"
                    };

    
    var secattributes = {
        'AttendanceType' : "AttendanceType",
        'AttendanceDate': "AttendanceDate"
    };
    // Entry point of script: Load config file and then load phenotype json if successful
    var load_qb_config = ()=>
        $.ajax({url: urls['qb_config'], dataType: 'json'})
        .done((jsonData)=> load_phen_json(jsonData))
        .fail(()=> alert(error['load_config']))

    //load_qb_config()
    var template = {}

    //Load predefined attributes
    $.each(attributes, function(k, v) {
        $('select.medication').append($('<option></option>').attr('value', v).text(k))
    })
    //Load predefined attributes
    $.each(secattributes, function(k, v) {
        $('select.secattendances').append($('<option></option>').attr('value', v).text(k))
    })
    template['patient'] = $('#pat_container .rule')[0].outerHTML
    template['secattendances'] = $('#secatend_container .rule')[0].outerHTML
    initSelect2();

    const removeEmpty = (obj) => {
      Object.keys(obj).forEach(key => {
        if(Object.entries(obj[key]).length === 0 && obj[key].constructor === Object) { delete obj[key]; }
        else if (obj[key] && typeof obj[key] === 'object' && obj[key].length === 0) { delete obj[key];}
        else if (obj[key] && typeof obj[key] === 'object') { removeEmpty(obj[key]);}
      });
    };

    $('button.btnRemove').click(() => {
        $('select#values_phen_right :selected').each((key, el) => {
            var txt = $(el).val().split(') ')[1] + ' ' + $(el).val().split(') ')[0] + ')';
            $('#jstree_hpo a:contains("' + txt + '")').each(function(e) {
                $("#jstree_hpo").jstree("deselect_node", $(this).attr('id'))
            })
        });
    });

    $('button.btnRemoveAuto').click(() => {
        $('select#values_phen_rightAuto :selected').each((key, el) => {
            $(el).remove()
            sortSelect('values_phen_rightAuto')
        });
    });
    

    $('button.btnAddAuto').click(() => {
        $('select#values_phen_leftAuto :selected').each((key, el) => {
            if($("#values_phen_rightAuto option[value='" + $(el).val() + "']").length === 0) {
                $('select#values_phen_rightAuto').append(
                    $("<option></option>").attr("value", $(el).val()).text($(el).html())
                )    
            }
            
            // $(el).remove()
            // sortSelect('values_phen_left')
            //sortSelect('values_phen_rightAuto')
        })
    })

    function initSelect2() {

        $('select.keys').select2({ allowClear: true, theme: 'classic', placeholder: 'Select an attribute', dropdownAutoWidth: 'true' });
        $('select.conditions').select2({ allowClear: true, placeholder: 'Select operator', dropdownAutoWidth: 'true', width: '100%' });
        $('select.keys_altaf').select2({theme: 'classic', placeholder: 'Select an attribute', dropdownAutoWidth: 'true' });
        $('select.attribute').select2({placeholder: 'Select an attribute', dropdownAutoWidth: 'true', width: '100%' });
        $('select.values_altall').select2({allowClear: true, theme: 'classic', placeholder: 'ALT', dropdownAutoWidth: 'true' });
        $('select.values_refall').select2({allowClear: true, theme: 'classic', placeholder: 'REF', dropdownAutoWidth: 'true' });

        $('select.values').select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
        $('select.values_pat').select2({ allowClear: true, placeholder: 'Select/Input value', dropdownAutoWidth: 'true',  width: '100%' });
        $('select.values_patAuto').select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
        $('select.values_pos').select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input position', dropdownAutoWidth: 'true' });
        $('select.values_altaf').select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value'});
    }

    $(document).on('change', "select.attribute", function () {
        $(this).closest('.rule').find('select.values_pat').select2('destroy')
        $val = $(this).closest('.rule').find('select.values_pat');
        $val.empty()

        $.getJSON(baseurl + 'AjaxApi/searchonindex/' + $('#network_key').val() + "/" + $(this).val() + "/true" , (data) => {
            data.forEach(function(v) {
                $val.append($('<option></option>').attr('value', v).text(v))
            })
        })
        .fail(function() { log('Autocomplete error.'); });;        

        $val.select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });

    }
    )

    $('#values_med').typeahead( { 
        highlighter: function (item) {

            this.query.trim().split(' ').forEach((term) => {
                    if(term.length > 1) {
                        item = item.replace(new RegExp( '(' + term + ')', 'gi' ), "<div style='background:gray;'><b class='custom-bold'>$1</b></div>" )    
                    }
                })
                return item;
        },
        hint: true,
        highlight: true,
        minLength: 2
      },
      {
        limit: 12,
        async: true,
        source: function (query, processSync, processAsync) {
          str = query.trim().split(' ').filter((term) => term.length != 1).reduce((v1, v2) => v1 + " " + v2)
          return $.ajax({
            url: "https://www185.lamp.le.ac.uk/EpadGreg/hpo/query/" + str + "/1/1/0/1", 
            type: 'GET',
            data: {query: query},
            dataType: 'json',
            success: function (json) {
              return processAsync(json);
            }
          })
        }
      }
        
    );

    $search_str = ''
    $('#search_filterAuto').keyup(function() {
        if($search_str == $(this).val()) return;
        $('select#values_phen_leftAuto').empty()
        str = $(this).val().trim().split(' ').filter((term) => term.length != 1).reduce((v1, v2) => v1 + " " + v2)
        $.getJSON('https://www185.lamp.le.ac.uk/EpadGreg/hpo/query/' + (str) + '/1/1', (data) => {
            $('select#values_phen_leftAuto').empty()
            data.forEach((term) => {
                $('select#values_phen_leftAuto').append($('<option></option>').attr('value', term).text(term))
            })
        })
        .fail(function() { log('FIND GREG!!!'); });
        $search_str = $(this).val()
    })

    /*
    $(document).on('change', "input.values_med", function () {
        //$(this).closest('.rule').find('select.values_patAuto').select2('destroy')
        $val = $('select.values_med').value();
        //$val.empty()
        $val.append('<option></option>');

        $.getJSON('https://www185.lamp.le.ac.uk/EpadGreg/hpo/query/'+ $val + '/1/1/0/1' , (data) => {
            data.forEach(function(val) {
                $val.append($('<option>', {
                    value: val,
                    text: val.toUpperCase()
                }));    
            })
        })
        .fail(function() { log('FIND GREG!!!'); });;        

        $val.select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
    })
    */
    function logic_eav(rule, eav, logic) {
        if(typeof rule[1] !== 'undefined' && typeof rule[2] !== 'undefined' && rule[1] !== '' && rule[2] !== '') {
            eav.push({'attribute' : rule[0], 'operator': rule[1], 'value': rule[2]})
            logic['-AND'].push({'-AND':"/query/components/eav/" + (eav.length-1)})
        }
    }
    var source;

    $('#build_query').click(() => {
        if (source){
            source.close();
        }
        $('#waiting').removeClass('hide')
        $('#build_query').addClass('disabled');
        $('#query_result tbody').html('')
        $.ajax({ url: urls['qb_json'], dataType: 'json', })
        .done((jsonAPI) => {
            var primaryQuery = JSON.parse(JSON.stringify(jsonAPI));
            var secondaryQuery = JSON.parse(JSON.stringify(jsonAPI));

            //set query id 
            //same query id is set for both query objects
            //primaryQuery.meta.components.queryIdentification.queryID = $('#query_id').val()
            //secondaryQuery.meta.components.queryIdentification.queryID = $('#query_id').val()

            primaryQuery.meta.components.queryIdentification.queryID = ""
            secondaryQuery.meta.components.queryIdentification.queryID = ""

            primaryQuery.meta.components.queryIdentification.target = "118";
            secondaryQuery.meta.components.queryIdentification.target = "149";

            var logic = {"-AND": []}
            var eav = []
            var phe = []
            var gen = []

            var logicSec =  {"-AND": []}
            var eavSec = []

            // Gender
            if($('#genany').prop('checked')){
                logic_gender = []
                eav.push({'attribute' : "Gender", 'operator': "is", 'value': "m"})
                logic_gender.push("/query/components/eav/" + (eav.length-1))
                eav.push({'attribute' : "Gender", 'operator': "is", 'value': "f"})
                logic_gender.push("/query/components/eav/" + (eav.length-1))
                logic['-AND'].push({'-OR': logic_gender})
            }
            if ($('#genmale').prop('checked')) {
                logic_eav(['Gender', 'is', 'm'], eav, logic);
                logic['-AND'].push("/query/components/eav/" + (eav.length-1))    
            }
            else if($('#genfemale').prop('checked')){
                logic_eav(['Gender', 'is', 'f'], eav, logic);
                logic['-AND'].push("/query/components/eav/" + (eav.length-1))    
            }

            $('#pat_container .rule').each(function() {
                var attr = $('select.attribute.medication', this).val().toLowerCase();
                var opr = $('select.conditions', this).val();
                var val = $('input#values_med', this).val().toLowerCase();

                if(val != '') {
                    //var regExp = /\(([^)]+)\)/;
                    val = val.split(' ')[0];
                	//val = regExp.exec(val)[1];
                    //logic_eav([attr, opr, val], eav, logic);
                    logic_eav(['idmultilexdmd', opr, val], eav, logic);
                    
                }
            })

            /*
            $('#secatend_container .rule').each(function() {
                var attr = $('select.secattendances', this).val();
                var opr = $('select.conditions', this).val();
                var val = $('select.values_pat', this).val();
                if(val != '') {
                    logic_eav([attr, opr, val], eavSec, logicSec);
                }
            })

            if ($('#EpisodeNum').val() != '' && $('select.conditions.episode', this).val() != '') {

                var val = $('#EpisodeNum').val();
                var opr = $('select.conditions.episode', this).val();

                logic_eav(['EpisodeNumber', opr, val], eav, logic);
            }

            if ($('select.episodedate', this).val() != '') {

                var val = $('select.episodedate', this).val();
                var currentDate = new Date();
                currentDate.setMonth(currentDate.getMonth() - val);
                //logic_eav(['DateAttended', '>', currentDate.toLocaleDateString()], eav, logic);
            }
			*/
            //Secondary Box
            
            if ($('#AENum').val() != '' && $('select.conditions.ae').val() != '') {

                var val = $('#AENum').val();
                var opr = $('select.conditions.ae', this).val();
				var daterange = $('select.aedate').val();
				if (daterange==1){
					logic_eav(['a+e_1', opr, val], eavSec, logicSec);
				}
				else if(daterange == 6){
					logic_eav(['a+e_6', opr, val], eavSec, logicSec);
				}
				else if(daterange == 12){
					logic_eav(['a+e_12', opr, val], eavSec, logicSec);
				}
            }

            if ($('#OutPNum').val() != '' && $('select.conditions.outp', this).val() != '') {

                var val = $('#OutPNum').val();
                var opr = $('select.conditions.outp', this).val();
                var daterange = $('select.outpdate').val();
                if (daterange==1){
                    logic_eav(['Outpatient_1', opr, val], eavSec, logicSec);
                }
                else if(daterange == 6){
                    logic_eav(['Outpatient_6', opr, val], eavSec, logicSec);
                }
                else if(daterange == 12){
                    logic_eav(['Outpatient_12', opr, val], eavSec, logicSec);
                }
            }
             if ($('#InPNum').val() != '' && $('select.conditions.inp', this).val() != '') {

                var val = $('#InPNum').val();
                var opr = $('select.conditions.inp', this).val();
                var daterange = $('select.inpdate').val();
                if (daterange==1){
                    logic_eav(['Inpatient_1', opr, val], eavSec, logicSec);
                }
                else if(daterange == 6){
                    logic_eav(['Inpatient_6', opr, val], eavSec, logicSec);
                }
                else if(daterange == 12){
                    logic_eav(['Inpatient_12', opr, val], eavSec, logicSec);
                }
            }

            //snomed_term

            $("#values_phen_rightAuto option").each(function() { 
                logic_eav(['snomed_term', '=', $(this).val().split(' ')[0]], eav, logic);

                //terms.push($(this).val().split(' ')[0])
            })

            //End Secondary Box

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
                        'chr' : $('select.values_chr', this).val(),
                        'start' : $('input.values_start', this).val(),
                        'end' : $('input.values_end', this).val(),
                        'referencebases' : $('select.values_refall', this).val(),
                        'alternatebases' : $('select.values_altall', this).val()
                    };
                    // log(v)
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

            primaryQuery['query']['components']['eav'] = eav;
            primaryQuery['query']['components']['subjectVariant'] = gen;
            primaryQuery['query']['components']['phenotype'] = phe;
            primaryQuery['query']['components']['sim'] = sim;
            primaryQuery['logic'] = logic;

            secondaryQuery['query']['components']['eav'] = eavSec;
            secondaryQuery['logic'] = logicSec;
            
            log(primaryQuery);
            // log(JSON.stringify(jsonAPI));

            var jsonQuery = [];
            jsonQuery.push(secondaryQuery)
            jsonQuery.push(primaryQuery)
            $.ajax({url: baseurl + 'AjaxApi/query/' + $('#network_key').val(),
                dataType: 'html',
                delay: 200,
                type: 'POST',
                data: {'jsonAPI': primaryQuery, 'user_id' : $('#user_id').val(), 'installation_key': $('#installation_key').val()},
                dataType: 'json', 
                success: function (data) {

                        $.each(data, function(key, val) {
                            if(val.length > 0) {
                                resp = $.parseJSON(val)
                                $.each(resp, function(key, val1) {
                                    //if($('#query_result tbody tr' + '#' + key).length == 0) {
                                        trow = "<tr id = " + key + "><td>" + key.titleCase() + "</a></td><td>" + ((val1 != "Access Denied") ? (val1.length > 0 ? val1.length  : "0") : val1) + "</td></tr>";
                                        $('#query_result tbody').append(trow);
                                    //}
                                })    
                            }
                        })
                    

                    // if(typeof(EventSource) !== "undefined") {
                    //     $('#query_result')[0].innerText = "";
                    //     source = new EventSource(baseurl + 'deliver/poll_pooler/' + data);//$('#query_id').val());
                    //     source.onmessage = function(event) {
                    //         //document.getElementById("result").innerHTML += event.data + "<br>";
                            
                    //         if(event.data !== 'false') {
                    //             $('#query_result')[0].innerText = "Count: " + event.data;
                    //             source.close();
                    //         }
                    //         else{
                    //             $('#query_result')[0].innerText = "Waiting for results...";
                    //         }
                    //     }
                    // }
                    
                    // else {
                    //     document.getElementById("query_result").innerHTML = "Sorry, your browser does not support server-side events...";
                    // }                    

               },
                'complete': function(data) {
                    $('#query_result').removeClass('hide');
                    $('#build_query').removeClass('disabled');
                    $('#waiting').addClass('hide')
                },
            })
        }).fail(()=> alert(error['load_json']));
    })
    
    setTimeout(() => {$('#isPhenotype').trigger('click')}, 200)
    setTimeout(() => {$('#isPrimarySearch').trigger('click')}, 200)
    setTimeout(() => {$('#isGenotype').trigger('click')}, 200)
    setTimeout(() => {$('#isDemographic').trigger('click')}, 200)
    setTimeout(() => {$('#ishaplogroup').trigger('click')}, 200)
    setTimeout(() => {$('#istissue').trigger('click')}, 200)
    setTimeout(() => {$('#isPatient').trigger('click')}, 200)
    setTimeout(() => {$('#isPatientAuto').trigger('click')}, 200)
    setTimeout(() => {$('#demo_container .select2-arrow').remove()}, 400)
    setTimeout(() => {$('#isSecondaryAtt').trigger('click')}, 200)

    $(document).on('click', ".hover_test", function (e) {
        e.preventDefault()
        
    });

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
            $('#sec_container').append($rule);
        }
        initSelect2();
    })

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

    function sortSelect(id) {
        var options = $('select#' + id + ' option');
        var arr = options.map(function(_, o) { return { t: $(o).text(), v: o.value }; }).get();
        arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
        options.each(function(i, o) {
          o.value = arr[i].v;
          $(o).text(arr[i].t);
        });
    }

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
        if($(this).hasClass('btn-default')) {
            $(this).addClass('active').addClass('btn-primary').removeClass('btn-default')
            $(this).siblings().removeClass('active').addClass('btn-default').removeClass('btn-primary')
        }
    })

    // var bmi_values = ['Underweight', 'Healthy', 'Overweight', 'Obese'];
    // var input = document.getElementById('val_bmi');
    // var output = document.getElementById('text_bmi');
    // input.oninput = function(){output.innerHTML = bmi_values[this.value];};
    // input.oninput();

    $( "#val_sbp" ).slider({
      range: true, min: 0, max: 190, values: [ 70, 140 ],
      slide: function(event, ui) {$("#sbp").val(ui.values[0] + " - " + ui.values[1]);}
    });
    $("#sbp").val($("#val_sbp").slider("values", 0) + " - " + $("#val_sbp").slider("values", 1));

    $( "#val_age" ).slider({
      range: true, min: 0, max: 100, values: [ 40, 60 ],
      slide: function(event, ui) {$("#age").val(ui.values[0] + " - " + ui.values[1]);}
    });
    $("#age").val($("#val_age").slider("values", 0) + " - " + $("#val_age").slider("values", 1));



    $('.val_sex').click(function() {
        $this = $(this)
        $('.val_sex').removeClass('btn-default').removeClass('btn-primary').removeClass('active').addClass('btn-default')
        $this.removeClass('btn-primary').addClass('btn-primary').addClass('active')
    })
});