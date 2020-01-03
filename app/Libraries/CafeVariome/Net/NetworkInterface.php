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

    private $installation_key;

    public function __construct() {
        $this->setting = new Settings();
        $this->installation_key = $this->setting->settingData['installation_key'];

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

    public function GetNetwork(int $network_key)
    {
        $this->adapterw('networkapi/getNetwork', ['network_key' => $network_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function GetNetworkThreshold(int $network_key)
    {
        $this->adapterw('networkapi/getNetworkThreshold', ['network_key' => $network_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function SetNetworkThreshold(int $network_key, int $network_threshold)
    {
        $this->adapterw('networkapi/setNetworkThreshold', ['network_key' => $network_key, 'network_threshold' => $network_threshold]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function LeaveNetwork(int $network_key)
    {
        $this->adapterw('networkapi/leaveNetwork', ['network_key' => $network_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function GetAvailableNetworks()
    {
        $this->adapterw('networkapi/getAvailableNetworks', []);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function RequestToJoinNetwork(int $network_key, string $email, string $justification)
    {
        $this->adapterw('networkapi/requestToJoinNetwork', ['network_key' => $network_key, 'email' => $email, 'justification' => $justification]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function AcceptRequest(string $token)
    {
        $this->adapterw('networkapi/acceptRequest', ['token' => $token]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function DenyRequest(string $token)
    {
        $this->adapterw('networkapi/denyRequest', ['token' => $token]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function adapterw(string $uriTail, array $data)
    {
        $this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail);
        $this->networkAdapter->setOption(CURLOPT_POST, true);
        $data['installation_key'] = $this->installation_key; 
        $this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $data);
    }

    private function processResponse($response)
    {
        $responseObj = json_decode($response);
        return $responseObj;
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
