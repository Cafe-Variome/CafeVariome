<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * DiscoveryGroupHelper.php
 * Created: 06/09/2022
 * @author Mehdi Mehtarizadeh
 *
 * This class offers helper functions for discovery groups in the user interface.
 */

class DiscoveryGroupHelper
{
	public static function GetPolicy(int $policy): string
	{
		switch($policy)
		{
			case DISCOVERY_GROUP_POLICY_EXISTENCE:
				return 'Show source existence';
			case DISCOVERY_GROUP_POLICY_BOOLEAN:
				return 'Show boolean results based on network threshold';
			case DISCOVERY_GROUP_POLICY_COUNT:
				return 'Show number of results';
			case DISCOVERY_GROUP_POLICY_LIST_WITH_ATTRIBUTES:
				return 'Show list of results with selected attributes';
		}
		return 'Undefined';
	}
}
