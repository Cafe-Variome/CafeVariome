<?php namespace App\Libraries\CafeVariome\Net;

/**
 * CellBaseNetworkInterface.php
 * 
 * Created: 18/03/2021
 * 
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 * This class interfaces the software with the variant validation service provided at https://variantvalidator.org
 */

class CellBaseNetworkInterface extends AbstractNetworkInterface
{
    
    private $version = "v4";
    private $species = "hsapiens";
    private $urlParams;

    public function __construct(string $targetUri = 'http://bioinfo.hpc.cam.ac.uk/cellbase/webservices/rest/', array $urlParams = []) 
    {
        parent::__construct($targetUri);

        $this->urlParams = $urlParams;

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

    public function AnnotateVariant(string $chromosome, int $position, string $ref, string $alt, string $assembly)
    {
        $variantDescription = $chromosome . ':' . $position . ':' . $ref . ':' . $alt;
        $this->setParams(['assembly' => $assembly]);
        $this->adapterw('genomic/variant/annotation', [$variantDescription]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    public function setParams(array $params)
    {
        $urlParams = $this->getURLParameters();

        foreach ($params as $key => $value) {
            if (array_key_exists($key, $urlParams)) {
                $urlParams[$key] = $value;
            }
            else {
                $urlParams[$key] = $value;
            }
        }

        $this->urlParams = $urlParams;
    }

    private function getDefaultURLParameters(): array
    {
        return [
            'assembly' => '',
            // 'exclude' => '',
            // 'include' => '',
            'limit' => '-1',
            'skip' => '-1',
            'skipCount' => 'false',
            'count' => 'false',
            'Output%20format' => 'json',
            'normalize' => 'false',
            'phased' => 'false',
            'useCache' => 'false',
            'imprecise' => 'true',
            'svExtraPadding' => '0',
            'cnvExtraPadding' => '0'
        ];
    }

    public function getURLParameters(): array
    {
        $defaultParams = $this->getDefaultURLParameters();

        foreach ($this->urlParams as $key => $value) {
            if (array_key_exists($key, $defaultParams)) {
                $defaultParams[$key] = $value;
            }
            else {
                $defaultParams[$key] = $value;
            }
        }

        return $defaultParams;
    }

    private function makeQuerytring(array $params): string
    {
        $queryString = '?';
        foreach ($params as $key => $value) {
            if ($value != "" || $value != null) {
                $queryString .= $key . '=' . $value . '&';
            }
        }

        $queryString = rtrim($queryString, '&');

        return $queryString;
    }

    protected function adapterw(string $uriTail, array $data)
    {
        $urlParams = $this->getURLParameters();
        $queryString = $this->makeQuerytring($urlParams);
        echo $this->serverURI . $this->version . '/' . $uriTail . $queryString;
        $this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $this->version . '/' . $this->species . '/' . $uriTail . $queryString);
        $this->networkAdapter->setOption(CURLOPT_HTTPHEADER, ["Content-Type: text/plain", "Accept: application/json"]);
        $this->networkAdapter->setOption(CURLOPT_POSTFIELDS, $data[0]);
        $this->networkAdapter->setOption(CURLOPT_POST, true);
    }

    protected function processResponse($response)
    {
        $responseObj = json_decode($response);

        if ($responseObj == Null) {
            $responseObj = new \StdClass();
            $responseObj->status = false;
        }

        return $responseObj;
    }
}
