<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * AdapterFactory.php
 * Created 25/04/2022
 *
 * This is an abstract factory class for handling object creation of Adapter classes.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;

abstract class AdapterFactory
{
	/**
	 * Creates and returns an object that implements IAdapter.
	 *
	 * @return IAdapter
	 */
	public abstract function getInstance(): IAdapter;
}
