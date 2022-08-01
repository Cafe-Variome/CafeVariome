<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * SettingAdapterFactory.php
 * Created 21/07/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\SettingAdapter;

class SettingAdapterFactory extends AdapterFactory
{
    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return SettingAdapter::GetSingletonInstance();
    }
}
