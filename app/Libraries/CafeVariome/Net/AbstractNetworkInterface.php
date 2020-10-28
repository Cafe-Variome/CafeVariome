<?php namespace App\Libraries\CafeVariome\Net;

/**
 * AbstractNetworkInterface.php
 * 
 * Created: 28/10/2020
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * This is an abstract class for communicating with RESTful endpoints of other Cafe Variome instances.
 * 
 */

use App\Models\Settings;


abstract class AbstractNetworkInterface
{
    protected $serverURI;
    protected $networkAdapter;
    protected $setting; 
    protected $networkAdapterConfig;

    public function __construct(string $targetUri) {
        $this->setting = Settings::getInstance();
        $this->serverURI = $targetUri;
        $this->networkAdapterConfig = config('NetworkAdapter');
    }

    protected abstract function configureProxy(array $proxyConfig);

    protected abstract function adapterw(string $uriTail, array $data);

    protected function processResponse($response)
    {
        $responseObj = json_decode($response);

        if ($responseObj == Null || !property_exists($responseObj, 'status')) {
            $responseObj = new \StdClass();
            $responseObj->status = false;
        }

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
