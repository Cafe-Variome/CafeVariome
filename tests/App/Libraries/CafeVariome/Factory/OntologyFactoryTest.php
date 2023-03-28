<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Ontology;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 * @covers \App\Libraries\CafeVariome\Entities\Ontology
 * @covers \App\Libraries\CafeVariome\Factory\OntologyFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class OntologyFactoryTest extends TestCase
{

    public function testGetInstanceFromParameters()
    {
		$ontology = (new OntologyFactory())->GetInstanceFromParameters(
			uniqid(), uniqid(), uniqid(), uniqid(), uniqid(), uniqid()
		);
		$this->assertIsObject($ontology);
		$this->assertInstanceOf(Ontology::class, $ontology);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->description = uniqid();
		$object->node_key = uniqid();
		$object->node_type = uniqid();
		$object->key_prefix = uniqid();
		$object->term_name = uniqid();
		$ontology = (new OntologyFactory())->GetInstance($object);

		$this->assertIsObject($ontology);
		$this->assertInstanceOf(Ontology::class, $ontology);

		$emptyObject = new \stdClass();
		$nullEntity = (new OntologyFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
	}
}
