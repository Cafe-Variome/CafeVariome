<?php namespace App\Helpers;

/**
 * ValidationHelper.php
 * 
 * Created: 19/08/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * This class contains helper functions for form validation.
 */

class ValidationHelper{

    private $db;


    function __construct(){
        $this->db = \Config\Database::connect();
    }

    function unique_network_group_name_check(string $group_name, string $network_key): bool {
        $session = \Config\Services::session();

        $networkModel = new \App\Models\Network($this->db);
        $token = $session->get('Token');

        //return ($networkModel->getNetworkGroups('', array('network_key' => $network_key, 'name' => $group_name)) ? true : false);
        return true;
    }
}