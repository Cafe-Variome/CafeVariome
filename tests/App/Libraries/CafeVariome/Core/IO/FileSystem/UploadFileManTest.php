<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * UploadFileManTest.php
 * Created 10/11/2022
 * @author Mehdi Mehtarizadeh
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Core\IO\FileSystem\UploadFileMan
 * @covers \App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan
 * @covers \App\Libraries\CafeVariome\Core\IO\FileSystem\File
 */
class UploadFileManTest extends TestCase
{
    public function testGetFiles()
    {
		$_FILES = [
			'testFile' => [
				"name" => "MyFile.jpg",
				"type" => "image/jpeg",
				"tmp_name" => "/tmp/php/" . uniqid(),
				"error" => UPLOAD_ERR_OK,
				"size" => 2563
			]
		];

		$fileMan = new UploadFileMan();
		$files = $fileMan->getFiles();
		$this->assertIsArray($files);
		$this->assertEquals(1, count($files));

		$_FILES = [
			'testFile' => [
				"name" => [0 => "MyFile.jpg", 1 =>"MyFile2.txt"],
				"type" => [0 => "image/jpeg", 1 => "text/plain"],
				"tmp_name" => [0 => "/tmp/php/" . uniqid(), 1 => "/tmp/php/" . uniqid()],
				"error" => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
				"size" => [0 => 2563, 1 => 6598]
			]
		];

		$fileMan = new UploadFileMan();
		$files = $fileMan->getFiles();
		$this->assertIsArray($files);
		$this->assertEquals(2, count($files));
	}

    public function testGetMaximumAllowedUploadSize()
    {
		$uploadMaxSize = ini_get('upload_max_filesize');
		$postMaxSize = ini_get('post_max_size');
		$this->assertEquals($uploadMaxSize && $uploadMaxSize < $postMaxSize ? $uploadMaxSize : $postMaxSize, UploadFileMan::getMaximumAllowedUploadSize());
    }

    public function testGetAllowedDataFileFormats()
    {
		$fileFormatsArray = UploadFileMan::GetAllowedDataFileFormats(true);
		$this->assertIsArray($fileFormatsArray);

		$fileFormatsArray = UploadFileMan::GetAllowedDataFileFormats(false);
		$this->assertIsString($fileFormatsArray);
	}

	public function testParseSizeToByte()
	{
		$parsedSize = UploadFileMan::parseSizeToByte('1M');
		$this->assertEquals(1048576, $parsedSize);

		$noUnitParsedSize = UploadFileMan::parseSizeToByte('1.5');
		$this->assertEquals(2, $noUnitParsedSize);
	}

//    public function testSave()
//    {
//
//    }
}
