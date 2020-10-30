<?php namespace App\Libraries\CafeVariome\Auth;


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
    private $accessToken; 

    function __construct(Keycloak $keycloak = null){
        if ($keycloak) {
            $this->keycloakConfig = $keycloak->getKeyCloakConfig();
        }
        else {
            $keyCloak = new KeyCloak();
            $this->keycloakConfig = $keyCloak->getKeyCloakConfig(); 
        }
    }

    private function getAccessToken():string{
        if ($this->accessToken != null) {
            return $this->accessToken;
        }
        else{
            $access = "";
            $url = '/realms/' . $this->keycloakConfig['realm'] . '/protocol/openid-connect/token';
            $post_fields = "client_id=" . $this->keycloakConfig['client_id'] . "&username=" . $this->keycloakConfig['admin_username'] . "&password=" . $this->keycloakConfig['admin_password'] . "&grant_type=password";
            $headers = array();
            $headers[] = 'Content-Type: application/x-www-form-urlencoded';
            $result = json_decode($this->curlCall($url,false,$post_fields,$headers),1);
            $access = $result['access_token'];
            return $access;
        }
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

    function createUser(string $email, string $first_name, string $last_name):void{
        $access = $this->getAccessToken();
        $url = '/admin/realms/'.$this->keycloakConfig['realm'].'/users?realm='.$this->keycloakConfig['realm'];
        $post_fields = "{\"username\" : \"$email\", \"emailVerified\": true, \"enabled\": true, \"email\" : \"$email\", \"firstName\": \"$first_name\", \"lastName\": \"$last_name\", \"realmRoles\": [ \"offline_access\"  ], \"clientRoles\": {\"account\": [ \"manage-account\", \"view-profile\" ] }}'";
        $headers = array();
        $headers[] = 'Accept: application/json';
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$access;
        $this->curlCall($url,false,$post_fields,$headers);
    }

    function getUserId(string $email){
        $access = $this->getAccessToken();
        $url = '/admin/realms/'.$this->keycloakConfig['realm'].'/users?username='.$email;
        $custom_request = 'GET';
        $headers = array();
        $headers[] = 'Authorization: Bearer '.$access;
        $result = json_decode($this->curlCall($url,$custom_request,false,$headers),1);

        if (count($result) == 1) {
            return $result[0]['id'];
        }

        return null;
    }

    function setPassword(string $user_id, string $password): void{
        $access = $this->getAccessToken();
        $url = '/admin/realms/'.$this->keycloakConfig['realm'].'/users/'.$user_id.'/reset-password';
        $post_fields = "{\"type\" : \"password\", \"temporary\": true, \"value\" : \"$password\" }";
        $custom_request = 'PUT';
        $headers = array();
        $headers[] = 'Authorization: Bearer '.$access;
        $headers[] = 'Content-Type: application/json';
        $this->curlCall($url,$custom_request,$post_fields,$headers);
    
    }

    function logout(){
        $access = $this->getAccessToken();
        $url = '/admin/realms/'.$this->keycloakConfig['realm'].'/users/'.$this->keycloakConfig['admin_id'].'/logout';
        $post_fields = 'POST';
        $headers = array();
        $headers[] = 'Authorization: Bearer '.$access;
        $result = json_decode($this->curlCall($url,false,$post_fields,$headers),1);
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