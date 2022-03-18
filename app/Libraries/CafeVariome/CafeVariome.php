<?php namespace App\Libraries\CafeVariome;

/**
 * CafeVariome.php
 *
 * @auhtor Mehdi Mehtarizadeh
 *
 */

class CafeVariome
{

	/**
	 * @var string $version of Cafe Variome Software
	 */
	private static string $version = '2.2.0';

	/**
	 * Boot - Initialises constants from .env file
	 * @return void
	 */
	public static function Boot()
	{
		// PHP Binary Path, used in CLI calls
		define('PHP_BIN_PATH', getenv('PHP_BIN_PATH'));

		//Data stream batch sizes
		define('EAV_BATCH_SIZE', getenv('EAV_BATCH_SIZE'));
		define('NEO4J_BATCH_SIZE', getenv('NEO4J_BATCH_SIZE'));

		//Data input batch size used in transactions
		define('SPREADSHEET_BATCH_SIZE', getenv('SPREADSHEET_BATCH_SIZE'));

		//Elasticsearch Aggregate Size
		define('ELASTICSERACH_AGGREGATE_SIZE', getenv('ELASTICSERACH_AGGREGATE_SIZE'));
		define('ELASTICSERACH_EXTRACT_AGGREGATE_SIZE', getenv('ELASTICSERACH_EXTRACT_AGGREGATE_SIZE'));
	}

	public static function getVersion(): string
	{
		return self::$version;
	}
}
