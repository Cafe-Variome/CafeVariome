    // var jstree_hpo;
    var jstree_hpo;
    var hpo_json = {};

    function init_hpotree(data) {
        hpo_json = data;
        jstree_hpo = $('#jstree_hpo')
        .jstree({
            'core' : {
                'data' : function (node, cb) {
                    if(node.id == '#'){
                        cb.call(this, hpo_json);
                    } else {
                        $.ajax({
                            url: baseurl + "ContentAPI/hpoQuery/"+node.id,
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

    function sortSelect(id) {
        var options = $('select#' + id + ' option');
        var arr = options.map(function(_, o) { return { t: $(o).text(), v: o.value }; }).get();
        arr.sort(function(o1, o2) { return o1.t > o2.t ? 1 : o1.t < o2.t ? -1 : 0; });
        options.each(function(i, o) {
          o.value = arr[i].v;
          $(o).text(arr[i].t);
        });
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
            url: baseurl + 'ContentAPI/buildHPOTree',
            type: 'POST',
            dataType: 'JSON',
            data: {'hpo_json' : JSON.stringify(hpo_json), 'ancestry': $('select#values_phen_left').val(), 'hp_term': $('select#values_phen_left :selected').text()},
        })
        .done(function(data) {

            hpo_json = data;
            init_hpotree(hpo_json);
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
                            url: baseurl + "ContentAPI/hpoQuery/"+node.id,
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
        init_hpotree(hpo_json);
    })