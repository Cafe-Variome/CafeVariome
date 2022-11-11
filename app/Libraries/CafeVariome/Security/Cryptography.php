<?php namespace App\Libraries\CafeVariome\Security;

/**
 * Cryptography.php
 * Created 05/05/2022
 *
 * This class provides encryption/decryption functionality.
 * @author Mehdi Mehtarizadeh
 */

class Cryptography
{
	/**
	 * @return string Hex format
	 */
	public static function GenerateSecretKey(): string
	{
		return sodium_bin2hex(sodium_crypto_secretbox_keygen());
	}

	/**
	 * @param string $input
	 * @param string $key in Hex format
	 * @param int $block_size
	 * @return string
	 * @throws \SodiumException
	 */
	public static function Encrypt(string $input, string $key, int $block_size = 1): string
	{
		$key = sodium_hex2bin($key);
		$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
		$padded_input = sodium_pad($input, min($block_size, 512));
		$output = base64_encode($nonce . sodium_crypto_secretbox($padded_input, $nonce, $key));

		sodium_memzero($input);
		sodium_memzero($key);

		return $output;
	}

	/**
	 * @param string $input
	 * @param string $key in Hex format
	 * @param int $block_size
	 * @return string
	 * @throws \SodiumException
	 */
	public static function Decrypt(string $input, string $key, int $block_size = 1): string
	{
		$key = sodium_hex2bin($key);
		$decoded = base64_decode($input, true);

		if ($decoded === false)
		{
			throw new \Exception('The encoding failed');
		}

		if (!defined('CRYPTO_SECRETBOX_MACBYTES')) define('CRYPTO_SECRETBOX_MACBYTES', 16);
		if (mb_strlen($decoded, '8bit') < (SODIUM_CRYPTO_SECRETBOX_NONCEBYTES + CRYPTO_SECRETBOX_MACBYTES))
		{
			throw new \Exception('The message was truncated');
		}

		$nonce = mb_substr($decoded, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, '8bit');
		$ciphertext = mb_substr($decoded, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES, null, '8bit');
		$decrypted_padded_message = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);

		if ($decrypted_padded_message === false)
		{
			throw new \Exception('Failed to get the decrypted padded string!');
		}

		$output = sodium_unpad($decrypted_padded_message, min($block_size, 512));

		if ($output === false)
		{
			throw new \Exception('The message was tampered with in transit');
		}

		sodium_memzero($ciphertext);
		sodium_memzero($key);

		return $output;
	}
}
