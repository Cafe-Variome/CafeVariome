<?php namespace App\Controllers;

/**
 * @author Sadegh Abadijou
 */

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @covers \App\Controllers\Admin
 */

class AdminTest extends CIUnitTestCase
{
	use ControllerTestTrait;
	use DatabaseTestTrait;

	public function testIndex()
	{
		$url = base_url() . 'Admin/Index';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$headers = [
			'Accept: application/json', // Example header
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$this->assertIsString($response);
		$this->assertEquals(200, $httpCode);
	}
}
