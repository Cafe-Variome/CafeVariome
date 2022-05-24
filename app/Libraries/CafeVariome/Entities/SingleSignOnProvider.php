<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * SingleSignOnProvider.php
 * Created 12/05/2022
 *
 * This class extends Entity.
 * @author Mehdi Mehtarizadeh
 */

class SingleSignOnProvider extends Entity
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
	 * @var string
	 */
	public string $display_name;

	/**
	 * @var string
	 */
	public ?string $icon;

	/**
	 * @var int
	 */
	public int $type;

	/**
	 * @var int
	 */
	public int $port;

	/**
	 * @var string
	 */
	public ?string $logout_url;

	/**
	 * @var string|null
	 */
	public ?string $realm;

	/**
	 * @var bool
	 */
	public bool $user_login;

	/**
	 * @var bool
	 */
	public bool $query;

	/**
	 * @var int
	 */
	public int $authentication_policy;

	/**
	 * @var int|null
	 */
	public ?int $credential_id;

	/**
	 * @var int|null
	 */
	public ?int $proxy_server_id;

	/**
	 * @var int
	 */
	public int $removable;
}
