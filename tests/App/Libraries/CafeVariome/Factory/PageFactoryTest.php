<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\NullEntity;
use App\Libraries\CafeVariome\Entities\Page;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Entities\Page
 * @covers \App\Libraries\CafeVariome\Factory\PageFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\NullEntity
 */
class PageFactoryTest extends TestCase
{
	public function testGetInstanceFromParameters()
	{
		$page = (new PageFactory())->GetInstanceFromParameters(uniqid(), uniqid(), rand(1, PHP_INT_MAX), rand(0, 1), rand(0, 1));
		$this->assertIsObject($page);
		$this->assertInstanceOf(Page::class, $page);
	}

    public function testGetInstance()
    {
		$object = new \stdClass();
		$object->title = uniqid();
		$object->content = uniqid();
		$object->user_id = rand(1, PHP_INT_MAX);
		$object->active = rand(0, 1);
		$object->removable = rand(0, 1);

		$page = (new PageFactory())->GetInstance($object);
		$this->assertIsObject($page);
		$this->assertInstanceOf(Page::class, $page);

		$emptyObject = new \stdClass();
		$nullEntity = (new PageFactory())->GetInstance($emptyObject);

		$this->assertIsObject($nullEntity);
		$this->assertInstanceOf(NullEntity::class, $nullEntity);
    }
}
