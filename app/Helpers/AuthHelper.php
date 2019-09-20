<?php namespace App\Helpers;

/**
 * Name: AuthHelper.php
 * Created: 12/07/2019
 * @author Mehdi Mehtarizadeh
 * 
 * Helper functions for authentication of users
 */

use App\Models\Settings;

class AuthHelper
{
  static function authPostRequest($data, $uri) {

    $url = $uri . '/format/json';
    $url = preg_replace('/([^:])(\/{2,})/', '$1/', $url); // Strip out any double forward slashes from the url

            
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'TLSv1');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    "Access-Control-Allow-Origin: *"
    ));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch,CURLOPT_POST, true);
    curl_setopt($ch,CURLOPT_POSTFIELDS, $data);

    $result = curl_exec($ch);

    $session_status = json_decode($result, 1);
    curl_close($ch);

    return $result;    
  }
    
    /**
     * checkAuthServer($uri, $port)
     * 
     */
    public static function checkServer($uri, $port)
    {
      if(AuthHelper::contains("http://", $uri)) {
        $uri = str_replace("http://", "", $uri);
      }

      if(AuthHelper::contains("https://", $uri)) {
        $uri = str_replace("https://", "", $uri);
      }

      $fp = @fsockopen($uri, $port, $errno, $errstr, 10);

      if ($fp) {
        //server is available
        return true;
      }
      return false;
    }

    /**
     * contains($needle, $haystack)
     * 
     */
    static function contains($needle, $haystack)
    {
        return strpos($haystack, $needle) !== false;
    }
}

