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

class NetworkInterface extends AbstractNetworkInterface
{
    protected $installation_key;

    public function __construct(string $targetUri = '') {
        parent::__construct($targetUri);

        $this->installation_key = $this->setting->getInstallationKey();

        $this->serverURI = $this->setting->getAuthServerUrl();
        $curlOptions = [CURLOPT_RETURNTRANSFER => TRUE,
                        CURLOPT_HTTPHEADER => ['Expect:'] //Removing Expect header results in speed-up on some LAMPs running with CENTOS];
        ];
        
        $this->networkAdapter = new cURLAdapter(null, $curlOptions);

        if ($this->networkAdapterConfig->useProxy) {
            $proxyDetails = $this->networkAdapterConfig->proxyDetails;
            $this->configureProxy($proxyDetails);
        }
    }

    protected function configureProxy(array $proxyConfig)
    {
        $this->networkAdapter->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->networkAdapter->setOption(CURLOPT_HTTPPROXYTUNNEL, 1);
        $this->networkAdapter->setOption(CURLOPT_PROXY, $proxyConfig['hostname']);
        $this->networkAdapter->setOption(CURLOPT_PROXYPORT, $proxyConfig['port']);

        if ($proxyConfig['username'] != '' && $proxyConfig['password'] != '') {
            $this->networkAdapter->setOption(CURLOPT_PROXYUSERPWD, $proxyConfig['username'] . ':' . $proxyConfig['password']);
        }
    }

    public function CreateNetwork(array $data)
    {
        $this->adapterw('NetworkApi/createNetwork', $data);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function AddInstallationToNetwork(array $data)
    {
        $this->adapterw('NetworkApi/addInstallationToNetwork', $data);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function GetNetworksByInstallationKey(string $installation_key)
    {
        $this->adapterw('NetworkApi/getNetworksByInstallationKey', ['installation_key' => $installation_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function GetNetwork(int $network_key)
    {
        $this->adapterw('NetworkApi/getNetwork', ['network_key' => $network_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function GetNetworkThreshold(int $network_key)
    {
        $this->adapterw('NetworkApi/getNetworkThreshold', ['network_key' => $network_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function SetNetworkThreshold(int $network_key, int $network_threshold)
    {
        $this->adapterw('NetworkApi/setNetworkThreshold', ['network_key' => $network_key, 'network_threshold' => $network_threshold]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function LeaveNetwork(int $network_key)
    {
        $this->adapterw('NetworkApi/leaveNetwork', ['network_key' => $network_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function GetAvailableNetworks()
    {
        $this->adapterw('NetworkApi/getAvailableNetworks', []);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function RequestToJoinNetwork(int $network_key, string $email, string $justification)
    {
        $this->adapterw('NetworkApi/requestToJoinNetwork', ['network_key' => $network_key, 'email' => $email, 'justification' => $justification]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function AcceptRequest(string $token)
    {
        $this->adapterw('NetworkApi/acceptRequest', ['token' => $token]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function DenyRequest(string $token)
    {
        $this->adapterw('NetworkApi/denyRequest', ['token' => $token]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function GetInstallationsByNetworkKey(int $network_key)
    {
        $this->adapterw('NetworkApi/getInstallationsByNetworkKey', ['network_key' => $network_key]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }
    
    protected function adapterw(string $uriTail, array $data)
    {
        $this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail);
        $this->networkAdapter->setOption(CURLOPT_POST, true);
        $data['installation_key'] = $this->installation_key; 
        $this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $data);
    }

    // protected function processResponse($response)
    // {
    //     $responseObj = json_decode($response);

    //     if ($responseObj == Null || !property_exists($responseObj, 'status')) {
    //         $responseObj = new \StdClass();
    //         $responseObj->status = false;
    //     }

    //     return $responseObj;
    // }

    /**
     * Checks availability of the authentication server before sending requests.
     */
    // public function ping(): bool
    // {
    //     $fp = @fsockopen($this->serverURI);

    //     if ($fp) {
    //     //server is available
    //     return true;
    //     }
    //     return false;
    // }
}
