<?php namespace App\Libraries\CafeVariome\Net;

/**
 * HPONetworkInterface.php
 * 
 * Created: 21/01/2021
 * 
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 * This class interfaces the software with the HPO services provided by Tim on LAMP 240.
 */

class HPONetworkInterface extends AbstractNetworkInterface
{
    public function __construct(string $targetUri = 'https://www592.lamp.le.ac.uk/') {
        parent::__construct($targetUri);

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

    public function getHPO(string $term)
    {
        $this->adapterw("hpo/query.php", ['id' => $term]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function getAncestor(string $term)
    {
        $this->adapterw("hpo/ancestor.php", ['id' => $term]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    protected function adapterw(string $uriTail, array $data)
    {
        $queryString = '?';
        foreach ($data as $key => $value) {
            if ($value != "" || $value != null) {
                $queryString .= $key . '=' . $value . '&';
            }
        } 
        $this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail . $queryString);
        $this->networkAdapter->setOption(CURLOPT_HTTPGET, true);
    }

    protected function processResponse($response)
    {
        return json_decode($response);
    }
}