<?php namespace App\Libraries\CafeVariome\Factory;

/**
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Database\PipelineAdapter;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Factory\PipelineAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */
class PipelineAdapterFactoryTest extends TestCase
{
    public function testGetInstance()
    {
		$pipelineAdapter = (new PipelineAdapterFactory())->GetInstance();
		$this->assertIsObject($pipelineAdapter);
		$this->assertInstanceOf(PipelineAdapter::class, $pipelineAdapter);
    }
}
