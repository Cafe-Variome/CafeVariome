<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\OntologyRelationshipAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\OntologyRelationshipAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class OntologyRelationshipAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$ontologyRelationshipAdapter = (new OntologyRelationshipAdapterFactory())->GetInstance();
		$this->assertIsObject($ontologyRelationshipAdapter);
		$this->assertInstanceOf(OntologyRelationshipAdapter::class, $ontologyRelationshipAdapter);
    }
}
