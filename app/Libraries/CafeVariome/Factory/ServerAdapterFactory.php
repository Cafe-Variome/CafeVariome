<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * ServerAdapterFactory.php
 * Created 25/04/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\ServerAdapter;

class ServerAdapterFactory extends AdapterFactory
{

	/**
	 * Creates and returns an object of the ServerAdapter type.
	 * @return IAdapter
	 */
    public function GetInstance(): IAdapter
    {
        return new ServerAdapter();
    }
}
