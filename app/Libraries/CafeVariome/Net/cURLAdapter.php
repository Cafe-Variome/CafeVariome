<?php namespace App\Libraries\CafeVariome\Net;

/**
 * cURLRequest.php
 * 
 * Created: 15/10/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 */

 class cURLAdapter extends NetworkAdapter 
 {  
    protected $adapterInstance;

    private $baseUrl;

    public function __construct(string $url = null, array $option_values = null) {

        if ($this->curlEnabled()) {
            if ($url) {
                $this->baseUrl = $url;
                $this->adapterInstance = curl_init($this->baseUrl);
            }
            else {
                $this->adapterInstance = curl_init();
            }

            if ($option_values && count($option_values) > 0) {
                foreach ($option_values as $option => $value) {
                    $this->setOption($option, $value);
                }
            }
        }
        else {
            throw new \Exception('cURL is not installed or enabled.');
        }
        
    }

    public function setOption(int $option, $value): bool
    {
        return curl_setopt($this->adapterInstance, $option, $value);
    }

    public function Send()
    {
        return curl_exec($this->adapterInstance);
    }

    public function getInfo(int $opt = null)
    {
        return curl_getinfo($this->adapterInstance, $opt);
    }

    private function curlEnabled(): bool
    {
        return function_exists('curl_version');
    }
    
    function __destruct() {
        curl_close($this->adapterInstance);
    }
 }
 