<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * AttributeMappingAdapterFactory.php
 * Created 15/12/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\AttributeMappingAdapter;
use App\Libraries\CafeVariome\Database\IAdapter;

class AttributeMappingAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new AttributeMappingAdapter();
    }
}
