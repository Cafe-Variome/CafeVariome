<?php namespace App\Libraries\CafeVariome\Security;

/**
 * CryptographyTest.php
 * Created 1/11/2022
 * @author Mehdi Mehtarizadeh
 */

use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Libraries\CafeVariome\Security\Cryptography
 */
class CryptographyTest extends TestCase
{
	protected string $stringToEncrypt;
	protected string $encryptedString;
	protected string $key;

	protected function setUp(): void
	{
		$this->stringToEncrypt = 'This is Cafe Variome V2.0!';
	}

	/**
	 * @depends testEncrypt
	 * @return void
	 */
	public function testDecrypt(array $input)
    {
		$decryptedString = Cryptography::Decrypt($input[1], $input[0]);
		$this->assertIsString($decryptedString);
		$this->assertEquals($this->stringToEncrypt, $decryptedString);
		return $input;
    }

	/**
	 * @depends testDecrypt
	 * @return void
	 * @throws \SodiumException
	 */
	public function testDecryptBase64DecodeException(array $input)
	{
		$this->expectExceptionMessage('The encoding failed!');
		Cryptography::Decrypt('/<^',  $input[0]);
	}

	/**
	 * @depends testDecrypt
	 * @return void
	 * @throws \SodiumException
	 */
	public function testDecryptMessageTruncatedException(array $input)
	{
		$truncatedString = substr($input[1], 0, 2);
		$this->expectExceptionMessage('The message was truncated!');
		Cryptography::Decrypt($truncatedString, $input[0]);
	}

	/**
	 * @depends testDecrypt
	 * @return void
	 * @throws \SodiumException
	 */
	public function testDecryptPaddedException(array $input)
	{
		$fakeKey = sodium_bin2hex(random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES));
		$this->expectExceptionMessage('Failed to get the decrypted padded string!');
		Cryptography::Decrypt($input[1], $fakeKey);
	}

    public function testGenerateSecretKey(): string
    {
		$key = Cryptography::GenerateSecretKey();
		$this->assertIsString($key);
		return $key;
    }

	/**
	 * @depends testGenerateSecretKey
	 * @return void
	 * @throws \SodiumException
	 */
    public function testEncrypt(string $key) : array
    {
		$encryptedString = Cryptography::Encrypt($this->stringToEncrypt, $key);
		$this->assertIsString($encryptedString);
		return [$key, $encryptedString];
    }
}
