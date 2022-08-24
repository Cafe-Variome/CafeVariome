<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ShutdownMessageFactory.php
 * Created 24/08/2022
 *
 * This is a factory class for handling object creation of ShutdownMessage class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Net\Service\IMessage;
use App\Libraries\CafeVariome\Net\Service\ShutdownMessage;

class ShutdownMessageFactory extends MessageFactory
{

    public function GetInstance(): IMessage
    {
        return new ShutdownMessage();
    }
}
