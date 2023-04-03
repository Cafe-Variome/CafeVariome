<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\User;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\User
 * @covers \App\Libraries\CafeVariome\Factory\UserFactory
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class UserFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$user = (new UserFactory())->GetInstanceFromParameters(
			uniqid(), uniqid(), uniqid(), uniqid(), uniqid(), rand(1, PHP_INT_MAX), uniqid(), uniqid(), rand(0, 1), rand(0, 1), rand(0, 1)
		);
		$this->assertIsObject($user);
		$this->assertInstanceOf(User::class, $user);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->email = uniqid();
		$object->username = uniqid();
		$object->first_name = uniqid();
		$object->last_name = uniqid();
		$object->ip_address = uniqid();
		$object->created_on = rand(1, PHP_INT_MAX);
		$object->phone = uniqid();
		$object->company = uniqid();
		$object->is_admin = rand(0, 1);
		$object->remote = rand(0, 1);
		$object->active = rand(0, 1);

		$user = (new UserFactory())->GetInstance($object);
		$this->assertIsObject($user);
		$this->assertInstanceOf(User::class, $user);

		$emptyObject = new \stdClass();
		$nullEntity = (new UserFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
