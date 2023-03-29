<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Setting;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Factory\SettingFactory
 * @covers \App\Libraries\CafeVariome\Entities\Setting
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class SettingFactoryTest extends TestCase
{
    public function testGetInstanceFromParameters()
    {
		$setting = (new SettingFactory())->GetInstanceFromParameters(uniqid(), uniqid(), uniqid(), uniqid(), uniqid());
		$this->assertIsObject($setting);
		$this->assertInstanceOf(Setting::class, $setting);
    }

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->key = uniqid();
		$object->name = uniqid();
		$object->value = uniqid();
		$object->group = uniqid();
		$object->info = uniqid();
		$setting = (new SettingFactory())->GetInstance($object);

		$this->assertIsObject($setting);
		$this->assertInstanceOf(Setting::class, $setting);

		$emptyObject = new \stdClass();
		$nullEntity = (new SettingFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
