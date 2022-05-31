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
use League\OAuth2\Client\Token\AccessToken;

class QueryNetworkInterface extends AbstractNetworkInterface
{
    public function __construct(string $targetUri) {
        parent::__construct($targetUri);

        $curlOptions = [CURLOPT_RETURNTRANSFER => TRUE
                        //,CURLOPT_HTTPHEADER => ['Expect:'] //Removing Expect header results in speed-up on some LAMPs running with CENTOS
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

    public function query(string $query, int $network_key, string $authentication_url, string $token = null)
    {
        $this->adapterw('QueryApi/Query', [
			'query' => $query,
			'network_key' => $network_key,
			'token' => json_encode($token),
			'authentication_url' => $authentication_url
		]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function getJSONDataModificationTime(int $network_key, string $checksum = Null, bool $isHPO = false, bool $loadfile = false)
    {
        $this->adapterw('QueryApi/getJSONDataModificationTime', ['network_key' => $network_key, 'checksum' => $checksum, 'ishpo' => $isHPO, 'loadfile' => $loadfile]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function getEAVJSON(int $network_key, int $modification_time)
    {
        $this->adapterw('QueryApi/getEAVJSON', ['network_key' => $network_key, 'modification_time' => $modification_time]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function getHPOJSON(int $network_key, int $modification_time)
    {
        $this->adapterw('QueryApi/getHPOJSON', ['network_key' => $network_key, 'modification_time' => $modification_time]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    protected function adapterw(string $uriTail, array $data)
    {
        $this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail);
        $this->networkAdapter->setOption(CURLOPT_POST, true);
        $this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $data);
    }

}
