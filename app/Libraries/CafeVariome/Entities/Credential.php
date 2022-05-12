<?php namespace App\Libraries\CafeVariome\Entities;

use App\Libraries\CafeVariome\Security\Cryptography;

/**
 * Credential.php
 * Created 22/04/2022
 *
 * This class extends Entity.
 * @author Mehdi Mehtarizadeh
 */

class Credential extends Entity
{
	/**
	 * @var string
	 */
	public string $name;

	/**
	 * @var string
	 */
	public ?string $username;

	/**
	 * @var string
	 */
	public ?string $password;

	/**
	 * @var string
	 */
	public string $hash;

	/**
	 * @var bool
	 */
	public bool $hide_username;

	/**
	 * @var bool
	 */
	public bool $removable;

	/**
	 * @param array $properties
	 * @param bool $encrypt
	 * @throws \Exception
	 */
	public function __construct(array $properties, bool $encrypt = false)
	{
		parent::__construct($properties);

		if ($this->username == null)
		{
			unset($this->username);
		}

		if ($encrypt)
		{
			if($this->hash == '')
			{
				$this->hash = Cryptography::GenerateSecretKey();
			}

			if ($this->password != null)
			{
				$this->password = $this->encrypt($this->password);
			}
			else
			{
				unset($this->password);
			}
		}
	}

	/**
	 * @return string
	 * @throws \SodiumException
	 */
	public function decryptPassword(): string
	{
		return $this->decrypt($this->password);
	}

	/**
	 * @param string $input
	 * @return string
	 * @throws \SodiumException
	 */
	private function encrypt(string $input): string
	{
		return Cryptography::Encrypt($input, $this->hash);
	}

	/**
	 * @param string $input
	 * @return string
	 * @throws \SodiumException
	 */
	private function decrypt(string $input): string
	{
		return Cryptography::Decrypt($input, $this->hash);
	}
}
