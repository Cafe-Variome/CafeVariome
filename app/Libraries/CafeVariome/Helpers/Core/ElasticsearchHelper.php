<?php namespace App\Libraries\CafeVariome\Helpers\Core;

use App\Models\Settings;
use App\Models\Source;

/**
 * ElasticsearchHelper.php
 * Created 02/10/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 *
 *
 */

class ElasticsearchHelper
{
	/**
	 * getIndexPrefix()
	 * This funcion returns the first part of site_title variable in settings table in the database.
	 *
	 * @param void
	 * @return string
	 */
	public static function getIndexPrefix(): string
	{
		$setting = Settings::getInstance();

		$title = $setting->getSiteTitle();
		$title = preg_replace("/\s.+/", '', $title);

		$baseUrl = base_url();
		if(strpos($baseUrl, "http://") !== false){
			$baseUrl = str_replace('http://', '', $baseUrl);
		}
		elseif (strpos($baseUrl, 'https://') !== false) {
			$baseUrl = str_replace('https://', '', $baseUrl);
		}

		$segments = explode('/', $baseUrl);
		$prefix = count($segments) > 1 ? $segments[1] : $title;

		return strtolower($prefix);
	}

	public static function getSourceIndexName(int $source_id): string
	{
		$sourceModel = new Source();
		$uid = $sourceModel->getSourceUID($source_id);
		$prefix = ElasticsearchHelper::getIndexPrefix();
		return $prefix . '_' . $source_id . '_' . $uid;
	}

	/**
	 * ping()
	 *
	 * pings elastic server hosts and returns true if they respond, false otherwise.
	 *
	 * @return bool
	 */
	public static function ping():bool
	{
		$setting = Settings::getInstance();

		try {
			$client = \Elasticsearch\ClientBuilder::create()->setHosts([$setting->getElasticSearchUri()])->build();
			$status = $client->ping();
			return $status;
		} catch (\Exception $ex) {
			return false;
		}
	}
}
