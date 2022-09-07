<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * DiscoveryGroup.php
 * Created 05/09/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class DiscoveryGroup extends Entity
{
	public string $name;

	public int $network_id;

	public string $description;

	public int $policy;
}
