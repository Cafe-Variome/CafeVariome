var rchart = document.getElementById('recordsrc_chart');
var dchart = document.getElementById('disk_chart');

function loadCharts(sourceNames, diskUsed, diskAvailable){
    var csrf_token = $('#csrf_token').val();
    var csrf_token_name = $('#csrf_token').prop('name');
    $.ajax({
        url: baseurl + "AjaxApi/getSourceCounts",
        type: 'post',
        data:  csrf_token_name + '=' + csrf_token,
        success: function(result){
            var recordChart = new Chart(rchart.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: sourceNames,
            datasets: [{
                label: 'Records',
                backgroundColor: '#36a2eb',
                borderColor: 'rgb(255, 99, 132)',
                data: JSON.parse(result)
            }]
        },
            options: {
                scales: {
                    yAxes: [{
                        ticks: {
                            beginAtZero: true
                        }
                    }]
                }
            }
        });
            rchart.style.display = 'block';
            document.getElementById('records_spinner').style.display = 'none';
        }
    });

    var chart = new Chart(dchart.getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Available G.B.', 'Used G.B.'],
            datasets: [{
                label: 'Disk Space Usage',
                data: [ diskAvailable, diskUsed],
                        backgroundColor:["lightgreen", "orange"]
            }]
        },
        options:{}
    });

}