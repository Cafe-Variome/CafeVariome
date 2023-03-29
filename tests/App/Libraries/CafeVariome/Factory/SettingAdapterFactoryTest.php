<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\SettingAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\SettingAdapterFactory
 * @covers \App\Libraries\CafeVariome\Database\SettingAdapter
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class SettingAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$settingAdapter = (new SettingAdapterFactory())->GetInstance();
		$this->assertIsObject($settingAdapter);
		$this->assertInstanceOf(SettingAdapter::class, $settingAdapter);
    }
}
