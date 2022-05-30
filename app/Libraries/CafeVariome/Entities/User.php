<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * User.php
 * Created 27/05/2022
 *
 * This class extends Entity.
 * @author Mehdi Mehtarizadeh
 */

class User extends Entity
{
	/**
	 * @var string
	 */
	public string $ip_address;

	/**
	 * @var string
	 */
	public ?string $username;

	public ?string $password;

	public string $email;

	public int $created_on;

	public ?int $last_login;

	public ?string $first_name;

	public ?string $last_name;

	public ?string $company;

	public ?string $phone;

	public bool $is_admin;

	public ?string $token;

	public bool $remote;

	public bool $active;

	public function __construct(array $properties)
	{
		parent::__construct($properties);

	}
}
