<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\SettingsSeeder;
use App\Libraries\CafeVariome\CafeVariome;
use App\Libraries\CafeVariome\Factory\SettingAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\SettingAdapter
 * @covers \App\Libraries\CafeVariome\Factory\SettingFactory
 * @covers \App\Libraries\CafeVariome\Factory\SettingAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\Setting
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\SettingsFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class SettingAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'SettingsSeeder';

	protected $basePath    = 'app/Database';

	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new SettingsSeeder($this->config);
		$this->insertedData = $seeder->run();
	}

	protected function tearDown(): void
	{
		parent::tearDown();
	}

	public function testLoad()
	{
		$dbAdapter = new SettingAdapterFactory();
		$dbRecords = $dbAdapter->GetInstance()->Load();

		$this->assertNotEmpty($dbRecords);
	}

	public function testGetSingletonInstance()
	{
		$dbAdapter = new SettingAdapterFactory();
		$singletonInstance = $dbAdapter->GetInstance()->GetSingletonInstance();

		$this->assertNotEmpty($singletonInstance);
	}

	public function testGetNeo4JUserName()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetNeo4JUserName();

		$query = $db->table('settings')->select('value')->where('key', 'neo4j_username')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetNeo4JPassword()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetNeo4JPassword();

		$query = $db->table('settings')->select('value')->where('key', 'neo4j_password')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetInstallationKey()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetInstallationKey();

		$query = $db->table('settings')->select('value')->where('key', 'installation_key')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetSiteTitle()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetSiteTitle();

		$query = $db->table('settings')->select('value')->where('key', 'site_title')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetHPOAutoCompleteURL()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetHPOAutoCompleteURL();

		$query = $db->table('settings')->select('value')->where('key', 'hpo_autocomplete_url')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetGeneAutoCompleteURL()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetGeneAutoCompleteURL();

		$query = $db->table('settings')->select('value')->where('key', 'gene_autocomplete_url')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetNeo4JUri()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetNeo4JUri();

		$query = $db->table('settings')->select('value')->where('key', 'neo4j_server')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetAuthServerUrl()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetAuthServerUrl();

		$query = $db->table('settings')->select('value')->where('key', 'auth_server')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetElasticSearchUri()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetElasticSearchUri();

		$query = $db->table('settings')->select('value')->where('key', 'elastic_url')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}

	public function testGetSNOMEDAutoCompleteURL()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetSNOMEDAutoCompleteURL();

		$query = $db->table('settings')->select('value')->where('key', 'snomed_autocomplete_url')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);

	}

	public function testGetNeo4JPort()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetNeo4JPort();

		$query = $db->table('settings')->select('value')->where('key', 'neo4j_port')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);

	}

	public function testGetORPHAAutoCompleteURL()
	{
		$cvSettings = CafeVariome::Settings();
		$db         = db_connect($this->config->tests);

		$dbRecords  = $cvSettings->GetORPHAAutoCompleteURL();

		$query = $db->table('settings')->select('value')->where('key', 'orpha_autocomplete_url')->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->value);
	}
}
