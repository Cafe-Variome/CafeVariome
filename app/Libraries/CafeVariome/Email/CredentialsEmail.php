<?php namespace App\Libraries\CafeVariome\Email;

/**
 * CredentialsEmail.php
 * 
 * Created: 19/09/2019
 * @author Mehdi Mehtarizadeh
 */

class CredentialsEmail extends CafeVariomeEmail
{

    public function __construct($adapter){
        parent::__construct($adapter);
        $this->message = new EmailMessage();
    }

    public function setCredentials(string $email, string $password){
        $this->message->setFromAddress("noreply@cafevariome.org");
        $this->message->setSenderName("Cafe Variome Email Notification Centre");
        $subject = "Registration Details";

        $body = "You have been added as a user to CafeVariome" . PHP_EOL;
        $body .= "You have been provided with a temporary password to login with." . PHP_EOL;
        $body .= "To login please go to: ". base_url(). PHP_EOL;
        $body .= "Credentials: ". PHP_EOL;
        $body .= "Username: " . $email . PHP_EOL;
        $body .= "Password: " . $password . PHP_EOL;

        $this->message->compose($email, $subject, $body);
    }

    public function send()
    {
        parent::send();
    }
}
