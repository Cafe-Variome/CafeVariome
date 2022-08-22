<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * OntologyPrefixAdapterFactory.php
 * Created 22/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\OntologyPrefixAdapter;

class OntologyPrefixAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new OntologyPrefixAdapter();
    }
}
