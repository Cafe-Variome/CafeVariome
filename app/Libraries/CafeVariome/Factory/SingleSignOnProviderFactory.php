<?php namespace App\Libraries\CafeVariome\Factory;

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\SingleSignOnProvider;

/**
 * SingleSignOnProviderFactory.php
 * Created 25/05/2022
 *
 * This class handles object creation of the SingleSignOnProvider class.
 * @author Mehdi Mehtarizadeh
 */

class SingleSignOnProviderFactory extends EntityFactory
{
	/**
	 * @param object|null $input
	 * @return IEntity
	 * @throws \Exception
	 */
	public function getInstance(?object $input): IEntity
	{
		if (is_null($input) || count($objectVars = get_object_vars($input)) == 0 )
		{
			return new NullEntity();
		}

		$properties = [];
		foreach ($objectVars as $var => $value)
		{
			$properties[$var] = $value;
		}

		return new SingleSignOnProvider($properties);
	}

	/**
	 * @param string $name name of the provider as referred to in the admin panel
	 * @param string $display_name name of the provider as referred to in the user interface login page
	 * @param int $type type of provider OIDC2.0|SAML2.0, at the moment only OIDC2 is supported
	 * @param int $port network port of the provider is reachable on
	 * @param int $authentication_policy post authentication policy, whether to create a local account or try to link the user to an existing local account
	 * @param bool $query whether this provider is available for authenticating incoming queries
	 * @param bool $user_login whether this provider is available for authenticating user login through the UI
	 * @param int $server_id server to be used for the provider
	 * @param int|null $credential_id credential to be used for the provider if the client is confidential
	 * @param int|null $proxy_server_id proxy server to be used to access the provider server
	 * @param string|null $icon icon file to be shown in the login page
	 * @param string|null $logout_url logout URL if it cannot be retrieved automatically
	 * @param string|null $realm realm of the client, if provided
	 * @param bool $removable if the provider is removable by the admin
	 * @return SingleSignOnProvider
	 * @throws \Exception
	 */
	public function getInstanceFromParameters(
		string $name,
		string $display_name,
		int $type,
		int $port,
		int $authentication_policy,
		bool $query,
		bool $user_login,
		int $server_id,
		?int $credential_id,
		?int $proxy_server_id,
		?string $icon,
		?string $logout_url,
		?string $realm,
		bool $removable = true
	): SingleSignOnProvider
	{
		return new SingleSignOnProvider([
			'name' => $name,
			'display_name' => $display_name,
			'type' => $type,
			'port' => $port,
			'authentication_policy' => $authentication_policy,
			'query' => $query,
			'user_login' => $user_login,
			'server_id' => $server_id,
			'credential_id' => $credential_id,
			'proxy_server_id' => $proxy_server_id,
			'icon' => $icon,
			'logout_url' => $logout_url,
			'realm' => $realm,
			'removable' => $removable
		]);
	}
}
