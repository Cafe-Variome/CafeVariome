<?php namespace App\Libraries\CafeVariome\Core;

/**
 * APIResponseBundle.php
 * 
 * Created: 17/12/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 */

use App\Models\NetworkAPIResponse;


class APIResponseBundle
{
    public $response;

    public function __construct(){
        
    }

    public function initiateResponse(int $status, array $data = null)
    {
        $this->response = new NetworkAPIResponse($status, $data);
    }

    public function setResponseMessage(string $message)
    {
        $this->response->setMessage($message);
    }

    public function getResponse(): NetworkAPIResponse
    {
        return $this->response;
    }

    public function getResponseArray(): array
    {
        return $this->response->toArray();
    }

    public function getResponseJSON(): string
    {
        return $this->response->toJSON();
    }
}
