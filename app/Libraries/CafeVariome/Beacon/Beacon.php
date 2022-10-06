<?php namespace App\Libraries\CafeVariome\Beacon;

/**
 * Beacon.php
 * Created: 10/02/2022
 * @author Mehdi Mehtarizadeh
 *
 * This class handles incoming Beacon queries.
 * @see https://beacon-project.io/
 *
 */

class Beacon
{
	public CONST BEACON_VERSION = 'v2.0';

	private CONST BEACON_CONTROLLER = 'BeaconAPI';

	public static function GetBeaconID():string
	{
		$beaconId = Beacon::BEACON_CONTROLLER . '.';
		$baseURL = strtolower(base_url());
		$baseURL = str_replace('https://', '', $baseURL);
		$baseURL = str_replace('http://', '', $baseURL);

		$urlSegments = explode('/', $baseURL);
		$domainSegments  = strpos($urlSegments[0], '.') !== false ? explode('.', $urlSegments[0]) : [$urlSegments[0]];

		unset($urlSegments[0]);

		foreach ($urlSegments as $urlSegment)
		{
			$beaconId .= $urlSegment . '.';
		}

		for($i = 0; $i < count($domainSegments); $i++)
		{
			$beaconId .= $domainSegments[$i];
			$beaconId .= $i == (count($domainSegments) - 1) ? '' : '.';
		}

		return $beaconId;
	}

	private static function GetEndpointURL(string $endpoint)
	{
		return base_url(self::BEACON_CONTROLLER . '/' . $endpoint);
	}

	public static function GetIndividualsURL()
	{
		return self::GetEndpointURL('Individuals');
	}
	
	public static function GetBiosamplesURL()
	{
		return self::GetEndpointURL('Biosamples');
	}

}
