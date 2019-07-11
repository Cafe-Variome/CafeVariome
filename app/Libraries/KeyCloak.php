<?php
namespace App\Libraries;

/**
 * Name: KeyCloak.php
 * Created: 10/07/2019
 * @author Gregory Warren
 * @author Owen Lancaster
 * 
 */
use App\Models\Settings;


class KeyCloak{


    public function __construct(){
        $this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);

    }


    /**
     * Login - Begin Keycloak login process when called first time. If redirected from 
     *         keycloak upon successful login pull out tokens for success process 
     *
     * @param N/A
     * @return N/A
     */

    public function login() {

        $key = parse_ini_file($this->setting->settingData["keycloak_ini"], true);
        try {
            $provider = new \Stevenmaguire\OAuth2\Client\Provider\Keycloak([
                'authServerUrl'         => $key['base']['authServerUrl'],
                'realm'                 => $key['base']['realm'],
                'clientId'              => $key['base']['clientId'],
                'clientSecret'          => $key['base']['clientSecret'],
                'redirectUri'           => $key['login']['redirectUri'],
            ]);
        }
        catch (Exception $e) {
            error_log("failed");
            header('Location: '.BASE_URL);
            return;
        }	    	
        if (!isset($_GET['code'])) {
            $authUrl = $provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $provider->getState();
            header('Location: '.$authUrl);
            exit;

        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            unset($_SESSION['oauth2state']);
               redirect('auth_federated/logout', 'refresh');

        } else {
            // Try to get an access token (using the authorization code grant)
            try {
                $token = $provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
            } catch (Exception $e) {
                exit('Failed to get access token: '.$e->getMessage());
            }
            // Optional: Now you have a token you can look up a users profile data
            try {
                // We got an access token, let's now get the user's details
                $user = $provider->getResourceOwner($token);
                
            } 
            catch (Exception $e) {
                exit('Failed to get resource owner: '.$e->getMessage());
            }
            $this->login_success($token, $user);
        }
    }	
}


?>