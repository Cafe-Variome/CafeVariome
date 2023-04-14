<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * DiscoveryGroupAdapterFactory.php
 * Created 06/09/2022
 *
 * @author Mehdi Mehtarizadeh
 */


use App\Libraries\CafeVariome\Database\DiscoveryGroupAdapter;
use App\Libraries\CafeVariome\Database\IAdapter;

class DiscoveryGroupAdapterFactory extends AdapterFactory
{
    /**
     * @inheritDoc
     */
    public function GetInstance(): DiscoveryGroupAdapter
    {
        return new DiscoveryGroupAdapter();
    }
}
