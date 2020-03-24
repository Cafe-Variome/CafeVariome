$(function() {
    // console.log alias
    const log = console.log.bind(console)
    // urls object
    const urls = {'qb_config': baseurl + 'resources/js/config.json', 'qb_json': baseurl + 'resources/js/querybuilder.json', 'phen_json': baseurl + 'AjaxApi/getPhenotypeAttributes/' + $('#network_key').val() }
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

    var hpo = {
        'HP:0008066': "Abnormal Blistering of the Skin",
        'HP:0008071': "Abnormality of Cardiovascular System Morphology",
        'HP:0008773': "Aplasia/Hypoplasia of the middle ear",
        'HP:0000413': "Atresia of the external auditory canal",
        'HP:0000453': "Choanal atresia",
        'HP:0000405': "Conductive hearing impairment",
        'HP:0011451': "Congenital microcephaly",
        'HP:0011471': "Gastrostomy tube feeding in infancy",
        'HP:0010880': "Increased nuchal translucency",
        'HP:0000272': "Malar flattening",
        'HP:0000347': "Micrognathia",
        'HP:0008551': "Microtia",
        'HP:0011342': "Mild global developmental delay",
        'HP:0000545': "Myopia",
        'HP:0000384': "Preauricular skin tag",
        'HP:0001622': "Premature birth",
        'HP:0009623': "Proximal placement of thumb",
        'HP:0100026': "Arteriovenous malformation",
        'HP:0003561': "Birth length less than 3rd percentile",
        'HP:0009879': "Cortical gyral simplification",
        'HP:0011097': "Epileptic spasms",
        'HP:0001263': "Global developmental delay",
        'HP:0012469': "Infantile spasms",
        'HP:0001336': "Myoclonus",
        'HP:0000648': "Optic atrophy"
    }

    var attributes = {
                        'Affected Gene Symbol' : "genes_symbol",
                        'Disease Label': "diseases_label",
                        'Sex': "sex_label"
                    };

    // Entry point of script: Load config file and then load phenotype json if successful


    //load_qb_config()
    load_phen_json();
    var template = {}
    var phen_data = {};
    // Load phenotype json and then load JSON API template if successful
    function load_phen_json(/*qb_config*/) {
        var phen_attrib = []
        $.ajax({ url: urls['phen_json'], dataType: 'json'})
        .done((jsonData)=> { 
            //log(jsonData)
            phen_data = jsonData[0];

            $.each( jsonData[0].chr, function( key, value ) {
                $('#values_chr').append($('<option></option>').attr('value', value.toLowerCase()).text('Chr:' + value));
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
        url: baseurl + "AjaxApi/hpo_query",
        data: null,
        success: function(data){
            hpo_json = data;
            init_hpotree();
        }
    });


    // var jstree_hpo;
    var jstree_hpo;
    function init_hpotree() {
        jstree_hpo = $('#jstree_hpo')
        .jstree({
            'core' : {
                'data' : function (node, cb) {
                    if(node.id == '#'){
                        cb.call(this, hpo_json);
                    } else {
                        $.ajax({
                            url: baseurl + "AjaxApi/hpo_query/"+node.id,
                            type: 'POST',
                            dataType: 'JSON'
                        }).done(function(data) {
                            cb.call(this, data);
                            hpo_json = jstree_hpo.jstree().get_json('#')[0]
                            // log(JSON.stringify(hpo_json))
                        });
                    }
                }
            },
            'checkbox': {
                three_state: false,
                cascade: 'up+undetermined'
            },
            "plugins" : [ "wholerow", "checkbox"]
        }).on("loaded.jstree.jstree", function (e, data) {
            $(this).jstree().open_node("HP:0000001.HP:0000118_anchor",function(){;},false);
        }).on("changed.jstree", function (e, data) {
            $('select#values_phen_right').empty();
            $("#jstree_hpo").jstree("get_selected", true).forEach(function(term) {
                txt = '(' + term.text.split(' (')[1] + ' ' + term.text.split(' (')[0];
                if($('select#values_phen_right option[value="' + txt + '"]').length == 0) {
                    $('select#values_phen_right').append($("<option></option>").attr("value", txt).text(txt))    
                } else {
                    var f = $('select#values_phen_right option[value="' + txt + '"]').text()
                    if(!f.includes("*"))
                        $('select#values_phen_right option[value="' + txt + '"]').text('*' + f)
                }
            })
            sortSelect('values_phen_right')
        }).on("ready.jstree", function (e, data) {
            $('select#values_phen_right').empty();
            $("#jstree_hpo").jstree("get_selected", true).forEach(function(term) {
                txt = '(' + term.text.split(' (')[1] + ' ' + term.text.split(' (')[0];
                if($('select#values_phen_right option[value="' + txt + '"]').length == 0) {
                    $('select#values_phen_right').append($("<option></option>").attr("value", txt).text(txt))    
                } else {
                    var f = $('select#values_phen_right option[value="' + txt + '"]').text()
                    if(!f.includes("*"))
                        $('select#values_phen_right option[value="' + txt + '"]').text('*' + f)
                }
            })
            sortSelect('values_phen_right');
            $('select#values_phen_right').filterByText($('#search_filter_phen_right'));
        });

    }

    function destroy_hpotree() {
        $("#jstree_hpo").jstree("destroy");    
    }

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
            });
            $('select#values_phen_right').filterByText($('#search_filter_phen_right'));
        });
    });
    
    $('button.btnAdd').click(() => {

        hpo_json = jstree_hpo.jstree().get_json('#')[0]
        removeEmpty(hpo_json)
        destroy_hpotree();

        $.ajax({
            url: baseurl + 'AjaxApi/build_tree',
            type: 'POST',
            dataType: 'JSON',
            data: {'hpo_json' : JSON.stringify(hpo_json), 'ancestry': $('select#values_phen_left').val(), 'hp_term': $('select#values_phen_left :selected').text()},
        })
        .done(function(data) {

            hpo_json = data;
            init_hpotree();
        }).always(function() {
        });
    })

    var jstreeArea;
    $('#full_screen').click(function(e) {
        e.preventDefault();
        hpo_json = jstree_hpo.jstree().get_json('#')[0];
        //log(JSON.stringify(hpo_json))
        jstreeArea = $('#jstreeArea')
        .jstree({
            'core' : {
                'data' : function (node, cb) {
                    if(node.id == '#') {
                        cb.call(this, hpo_json);
                    } else {
                        $.ajax({
                            url: baseurl + "discovery/hpo_query/"+node.id,
                            type: 'POST',
                            dataType: 'JSON'
                        }).done(function(data) {
                            cb.call(this, data);
                            hpo_json = jstreeArea.jstree(true).get_json('#')[0]
                            // log(JSON.stringify(hpo_json))
                        });
                    }
                }
            },
            'checkbox': {
                three_state: false,
                cascade: 'up+undetermined'
            },
            "plugins" : [ "wholerow", "checkbox"]
        });
        $('#hpoTreeModal').modal('show');
    })

    $('#hpoTreeModal').on('hidden.bs.modal', function () {
        hpo_json = jstreeArea.jstree().get_json('#')[0];
        $("#jstreeArea").jstree("destroy");
        $("#jstree_hpo").jstree("destroy");
        init_hpotree();
    })

    $('#jstreeArea').on("changed.jstree", function (e, data) {
        // hpo_json = jstreeArea.jstree(true).get_json('#')[0]
        // log(JSON.stringify(hpo_json));
    });



    // Load JSON API template and initialise query builder if successful
    function load_json_api_template(qb_config, phen_attrib) {
        $.ajax({ url: urls['qb_json'], dataType: 'json', })
        .done()
        .fail(()=> alert(error['load_json']))
    }

    function initSelect2() {
        $('select.keys').select2({ allowClear: true, theme: 'classic', placeholder: 'Select an attribute', dropdownAutoWidth: 'true' });
        $('select.conditions').select2({ allowClear: true, theme: 'classic', placeholder: 'Select operator', dropdownAutoWidth: 'true' });
        $('select.keys_altaf').select2({theme: 'classic', placeholder: 'Select an attribute', dropdownAutoWidth: 'true' });
        $('select.keys_pat').select2({theme: 'classic', placeholder: 'Select an attribute', dropdownAutoWidth: 'true' });
        $('select.values_altall').select2({allowClear: true, theme: 'classic', placeholder: 'ALT', dropdownAutoWidth: 'true' });
        $('select.values_refall').select2({allowClear: true, theme: 'classic', placeholder: 'REF', dropdownAutoWidth: 'true' });

        $('select.values').select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
        $('select.values_pat').select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
        $('select.values_pos').select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input position', dropdownAutoWidth: 'true' });
        $('select.values_altaf').select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value'});
    }

    $(document).on('change', "select.keys_pat", function () {
        $(this).closest('.rule').find('select.values_pat').select2('destroy')
        $val = $(this).closest('.rule').find('select.values_pat');
        $val.empty()
        // log($(this).val())
        // log(phen_data[$(this).val()])

        $val.append('<option></option>');

        phen_data[$(this).val()].forEach(function(val) {
            //console.log(val);
            $val.append($('<option>', {
                value: val,
                text: val.toUpperCase()
            }));    
        })
        

        $val.select2({ allowClear: true, theme: 'classic', placeholder: 'Select/Input value', dropdownAutoWidth: 'true' });
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

            $('#pat_container .rule').each(function() {
                var attr = $('select.keys_pat', this).val()
                var opr = $('select.conditions', this).val()
                var val = $('select.values_pat', this).val()
                if(val != '') {
                    logic_eav([attr, opr, val], eav, logic);
                }
            })

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
                    log(v)
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
            //log(jsonAPI);
             log(JSON.stringify(jsonAPI));

            $.ajax({url: baseurl + 'AjaxApi/query/' + $('#network_key').val(),
                dataType: 'html',
                delay: 200,
                type: 'POST',
                data: {'jsonAPI': jsonAPI, 'user_id': $('#user_id').val()},
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
        if($(this).hasClass('btn-secondary')) {
            $(this).addClass('active').addClass('btn-primary').removeClass('btn-secondary')
            $(this).siblings().removeClass('active').addClass('btn-secondary').removeClass('btn-primary')
        }
    });
    /*
    $('#search_test').autocomplete({
        source: function(req, res) {
          $.getJSON(baseurl + 'discovery/search_on_index/' + req, function(attr, st) {
            res(attr)})
        },

    })

    $('#search_test2').typeahead( { 
        minLength: 2,
        highlighter: function (item) {
            this.query.trim().split(' ').forEach((term) => {
                if(term.length > 1) {
                    item = item.replace(new RegExp( '(' + term + ')', 'gi' ), "<b style='font-weight: bold'>$1</b>" )    
                }
            })
            return item;
        },
        source: function(query, process) {
            str = query.trim().split(' ').filter((term) => term.length != 1).reduce((v1, v2) => v1 + " " + v2)
            $.getJSON(baseurl + 'discovery/search_on_index/' + str, (data) => {
                return process(data);
            });
        }
    });


    $('#search_test3').focus(function() {
        if($('#search_test3').val().length == 0) {
            $('#search_test3').siblings('ul.dropdown-menu:not(.custom-menu)').remove()
            $('#search_test3').siblings('.custom-menu').css('display', 'block')
        }
    })

    $('#search_test3').keyup(function() {
        if($('#search_test3').val().length == 0) {
            $('#search_test3').siblings('ul.dropdown-menu:not(.custom-menu)').remove()
            $('#search_test3').siblings('.custom-menu').css('display', 'block')
        }
        else
            $('#search_test3').siblings('.custom-menu').css('display', 'none')
    })

    $('#search_test3').focusout(function() {
        $('#search_test3').siblings('.custom-menu').css('display', 'none')
    })



    $('#search_test3').typeahead( { 
        minLength: 2,
        highlighter: function (item) {
            this.query.trim().split(' ').forEach((term) => {
                if(term.length > 1) {
                    item = item.replace(new RegExp( '(' + term + ')', 'gi' ), "<b class='custom-bold'>$1</b>" )    
                }
            })
            return item;
        },
        source: function(query, process) {
            str = query.trim().split(' ').filter((term) => term.length != 1).reduce((v1, v2) => v1 + " " + v2)
            $.getJSON(baseurl + 'discovery/search_on_index/' + str, (data) => {
                return process(data);
            });
        }
    });



    */
});