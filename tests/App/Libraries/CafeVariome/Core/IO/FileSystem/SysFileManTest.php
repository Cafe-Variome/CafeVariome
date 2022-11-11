<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * SysFileManTest.php
 * Created 1/11/2022
 * @author Mehdi Mehtarizadeh
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan
 * @covers \App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan
 * @covers \App\Libraries\CafeVariome\Core\IO\FileSystem\File
 */
class SysFileManTest extends TestCase
{
	private string $testResources;

	protected function setUp(): void
	{
		$this->testResources = FCPATH . 'tests' . DIRECTORY_SEPARATOR . 'resources';
	}

	public function testSave()
	{
		$fileMan = new SysFileMan($this->testResources,true, ['csv', 'xls'], true, 32);
		$files = $fileMan->getFiles();
		$fileMan->Save($files[0], 'recdir');
		$this->assertFileExists($this->testResources . DIRECTORY_SEPARATOR . 'recdir' . DIRECTORY_SEPARATOR . $files[0]->getDiskName());
		$fileMan->Delete('recdir'. DIRECTORY_SEPARATOR . $files[0]->getDiskName());
	}

	public function testGetFiles()
	{
		$fileMan = new SysFileMan($this->testResources, true, ['csv', 'xls'], true, 32);
		$files = $fileMan->getFiles();
		$this->assertIsArray($files);
		$this->assertEquals(count($files), 4);

		$fileMan = new SysFileMan($this->testResources, false, ['csv', 'xls'], true, 32);
		$files = $fileMan->getFiles();
		$this->assertIsArray($files);
		$this->assertEquals(count($files), 3);

		$files = $fileMan->getFiles('recdir', true, ['xls']);
		$this->assertIsArray($files);
		$this->assertEquals(count($files), 1);
	}

}
