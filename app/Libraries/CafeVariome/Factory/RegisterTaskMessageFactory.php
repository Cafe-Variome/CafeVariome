<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * RegisterTaskMessageFactory.php
 * Created 20/07/2022
 *
 * This is a factory class for handling object creation of RegisterTaskMessage class.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Net\Service\RegisterTaskMessage;

class RegisterTaskMessageFactory
{

    public function GetInstance(int $task_id, string $status = '', bool $batch = false): RegisterTaskMessage
    {
        return new RegisterTaskMessage($task_id, $status, $batch);
    }
}
