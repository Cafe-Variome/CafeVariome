<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Net\Service\RegisterTaskMessage;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\RegisterTaskMessageFactory
 */
class RegisterTaskMessageFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$registerMessageTask = (new RegisterTaskMessageFactory())->GetInstance(
			rand(1, PHP_INT_MAX), uniqid(), rand(0, 1)
		);
		$this->assertIsObject($registerMessageTask);
		$this->assertInstanceOf(RegisterTaskMessage::class, $registerMessageTask);
    }
}
