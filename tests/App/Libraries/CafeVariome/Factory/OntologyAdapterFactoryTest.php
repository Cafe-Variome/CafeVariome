<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\OntologyAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\OntologyAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class OntologyAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$ontologyAdapter = (new OntologyAdapterFactory())->GetInstance();
		$this->assertIsObject($ontologyAdapter);
		$this->assertInstanceOf(OntologyAdapter::class, $ontologyAdapter);
    }
}
