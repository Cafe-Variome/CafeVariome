<?php namespace App\Libraries\CafeVariome;

use App\Libraries\CafeVariome\Database\SettingAdapter;
use App\Libraries\CafeVariome\Factory\SettingAdapterFactory;

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
	private static string $version = '2.3.3';

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

		//Local Authentication
		define('ALLOW_LOCAL_AUTHENTICATION', strtolower(getenv('ALLOW_LOCAL_AUTHENTICATION')) == 'true');

		define('AUTHENTICATOR_SESSION_NAME', getenv('AUTHENTICATOR_SESSION_NAME'));
		define('SSO_RANDOM_STATE_SESSION_NAME', getenv('SSO_RANDOM_STATE_SESSION_NAME'));
		define('SSO_TOKEN_SESSION_NAME', getenv('SSO_TOKEN_SESSION_NAME'));
		define('SSO_REFRESH_TOKEN_SESSION_NAME', getenv('SSO_REFRESH_TOKEN_SESSION_NAME'));
		define('POST_AUTHENTICATION_REDIRECT_URL_SESSION_NAME', getenv('POST_AUTHENTICATION_REDIRECT_URL_SESSION_NAME'));
		define('SSO_ID_TOKEN_SESSION_NAME', getenv('SSO_ID_TOKEN_SESSION_NAME'));
	}

	public static function GetVersion(): string
	{
		return self::$version;
	}

	public static function Settings(): Database\IAdapter
	{
		return (new SettingAdapterFactory())->GetInstance()->Load();
	}
}
