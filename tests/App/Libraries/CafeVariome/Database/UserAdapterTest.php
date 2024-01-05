<?php namespace App\Libraries\CafeVariome\Database;


/**
 * @author Sadegh Abadijou
 */

use App\Database\Seeds\UsersSeeder;
use App\Libraries\CafeVariome\Factory\UserAdapterFactory;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @covers \App\Libraries\CafeVariome\Database\BaseAdapter
 * @covers \App\Libraries\CafeVariome\Database\UserAdapter
 * @covers \App\Libraries\CafeVariome\Factory\UserFactory
 * @covers \App\Libraries\CafeVariome\Factory\UserAdapterFactory
 * @covers \App\Libraries\CafeVariome\Entities\User
 * @covers \App\Libraries\CafeVariome\Database\DataFabricator\UsersFabricator
 * @covers \App\Libraries\CafeVariome\Entities\Entity
 */

class UserAdapterTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $migrate     = true;
	protected $migrateOnce = false;
	protected $refresh     = true;

	protected $namespace   = ' App\Libraries\CafeVariome\Database';
	protected $seedOnce    = false;
	protected $seed        = 'UsersSeeder';

	protected $basePath    = 'app/Database';


	protected function setUp(): void
	{
		parent::setUp();
		$this->config = new Database();
		$seeder = new UsersSeeder($this->config);
		$this->insertedData = $seeder->run();
	}
	protected function tearDown(): void
	{
		parent::tearDown();
	}

    public function testReadIdByEmail()
    {
		$dummyEmail   = $this->insertedData["insertedData"]["email"];

		$db        = db_connect($this->config->tests);
		$dbAdapter =  (new UserAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->ReadIdByEmail($dummyEmail);
		$query = $db->table('users')->where('email', $dummyEmail)->get();

		$this->assertNotEmpty($dbRecords);
		$this->assertEquals($dbRecords, $query->getResult()[0]->id);
    }


    public function testRead()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);

		$dbAdapter = (new UserAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->Read((int)$dummyID);
		$query = $db->table('users')->where('id', $dummyID)->get();
		$record = $dbAdapter->Read($dummyID);

		$this->assertEquals($record->getID(), $query->getResult()[0]->id);
    }

    public function testReadEmail()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);

		$dbAdapter = (new UserAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->ReadEmail((int)$dummyID);
		$query = $db->table('users')->where('id', $dummyID)->get();

		$this->assertEquals($dbRecords, $query->getResult()[0]->email);
    }

    public function testUpdateLastLogin()
    {
		$dummyID   = $this->insertedData["id"];

		$db        = db_connect($this->config->tests);

		$dbAdapter = (new UserAdapterFactory())->GetInstance();

		$dbRecords = $dbAdapter->UpdateLastLogin((int)$dummyID);
		$query = $db->table('users')->where('id', $dummyID)->get();
		$record = $dbAdapter->Read($dummyID);

		$this->assertEquals($record->last_login, $query->getResult()[0]->last_login);
    }
}
