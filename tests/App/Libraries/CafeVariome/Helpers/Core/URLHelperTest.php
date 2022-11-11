<?php namespace App\Libraries\CafeVariome\Helpers\Core;

/**
 * URLHelperTest.php
 * Created 1/11/2022
 * @author Mehdi Mehtarizadeh
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Helpers\Core\URLHelper
 */
class URLHelperTest extends TestCase
{
	public function testInsertPort()
	{
		$this->assertSame('https://www.cafevariome.org:443/', URLHelper::InsertPort('https://www.cafevariome.org/', 443));
		$this->assertSame('http://cafevariome.org:80/', URLHelper::InsertPort('http://cafevariome.org/', 80));
		$this->assertSame('www.cafevariome.org:80/', URLHelper::InsertPort('www.cafevariome.org/', 80));
		$this->assertSame('www.cafevariome.org:80/index.php/5/3/9/test', URLHelper::InsertPort('www.cafevariome.org/index.php/5/3/9/test', 80));
		$this->assertSame('www.cafevariome.org:80/index.php/5/3/9/test/', URLHelper::InsertPort('www.cafevariome.org/index.php/5/3/9/test/', 80));
		$this->assertSame('1.1.1.1:80', URLHelper::InsertPort('1.1.1.1', 80));
		$this->assertSame('1.1.1.1:443', URLHelper::InsertPort('1.1.1.1', 443));
	}

	public function testExtractPort()
	{
		$this->assertSame(443, URLHelper::ExtractPort('https://www.cafevariome.org:443/'));
		$this->assertSame(443, URLHelper::ExtractPort('https://www.cafevariome.org/'));
		$this->assertSame(443, URLHelper::ExtractPort('https://www.cafevariome.org'));
		$this->assertSame(443, URLHelper::ExtractPort('www.cafevariome.org:443'));
		$this->assertSame(80, URLHelper::ExtractPort('www.cafevariome.org:80'));
		$this->assertSame(80, URLHelper::ExtractPort('http://www.cafevariome.org'));
		$this->assertSame(80, URLHelper::ExtractPort('http://www.cafevariome.org/'));
		$this->assertSame(80, URLHelper::ExtractPort('http://www.cafevariome.org:80/'));
		$this->assertSame(80, URLHelper::ExtractPort('http://www.cafevariome.org:80/3/6/9'));
		$this->assertSame(80, URLHelper::ExtractPort('1.1.1.1:80'));
		$this->assertSame(443, URLHelper::ExtractPort('1.1.1.1:443'));
		$this->assertSame(443, URLHelper::ExtractPort('https://1.1.1.1:443/'));
		$this->assertSame(80, URLHelper::ExtractPort('http://localhost:80/'));
		$this->assertSame(-1, URLHelper::ExtractPort('localhost'));
	}
}
