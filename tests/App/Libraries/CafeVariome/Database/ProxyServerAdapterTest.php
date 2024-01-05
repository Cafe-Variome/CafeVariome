<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\ProxyServerSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\ProxyServerAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\ProxyServerAdapter
 * @covers \App\Libraries\CafeVariome\Factory\ProxyServerFactory
 * @covers \App\Libraries\CafeVariome\Factory\ProxyServerAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\ProxyServer
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\ProxyServersFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */


class ProxyServerAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'ProxyServerSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new ProxyServerSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}
	public function testToEntity()
	{
		$object = (object) $this->insertedData['insertedData'];

		$dbAdapter = (new ProxyServerAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
	}
}
