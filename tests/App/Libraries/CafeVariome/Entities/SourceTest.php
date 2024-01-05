<?php namespace App\Libraries\CafeVariome\Entities;

use PHPUnit\Framework\TestCase;


/**
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Entities\Source
 */

class SourceTest extends TestCase
{
	public function testGetElasticSearchIndexName()
	{
		$properties = [
			'uid' => "ABC123",
			'name' => "testsource",
			'display_name' => "testsource",
			'description' => "This is a sample for testing",
			'owner_name' => "cvtester",
			'owner_email' => "cvtester@example.com",
			'uri' => "https://cafevariome.org",
			'date_created' => time(),
			'record_count' => 10000,
			'locked' => false,
			'status' => true,
			'id' => 1
			];
		$prefix = "HP";
		$source = new Source($properties);
		$testResult1 = $source->GetElasticSearchIndexName("HP");
		$this->assertEquals( $prefix . '_' . $source->getID() . '_' . $source->uid,$testResult1);
		$prefix = "ORPHA";
		$testResult1 = $source->GetElasticSearchIndexName("ORPHA");
		$this->assertEquals( $prefix . '_' . $source->getID() . '_' . $source->uid,$testResult1);

	}
}
