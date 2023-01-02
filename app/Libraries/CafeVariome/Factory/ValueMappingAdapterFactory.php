<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ValueMappingFactoryAdapter.php
 * Created 19/12/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\ValueMappingAdapter;

class ValueMappingAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new ValueMappingAdapter();
    }
}
