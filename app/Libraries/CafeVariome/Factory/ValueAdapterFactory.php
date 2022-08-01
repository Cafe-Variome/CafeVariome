<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ValueAdapterFactory.php
 * Created 28/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\ValueAdapter;

class ValueAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new ValueAdapter();
    }
}
