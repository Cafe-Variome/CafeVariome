<?php namespace App\Libraries\CafeVariome\Helpers\UI;

/**
 * SingleSignOnProviderHelper.php
 * Created 18/05/2022
 *
 * @author Mehdi Mehtarizadeh
 * This class offers helper functions for single sign-on providers in the user interface.
 */

class SingleSignOnProviderHelper
{
	/**
	 * @param int $type
	 * @return string user-friendly type of sign-on provider
	 */
	public static function getType(int $type): string
	{
		switch ($type)
		{
			case SINGLE_SIGNON_SAML2:
				return 'SAML 2.0';
			case SINGLE_SIGNON_OIDC2:
				return 'OIDC 2.0';
		}
		return 'Undefined';
	}

	public static function getPostAuthenticanPolicy(int $policy): string
	{
		switch ($policy)
		{
			case SINGLE_SIGNON_POST_AUTH_CREATE_ACCOUNT:
				return 'Create a new user account or link to an existing user account';
			case SINGLE_SIGNON_POST_AUTH_LINK_ACCOUNT:
				return 'Link account to an existing user account';
		}
		return 'undefined';
	}

}
