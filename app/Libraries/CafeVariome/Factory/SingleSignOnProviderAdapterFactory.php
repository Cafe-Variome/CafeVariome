<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * SingleSignOnProviderAdapterFactory.php
 * Created 25/04/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\SingleSignOnProviderAdapter;

class SingleSignOnProviderAdapterFactory extends AdapterFactory
{

	/**
	 * Creates and returns an object of the SingleSignOnProviderAdapter type.
	 * @return IAdapter
	 */
	public function getInstance(): IAdapter
	{
		return new SingleSignOnProviderAdapter();
	}
}
