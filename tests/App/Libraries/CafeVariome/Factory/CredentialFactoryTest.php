<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\Credential;
use App\Libraries\CafeVariome\Entities\NullEntity;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\CredentialFactory
 * @covers \App\Libraries\CafeVariome\Entities\Credential
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Security\Cryptography
 */
class CredentialFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$credential = (new CredentialFactory())->getInstanceFromParameters('test cred', null, null, false);
		$this->assertIsObject($credential);
		$this->assertInstanceOf(Credential::class, $credential);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = 'test cred';
		$object->username = null;
		$object->password = null;
		$object->hide_username = false;
		$credential = (new CredentialFactory())->GetInstance($object);

		$this->assertIsObject($credential);
		$this->assertInstanceOf(Credential::class, $credential);

		$emptyObject = new \stdClass();
		$nullEntity = (new CredentialFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
