<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * NetworkRequestAdapterFactory.php
 * Created 30/01/2023
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\NetworkRequestAdapter;

class NetworkRequestAdapterFactory extends AdapterFactory
{
    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new NetworkRequestAdapter();
    }
}
