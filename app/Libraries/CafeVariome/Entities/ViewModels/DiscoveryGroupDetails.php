<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * DiscoveryGroupDetails.php
 * Created 07/09/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\DiscoveryGroupHelper;

class DiscoveryGroupDetails extends BaseViewModel
{
	public string $name;

	public string $description;

	public int $network_id;

	public string $network_name;

	public string $policy;

	public function __construct(object $input = null)
	{
		if (!is_null($input))
		{
			parent::__construct($input);
			$this->policy = DiscoveryGroupHelper::GetPolicy($this->policy);
		}
	}
}
