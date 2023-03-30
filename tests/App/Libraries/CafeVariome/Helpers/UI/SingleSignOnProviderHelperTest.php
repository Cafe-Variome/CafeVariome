<?php namespace Libraries\CafeVariome\Helpers\UI;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\SingleSignOnProviderHelper;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Helpers\UI\SingleSignOnProviderHelper
 */
class SingleSignOnProviderHelperTest extends TestCase
{

    public function testGetType()
    {
		$this->assertEquals('Undefined', SingleSignOnProviderHelper::getType(-1));
		$this->assertEquals('SAML 2.0', SingleSignOnProviderHelper::getType(SINGLE_SIGNON_SAML2));
		$this->assertEquals('OIDC 2.0', SingleSignOnProviderHelper::getType(SINGLE_SIGNON_OIDC2));
    }

    public function testGetPostAuthenticanPolicy()
    {
		$this->assertEquals('Undefined', SingleSignOnProviderHelper::getPostAuthenticanPolicy(-1));
		$this->assertEquals('Create a new user account or link to an existing user account', SingleSignOnProviderHelper::getPostAuthenticanPolicy(SINGLE_SIGNON_POST_AUTH_CREATE_ACCOUNT));
		$this->assertEquals('Link account to an existing user account', SingleSignOnProviderHelper::getPostAuthenticanPolicy(SINGLE_SIGNON_POST_AUTH_LINK_ACCOUNT));

    }
}
