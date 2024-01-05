<?php namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\ControllerTestTrait;
use CodeIgniter\Test\DatabaseTestTrait;
/**
 * @covers \App\Controllers\Source
 */

class SourceTest extends CIUnitTestCase
{

	use ControllerTestTrait;
	use DatabaseTestTrait;
	private $controllerName = 'Source';

    public function testCreate()
    {
		$url = base_url() . $this->controllerName . '/Create';

		$postData = [
			'owner_name' => 'tester',
			'owner_email' => 'test@example.com',
			'uri' => 'example.com',
			'status' => 'Just Test'
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
	public function testNeo4J()
	{
		$sourceID = 10000;

		$url = base_url() . $this->controllerName . '/Neo4J/' . $sourceID;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPGET, true);

		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			$this->fail("Curl Error: $error_msg");
		}

		curl_close($ch);

		$this->assertIsString($response);
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
    public function testUpdate()
    {
		$userID = 1;
		$url = base_url() . $this->controllerName . '/Update/'. $userID;

		$postData = [
			'owner_name' => 'tester',
			'owner_email' => 'test@example.com',
			'uri' => 'example.com',
			'status' => 'Update'
		];
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);

		$response = curl_exec($ch);
		$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

		curl_close($ch);

		$expectedRedirectUrl = base_url() . 'index.php/';
		$this->assertEquals($expectedRedirectUrl, $redirectUrl);
		$this->assertIsString($response);
    }
    public function testElasticsearch()
    {
		$sourceID = 10000;

		$url = base_url() . $this->controllerName . '/Elasticsearch/' . $sourceID;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPGET, true);

		$response = curl_exec($ch);
		if (curl_errno($ch)) {
			$error_msg = curl_error($ch);
			$this->fail("Curl Error: $error_msg");
		}

		curl_close($ch);

		$this->assertIsString($response);
    }
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
	public function testUserInterface_ValidID_FileExists()
	{
		$sourceId = 10000;
		$url = base_url() . $this->controllerName . '/UserInterface/' . $sourceId;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);

		curl_close($ch);

		$this->assertIsString($response);
	}

	public function testUserInterface_ValidID_FileNotExists()
	{
		$sourceId = 10000;
		$url = base_url() . $this->controllerName . '/UserInterface/' . $sourceId;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		curl_close($ch);
		$this->assertIsString($response);
	}

	public function testUserInterface_InvalidID()
	{
		$sourceId = 10000;
		$url = base_url() . $this->controllerName . '/UserInterface/' . $sourceId;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($ch);
		$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);

		curl_close($ch);

		$expectedRedirectUrl = base_url() . 'auth' . '/login';
		$this->assertEquals($expectedRedirectUrl, $redirectUrl);
		$this->assertIsString($response);
	}
}
