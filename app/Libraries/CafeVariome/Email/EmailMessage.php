<?php namespace App\Libraries\CafeVariome\Email;

/**
 * EmailMessage.php
 * 
 * Created: 19/09/2019
 * @author Mehdi Mehtarizadeh
 */

 class EmailMessage {
     
    private $body;

    private $subject;

    private $toAddress;

    private $fromAddress;

    private $senderName;

    public function setBody(string $body){
        $this->body = $body;
    }

    public function getBody():string
    {
        return $this->body;
    }

    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    public function getSubject(): string
    {
       return $this->subject;
    }

    public function setToAddress(string $address)
    {
        $this->toAddress = $address;
    }

    public function getToAddress(): string
    {
       return $this->toAddress;
    }

    public function setFromAddress(string $address)
    {
        $this->fromAddress = $address;
    }

    public function getFromAddress():string
    {
        return $this->fromAddress;
    }

    public function setSenderName(string $name)
    {
        $this->senderName = $name;
    }

    public function getSenderName():string
    {
        return $this->senderName;
    }

    public function Compose(string $to, string $subject, string $body)
    {
        $this->body = $body;
        $this->subject = $subject;
        $this->toAddress = $to;
    }
 }