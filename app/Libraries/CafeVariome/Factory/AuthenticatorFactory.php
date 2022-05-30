<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * AuthenticatorFactory.php
 * Created 26/05/2022
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Auth\OpenIDAuthenticator;
use App\Libraries\CafeVariome\Entities\SingleSignOnProvider;

class AuthenticatorFactory
{
	public function GetInstance(SingleSignOnProvider $provider)
	{
		switch ($provider->type)
		{
			case SINGLE_SIGNON_OIDC2:
				return new OpenIDAuthenticator($provider);
		}
	}

}
