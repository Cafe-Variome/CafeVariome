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
use App\Models\User;
use App\Helpers\AuthHelper;
use App\Models\Network;

class KeyCloak{

    private $db;
    private $setting;

    public $provider;

    private $serverURI;
    private $serverPort;
    private $realm;
    private $clientId;
    private $clientSecret;

    private $loginURI;
    private $logoutURI;

    private $keyCloakSession;

    public function __construct(){

        $this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);
        $this->session =  \Config\Services::session();

        $this->serverURI = $this->setting->settingData["key_cloak_uri"];
        $this->serverPort = $this->setting->settingData["key_cloak_port"];
        $this->realm = $this->setting->settingData["key_cloak_realm"];
        $this->clientId = $this->setting->settingData["key_cloak_client_id"];
        $this->clientSecret = $this->setting->settingData["key_cloak_client_secret"];

        $this->loginURI = $this->setting->settingData["key_cloak_login_uri"];
        $this->logoutURI = $this->setting->settingData["key_cloak_logout_uri"];

        try {
            $this->provider = new \Stevenmaguire\OAuth2\Client\Provider\Keycloak([
                'authServerUrl'         => $this->serverURI,
                'realm'                 => $this->realm,
                'clientId'              => $this->clientId,
                'clientSecret'          => $this->clientSecret,
                'redirectUri'           => $this->loginURI,
            ]);
        }
        catch (Exception $e) {
            error_log("failed");
            header('Location: '.BASE_URL);
            return;
        }
    }

    /**
     * @deprecated 
    */
    public function setSession(&$session){
        $this->keyCloakSession = &$session;
    }

    public function getToken(){
        $token = $this->provider->getAccessToken('refresh_token', ['refresh_token' => $_SESSION['state']->getRefreshToken()]);
        $_SESSION['state'] = $token;
        $access = $_SESSION['state']->getToken();
        return $access;
    }

    public function getUser($token){
        $user = $this->provider->getResourceOwner($token);
        return $user;
    }
    public function getUsername(){
        return $this->session->get('username');
    }

    /**
	 * Get user id
	 * @author Mehdi Mehtarizadeh
	 * @return integer|null The user's ID from the session user data or NULL if not found
	 * 
	 **/
    public function getUserId(){
        return $this->session->get('user_id');
    }

    /**
	 * Check to see if the currently logged in user is an admin.
     * 
	 * @author Mehdi Mehtarizadeh
	 * @param integer $id User id
	 *
	 * @return boolean Whether the user is an administrator
	 * 
	 */
	public function isAdmin(int $id=0): bool
	{
        $is_admin = $this->session->get('is_admin');
        return ($is_admin) ? $is_admin : false;
	}

    public function getAuthorizationUrl(){
        return $this->provider->getAuthorizationUrl();
    }

    public function getState(){
        return $this->provider->getState();
    }



    /**
     * Login - Begin Keycloak login process when called first time. If redirected from 
     *         keycloak upon successful login pull out tokens for success process 
     *
     * @param N/A
     * @return N/A
     */

    public function login():bool {  	
        if (!isset($_GET['code'])) {

            // If we don't have an authorization code then get one
            $authUrl = $this->provider->getAuthorizationUrl();
            $_SESSION['oauth2state'] = $this->provider->getState();
            header('Location: '.$authUrl);

            exit;
        
        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
        
            unset($_SESSION['oauth2state']);
            exit('Invalid state, make sure HTTP sessions are enabled.');
        
        } else {
        
            // Try to get an access token (using the authorization coe grant)
            try {
                $token = $this->provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
                $_SESSION['state'] = $token;

            } catch (\Exception $e) {
                exit('Failed to get access token: '.$e->getMessage());
            }
        
            // Optional: Now you have a token you can look up a users profile data
            try {
        
                // We got an access token, let's now get the user's details
                $user = $this->provider->getResourceOwner($token);
        
                // Use these details to create a new profile
                printf('Hello %s!', $user->getEmail());
        
            } catch (\Exception $e) {
                exit('Failed to get resource owner: '.$e->getMessage());
            }
        

        $this->login_success($token, $user);
        return true;
        }
    }

     /**
     * Login Success - Once a user signs correctly on keycloak it is now time to check
     *                 whether that user exists locally. If the user exists ensure
     *                 that they are both active and not a remote user. If all passes
     *                 create session for the user and redirect to home.
     *                 If remote or active fail checks log out in keycloak and redirect 
     *                 to index with an error code
     *
     * @param stdObject $keys - an Object containing all tokens from keycloak
     * @param stdObject $user - an Object containing details on the user
     * @return N/A
     */
    public function login_success ($keys, $user):bool{

        $_user = new User($this->db);

        //authUser stores the user we are looking for in the authentication step.
        $authUser = $_user->getUserByUsername($user->getEmail());
        var_dump($authUser);

        if($authUser)
        {
            if($authUser[0]->active == 0){
                // This user is not active. Logout from keycloak and display error message 2
                $this->error_logout(1);
                return FALSE;
            }
            if ($authUser[0]->remote == 1) {
                // This user is a remote user. Logout from keycloak and display error message 1
                $this->error_logout(0);
                return FALSE;
            }
            $sess = $this->_get_Unique_Identifier();                               
            $session_data = array(
                'identity'                  => $authUser[0]->username,
                'user_id'                   => $authUser[0]->id,
                'ip_address'                => $authUser[0]->ip_address,
                'username'                  => $authUser[0]->username,
                'password'                  => $authUser[0]->password,
                //'salt'                      => $authUser[0]->salt,
                'email'                     => $authUser[0]->email,
                'activation_code'           => $authUser[0]->activation_code,
                'forgotten_password_code'   => $authUser[0]->forgotten_password_code,
                'forgotten_password_time'   => $authUser[0]->forgotten_password_time,
                'remember_code'             => $authUser[0]->remember_code,
                'created_on'                => $authUser[0]->created_on,
                'old_last_login'            => $authUser[0]->last_login,
                'active'                    => $authUser[0]->active,
                'first_name'                => $authUser[0]->first_name,
                'last_name'                 => $authUser[0]->last_name,
                'company'                   => $authUser[0]->company,
                //'orcid'                     => $authUser[0]->orcid,
                'controller'                => "auth_federated",
                'is_admin'                  => $authUser[0]->is_admin,
                'Token'                     => $sess,
                //'email_notification'        => $authUser[0]->email_notification ? "yes" : "no",
                'query_builder_basic'		=> 'yes',
                //'query_builder_advanced'	=> $authUser[0]->query_builder_advanced ? "yes" : "no",
                //'query_builder_precan'		=> $authUser[0]->query_builder_precan ? "yes" : "no",
                //'view_derids'				=> $authUser[0]->view_derids ? "yes" : "no",
                //'create_precan_query'       => $authUser[0]->create_precan_query ? "yes" : "no",
                'state_bool'                => 1,
                'state'						=> $keys
            );  
        }
        else {
            // error_log("result 0");
            $this->error_logout(2);
            return FALSE;
        }	
        $this->session->set($session_data);

        error_log("User: " . $this->session->get('email') . " has logged in || " . date("Y-m-d H:i:s"));  	
        //header('Location: '.base_url('auth/index'));
        return true;
    }

    public function logout():bool
    {
    	// Destroy the session
		$this->session->destroy();

        header('Location: '.base_url());
        exit;
    }

    /**
     * 
     */
    public function register(string $email, string $username, string $password, array $additionaldata, array $groups){
        $ionAuth = new IonAuth();
        $result = $ionAuth->register($email, $password, $email, $additionaldata);
        if ($result){
            //Assuming $result is user_id
            if( $groups ){
                $networkModel = new Network($this->db);
                $installation_key = $additionaldata["installation_key"];
                foreach ($groups as $g) {
                    $groups_exploded = explode(',', $g);
                    $group_id = $groups_exploded[0];
                    $network_key = $groups_exploded[1];

                    $id = $networkModel->addUserToNetworkGroup($result, $group_id, $installation_key, $network_key);
                }
            }
            // Create user in Keycloak H2 database 

            $keyCloakApi = new KeyCloakApi($this);
            if (!$keyCloakApi->userExists($email)) {
                $first_name = $additionaldata['first_name'];
                $last_name = $additionaldata['last_name'];
                $keyCloakApi->createUser($email, $first_name, $last_name);
                
                $user_id =  $keyCloakApi->getUserId($email);
                $keyCloakApi->setPassword($user_id, $password);

                //Send email confirmation
                
            }

        }
    }

    public function update(int $user_id, array $additionaldata, array $groups){
        $ionAuthModel = new \App\Models\IonAuthModel();
        $networkModel = new Network($this->db);

        if($ionAuthModel->update($user_id, $additionaldata))
        {
            //network groups user is already in
            $user_groups = $networkModel->getNetworkGroupsForInstallationForUser($user_id);
            $installation_key = $this->setting->settingData["installation_key"];

            foreach ($groups as $g) {
                $found = false;
                $group_id = explode(',', $g)[0];
                $network_key = explode(',', $g)[1];

                foreach ($user_groups as $ug) {
                    if (explode(',', $g)[0] == $ug['group_id']){
                        $found = true;
                    }
                }
                if(!$found){
                    //add user to new groups
                    $networkModel->addUserToNetworkGroup($user_id, $group_id, $installation_key, $network_key);
                }
            }
            foreach ($user_groups as $ug) {
                $found = false;
                $network_key = $ug['network_key'];
                $group_id = $ug['group_id'];
                foreach ($groups as $g) {
                    if (explode(',', $g)[0] == $ug['group_id']){
                        $found = true;
                    }
                }
                if (!$found){
                    //add user to new groups
                    $networkModel->deleteUserFromNetworkGroup($user_id, $group_id, $installation_key, $network_key);
                }
            }
        }


    }

    public function deleteUser(int $user_id){
        $ionAuthModel = new \App\Models\IonAuthModel();
        $networkModel = new Network($this->db);

        if($ionAuthModel->deleteUser($user_id)){
            $networkModel->deleteUserFromAllNetworkGroups($user_id);
        }
    }

    /**
     * Error Logout - Log out user from keycloak due to failure of passing local login
     *                checks
     *
     * @param int $error - Array index of the error message we wish to display
     * @return N/A
     */
    function error_logout($error) {
        if($this->session->get('email'))
                error_log("User: " . $this->session->userdata('email') . " has logged out || " . date("Y-m-d H:i:s"));
            //$this->keyCloakSession->destroy();
            $this->provider = new \Stevenmaguire\OAuth2\Client\Provider\Keycloak([
                'authServerUrl'         => $this->serverURI,
                'realm'                 => $this->realm,
                'clientId'              => $this->clientId,
                'clientSecret'          => $this->clientSecret,
                'redirectUri'           => $this->loginURI,
            ]);
            $logoutUrl = $this->provider->getLogoutUrl();
            header('Location: '.$logoutUrl);
        }  
    
    /**
	 * Returns true if the user is logged in.
	 *
	 * @author Mehdi Mehtarizadeh
	 *
	 * @return boolean Whether the user is logged in
	 */
    public function loggedIn():bool
    {
        return($this->get_session_status() == 'not expired');

    }


    /**
     * Get Session Status - Check whether the user still has a valid session on 
     *                      Keycloak
     *
     * @param N/A
     * @return string $return - expired|not expired|n/a (Values to signify state
     *                          of current session to frontend)
     */
    public function get_session_status() {
    	
        if (isset($_SESSION['state'])) {
            try {
                //$token = $this->provider->getAccessToken('refresh_token', ['refresh_token' => $_SESSION['state']]);
                $token = new \League\OAuth2\Client\Token\AccessToken(['access_token' => $_SESSION['state']->getToken()]);
                //$token = $this->provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
                $user = $this->provider->getResourceOwner($token);
                if (!$user->getEmail() == $_SESSION['email']){
                    throw new Exception('user not found');
                }
                $return = "not expired";
            }
            catch (\Exception $e) {
                if ($e == "user not found") {
                    $return = "expired";
                }
                else {
                    try {
                        $token = $this->provider->getAccessToken('refresh_token', ['refresh_token' => $_SESSION['state']->getRefreshToken()]);
                        $return = "not expired";
                        $_SESSION['state'] = $token;
                    }
                    catch (\Exception $e) {
                        $return = "expired";
                    }
                }        
            }
        }
        else {
            $return = "n/a";
        }	
        return $return;
    }

    
    /**
     * checkKeyClockServer
     * Checks the availability of auth server.
     * @param N/A
     * @return bool 
     */
    public function checkKeyCloakServer(){
        return AuthHelper::checkServer($this->serverURI, $this->serverPort);  
    }

    /**
     * _get_Unique_Identifier
     * Generates a uuid for Token attribute in session data.
     * @param N/A
     * @return string
     */
    private function _get_Unique_Identifier(){
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }

    function getKeyCloakConfig(){
        $kcConf = array();
        $kcConf['authServerUrl'] = $this->serverURI;
        $kcConf['realm'] = $this->realm;
        $kcConf['client_id'] = $this->clientId;
        $kcConf['client_secret'] = $this->clientSecret;

        return $kcConf;
    }
}


?>