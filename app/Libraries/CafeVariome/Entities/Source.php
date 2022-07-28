<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Source.php
 * Created 21/06/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class Source extends Entity
{
	public string $uid;

	public string $name;

	public string $display_name;

	public string $description;

	public string $owner_name;

	public string $owner_email;

	public ?string $uri;

	public int $date_created;

	public int $record_count;

	public bool $locked;

	public bool $status;

}
