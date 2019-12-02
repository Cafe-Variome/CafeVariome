<?php namespace App\Libraries\Net;

/**
 * NetworkAPIResponse.php
 * Created: 19/11/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * Response structure for Network model operations.
 * 
 */

class NetworkAPIResponse extends APIResponse
{
    private $status;
    private $message;

    private $data;

    public function __construct(int $status = null, array $data = null) {
        parent::__construct($status);

        $this->status = $status;

        switch ($status) {
            case 0:
                $this->message = 'Operation failed.';
                break;
            case 1:
                $this->message = 'Operation was successful.';
                break;           
            default:
                break;
        }

        $this->data = $data;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message)
    {
        $this->message = $message;
    }

    public function toArray(): array
    {
        $responseArray = [
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data
        ];

        return $responseArray;
    }

    public function toJSON()
    {
        return json_encode($this->toArray());
    }
}
