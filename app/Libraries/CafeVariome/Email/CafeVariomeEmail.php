<?php namespace App\Libraries\CafeVariome\Email;

/**
 * CafeVariomeEmail.php
 * 
 * Created: 19/09/2019
 * @author Mehdi Mehtarizadeh
 */

class CafeVariomeEmail implements IEmail{

    protected $emailAdapter;

    protected $message;

    public function __construct($adapter){
        $this->message = new EmailMessage();
        $this->emailAdapter = $adapter;
    }

    public function setEmailAdapter($emailAdapter){
        $this->emailAdapter = $emailAdapter;
    }

    public function send(){
        $this->emailAdapter->setFrom($this->message->getFromAddress(), $this->message->getSenderName());
        $this->emailAdapter->setTo($this->message->getToAddress());
        $this->emailAdapter->setSubject($this->message->getSubject());
        $this->emailAdapter->setMessage($this->message->getBody());

        $this->emailAdapter->send();
    }


}