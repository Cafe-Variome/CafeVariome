<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * UserAdapterFactory.php
 * Created 27/05/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\UserAdapter;

class UserAdapterFactory extends AdapterFactory
{

    /**
     * @inheritDoc
     */
    public function GetInstance(): IAdapter
    {
        return new UserAdapter();
    }
}
