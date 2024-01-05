<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\NetworkSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\NetworkAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\NetworkAdapter
 * @covers \App\Libraries\CafeVariome\Factory\NetworkAdapterFactory
 * @covers \App\Libraries\CafeVariome\Factory\NetworkFactory
 * @covers \App\Libraries\CafeVariome\Entities\Network
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\NetworkFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */


class NetworkAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'NetworkSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new NetworkSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

	public function testToEntity()
	{
		$object = (object) $this->insertedData;

		$dbAdapter = (new NetworkAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
	}
}
