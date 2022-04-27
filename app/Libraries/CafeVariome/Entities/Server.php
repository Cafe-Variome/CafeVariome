<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Server.php
 * Created 22/04/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class Server extends Entity
{
	/**
	 * @var string name of the server
	 */
	public string $name;

	/**
	 * @var string URL or IP address of the server.
	 */
	public string $address;

	/**
	 * @var bool if the server is required as part of the base functionality of the system it is not removable, true otherwise.
	 */
	public bool $removable;

	public function __construct(array $properties)
	{
		parent::__construct($properties);
	}
}
