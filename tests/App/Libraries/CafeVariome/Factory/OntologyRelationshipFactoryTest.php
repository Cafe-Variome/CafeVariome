<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\OntologyRelationship;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\OntologyRelationshipFactory
 * @covers \App\Libraries\CafeVariome\Entities\OntologyRelationship
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class OntologyRelationshipFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$ontologyRelationship = (new OntologyRelationshipFactory())->GetInstanceFromParameters(uniqid(), rand(1, PHP_INT_MAX));
		$this->assertIsObject($ontologyRelationship);
		$this->assertInstanceOf(OntologyRelationship::class, $ontologyRelationship);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->ontology_id = rand(1, PHP_INT_MAX);
		$ontologyRelationship = (new OntologyRelationshipFactory())->GetInstance($object);
		$this->assertIsObject($ontologyRelationship);
		$this->assertInstanceOf(OntologyRelationship::class, $ontologyRelationship);

		$emptyObject = new \stdClass();
		$nullEntity = (new OntologyRelationshipFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
