<?php namespace App\Controllers;

/**
 * ServiceApi.php
 * 
 * Created : 17/02/2021
 * 
 * @author Mehdi Mehtarizadeh
*/

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use App\Libraries\CafeVariome\Net\ServiceInterface;
use App\Libraries\CafeVariome\Core\APIResponseBundle;
use CodeIgniter\Config\Services;

class ServiceApi extends ResourceController
{
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);
 
    }

    public function pollUploadedFiles()
    {
        $si = new ServiceInterface();
        $result = "retry: 3000\nid: 0\ndata: {\"progress\": \"-1\", \"status\": \"\"}\n\n";
        try {
            $fileStatus = json_decode($si->GetUploadedFilesStatus(), true);

            if (json_last_error() == JSON_ERROR_NONE && count($fileStatus) > 0) {
                $result = "retry: 1000\n";
                foreach ($fileStatus as $taskId => $value) {
                    //$result .= "id: " . $taskId . "\ndata: " . $value ."\n\n";
                    $result .= "id: " . $taskId . "\n";
                    $result .= "data: {\"progress\": \"" . $value['progress'] . "\", \"status\": \"" . $value['status'] . "\"}\n\n";
                }
            }
        } catch (\Exception $ex) {

        }

        $this->response->setHeader("Content-Type", "text/event-stream");
        $this->response->setHeader("Cache-Control", "no-cache");


        return $this->respond($result);

    }

    public function pollElasticSearch()
    {
        $si = new ServiceInterface();
        $result = "retry: 3000\nid: 0\ndata: {\"progress\": \"-1\", \"status\": \"\"}\n\n";
        try {
            $fileStatus = json_decode($si->GetElasticsearchStatus(), true);

            if (json_last_error() == JSON_ERROR_NONE && count($fileStatus) > 0) {
                $result = "retry: 1000\n";
                foreach ($fileStatus as $taskId => $value) {
                    $result .= "id: " . $taskId . "\n";
                    $result .= "data: {\"progress\": \"" . $value['progress'] . "\", \"status\": \"" . $value['status'] . "\"}\n\n";
                }
            }
        } catch (\Exception $ex) {

        }

        $this->response->setHeader("Content-Type", "text/event-stream");
        $this->response->setHeader("Cache-Control", "no-cache");


        return $this->respond($result);

    }

}