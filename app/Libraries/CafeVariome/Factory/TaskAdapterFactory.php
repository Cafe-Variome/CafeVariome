<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * TaskAdapterFactory.php
 * Created 05/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\TaskAdapter;

class TaskAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new TaskAdapter();
    }
}
