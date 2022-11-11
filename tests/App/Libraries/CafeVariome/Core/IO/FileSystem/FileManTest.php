<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * FileManTest.php
 * Created 1/11/2022
 * @author Mehdi Mehtarizadeh
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan
 */
class FileManTest extends TestCase
{
	private string $testDirectoryName;
	private string $testPath;

	protected function setUp(): void
	{
		$this->testDirectoryName = 'test_create_dir';
		$this->testPath = FCPATH . 'writable' . DIRECTORY_SEPARATOR . $this->testDirectoryName;
	}

	public function test__construct()
	{
		$this->expectExceptionMessage('The $basePath cannot be file.');
		$fileMan = new FileMan(FCPATH . 'writable' . DIRECTORY_SEPARATOR . '.htaccess');
	}

	public function testCreateDirectory()
    {
		$fileMan = new FileMan(FCPATH . 'writable' . DIRECTORY_SEPARATOR);
		$fileMan->CreateDirectory($this->testDirectoryName);
		$this->assertDirectoryExists(FCPATH . 'writable' . DIRECTORY_SEPARATOR . $this->testDirectoryName);
    }

	/**
	 * @depends testDelete
	 * @return void
	 * @throws \Exception
	 */
	public function testDeleteDirectory()
	{
		$fileMan = new FileMan(FCPATH . 'writable' . DIRECTORY_SEPARATOR);
		$fileMan->DeleteDirectory($this->testDirectoryName);
		$this->assertDirectoryDoesNotExist(FCPATH . 'writable' . DIRECTORY_SEPARATOR . $this->testDirectoryName);
	}

	/**
	 * @return void
	 * @throws \Exception
	 */
    public function testCountFiles()
    {
		$fileMan = new FileMan($this->testPath);
		$this->assertEquals($fileMan->countFiles(), 0);
	}

	/**
	 * @depends testCreateDirectory
	 * @return array[]
	 * @throws \Exception
	 */
	public function testWrite(): array
	{
		$fileMan = new FileMan($this->testPath);

		$fileNamePrefix = 'tfn_';
		$fileFormatPrefix = 'tff_';
		$files = [
			[
				$fileNamePrefix . uniqid(),
				random_bytes($length = random_int(0, 1024)),
				$length,
				''
			],
			[
				$fileNamePrefix . uniqid() . '.',
				random_bytes($length = random_int(0, 1024)),
				$length,
				''
			],
			[
				'.' . ($extension = $fileNamePrefix . uniqid()),
				random_bytes($length = random_int(0, 1024)),
				$length,
				$extension
			],
			[
				$fileNamePrefix . uniqid() . '.' . ($extension = $fileFormatPrefix . uniqid()),
				random_bytes($length = random_int(0, 1024)),
				$length,
				$extension
			],
			[
				$fileNamePrefix . uniqid() . '.' . uniqid() . '.' . ($extension = $fileFormatPrefix . uniqid()),
				random_bytes($length = random_int(0, 1024)),
				$length,
				$extension
			]
		];

		for($c = 0; $c < count($files); $c++)
		{
			$fileMan->Write($files[$c][0], $files[$c][1]);
			$this->assertFileExists($this->testPath . '/' . $files[$c][0]);
		}

		return $files;
	}

	/**
	 * @depends testWrite
	 */
	public function testExists(array $files): array
	{
		$fileMan = new FileMan($this->testPath);
		for($c = 0; $c < count($files); $c++)
		{
			$this->assertTrue($fileMan->Exists($files[$c][0]));
		}
		return $files;
	}

	/**
	 * @depends testExists
	 * @param array $files
	 * @return array
	 */
	public function testIsFile(array $files):array
	{
		for($c = 0; $c < count($files); $c++)
		{
			$this->assertTrue(FileMan::IsFile($this->testPath . '/' . $files[$c][0]));
		}
		return $files;
	}

	/**
	 * @depends testIsFile
	 * @param array $files
	 * @return array
	 */
	public function testGetFileName(array $files): array
	{
		for($c = 0; $c < count($files); $c++)
		{
			$this->assertEquals(FileMan::GetFileName($this->testPath . '/' . $files[$c][0]), $files[$c][0]);
		}
		return $files;
	}

	/**
	 * @depends testGetFileName
	 * @param array $files
	 * @return array
	 */
	public function testGetExtension(array $files): array
    {
		$fileMan = new FileMan($this->testPath);

		for($c = 0; $c < count($files); $c++)
		{
			$this->assertEquals($fileMan->getExtension($files[$c][0]), $files[$c][3]);
			$this->assertEquals($fileMan->getExtension($this->testPath . DIRECTORY_SEPARATOR . $files[$c][0]), $files[$c][3]);
		}

		return $files;
    }

	/**
	 * @depends testGetExtension
	 * @param array $files
	 * @return array
	 */
    public function testGetFileExtension(array $files): array
    {
		for($c = 0; $c < count($files); $c++)
		{
			$this->assertEquals(FileMan::GetFileExtension($this->testPath . '/' . $files[$c][0]), $files[$c][3]);
		}

		return $files;
    }

	/**
	 * @depends testGetFileExtension
	 * @return void
	 * @throws \Exception
	 */
	public function testRead(array $files): array
	{
		$fileMan = new FileMan($this->testPath);
		for($c = 0; $c < count($files); $c++)
		{
			$content = $fileMan->Read($files[$c][0]);
			$this->assertSame($content, $files[$c][1]);
		}

		return $files;
	}

	/**
	 * @depends testRead
	 * @return void
	 * @throws \Exception
	 */
	public function testGetFileSize(array $files):array
    {
		for($c = 0; $c < count($files); $c++)
		{
			$this->assertEquals($files[$c][2], FileMan::GetFileSize($this->testPath . '/' . $files[$c][0]));
		}
		return $files;
    }

	/**
	 * @depends testGetFileSize
	 * @param array $files
	 * @return void
	 */
	public function testGetSize(array $files): array
	{
		$fileMan = new FileMan($this->testPath);
		for($c = 0; $c < count($files); $c++)
		{
			$this->assertEquals($files[$c][2], $fileMan->getSize($files[$c][0]));
		}

		return $files;
	}

	/**
	 * @depends testGetModificationTimeStamp
	 * @param array $files
	 * @return void
	 * @throws \Exception
	 */
	public function testDelete(array $files)
	{
		$fileMan = new FileMan($this->testPath);
		for($c = 0; $c < count($files); $c++)
		{
			$fileMan->Delete($files[$c][0]);
			$this->assertFileDoesNotExist($this->testPath . '/'. $files[$c][0]);
		}
	}

	/**
	 * @depends testGetSize
	 * @return array
	 */
	public function testReadLine(array $files): array
	{
		$fileName = uniqid();
		$firstLine = 'first line' . PHP_EOL;
		$secondLine = 'second line';
		$fileMan = new FileMan($this->testPath);
		$fileMan->Write($fileName, $firstLine);
		$fileMan->Write($fileName, $secondLine, true);
		$this->assertEquals($firstLine, $fileMan->ReadLine($fileName));
		$this->assertEquals($secondLine, $fileMan->ReadLine($fileName));
		$fileMan->Delete($fileName);
		return $files;
	}

	/**
	 * @depends testReadLine
	 * @return void
	 */
	public function testGetModificationTimeStamp(array $files): array
	{
		$fileName = uniqid();
		$fileMan = new FileMan($this->testPath);
		$time = time();
		$fileMan->Write($fileName,  random_bytes(random_int(0, 1024)));
		$this->assertEquals($fileMan->GetModificationTimeStamp($fileName), $time);
		$fileMan->Delete($fileName);
		return $files;
	}

	/**
	 * @covers \App\Libraries\CafeVariome\Core\IO\FileSystem\File
	 */
	public function testIsValid()
	{
		$fileMan = new FileMan(FCPATH . 'tests/resources');
		$mock_xls = new File('mock-xls.xls', $fileMan->getSize('mock-xls.xls'), FCPATH . 'tests/resources/mock-xls.xls', '', 0);
		$error = '';
		$this->assertTrue($fileMan->isValid($mock_xls, $error));

		$mock_csv = new File('mock-csv.csv', $fileMan->getSize('mock-csv.csv'), FCPATH . 'tests/resources/mock-csv.csv', '', 0);
		$this->assertTrue($fileMan->isValid($mock_csv, $error));

		$invalid_csv = new File('invalid-csv.csv', $fileMan->getSize('invalid-csv.csv'), FCPATH . 'tests/resources/invalid-csv.csv', '', 0);
		$this->assertFalse($fileMan->isValid($invalid_csv, $error));

		$mock_json = new File('mock-json.json', $fileMan->getSize('mock-json.json'), FCPATH . 'tests/resources/mock-json.json', '', 0);
		$this->assertTrue($fileMan->isValid($mock_json, $error));

		$invalid_json = new File('invalid-json.json', $fileMan->getSize('invalid-json.json'), FCPATH . 'tests/resources/invalid-json.json', '', 0);
		$this->assertFalse($fileMan->isValid($invalid_json, $error));

		$mock_xlsx = new File('mock-xlsx.xlsx', $fileMan->getSize('mock-xlsx.xlsx'), FCPATH . 'tests/resources/mock-xlsx.xlsx', '', 0);
		$this->assertTrue($fileMan->isValid($mock_xlsx, $error));

		$invalid_xlsx = new File('invalid-xlsx.xlsx', $fileMan->getSize('invalid-xlsx.xlsx'), FCPATH . 'tests/resources/invalid-xlsx.xlsx', '', 0);
		$this->assertFalse($fileMan->isValid($invalid_xlsx, $error));
	}

	public function testGetMimeType()
	{
		$fileMan = new FileMan(FCPATH . 'tests/resources');
		$this->assertEquals($fileMan->getMimeType('mock-xls.xls'), 'application/vnd.ms-excel');
		$this->assertEquals($fileMan->getMimeType('mock-xlsx.xlsx'), 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheetapplication/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		$this->assertEquals($fileMan->getMimeType('invalid-csv.csv'), 'text/plain');
	}

	public function testGetFileMimeType()
	{
		$this->assertEquals(FileMan::GetFileMimeType(FCPATH . 'tests/resources/mock-xls.xls'), 'application/vnd.ms-excel');
		$this->assertEquals(FileMan::GetFileMimeType(FCPATH . 'tests/resources/invalid-csv.csv'), 'text/plain');
	}

	public function testReadCSV()
	{
		$fileMan = new FileMan(FCPATH . 'tests/resources');
		$firstLine = $fileMan->ReadCSV('mock-csv.csv');
		$this->assertIsArray($firstLine);
		$this->assertTrue(in_array('A', $firstLine));
		$this->assertTrue(in_array('B', $firstLine));
		$this->assertTrue(in_array('C', $firstLine));
		$secondLine = $fileMan->ReadCSV('mock-csv.csv');
		$this->assertIsArray($secondLine);
		$this->assertTrue(in_array('1', $secondLine));
		$this->assertTrue(in_array('2', $secondLine));
		$this->assertTrue(in_array('3', $secondLine));
		$this->assertFalse($fileMan->ReadCSV('mock-csv.csv'));
	}

    public function testGetImageSize()
    {
		$fileMan = new FileMan(FCPATH . 'tests/resources');
		$size = $fileMan->GetImageSize('cafevariome_icon.png');
		$this->assertIsArray($size);
		$this->assertEquals($size[0], 32);
		$this->assertEquals($size[1], 35);
	}

    public function testResizeImage()
    {
		$fileMan = new FileMan(FCPATH . 'tests/resources');
		$imageContent = $fileMan->Read('cafevariome_icon.png');
		ob_start();
		$fileMan->ResizeImage($imageContent, 24, null);
		$resizedImageData = ob_get_contents();
		ob_end_clean();
		$fileMan->Write('cafevariome_icon_resized.png', $resizedImageData);
		$size = $fileMan->GetImageSize('cafevariome_icon_resized.png');
		$fileMan->Delete('cafevariome_icon_resized.png');
		$this->assertIsArray($size);
		$this->assertEquals($size[0], 24);
	}
}
