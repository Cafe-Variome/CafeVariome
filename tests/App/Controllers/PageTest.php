<?php namespace App\Controllers;

/**
 * @author Sadegh Abadijou
 */

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @covers \App\Controllers\Page
 */

class PageTest extends CIUnitTestCase
{
	use ControllerTestTrait;
	use DatabaseTestTrait;

	public function testIndex()
	{
		$url = base_url() . 'Page/Index';

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
	public function testList()
	{
		$url = base_url() . 'Page/List';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$headers = [
			'Accept: application/json',
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$this->assertIsString($response);
		$this->assertEquals(200, $httpCode);
	}
	public function testCreate()
	{
		$body = ['csrf_test_name'=> '64e342e8f1176e88afec55e5d5383db1',
				'ptitle'=>'Test',
				'pcontent'=>'<p>Test Content</p>',
				'submit'=>""];

		$headers = [
			'Accept: application/json',
		];

		$url = base_url() . 'Page/Create';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, true);

		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$this->assertIsString($response);
		$this->assertEquals(200, $httpCode);
	}
	public function testUpdate()
	{
		$body = ['csrf_test_name'=> '64e342e8f1176e88afec55e5d5383db1',
			'ptitle'=>'Test',
			'pcontent'=>'<p>Test Content</p>',
			'submit'=>""];

		$headers = [
			'Accept: application/json',
		];

		$url = base_url() . 'Page/Update/1';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_POST, true);

		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body));

		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$this->assertIsString($response);
		$this->assertEquals(200, $httpCode);
	}
	public function testDeactivate()
	{
		$url = base_url() . 'Page/Deactivate/1';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$headers = [
			'Accept: application/json',
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$this->assertIsString($response);
		$this->assertEquals(200, $httpCode);
	}

	public function testActivate()
	{
		$url = base_url() . 'Page/Activate/1';

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

		$headers = [
			'Accept: application/json',
		];
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		$this->assertIsString($response);
		$this->assertEquals(200, $httpCode);
	}
}
