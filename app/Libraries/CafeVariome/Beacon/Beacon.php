<?php namespace App\Libraries\CafeVariome\Beacon;

/**
 * Beacon.php
 * Created: 10/02/2022
 * @author Mehdi Mehtarizadeh
 *
 * This class handles some operations necessary to implement a Beacon service.
 * @see https://beacon-project.io/
 *
 */

class Beacon
{
	/**
	 * Beacon version implemented
	 */
	public CONST BEACON_VERSION = 'v2.0';

	/**
	 * Beacon Controller class name
	 */
	private CONST BEACON_CONTROLLER = 'BeaconAPI';

	/**
	 * @return string generates the reverse URL of Beacon endpoint as ID
	 */
	public static function GetBeaconID() : string
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

	/**
	 * @param string $endpoint
	 * @return string Absolute URL of endpoint
	 */
	private static function GetEndpointURL(string $endpoint)
	{
		return base_url(self::BEACON_CONTROLLER . '/' . $endpoint);
	}

	/**
	 * @return string
	 */
	public static function GetIndividualsURL(): string
	{
		return self::GetEndpointURL('Individuals');
	}

	/**
	 * @return string
	 */
	public static function GetBiosamplesURL(): string
	{
		return self::GetEndpointURL('Biosamples');
	}
}
