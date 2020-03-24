<?php namespace App\Libraries\CafeVariome\Net;

/**
 * QueryNetworkInterface.php
 * 
 * Created: 27/01/2020
 * 
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 * This class interfaces this installation with other installations in the network.
 * It is mainly for sending queries to other installations and handling the response.
 */

use App\Models\Settings;

class QueryNetworkInterface
{
    private $serverURI;
    private $networkAdapter;
    private $setting; 

    public function __construct(string $targetUri) {
        $this->setting = new Settings();

        $this->serverURI = $targetUri;
        $curlOptions = [CURLOPT_RETURNTRANSFER => TRUE];
        $this->networkAdapter = new cURLAdapter(null, $curlOptions);
    }

    public function query(string $query, int $network_key, int $user_id)
    {
        $this->adapterw('QueryApi/Query', ['query' => $query, 'network_key' => $network_key, 'user_id' => $user_id]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function getJSONDataModificationTime(int $network_key, string $checksum = Null, bool $isHPO = false, bool $loadfile = false)
    {
        $this->adapterw('QueryApi/getJSONDataModificationTime', ['network_key' => $network_key, 'checksum' => $checksum, 'ishpo' => $isHPO, 'loadfile' => $loadfile]);
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
        return $responseObj;
    }


}
 