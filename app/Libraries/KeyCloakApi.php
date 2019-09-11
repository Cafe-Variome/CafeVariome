<?php namespace App\Libraries;

/**
 * KeyCloakApi.php 
 * Created : 11/09/2019
 * 
 * This file contains class and functions to communicate with the KeyCloak api.
 * 
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 */

 class KeyCloakApi{

    private $keycloakConfig;

    function __construct(){
        $keyCloak = new KeyCloak();
        $this->keycloakConfig = $keyCloak->getKeyCloakConfig();
    }

    private function getAccessToken():string{
        $access = "";
        $url = '/realms/'.$this->keycloakConfig['realm'].'/protocol/openid-connect/token';
        $post_fields = "client_id=admin-cli&username=admin&password=13759806&grant_type=password";
        $headers = array();
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        //$result = $this->apiCall($url, explode('&', $post_fields));
        $result = json_decode($this->curlCall($url,false,$post_fields,$headers),1);

        $access = $result['access_token'];
        return $access;
    }

    function userExists(string $email):bool{
        $access = $this->getAccessToken();
        $url = '/admin/realms/'. $this->keycloakConfig['realm'] . '/users?username='.$email;
        $custom_request = 'GET';
        $headers = array();
        $headers[] = 'Authorization: Bearer '.$access;
        $result = json_decode($this->curlCall($url,$custom_request,false,$headers),1);
        foreach ($result as $user) {
            if ($user['username'] == $email) {
                return true;
            }
        }
        return false;
    }


    public function curlCall(string $url, $custom_request = false,$post_fields = false, $headers) {
        $ch = curl_init();
        $uri = $this->keycloakConfig['authServerUrl']. $url;
        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if ($post_fields) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }
        if ($custom_request) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $custom_request);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);

        return $result;
    }

 }