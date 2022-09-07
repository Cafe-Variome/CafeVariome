<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * NetworkAdapterFactory.php
 * Created 05/09/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\NetworkAdapter;

class NetworkAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new NetworkAdapter();
    }
}
