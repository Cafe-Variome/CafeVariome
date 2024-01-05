<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\DiscoveryGroupSeeder;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\DiscoveryGroupAdapter
 * @covers \App\Libraries\CafeVariome\Factory\DiscoveryGroupFactory
 * @covers \App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\DiscoveryGroup
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\DiscoveryGroupsFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */


class DiscoveryGroupAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;

	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = true;
	protected $seed        = 'DiscoveryGroupSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new DiscoveryGroupSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

	public function testReadAssociatedIdsAndSourceIds()
	{
		$dummyID = $this->insertedData['id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$selectedIds = $dbAdapter->ReadAssociatedIdsAndSourceIds([$dummyID]);
		$query = $db->table('discovery_group_sources')->select('discovery_group_id, source_id')->
					  where('discovery_group_id', (int)$dummyID)->get();
		$record = $query->getResult();

		$this->assertEquals($selectedIds[0]->source_id, $record[0]->source_id);
		$this->assertEquals($selectedIds[0]->discovery_group_id, $record[0]->discovery_group_id);
	}

	public function testDeleteUserAssociations()
	{
		$dummyID = $this->insertedData['id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$dbAdapter->DeleteUserAssociations($dummyID);
		$query = $db->table('discovery_group_users')->where('discovery_group_id', (int)$dummyID)->get();
		$record = $query->getResult();

		$this->assertEmpty($record);

	}

	public function testReadByNameAndNetworkId()
	{
		$dummyNetworkID = $this->insertedData['insertedData']['network_id'];
		$dummyName = $this->insertedData['insertedData']['name'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$dbRecords = $dbAdapter->ReadByNameAndNetworkId($dummyName, $dummyNetworkID);
		$query = $db->table('discovery_groups')->where('network_id', (int)$dummyNetworkID)
														 ->where('name', $dummyName)->get();
		$record = $query->getResult();

		$this->assertNotEmpty($dbRecords);

		if (is_null($dbAdapter))
		{
			$this->assertEmpty($dbAdapter);
		}
		elseif ($this->count($dbRecords) > 1)
		{
			$counter = 0;
			foreach ($dbRecords as $instance)
			{
				$this->assertEquals($instance->name, $record[$counter]->name);
				$this->assertEquals($instance->network_id, $record[$counter]->network_id);
				$this->assertEquals($instance->description, $record[$counter]->description);
				$this->assertEquals($instance->policy, $record[$counter]->policy);
			}
		}

	}

	public function testReadByUserId()
	{
		$dummyID = $this->insertedData['id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$selectedUsers = $dbAdapter->ReadByUserId($dummyID);
		$query = $db->table('discovery_group_users')->select('discovery_group_id')
					->where('user_id', (int)$dummyID)->get();
		$record = $query->getResult();

		$this->assertEquals($selectedUsers, $record);
	}

	public function testReadByNetworkId()
	{
		$dummyNetworkID = $this->insertedData['insertedData']['network_id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$selectedNetwork = $dbAdapter->ReadByNetworkId($dummyNetworkID);
		$query = $db->table('discovery_groups')->where('network_id', (int)$dummyNetworkID)->get();
		$record = $query->getResult();

		$counter = 0;
		foreach ($selectedNetwork as $Network)
		{
			$this->assertEquals($Network->name, $record[$counter]->name);
			$this->assertEquals($Network->network_id, $record[$counter]->network_id);
			$this->assertEquals($Network->description, $record[$counter]->description);
			$this->assertEquals($Network->policy, $record[$counter]->policy);
			$counter = $counter + 1;
		}
	}

	public function testCreateSourceAssociations()
	{
		$dummyID = $this->insertedData['id'];
		$dummySourceID = $this->insertedData['insertedData']['source_id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$dbAdapter->DeleteSourceAssociations($dummyID);
		$dbAdapter->CreateSourceAssociations($dummyID, [$dummySourceID]);
		$query = $db->table('discovery_group_sources')->select('source_id')->where('discovery_group_id', (int)$dummyID)->get();
		$record = $query->getResult();

		$this->assertEquals($dummySourceID, $record[0]->source_id);
	}

	public function testReadAssociatedSourceIds()
	{
		$dummyID = $this->insertedData['id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$selectedSources = $dbAdapter->ReadAssociatedSourceIds([$dummyID]);
		$query = $db->table('discovery_group_sources')->select('source_id')->where('discovery_group_id', (int)$dummyID)->get();
		$record = $query->getResult();

		$this->assertEquals($selectedSources[0], $record[0]->source_id);
	}

	public function testCreateUserAssociations()
	{
		$dummyID = $this->insertedData['id'];
		$dummyUserID = $this->insertedData['insertedData']['user_id'];
		$dummySourceID = $this->insertedData['insertedData']['source_id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$dbAdapter->DeleteUserAssociations($dummyID);
		$dbAdapter->DeleteSourceAssociations($dummyID);
		$dbAdapter->CreateUserAssociations($dummyID, [$dummyUserID]);

		$query = $db->table('discovery_group_users')->select('user_id')->where('discovery_group_id', (int)$dummyID)->get();
		$record = $query->getResult();

		$this->assertNotEmpty($record);
	}

	public function testReadAssociatedUserIds()
	{
		$dummyID = $this->insertedData['id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$selectedUsers = $dbAdapter->ReadAssociatedUserIds([$dummyID]);
		$query = $db->table('discovery_group_users')->select('user_id')->where('discovery_group_id', (int)$dummyID)->get();
		$record = $query->getResult();

		$this->assertEquals($selectedUsers[0], $record[0]->user_id);
	}

	public function testDeleteSourceAssociations()
	{
		$dummyID = $this->insertedData['id'];
		$dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$db = db_connect($this->config->tests);

		$dbAdapter->DeleteSourceAssociations($dummyID);
		$query = $db->table('discovery_group_sources')->where('discovery_group_id', (int)$dummyID)->get();
		$record = $query->getResult();
		$this->assertEmpty($record);
	}
}
