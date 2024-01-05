<?php namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
/**
 * @covers \App\Controllers\SingleSignOnProvider
 */
class SingleSignOnProviderTest extends CIUnitTestCase
{
	use ControllerTestTrait;
	use DatabaseTestTrait;
	private $controllerName = 'SingleSignOnProvider';
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
		$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

		curl_close($ch);

		$this->assertEquals(302, $httpCode);

		$expectedRedirectUrl = base_url() . 'auth' . '/login';
		$this->assertEquals($expectedRedirectUrl, $redirectUrl);
    }

    public function testDelete()
	{
		$userID = 10000;
		$url = base_url() . $this->controllerName . '/Delete/'. $userID;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);

		$response = curl_exec($ch);
		curl_close($ch);
		$this->assertIsString($response);
	}
    public function testCreate()
	{
		$url = base_url() . $this->controllerName . '/Create';
		$postData = [
			'name' => 'tester',
			'display_name' => 'test@example.com',
			'server_id' => 'example.com',
			'port' => 'Just Test',
			'credential_id' => 'Just Test',
			'proxy_server_id' => 'Just Test',
			'type' => 'Just Test',
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		curl_exec($ch);
		$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

		curl_close($ch);

		$expectedRedirectUrl = base_url() . 'index.php/';
		$this->assertEquals($expectedRedirectUrl, $redirectUrl);
	}

    public function testDetails()
    {
		$url = base_url() . $this->controllerName . '/Details';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);

		$response = curl_exec($ch);
		curl_close($ch);
		$this->assertIsString($response);
    }

    public function testUpdate()
    {
		$url = base_url() . $this->controllerName . '/Update';
		$postData = [
			'name' => 'tester',
			'display_name' => 'test@example.com',
			'server_id' => 'example.com',
			'port' => 'Just Test',
			'credential_id' => 'Just Test',
			'proxy_server_id' => 'Just Test',
			'type' => 'Just Test',
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		curl_exec($ch);
		$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

		curl_close($ch);

		$expectedRedirectUrl = base_url() . 'index.php/';
		$this->assertEquals($expectedRedirectUrl, $redirectUrl);
    }

    public function testList()
    {
		$url = base_url() . $this->controllerName . '/List';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);

		$response = curl_exec($ch);
		curl_close($ch);
		$this->assertIsString($response);
    }
}
