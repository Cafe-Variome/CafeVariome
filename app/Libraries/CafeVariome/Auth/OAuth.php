<?php
namespace App\Libraries\CafeVariome\Auth;

/**
 * Name: OAuth.php
 * Created: 02/10/2020
 * @author Mehdi Mehtarizadeh
 * 
 */
use App\Models\Settings;
use App\Models\User;
use App\Helpers\AuthHelper;
use App\Models\Network;
use App\Libraries\CafeVariome\Email\EmailFactory;
use App\Libraries\CafeVariome\Net\cURLAdapter;

class OAuth extends OpenIDAuthenticator{

    public function __construct(){
        parent::__construct();

        $this->options =  [
            'clientId'                  => $this->clientId,
            'clientSecret'              => $this->clientSecret,
            'redirectUri'               => $this->loginURI,
            'urlAuthorize'              => $this->serverURI . 'authorize',
            'urlAccessToken'            => $this->serverURI . 'token',
            'urlResourceOwnerDetails'   => $this->serverURI . 'userinfo',
            'urlLogout'                 => $this->serverURI . 'logout'
        ];

        if ($this->networkAdapterConfig->useProxy) {
            $this->configureProxy();
        }

        try {
            $this->provider = new \League\OAuth2\Client\Provider\GenericProvider($this->options);
        }
        catch (Exception $e) {
            header('Location: '. base_url());
            exit;
        }
    }

    public function logout()
    {
        $logoutUrl = $this->provider->getLogoutUrl();

        $this->session->destroy();
        header('Location: '.$logoutUrl);
        exit;
    }

}