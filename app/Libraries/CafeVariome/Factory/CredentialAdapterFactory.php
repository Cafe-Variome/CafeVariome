<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * CredentialAdapterFactory.php
 * Created 03/05/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\CredentialAdapter;
use App\Libraries\CafeVariome\Database\IAdapter;

class CredentialAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new CredentialAdapter();
    }
}
