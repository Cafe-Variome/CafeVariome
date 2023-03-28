<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\OntologyPrefix;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 * @covers \App\Libraries\CafeVariome\Entities\OntologyPrefix
 * @covers \App\Libraries\CafeVariome\Factory\OntologyPrefixFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class OntologyPrefixFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$ontologyPrefix = (new OntologyPrefixFactory())->GetInstanceFromParameters(uniqid(), rand(1, PHP_INT_MAX ));
		$this->assertIsObject($ontologyPrefix);
		$this->assertInstanceOf(OntologyPrefix::class, $ontologyPrefix);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->name = uniqid();
		$object->ontology_id = rand(1, PHP_INT_MAX );
		$ontologyPrefix = (new OntologyPrefixFactory())->GetInstance($object);
		$this->assertIsObject($ontologyPrefix);
		$this->assertInstanceOf(OntologyPrefix::class, $ontologyPrefix);

		$emptyObject = new \stdClass();
		$nullEntity = (new OntologyPrefixFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
