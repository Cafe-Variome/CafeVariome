<?php namespace App\Libraries\CafeVariome\Email;

/**
 * IEmailAbstractFactory.php
 * 
 * Created: 19/09/2019
 * @author Mehdi Mehtarizadeh
 */


interface IEmailAbstractFactory{

    public static function createCredentialsEmail($adapter): CredentialsEmail;

}