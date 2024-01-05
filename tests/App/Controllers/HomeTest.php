<?php namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @covers \App\Controllers\Home
 */
class HomeTest extends CIUnitTestCase
{
	use ControllerTestTrait;
	use DatabaseTestTrait;
	private $controllerName = 'Home';
    public function testIndex()
    {
		$url = base_url() . $this->controllerName . '/Index';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

		$headers = ['Accept: application/json'];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		$this->assertEquals(200, $httpCode);
    }
}
