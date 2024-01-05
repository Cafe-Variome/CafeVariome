<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\ValuesSeeder;
use App\Libraries\CafeVariome\Entities\Value;
use App\Libraries\CafeVariome\Entities\IEntity;
use CodeIgniter\Test\CIUnitTestCase;
use App\Libraries\CafeVariome\Database\ValueAdapter;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\ValueAdapter
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\ValueFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\Value
 * @covers \App\Libraries\CafeVariome\Factory\ValueFactory
 */

class ValueAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = 'App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'ValuesSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new ValuesSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}
	public function testCountByAttributeId()
	{
		$valueAdapter = new ValueAdapter();

		$attributeId = $this->insertedData['insertedData']['attribute_id'];
		$result = $valueAdapter->CountByAttributeId($attributeId);

		$this->assertIsInt($result);
	}

	public function testReadIdByNameAndAttributeId()
	{
		$valueAdapter = new ValueAdapter();

		$name = $this->insertedData['insertedData']['name'];
		$attributeId = $this->insertedData['insertedData']['attribute_id'];

		$result = $valueAdapter->ReadIdByNameAndAttributeId($name, $attributeId);

		$this->assertIsInt($result);
	}
	public function testReadByMappingNameAndAttributeId()
	{
		$valueAdapter = new ValueAdapter();
		$mappingName = "Null Sample";
		$attributeId = $this->insertedData['insertedData']['attribute_id'];

		$result = $valueAdapter->ReadByMappingNameAndAttributeId($mappingName, $attributeId);

		$this->assertInstanceOf(IEntity::class, $result);
	}
	public function testReadByAttributeId()
	{
		$valueAdapter = new ValueAdapter();

		$attributeId = $this->insertedData['insertedData']['attribute_id'];

		$includeInInterfaceIndex = true;

		$result = $valueAdapter->ReadByAttributeId($attributeId, $includeInInterfaceIndex);

		if (!empty($result)) {
			foreach ($result as $entity) {
				$this->assertInstanceOf(IEntity::class, $entity);
			}
		} else {
			$this->assertIsArray($result);
		}
	}
	public function testReadByNameAndAttributeId()
	{
		$valueAdapter = new ValueAdapter();

		$name = $this->insertedData['insertedData']['name'];
		$attributeId = $this->insertedData['insertedData']['attribute_id'];
		$result = $valueAdapter->ReadByNameAndAttributeId($name, $attributeId);
		$this->assertInstanceOf(IEntity::class, $result);
	}
	public function testReadFrequency()
	{
		$valueAdapter = new ValueAdapter();

		$valueId = $this->insertedData['id'];
		$result = $valueAdapter->ReadFrequency($valueId);

		$this->assertIsFloat($result);
	}
	public function testUpdateFrequency()
	{
		$valueAdapter = new ValueAdapter();

		$valueId = $this->insertedData['id'];
		$newFrequency = 25.0;
		$add = false;
		$result = $valueAdapter->UpdateFrequency($valueId, $newFrequency, $add);

		$this->assertTrue($result);

		$updatedFrequency = $valueAdapter->ReadFrequency($valueId);

		if ($add) {
			$expectedFrequency = $newFrequency;
			$this->assertEquals($expectedFrequency, $updatedFrequency);
		} else {
			$this->assertEquals($newFrequency, $updatedFrequency);
		}
	}

	public function testDeleteIfAbsent()
	{
		$valueAdapter = new ValueAdapter();
		$valueId = $this->insertedData['id'];

		$result = $valueAdapter->DeleteIfAbsent($valueId);

		$this->assertTrue($result);

		$deletedValue = $valueAdapter->ReadFrequency($valueId);
		$this->assertIsFloat($deletedValue);
	}
}
