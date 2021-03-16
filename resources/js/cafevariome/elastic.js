function regenElastic(id,add) {
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
  }
  
  function callElastic(id,force,add) {
    dataArray = {
      "id"   :id,
      "force":force,
      "add"  :add
    };
  
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
                  dataType: 'json'
                });
                setToOff(data.Time);

                $('#status-' + id.toString()).empty();
                $('#status-' + id.toString()).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + id.toString() + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0'>0%</div></div><p id='statusmessage-" + id.toString() + "' style='font-size: 10px'></p>")

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
      edata = JSON.parse(event.data);
      progress = edata.progress;
      status = edata.status;

      if (progress > -1) {
          if($('#progressbar-' + id.toString()).length){
            $('#progressbar-' + id.toString()).removeClass('bg-success');

              $('#progressbar-' + id.toString()).text(progress.toString() + '%');
              $('#progressbar-' + id.toString()).css( "width", progress.toString() + "%" );
              $('#statusmessage-' + id.toString()).html(status);

          }
          else{
              $('#status-' + id.toString()).empty();
              $('#status-' + id.toString()).html("<div class='progress'><div class='progress-bar' role='progressbar' id='progressbar-" + id.toString() + "' style='width: 0%;' aria-valuenow='0' aria-valuemin='0' aria-valuemax='0'>0%</div></div><p id='statusmessage-" + id.toString() + "' style='font-size: 10px'>" + status + "</p>")
              $('#action-' + id.toString()).children().hide();

              $('#progressbar-' + id.toString()).text(progress.toString() + '%');
              $('#progressbar-' + id.toString()).css( "width", progress.toString() + "%" );
          }
      }

      if(progress == 100 && status.toLowerCase() == 'finished')
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

$('#indexStatusModal').on('show.bs.modal', function (event) {
  var button = $(event.relatedTarget);
  var indexName = button.data('indexname'); 
  var indexStatus = button.data('elasticstatus'); 
  var modal = $(this)
  modal.find('.modal-title').html('Index Status for ' + indexName)
  modal.find('.modal-body div').html(indexStatus)
})

$('#indexStatusModal').on('hide.bs.modal', function (e) {
  var modal = $(this)
  modal.find('.modal-body div').empty()
})

})