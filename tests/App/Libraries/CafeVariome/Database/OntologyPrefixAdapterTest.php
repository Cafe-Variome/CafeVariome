<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\OntologyPrefixSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\OntologyPrefix;
use App\Libraries\CafeVariome\Factory\OntologyPrefixAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\OntologyPrefixAdapter
 * @covers \App\Libraries\CafeVariome\Factory\OntologyPrefixAdapterFactory
 * @covers \App\Libraries\CafeVariome\Factory\OntologyPrefixFactory
 * @covers \App\Libraries\CafeVariome\Entities\OntologyPrefix
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\OntologyPrefixesFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class OntologyPrefixAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'OntologyPrefixSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new OntologyPrefixSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testToEntity()
    {
		$object = (object) $this->insertedData['insertedData'];

		$dbAdapter = (new OntologyPrefixAdapterFactory())->GetInstance();
		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
    }

    public function testReadAllDistinct()
    {
		$ontologyPrefixAdapter = new OntologyPrefixAdapter();
		$result = $ontologyPrefixAdapter->ReadAllDistinct();

		$this->assertIsArray($result);
		foreach ($result as $item) {
			$this->assertInstanceOf(OntologyPrefix::class, $item);
		}
    }

    public function testReadByOntologyId()
    {
		$ontologyPrefixAdapter = new OntologyPrefixAdapter();
		$ontologyId = 1;
		$result = $ontologyPrefixAdapter->ReadByOntologyId($ontologyId);

		$this->assertIsArray($result);

		foreach ($result as $item) {
			$this->assertInstanceOf(OntologyPrefix::class, $item);
		}
    }

    public function testReadByNameAndOntologyId()
	{
		$ontologyPrefixAdapter = new OntologyPrefixAdapter();

		$name = $this->insertedData['insertedData']['name'];
		$ontologyId = $this->insertedData['id'];

		$result = $ontologyPrefixAdapter->ReadByNameAndOntologyId($name, $ontologyId);

		$this->assertInstanceOf(IEntity::class, $result);
	}
}
