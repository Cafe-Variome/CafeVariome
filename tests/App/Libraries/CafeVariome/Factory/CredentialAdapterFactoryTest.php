<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\CredentialAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\CredentialAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class CredentialAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$credentialAdapter = (new CredentialAdapterFactory())->GetInstance();
		$this->assertIsObject($credentialAdapter);
		$this->assertInstanceOf(CredentialAdapter::class, $credentialAdapter);
    }
}
