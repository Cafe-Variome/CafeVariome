<?php
namespace Config;

/**
 * NetworkAdapter.php
 * Created 24/07/2019
 * @author Mehdi Mehtarizadeh
 * 
 * This file contains configuration for NetworkAdapter class.
 * 
 */

class NetworkAdapter extends \CodeIgniter\Config\BaseConfig
{
    public $useProxy = false; // boolean, set true to direct network adapter to communicate through proxy
    
    public $proxyDetails = [
        'hostname' => '',
        'port' => '',
        'username' => '', // Leave empty if no username exists
        'password' => '' // Leave empty if no password exists
    ];
}