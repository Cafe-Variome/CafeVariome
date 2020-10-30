<?php
namespace App\Libraries\CafeVariome\Auth;

/**
 * Name: Authenticator.php
 * Created: 09/10/2020
 * @author Mehdi Mehtarizadeh
 * 
 */

use App\Models\Settings;
use App\Models\User;

abstract class Authenticator{

    protected $loggedIn;
    protected $userId;
    protected $adapterClassName;
    protected $isAdapterExternal;

    protected $session;
    protected $db;
    protected $setting;

    protected $provider; 

    public function __construct() {
        $this->adapterClassName = get_class($this);

        $this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance();
        $this->session =  \Config\Services::session();
    }

    public function getUserIdByEmail(string $email):int
    {
        $userModel = new User();
        $user = $userModel->getUserByEmail($email);

        return $user ? $user[0]->id : -1;
    }

    abstract public function getToken();
}