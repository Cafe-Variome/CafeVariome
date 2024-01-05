<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\OntologyRelationshipSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Entities\OntologyRelationship;
use App\Libraries\CafeVariome\Factory\OntologyRelationshipAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\OntologyRelationshipAdapter
 * @covers \App\Libraries\CafeVariome\Factory\OntologyRelationshipAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\OntologyRelationship
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\OntologyRelationshipsFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class OntologyRelationshipAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'OntologyRelationshipSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new OntologyRelationshipSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testReadByNameAndOntologyId()
    {
		$ontologyRelationshipAdapter = new OntologyRelationshipAdapter();
		$name = $this->insertedData['insertedData']['name'];
		$ontologyId = $this->insertedData['id'];

		$result = $ontologyRelationshipAdapter->ReadByNameAndOntologyId($name, $ontologyId);

		$this->assertInstanceOf(IEntity::class, $result);

	}

    public function testToEntity()
    {
		$object = (object) $this->insertedData['insertedData'];

		$dbAdapter = (new OntologyRelationshipAdapterFactory())->GetInstance();
		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
    }

    public function testReadByOntologyId()
	{
		$ontologyRelationshipAdapter = new OntologyRelationshipAdapter();
		$ontologyId = $this->insertedData['insertedData']['ontology_id'];

		$result = $ontologyRelationshipAdapter->ReadByOntologyId($ontologyId);
		$this->assertIsArray($result);
		foreach ($result as $item) {
			$this->assertInstanceOf(OntologyRelationship::class, $item);
		}
	}
}
