<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\SingleSignOnProviderAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class SingleSignOnProviderAdapterFactoryTest extends TestCase
{

    public function testGetInstance()
    {
		$singleSignOnProviderAdapter = (new SingleSignOnProviderAdapterFactory())->GetInstance();
		$this->assertIsObject($singleSignOnProviderAdapter);
		$this->assertInstanceOf(SingleSignOnProviderAdapter::class, $singleSignOnProviderAdapter);
    }
}
