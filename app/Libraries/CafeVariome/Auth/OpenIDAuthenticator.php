<?php
namespace App\Libraries\CafeVariome\Auth;

/**
 * Name: OpenIDAuthenticator.php
 * Created: 02/10/2020
 * @author Mehdi Mehtarizadeh
 * 
 */
use App\Models\User;
use App\Libraries\CafeVariome\Net\cURLAdapter;


abstract class OpenIDAuthenticator extends Authenticator
{
    protected $provider;
    protected $options;
    protected $serverURI;
    protected $serverPort;
    protected $realm;
    protected $clientId;
    protected $clientSecret;
    protected $loginURI;
    protected $networkAdapterConfig;

    private $proxyDetails;

    public function __construct() {
        parent::__construct();

        $this->initiateOpenIDParams();
    }

    private function initiateOpenIDParams()
    {
        $this->serverURI = $this->setting->getOpenIDEndpoint();
        $this->serverPort = $this->setting->getOpenIDPort();
        $this->realm = $this->setting->getOpenIDRealm();
        $this->clientId = $this->setting->getOpenIDClientId();
        $this->clientSecret = $this->setting->getOpenIDClientSecret();

        $this->networkAdapterConfig = config('NetworkAdapter');
        $this->proxyDetails = $this->networkAdapterConfig->proxyDetails;

        $this->loginURI = base_url('auth/login');//$this->setting->getOpenIDRedirectUri();
    }

    protected function configureProxy(){
        $proxyUserPass = ($this->proxyDetails['username'] != '' && $this->proxyDetails['password'] != '') ? $this->proxyDetails['username'] . ':' . $this->proxyDetails['password'] . '@' : '';
        $this->options['proxy'] = $proxyUserPass . $this->proxyDetails['hostname'] . ':' . $this->proxyDetails['port'];
        $this->options['verify'] = false;
    }

    public function getToken()
    {
        $token = new \League\OAuth2\Client\Token\AccessToken(['access_token' => $this->session->get('state')->getToken()]);
        return $token;
    }

    /**
     * Login - Begin Keycloak login process when called first time. If redirected from 
     *         keycloak upon successful login pull out tokens for success process 
     *
     * @param N/A
     * @return N/A
     */

    public function login():bool
    {  
        if (!isset($_GET['code'])) {

            $authUrl = $this->provider->getAuthorizationUrl();
            $this->session->set('oauth2state', $this->provider->getState());
            header('Location: '.$authUrl);

            exit;
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $this->session->get('oauth2state'))) {
        
            $this->session->remove('oauth2state');
            exit('Invalid state, make sure HTTP sessions are enabled.');
        
        } else {
            try {
                $token = $this->provider->getAccessToken('authorization_code', [
                    'code' => $_GET['code']
                ]);
                $this->session->set('state', $token);

            } catch (\Exception $e) {
                exit('Failed to get access token: '.$e->getMessage());
            }
        
            try {
                $user = $this->provider->getResourceOwner($token);
                $userArray = $user->toArray();
                $this->recordSession($token, $userArray['email']);     
                return true;
  
            } catch (\Exception $e) {
                exit('Failed to get resource owner: '.$e->getMessage());
            }
        }
        return false;
    }

    public function getUser($token){
        return $this->provider->getResourceOwner($token);
    }

    /**
	 * Get user id
	 * @return integer|null The user's ID from the session user data or NULL if not found
	 * 
	 **/
    public function getUserId(){
        return $this->session->get('user_id');
    }
    
    protected function recordSession($keys, string $email){

        $userModel = new User();
        $authenticatedUser = $userModel->getUserByUsername($email);

        if($authenticatedUser)
        {
            if($authenticatedUser->active == 0 && $authenticatedUser->remote == 1){
                $this->logout();
            }
            
            $sess = $this->_get_Unique_Identifier();                               
            $session_data = array(
                'identity'                  => $authenticatedUser->username,
                'user_id'                   => $authenticatedUser->id,
                'ip_address'                => $authenticatedUser->ip_address,
                'username'                  => $authenticatedUser->username,
                // 'password'                  => $authenticatedUser->password,
                'email'                     => $authenticatedUser->email,
                'activation_code'           => $authenticatedUser->activation_code,
                'forgotten_password_code'   => $authenticatedUser->forgotten_password_code,
                'forgotten_password_time'   => $authenticatedUser->forgotten_password_time,
                'remember_code'             => $authenticatedUser->remember_code,
                'created_on'                => $authenticatedUser->created_on,
                'old_last_login'            => $authenticatedUser->last_login,
                'active'                    => $authenticatedUser->active,
                'first_name'                => $authenticatedUser->first_name,
                'last_name'                 => $authenticatedUser->last_name,
                'company'                   => $authenticatedUser->company,
                // 'controller'                => "auth_federated",
                'is_admin'                  => $authenticatedUser->is_admin,
                'Token'                     => $sess,
                'query_builder_basic'		=> 'yes',
                'state_bool'                => 1,
                'state'						=> $keys
            );  
            $this->session->set($session_data);
        }
        else {
            $this->logout();
        }	
    }

    public function loggedIn(): bool {
        if ($this->session->has('state')) {
            try {
                $token = $this->getToken();
                $user = $this->getUser($token);
                if ($user && $user->toArray()['email'] == $this->session->get('email')) {
                    return true;
                }
            }
            catch (\Exception $ex) {
                try {
                    $token = $this->provider->getAccessToken('refresh_token', ['refresh_token' => $this->session->get('state')->getRefreshToken()]);
                    $this->session->set('state', $token);
                }
                catch (\Exception $ex) {
                    error_log($ex->getMessage());
                }
            }      
        }

        return false;
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
	public function isAdmin(): bool
	{
        return $this->session->get('is_admin');
    }
    
    /**
     * ping
     * Checks the availability of auth server.
     * @param N/A
     * @return bool 
     */
    public function ping(){

        if (strpos($this->serverURI, '/auth') == false){
            $this->serverURI .= '/auth/';
        }
        if (substr($this->serverURI, strlen($this->serverURI) - 1, strlen($this->serverURI)) !== '/') {
            $this->serverURI .= '/';
        }
        $curlOptions = [CURLOPT_NOBODY => true];
        $netAdapterConfig = config('NetworkAdapter');

        $cURLAdapter = new cURLAdapter($this->serverURI, $curlOptions);
        if ($netAdapterConfig->useProxy) {
            $proxyDetails = $netAdapterConfig->proxyDetails;

            $cURLAdapter->setOption(CURLOPT_FOLLOWLOCATION, true);
            $cURLAdapter->setOption(CURLOPT_HTTPPROXYTUNNEL, 1);
            $cURLAdapter->setOption(CURLOPT_PROXY, $proxyDetails['hostname']);
            $cURLAdapter->setOption(CURLOPT_PROXYPORT, $proxyDetails['port']);
    
            if ($proxyDetails['username'] != '' && $proxyDetails['password'] != '') {
                $cURLAdapter->setOption(CURLOPT_PROXYUSERPWD, $proxyDetails['username'] . ':' . $proxyDetails['password']);
            } 
        }

        $cURLAdapter->Send();
        $httpStatus = $cURLAdapter->getInfo(CURLINFO_HTTP_CODE);

        return $httpStatus == 200;
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
    
}
