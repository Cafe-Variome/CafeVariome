<?php
namespace App\Libraries\CafeVariome\Auth;

/**
 * Name: KeyCloak.php
 * Created: 10/07/2019
 * @author Gregory Warren
 * @author Owen Lancaster
 * @author Mehdi Mehtarizadeh
 * 
 */

use App\Models\User;
use App\Helpers\AuthHelper;
use App\Models\Network;
use App\Libraries\CafeVariome\Email\EmailFactory;

class KeyCloak extends OpenIDAuthenticator /*implements IAuthenticator*/{

    private $adminId;
    private $adminUsername;
    private $adminPassword;

    public function __construct(){
        parent::__construct();

        // $this->adminId = $this->setting->settingData["key_cloak_admin_id"];
        // $this->adminUsername = $this->setting->settingData["key_cloak_admin_username"];
        // $this->adminPassword = $this->setting->settingData["key_cloak_admin_password"];

        $this->options =  [
            'authServerUrl'         => $this->serverURI,
            'realm'                 => $this->realm,
            'clientId'              => $this->clientId,
            'clientSecret'          => $this->clientSecret,
            'redirectUri'           => $this->loginURI,
        ];

        if ($this->networkAdapterConfig->useProxy) {
            $this->configureProxy();
        }

        try {
            $this->provider = new \Stevenmaguire\OAuth2\Client\Provider\Keycloak($this->options);
        }
        catch (Exception $e) {
            header('Location: '. base_url());
            exit;
        }
    }

    public function logout():bool
    {
        $this->provider = new \Stevenmaguire\OAuth2\Client\Provider\Keycloak([
            'authServerUrl'         => $this->serverURI,
            'realm'                 => $this->realm,
            'clientId'              => $this->clientId,
            'clientSecret'          => $this->clientSecret,
            'redirectUri'           => base_url()
        ]);
        $logoutUrl = $this->provider->getLogoutUrl();
        $logoutUrl = str_replace('auth//', 'auth/', $logoutUrl);
        $this->session->destroy();

        header('Location: '.$logoutUrl);
        exit;
    }

    /**
     * 
     */
    public function register(string $email, string $username, string $password, array $additionaldata, array $groups){
        $ionAuth = new IonAuth();
        $result = $ionAuth->register($username, $email, $password, $additionaldata, $groups);
        //if ($result){
            //Assuming $result is user_id
            // if( $groups ){
            //     $networkModel = new Network($this->db);
            //     $installation_key = $additionaldata["installation_key"];
            //     foreach ($groups as $g) {
            //         $groups_exploded = explode(',', $g);
            //         $group_id = $groups_exploded[0];
            //         $network_key = $groups_exploded[1];

            //         $id = $networkModel->addUserToNetworkGroup($result, $group_id, $installation_key, $network_key);
            //     }
            // }
            // Create user in Keycloak H2 database 

            // $keyCloakApi = new KeyCloakApi($this);
            // if (!$keyCloakApi->userExists($email)) {
            //     $first_name = $additionaldata['first_name'];
            //     $last_name = $additionaldata['last_name'];
            //     $keyCloakApi->createUser($email, $first_name, $last_name);
                
            //     $user_id =  $keyCloakApi->getUserId($email);
            //     $keyCloakApi->setPassword($user_id, $password);
            //     $keyCloakApi->logout();
            //     //Send email confirmation
            //     $emailAdapter = \Config\Services::email();

            //     $credEmailInstance = EmailFactory::createCredentialsEmail($emailAdapter);
            //     $credEmailInstance->setCredentials($email, $password);
            //     $credEmailInstance->send();
            // }

        //}
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

    public function getKeyCloakConfig(){
        $kcConf = array();
        $kcConf['authServerUrl'] = $this->serverURI;
        $kcConf['realm'] = $this->realm;
        $kcConf['client_id'] = $this->clientId;
        $kcConf['client_secret'] = $this->clientSecret;
        $kcConf['admin_id'] = $this->adminId;
        $kcConf['admin_username'] = $this->adminUsername;
        $kcConf['admin_password'] = $this->adminPassword;
        return $kcConf;
    }
}
