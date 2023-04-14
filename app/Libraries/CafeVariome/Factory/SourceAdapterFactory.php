<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * SourceAdapterFactory.php
 * Created 21/06/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\IAdapter;
use App\Libraries\CafeVariome\Database\SourceAdapter;

class SourceAdapterFactory extends AdapterFactory
{

	public function GetInstance(): SourceAdapter
	{
		return new SourceAdapter();
	}
}
