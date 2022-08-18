<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * PageFactory.php
 * Created 17/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Page;

class PageFactory extends EntityFactory
{
	public function GetInstance(?object $input): IEntity
	{
		if (is_null($input) || count($objectVars = get_object_vars($input)) == 0 )
		{
			return new NullEntity();
		}

		$properties = [];
		foreach ($objectVars as $var => $value)
		{
			$properties[$var] = $value;
		}

		return new Page($properties);
	}

	public function GetInstanceFromParameters(string $title, string $content, int $user_id, bool $active, bool $removable = true): Page
	{
		return new Page([
			'title' => $title,
			'content' => $content,
			'user_id' => $user_id,
			'active' => $active,
			'removable' => $removable
		]);
	}
}
