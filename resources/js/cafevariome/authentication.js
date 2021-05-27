/**
 * authentication.js
 * 
 * @author Mehdi Mehtarizadeh
 * @date 28/06/2019
 * 
 * This file contains javascript code for authentication procedures.
 * @deprecated
 */

function login_user() {
    $callAjax = true;
    $('form[name="loginUser"]').submit(function (e) {
        
        e.preventDefault();
        $postData = $(this).serialize();

        $.ajax({url: baseurl + 'auth_federated/validate_login/',
            data: $postData,
            dataType: 'json',
            delay: 200,
            type: 'POST',
            async: 'false',
            success: function (data) {
                if (data.error) {
                    $("#loginError").removeClass('hide');
                    $("#loginError").html(data.error);
                } else if (data.success) {
                    if ($callAjax)
                    {
                        $.ajax({url: authurl + '/auth_accounts/login/',
                            data: $postData,
                            dataType: 'json',
                            delay: 200,
                            type: 'POST',
                            success: function (result) {
                                if (result.error) {
//                                            alert(result.error);
                                    $("#loginError").removeClass('hide');
                                    $("#loginError").text(result.error);
                                } else if (result.success) {
                                    $.ajax({url: baseurl + 'auth_federated/login_success/',
                                        data: result,
                                        dataType: 'json',
                                        delay: 200,
                                        type: 'POST',
                                        success: function (data) {
                                            if (data.success) {
//                                                            alert(data.success);
                                                window.location = baseurl;
                                            }
                                        }
                                    });
                                }
                            }
                        });
                        $callAjax = false;
                    }
                }
            }
        });
    });
}