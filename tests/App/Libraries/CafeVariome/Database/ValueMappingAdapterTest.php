<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\ValueMappingSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\ValueMappingAdapterFactory;
use App\Libraries\CafeVariome\Factory\ValueMappingFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\ValueMappingAdapter
 * @covers \App\Libraries\CafeVariome\Factory\ValueMappingFactory
 * @covers \App\Libraries\CafeVariome\Factory\ValueMappingAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\ValueMapping
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\ValueMappingsFabricator
 */

class ValueMappingAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'ValueMappingSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new ValueMappingSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testReadByValueId()
    {
		$dummyValueID = $this->insertedData['value_id'];

		$dbAdapter = (new ValueMappingAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$dbRecords = $dbAdapter->ReadByValueId($dummyValueID);
		$query = $db->table('value_mappings')->where('value_id', $dummyValueID)->get();
		$records = $query->getResult();

		$counter = 0;
		foreach ($dbRecords as $dbRecord)
		{
			$this->assertEquals($dbRecord->name, $records[$counter]->name);
			$counter = $counter + 1;
		}
    }

	public function testToEntity()
	{
		$object = (object) $this->insertedData;

		$dbAdapter = (new ValueMappingAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
	}
}
