function regenElastic(id,add) {
    //on = $("#elasticon").html();
    //console.log(on);
    //if (on != "ElasticSeach is running") {
    //  alert("Can't Regenerate ElasticSearch when ElasticSearch is not running.");
    //}
    //else {
      if (add) {
         callElastic(id,false,true);
      }
      else {
        if ($('#update_'+id+'_force').prop('checked')) {
          force = confirm("A forced Regeneration will completely rebuild your ElasticSearch Index. Do you wish to continue?");
          if (force) {
            callElastic(id,force,false);
          }
        }
        else {
          force = false;
          callElastic(id,force,false);
        }   
      }   
  //  }
  }
  
  function callElastic(id,force,add) {
    dataArray = {
      "id"   :id,
      "force":force,
      "add"  :add
    };
  
    console.log(dataArray);
    // param = "id="+id                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         ;
    // console.log(param);
     $.ajax({url: baseurl + 'elastic/elastic_check',
          delay: 200,
          type: 'POST',
          data : {u_data : JSON.stringify(dataArray)},
          // data: param,
          dataType: 'html',
          success: function (data) {
            console.log(data);
            data = $.parseJSON(data);
              if (data.Status == "Success") {
                $.ajax({url  : baseurl + 'elastic/elastic_start',
                  type: 'POST',
                  data : {u_data : JSON.stringify(dataArray)},
                  // data: param,
                  dataType: 'json'
                });
                setToOff(data.Time);
                //elasticSearchInterval(id);
              }
              else if (data.Status == "Empty") {
                alert("This Source doesnt have any data uploaded to it yet. Please go to Data tab to rectify this.");
              }
              else {
                  alert("ElasticSearch is fully up to date. Upload more data first.");
              }
  
          }
      });
  }

function setToOff(time) {
  $('#e_status').html('<div class="ad-left">\
        <i class="fa fa-spinner fa-spin" style="font-size:24px"></i>\
        </div><div class="ad-right">Update in Progress.</div>');
  $("#buttonswitch").children('a').addClass('disabled');
  $('#index_table tr').not(':first').each(function (i, row) {
    id = this.id;   
    id = id.substring('index_'.length);
    $("#update_"+id).replaceWith('<a onclick="regenElastic(\'test\');" class="btn disabled" rel="popover" id="update_test" data-content="Click to regenerate this ElasticSearch Index" data-original-title="Regenerate ElasticSearch"><i class="icon-list-alt"></i>  Regenerate epadtest_test </a>');
    if (document.getElementById("update_"+id+"_force")) {
        document.getElementById("update_"+id+"_force").disabled = true;
    }
  });
  var date = new Date(null);
  date.setSeconds(time); // specify value for SECONDS here
  var result = date.toISOString().substr(11, 8);
  $.notify({
    // options
    message: 'ElasticSearch is now regenerating. We estimate it will take '+result},{
    // settings
    timer: 200
  });
}