<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * EntityFactory.php
 * Created 20/07/2022
 *
 * This is an abstract factory class for handling object creation of Message classes.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Net\Service\IMessage;


abstract class MessageFactory
{
	public abstract function GetInstance(): IMessage;
}
