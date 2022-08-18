<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * PageAdapterFactory.php
 * Created 17/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\PageAdapter;

class PageAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new PageAdapter();
    }
}
