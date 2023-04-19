<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * EAVAdapterFactory.php
 * Created 18/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */



use App\Libraries\CafeVariome\Database\EAVAdapter;
use App\Libraries\CafeVariome\Database\IAdapter;

class EAVAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new EAVAdapter();
    }
}
