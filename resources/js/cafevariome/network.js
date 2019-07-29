$(document).ready(function() {
    $('#networkstable').DataTable();
} );


function join_network() {
    $callAjax = true;
    $('form[name="joinNetwork"]').submit(function (e) {
        e.preventDefault();
        $.ajax({url: baseurl + 'network/process_network_join_request',
            data: $(this).serialize(),
            dataType: 'json',
            delay: 200,
            type: 'POST',
            success: function (data) {
                if (data.error) {
				alert(data.error);
                    window.location.reload(true);
                }
                else if (data.success) {
				    alert("Successfully joined network");
                    alert("Successfully requested to join network");
                    window.location = baseurl + "network/networks";
                }
                else {
                    window.location.reload(true);
                }
            }
        });
    });
}