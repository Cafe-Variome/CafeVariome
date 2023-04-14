<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * AttributeAdapterFactory.php
 * Created 28/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\AttributeAdapter;
use App\Libraries\CafeVariome\Database\IAdapter;

class AttributeAdapterFactory extends AdapterFactory
{
    /**
     * @inheritDoc
     */
    public function GetInstance(): AttributeAdapter
    {
        return new AttributeAdapter();
    }
}
