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
          force = confirm("A forced Regeneration will completely rebuild your ElasticSearch Index. Do you wish to continue?");
          if (force) {
            callElastic(id,force,false);
          }
          else{
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
     $.ajax({url: baseurl + 'AjaxApi/elastic_check',
          delay: 200,
          type: 'POST',
          data : {u_data : JSON.stringify(dataArray)},
          // data: param,
          dataType: 'html',
          success: function (data) {
            console.log(data);
            data = $.parseJSON(data);
              if (data.Status == "Success") {
                $.ajax({url  : baseurl + 'AjaxApi/elastic_start',
                  type: 'POST',
                  data : {u_data : JSON.stringify(dataArray)},
                  // data: param,
                  dataType: 'json'
                });
                setToOff(data.Time);

                $('#status-' + id.toString()).empty();
                $('#status-' + id.toString()).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + id.toString() + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0'>0%</div></div>")

                $('#action-' + id.toString()).children().hide();

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
  $.notify({
    // options
    message: 'ElasticSearch is now regenerating.'},{
    // settings
    timer: 200
  });
}

$(document).ready(function() {
  let eventSource = new EventSource(baseurl + "ServiceApi/pollElasticSearch");

  eventSource.onmessage = function(event) {
      id = event.lastEventId;
      $progress = event.data;

      if ($progress > -1) {
          if($('#progressbar-' + id.toString()).length){
              $('#fActionOverwrite').prop('disabled', true);

              $('#progressbar-' + id.toString()).text(event.data.toString() + '%');
              $('#progressbar-' + id.toString()).css( "width", event.data.toString() + "%" );
          }
          else{
              $('#status-' + id.toString()).empty();
              $('#status-' + id.toString()).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + id.toString() + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0'>0%</div></div>")
              $('#action-' + id.toString()).children().hide();

              $('#progressbar-' + id.toString()).text(event.data.toString() + '%');
              $('#progressbar-' + id.toString()).css( "width", event.data.toString() + "%" );
          }
      }
      else if(id == 0){
          $('#fActionOverwrite').prop('disabled', false);
      }

      if(event.data == 100)
      {
          $('#progressbar-' + id.toString()).addClass('bg-success');
          $('#action-' + id.toString()).children().show();

      }
  };

  eventSource.onerror = function(err) {
  };

  if ($('#index_table').length) {
    $('#index_table').dataTable( {
    "sDom": "<'row'<'col 'l><'col'f>r>t<'row'<'col 'i><'col'p>>",
    "oLanguage": {
        "sLengthMenu": "_MENU_ records per page"
      }
    } );        
}

})