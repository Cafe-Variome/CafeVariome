<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * DataFileAdapterFactory.php
 * Created 17/06/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\DataFileAdapter;
use App\Libraries\CafeVariome\Database\IAdapter;

class DataFileAdapterFactory extends AdapterFactory
{
    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new DataFileAdapter();
    }
}
