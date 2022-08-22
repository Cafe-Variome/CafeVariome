<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * OntologyRelationshipAdapterFactory.php
 * Created 22/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\OntologyRelationshipAdapter;

class OntologyRelationshipAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new OntologyRelationshipAdapter();
    }
}
