<?php namespace App\Libraries\CafeVariome\Net\NetworkInterface;

/**
 * NetworkInterface.php
 * 
 * Created: 31/10/2019
 * 
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 * This class interfaces the software with the authentication server and handles data sent/received to the server.
 * It is mainly for CRUD operation of shared entities. 
 */


class NetworkInterface
{
    private $serverURI;

    private $networkAdapter;

    public function __construct() {
        $this->networkAdapter = new cURLAdapter();
    }

    public function CreateNetwork(array $data)
    {
        $this->networkAdapter->Send();
    }


    /**
     * Checks availability of the authentication server before sending requests.
     */
    public function ping(): bool
    {
        $fp = @fsockopen($this->serverURI);

        if ($fp) {
        //server is available
        return true;
        }
        return false;
    }
}
