<?php namespace  App\Controllers;

/**
 * @author Sadegh Abadijou
 */

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @covers \App\Controllers\UserInterfaceAPI
 */

class UserInterfaceAPITest extends CIUnitTestCase
{
	use ControllerTestTrait;
	use DatabaseTestTrait;

    public function testGetUIConstants()
    {
		$body = json_encode("");

		$results = $this->withBody($body)->controller(\App\Controllers\UserInterfaceAPI::class)
			->execute('GetUIConstants');

		$this->assertTrue($results->isOK());
    }
}
