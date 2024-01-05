<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\OntologySeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use App\Libraries\CafeVariome\Factory\OntologyAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\OntologyAdapter
 * @covers \App\Libraries\CafeVariome\Factory\OntologyFactory
 * @covers \App\Libraries\CafeVariome\Factory\OntologyAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Ontology
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\OntologiesFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class OntologyAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'OntologySeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new OntologySeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testCreateOntologyAttributeAssociation()
    {
		$dbAdapter = new OntologyAdapter();

		$attributeId = 1;
		$prefixId = 1;
		$relationshipId = 1;
		$ontologyId = 1;

		$insertedId = $dbAdapter->CreateOntologyAttributeAssociation($attributeId, $prefixId, $relationshipId, $ontologyId);

		$this->assertGreaterThan(0, $insertedId);
    }

    public function testToEntity()
    {
		$object = (object) $this->insertedData['insertedData'];

		$dbAdapter = (new OntologyAdapterFactory())->GetInstance();
		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
    }

    public function testReadOntologyPrefixesAndRelationshipsByAttributeId()
    {
		$ontologyAdapter = new OntologyAdapter();

		$attributeId = 1;
		$result = $ontologyAdapter->ReadOntologyPrefixesAndRelationshipsByAttributeId($attributeId);
		$this->assertIsArray($result);

		foreach ($result as $item) {
			$this->assertObjectHasProperty('id', $item);
			$this->assertObjectHasProperty('ontology_name', $item);
			$this->assertObjectHasProperty('prefix_name', $item);
			$this->assertObjectHasProperty('relationship_name', $item);
		}
	}

    public function testReadOntologyPrefixIdsAndRelationshipIdsByAttributeId()
	{
		$ontologyAdapter = new OntologyAdapter();

		$attributeId = 1;
		$result = $ontologyAdapter->ReadOntologyPrefixIdsAndRelationshipIdsByAttributeId($attributeId);

		$this->assertIsArray($result);

		foreach ($result as $item) {
			$this->assertObjectHasProperty('prefix_id', $item);
			$this->assertObjectHasProperty('relationship_id', $item);
		}

		$expectedPrefixId = 1;
		$expectedRelationshipId = 2;

		$foundMatchingItems = array_filter($result, function ($item) use ($expectedPrefixId, $expectedRelationshipId) {
			return $item->prefix_id == $expectedPrefixId && $item->relationship_id == $expectedRelationshipId;
		});

		$this->assertEmpty($foundMatchingItems);

	}

	public function testDeleteAttributeOntologyAssociation()
	{
		$ontologyAdapter = new OntologyAdapter();

		$associationId = 1;
		$ontologyAdapter->DeleteAttributeOntologyAssociation($associationId);

		$result=$this->db->table('attributes_ontology_prefixes_relationships')
						->where('id', $associationId)
						->get()
						->getRow();
		$this->assertNull($result);
	}

	public function testReadOntologyAssociationsByAttributeId()
	{
		$ontologyAdapter = new OntologyAdapter();

		$attributeId = 1;

		$result = $ontologyAdapter->ReadOntologyAssociationsByAttributeId($attributeId);
		$this->assertIsArray($result);

		foreach ($result as $item) {
			$this->assertObjectHasProperty('attribute_id', $item);
			$this->assertObjectHasProperty('ontology_id', $item);
			$this->assertObjectHasProperty('prefix_id', $item);
			$this->assertObjectHasProperty('relationship_id', $item);
		}
	}
}
