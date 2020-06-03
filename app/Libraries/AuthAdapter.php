<?php
namespace App\Libraries;

/**
 * Name: AuthAdapter.php
 * Created: 17/07/2019
 * 
 * @author: Mehdi Mehtarizadeh
 * 
 * This adapter class provides a simple and unified interface for auhentication.
 * It is an abstraction layer for all authentication engines used in the software.
 */

 use App\Models\User;

class AuthAdapter{

    /**
     * This attribute takes 3 inputs:
     * 1. KeyCloakFirst
     * 2. KeyCloakOnly
     * 3. IonAuthOnly
     */
    private $authRoutine;

    /**
     * Instant of the authentication library or its wrapper
     */
    public $authEngine;

    public function __construct($authRoutine){
        switch($authRoutine){
            case 'KeyCloakFirst':
                $this->authEngine = new KeyCloak();
                if(!$this->authEngine->checkKeyCloakServer()){
                    //Keycloak Server not available
                    //Switch to other 
                    $this->authEngine = new IonAuth();
                }
            break;
            case 'KeyCloakOnly':
                $this->authEngine = new KeyCloak();
                if(!$this->authEngine->checkKeyCloakServer()){
                    //Keycloak Server not available
                    //Switch to other 
                    throw new Exception('KeyCloak Server is not available.'); 
                }
            break;
            case 'IonAuthOnly':
                $this->authEngine = new IonAuth();
            break;
        }
    }

    public function login($username = '', $password = '', $remember = ''){
        if(get_class($this->authEngine) == 'App\Libraries\IonAuth'){
            return $this->authEngine->login($username, $password, $remember);
        }
        else{
            return $this->authEngine->login();
        }
    }

    public function register(string $email, string $username,  array $additionaldata, array $groups = []){
        $password = uniqid();
        //Add special character, uppercase, lowercase and number to meet requirements imposed by Keycloak API
        $password .= "!Kc1";
        
        return $this->authEngine->register($email, $username, $password, $additionaldata, $groups);
    }

    public function update(int $user_id, array $additionaldata, array $groups = []){
        if(get_class($this->authEngine) == 'App\Libraries\IonAuth'){
            return (new \App\Models\IonAuthModel())->update($user_id, $additionaldata);
        }
        else{
            return $this->authEngine->update($user_id, $additionaldata, $groups);
        }
    }

    public function delete(int $user_id){
        $this->authEngine->deleteUser($user_id);
    }

    public function logout():bool{
        return $this->authEngine->logout();
    }

    public function getUserId(){
        return $this->authEngine->getUserId();
    }

    public function loggedIn():bool{
        return $this->authEngine->loggedIn();
    }

    public function isAdmin(int $id=0):bool{
        return $this->authEngine->isAdmin($id);
    }

    public function getName(int $id=0, bool $fullName = false): string
    {
        $userModel = new User();

        $id ? $id : $id = $this->getUserId();
        return $userModel->getName($id, $fullName);
    }
    
    /**
     * getToken()
     * Retrieves Keycloak Token
     * Only valid when Keycloak is enabled.
     */
    public function getToken()
    {
       return ($this->getAuthEngineName() == "app\libraries\keycloak") ? $this->authEngine->getToken() : null;
    }

    /**
     * geyUserIdByToken()
     * Retrieves Keycloak Token
     * Only valid when Keycloak is enabled.
     * @param League\OAuth2\Client\Token\AccessToken $token 
     * @return int
     */
    public function getUserIdByToken(\League\OAuth2\Client\Token\AccessToken $token): int
    {
        if ($this->getAuthEngineName() == "app\libraries\keycloak"){
            $user = $this->authEngine->getUser($token);

            if($user != null){
                $email = $user->getEmail();
                return $this->authEngine->getUserIdByEmail($email);
            }
        }
    }

    public function getAuthEngineName(bool $lower_case = true): string{
        return ($lower_case) ? strtolower(get_class($this->authEngine)) : get_class($this->authEngine);
    }

}