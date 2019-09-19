<?php namespace App\Libraries\CafeVariome\Email;

/**
 * EmailFactory.php
 * 
 * Created: 19/09/2019
 * @author Mehdi Mehtarizadeh
 */


class EmailFactory implements IEmailAbstractFactory
{
    public static function createCredentialsEmail($adapter):CredentialsEmail
    {
        return new CredentialsEmail($adapter);
    }
}
