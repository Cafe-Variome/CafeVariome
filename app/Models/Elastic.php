<?php namespace App\Models;

/**
 * Elastic.php
 * Created 26/07/2019
 * 
 * @author Owen Lancaster 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 */

 use App\Models\Settings;

class Elastic{

    public function  __construct(){
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);
    }

    /**
     * Delete Elastic Index - For a given source delete all its ElasticSearch Indices
     *
     * @param int $source_id - The Id of the Source
     * @return N/A
     */
    function deleteElasticIndex($source_id) {
        $params = [];
        $title = $this->setting->settingData['site_title'];
        $index_name = $title."_".$source_id."*";	    
        $uri = $this->setting->settingData['elastic_url']."/".$index_name;
        try {
            $this->buildCurlCommand($uri);
        }	  
        catch (exception $e) {
            error_log("No Indices exist for source: ". $source_id);
        }      	 
    }

    function buildCurlCommand($uri){
        // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');

        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
    }
}