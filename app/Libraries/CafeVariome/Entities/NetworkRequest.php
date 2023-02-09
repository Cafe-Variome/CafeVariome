<?php namespace APP\Libraries\CafeVariome\Entities;

/**
 * NetworkRequest.php
 * Created 27/01/2023
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class NetworkRequest extends Entity
{
	/**
	 * @var int id of network
	 */
	public int $network_key;

	/**
	 * @var string installation key of requesting site
	 */
	public string $installation_key;

	/**
	 * @var string url of requesting site
	 */
	public string $url;

	/**
	 * @var string reason to join
	 */
	public string $justification;

	/**
	 * @var string email of the requesting user
	 */
	public string $email;

	/**
	 * @var string ip of the requesting server
	 */
	public string $ip;

	/**
	 * @var string token to recognise the request on the network registry
	 */
	public string $token;

	/**
	 * @var int status of request
	 * @see app/Config/Constants.php
	 */
	public int $status;
}
