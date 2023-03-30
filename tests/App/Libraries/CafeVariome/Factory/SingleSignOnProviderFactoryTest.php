<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\SingleSignOnProvider;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\SingleSignOnProviderFactory
 * @covers \App\Libraries\CafeVariome\Entities\SingleSignOnProvider
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class SingleSignOnProviderFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$singleSignOnProvider = (new SingleSignOnProviderFactory())->GetInstanceFromParameters(
			uniqid(), uniqid(), rand(1, PHP_INT_MAX), rand(1, 65535), rand(1, PHP_INT_MAX), rand(0, 1), rand(0, 1), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), rand(1, PHP_INT_MAX), uniqid(), rand(0, 1)
		);
		$this->assertIsObject($singleSignOnProvider);
		$this->assertInstanceOf(SingleSignOnProvider::class, $singleSignOnProvider);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->display_name = uniqid();
		$object->type = rand(1, PHP_INT_MAX);
		$object->port = rand(1, 65535);
		$object->authentication_policy = rand(1, PHP_INT_MAX);
		$object->query = rand(0, 1);
		$object->user_login = rand(0, 1);
		$object->server_id = rand(1, PHP_INT_MAX);
		$object->credential_id = rand(1, PHP_INT_MAX);
		$object->proxy_server_id = rand(1, PHP_INT_MAX);
		$object->icon = uniqid();
		$object->removable = rand(0, 1);

		$singleSignOnProvider = (new SingleSignOnProviderFactory())->GetInstance($object);
		$this->assertIsObject($singleSignOnProvider);
		$this->assertInstanceOf(SingleSignOnProvider::class, $singleSignOnProvider);

		$emptyObject = new \stdClass();
		$nullEntity = (new SingleSignOnProviderFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
