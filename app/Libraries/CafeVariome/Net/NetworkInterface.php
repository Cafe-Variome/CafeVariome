<?php namespace App\Libraries\CafeVariome\Net;

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

 use App\Models\Settings;

class NetworkInterface
{
    private $serverURI;

    private $networkAdapter;

    private $setting; 

    public function __construct() {
        $this->setting = new Settings();

        $this->serverURI = $this->setting->settingData['auth_server'];
        $curlOptions = [CURLOPT_RETURNTRANSFER => TRUE];

        $this->networkAdapter = new cURLAdapter(null, $curlOptions);
    }

    public function CreateNetwork(array $data)
    {
        $this->adapterw('networkapi/createNetwork', $data);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function AddInstallationToNetwork(array $data)
    {
        $this->adapterw('networkapi/addInstallationToNetwork', $data);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function GetNetworksByInstallationKey(string $installation_key)
    {
        $this->adapterw('networkapi/getNetworksByInstallationKey', ['installation_key' => $installation_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function adapterw(string $uriTail, array $data)
    {
        $this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail);
        $this->networkAdapter->setOption(CURLOPT_POST, true);
        $this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $data);
    }

    private function processResponse($response)
    {
        $responseObj = json_decode($response);
        if ($responseObj->data) {
            return $responseObj->data;
        }
        else{
            return $responseObj->status;
        }
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
