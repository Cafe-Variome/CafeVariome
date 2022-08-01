<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * GroupAdapterFactory.php
 * Created 27/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\GroupAdapter;
use App\Libraries\CafeVariome\Database\IAdapter;

class GroupAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new GroupAdapter();
    }
}
