<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ProxyServerAdapterFactory.php
 * Created 25/05/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\ProxyServerAdapter;

class ProxyServerAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function getInstance(): IAdapter
    {
        return new ProxyServerAdapter();
    }
}
