<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * OntologyAdapterFactory.php
 * Created 18/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\OntologyAdapter;

class OntologyAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new OntologyAdapter();
    }
}
