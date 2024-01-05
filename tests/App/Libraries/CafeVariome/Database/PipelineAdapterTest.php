<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Libraries\CafeVariome\Factory\PipelineAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Database\Seeds\PipelinesSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use Config\Database;
/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\PipelineAdapter
 * @covers \App\Libraries\CafeVariome\Factory\PipelineFactory
 * @covers \App\Libraries\CafeVariome\Factory\PipelineAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Pipeline
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\PipelinesFabricator
 */


class PipelineAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'PipelinesSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new PipelinesSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testToEntity()
    {
		$object = (object) $this->insertedData['insertedData'];

		$dbAdapter = (new PipelineAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
	}
}
