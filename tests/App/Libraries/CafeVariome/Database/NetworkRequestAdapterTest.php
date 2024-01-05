<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\NetworkRequestsSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\NetworkRequestAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\NetworkRequestAdapter
 * @covers \App\Libraries\CafeVariome\Factory\NetworkRequestFactory
 * @covers \App\Libraries\CafeVariome\Factory\NetworkRequestAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\NetworkRequest
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\NetworkRequestsFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */


class NetworkRequestAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate = true;
	protected $migrateOnce = false;
	protected $refresh = true;

	protected $namespace = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce = false;
	protected $seed = 'NetworkRequestsSeeder';

	protected $basePath = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new NetworkRequestsSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}
	public function testCountAllPending()
	{
		$db        = db_connect($this->config->tests);
		$dbAdapter =  (new NetworkRequestAdapterFactory())->GetInstance();

		$numOfPending = $dbAdapter->CountAllPending();
		$query = $db->table('network_requests')->where('status', 0)->get();
		$results = count($query->getResult());

		$this->assertEquals($numOfPending, $results);
	}
	public function testToEntity()
	{
		$object = (object) $this->insertedData;

		$dbAdapter = (new NetworkRequestAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
	}
}
