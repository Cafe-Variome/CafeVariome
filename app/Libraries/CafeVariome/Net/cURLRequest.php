<?php namespace App\Libraries\CafeVariome\Net;

/**
 * cURLRequest.php
 * 
 * Created: 15/10/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 */

 class cURLRequest 
 {
    private $curlInstance;

    private $baseUrl;

    private $sslEnabled;


    public function __construct(string $url = null, array $option_values = null) {

        if ($this->curlEnabled()) {
            if ($url) {
                $this->baseUrl = $url;
                $this->curlInstance = curl_init($this->baseUrl);
            }
            else {
                $this->curlInstance = curl_init();
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
        return curl_setopt($this->curlInstance, $option, $value);
    }

    public function Send(): mixed
    {
        return curl_exec($this->curlInstance);
    }

    private function curlEnabled(): bool
    {
        return function_exists('curl_version');
    }
    
    function __destruct() {
        curl_close($this->curlInstance);
    }
 }
 