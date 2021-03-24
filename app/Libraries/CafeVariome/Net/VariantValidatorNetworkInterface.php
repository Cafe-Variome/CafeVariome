<?php namespace App\Libraries\CafeVariome\Net;

/**
 * VariantValidatorNetworkInterface.php
 * 
 * Created: 18/03/2021
 * 
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * 
 * This class interfaces the software with the variant validation service provided at https://variantvalidator.org
 */

class VariantValidatorNetworkInterface extends AbstractNetworkInterface
{
    public function __construct(string $targetUri = 'https://rest.variantvalidator.org/') {
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
    
    public function ValidateVariant(string $chromosome, int $position, string $ref, string $alt, string $assembly, bool $returnShortDescription = true, string $transcriptModel = 'none', string $selectTranscripts = 'none')
    {
        $variantDescription = $chromosome . '-' . $position . '-' . $ref . '-' . $alt;
        $this->adapterw('VariantFormatter/variantformatter/', [$assembly, $variantDescription, $transcriptModel, $selectTranscripts, $returnShortDescription]);
        $response = $this->networkAdapter->Send();
        return $this->processResponse($response);
    }

    protected function adapterw(string $uriTail, array $data)
    {
        $queryString = '';
        foreach ($data as $key => $value) {
            if ($value != "" || $value != null) {
                $queryString .=$value . '/';
            }
        } 
        $queryString = rtrim($queryString, '/');
        $this->networkAdapter->setOption(CURLOPT_URL, $this->serverURI . $uriTail . $queryString);
        $this->networkAdapter->setOption(CURLOPT_HTTPGET, true);
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
