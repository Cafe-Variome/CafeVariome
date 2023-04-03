<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\UserAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\UserAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class UserAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$userAdapter = (new UserAdapterFactory())->GetInstance();
		$this->assertIsObject($userAdapter);
		$this->assertInstanceOf(UserAdapter::class, $userAdapter);
    }
}
