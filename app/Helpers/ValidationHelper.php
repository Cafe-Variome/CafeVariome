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


    function __construct()
    {

    }

    /**
     * unique_network_group_name_check
     * @param string $group_name the user input to be checked for uniqueness.
     * @param string $network_key the network key accompanying group name.
     * 
     * @return bool true if the network group name does not exist in the database, false otherwise.
     * 
     * @author Mehdi Mehtarizadeh
     */
    
    function unique_network_group_name_check(string $group_name, string $network_key): bool 
    {
        $networkGroupModel = new \App\Models\NetworkGroup();
        return ($networkGroupModel->getNetworkGroups('', array('network_key' => $network_key, 'name' => $group_name)) ? false : true);
    }

    public function valid_delimiter(string $delimiter): bool
    {
        $valid_delimiters = [',', '/', ';', ':', '|', '*', '&', '%', '$', '!', '~', '#', '-', '_', '+', '=', '^'];

        return in_array($delimiter, $valid_delimiters);
    }

}