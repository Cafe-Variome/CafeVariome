<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * EAVAdapterFactory.php
 * Created 18/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */



use App\Libraries\CafeVariome\Database\EAVAdapter;

class EAVAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): EAVAdapter
    {
        return new EAVAdapter();
    }
}
