<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\OntologyPrefixAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\OntologyPrefixAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class OntologyPrefixAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$ontologyPrefixAdapter = (new OntologyPrefixAdapterFactory())->GetInstance();
		$this->assertIsObject($ontologyPrefixAdapter);
		$this->assertInstanceOf(OntologyPrefixAdapter::class, $ontologyPrefixAdapter);
    }
}
