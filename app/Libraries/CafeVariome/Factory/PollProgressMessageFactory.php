<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * PollProgressMessageFactory.php
 * Created 25/07/2022
 *
 * This is a factory class for handling object creation of PollProgressMessage class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Net\Service\IMessage;
use App\Libraries\CafeVariome\Net\Service\PollProgressMessage;

class PollProgressMessageFactory extends MessageFactory
{

    public function GetInstance(): IMessage
    {
        return new PollProgressMessage();
    }
}
