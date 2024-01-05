<?php namespace App\Libraries\CafeVariome\Database;

/**
 * @author Sadegh Abadijou
 */

use App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use App\Database\Seeds\SingleSignOnProvidersSeeder;
use App\Libraries\CafeVariome\Entities\IEntity;
use Config\Database;
/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\SingleSignOnProviderAdapter
 * @covers \App\Libraries\CafeVariome\Factory\SingleSignOnProviderFactory
 * @covers \App\Libraries\CafeVariome\Factory\SingleSignOnProviderAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\SingleSignOnProvider
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\SingleSignOnProvidersFabricator
 * @covers \App\Libraries\CafeVariome\Factory\ServerAdapterFactory
 */


class SingleSignOnProviderAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'SingleSignOnProvidersSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new SingleSignOnProvidersSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testReadUserLoginSingleSignOnProviders()
    {
		$dbAdapter = (new SingleSignOnProviderAdapterFactory())->GetInstance();
		$dbRecords = $dbAdapter->ReadUserLoginSingleSignOnProviders();

		$db = db_connect($this->config->tests);
		$records = $db->table('single_sign_on_providers')->where('user_login', true)->get()->getResult();



		for ($i = 0; $i < count($dbRecords); $i++)
		{
			$this->assertEquals($dbRecords[$i]->name, $records[$i]->name);
			$this->assertEquals($dbRecords[$i]->display_name, $records[$i]->display_name);
			$this->assertEquals($dbRecords[$i]->icon, $records[$i]->icon);
			$this->assertEquals($dbRecords[$i]->type, $records[$i]->type);
			$this->assertEquals($dbRecords[$i]->port, $records[$i]->port);
			$this->assertEquals($dbRecords[$i]->user_login, $records[$i]->user_login);
			$this->assertEquals($dbRecords[$i]->authentication_policy, $records[$i]->authentication_policy);
			$this->assertEquals($dbRecords[$i]->credential_id, $records[$i]->credential_id);
			$this->assertEquals($dbRecords[$i]->proxy_server_id, $records[$i]->proxy_server_id);
			$this->assertEquals($dbRecords[$i]->removable, $records[$i]->removable);
		}
	}

    public function testReadByURL()
    {
		$dummyServerID = $this->insertedData['insertedData']['server_id'];
		$dummyQuery = $this->insertedData['insertedData']['query'];
		$db = db_connect($this->config->tests);
		$dummyURL = $db->table('servers')->where('id', $dummyServerID)->select('address')->get()->getResult();

		$dbAdapter = (new SingleSignOnProviderAdapterFactory())->GetInstance();
		$dbRecord = $dbAdapter->ReadByURL($dummyURL[0]->address, $dummyQuery);

		$this->assertEquals($dummyServerID, $dbRecord->server_id);
    }

	public function testToEntity()
	{
		$object = (object) $this->insertedData['insertedData'];

		$dbAdapter = (new SingleSignOnProviderAdapterFactory())->GetInstance();

		$instance = $dbAdapter->toEntity($object);

		$this->assertInstanceOf(IEntity::class, $instance);
	}
}
