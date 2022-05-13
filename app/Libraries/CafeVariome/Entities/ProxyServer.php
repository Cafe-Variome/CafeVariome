<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * ProxyServer.php
 * Created 12/05/2022
 *
 * This class extends Entity.
 * @author Mehdi Mehtarizadeh
 */

class ProxyServer extends Entity
{
	/**
	 * @var int
	 */
	public int $server_id;

	/**
	 * @var string
	 */
	public string $name;

	/**
	 * @var int
	 */
	public int $port;

	/**
	 * @var int|null
	 */
	public ?int $credential_id;
}
