<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\CredentialAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Database\Seeds\CredentialsSeeder;
use Config\Database;
/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\CredentialAdapter
 * @covers \App\Libraries\CafeVariome\Factory\CredentialFactory
 * @covers \App\Libraries\CafeVariome\Factory\CredentialAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Credential
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\CredentialsFabricator
 */



class CredentialAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'CredentialsSeeder';

	protected $basePath    = 'app/Database';
	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new CredentialsSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

	public function testCreate()
	{
		$db		 		 = db_connect($this->config->tests);
		$query	 		 = $db->table('credentials')->get();
		$results 		 = $query->getResult();
		$numberOfResults = count($results);
		$this->assertNotEmpty($results);
		$this->assertEquals($this->insertedData["name"], $results[$numberOfResults - 1]->name);
		$this->assertEquals($this->insertedData["username"], $results[$numberOfResults - 1]->username);
		$this->assertEquals($this->insertedData["password"], $results[$numberOfResults - 1]->password);
		$this->assertEquals($this->insertedData["hide_username"], $results[$numberOfResults - 1]->hide_username);
		$this->assertEquals($this->insertedData["removable"], $results[$numberOfResults - 1]->removable);
	}
	public function testRead()
	{
		$db        = db_connect($this->config->tests);
		$dbAdapter = new CredentialAdapterFactory();
		$dbRecords = $dbAdapter->GetInstance()->ReadAll();
		$query     = $db->table('credentials')->get();

		$this->assertNotEmpty($dbRecords);

		$this->assertEquals($dbRecords["1"]->name, 			$query->getResult()[0]->name);
		$this->assertEquals($dbRecords["1"]->username, 		$query->getResult()[0]->username);
		$this->assertEquals($dbRecords["1"]->password, 		$query->getResult()[0]->password);
		$this->assertEquals($dbRecords["1"]->hide_username, $query->getResult()[0]->hide_username);
		$this->assertEquals($dbRecords["1"]->removable, 	$query->getResult()[0]->removable);
	}
	public function testToEntity()
	{
		$object = (object) $this->insertedData;

		$dbAdapter = (new CredentialAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
	}
}
