<?php namespace App\Libraries\CafeVariome\Helpers\Core;

/**
 * URLHelper.php
 * Created 26/05/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class URLHelper
{
	public static function InsertPort(string $url, int $port): string
	{
		$hasTrailingSlash = str_ends_with($url, '/');
		$url = rtrim($url, '/');
		$urlPrefix = '';

		if (str_starts_with(strtolower($url), 'http://'))
		{
			$urlPrefix = 'http://';
			$urlNoPrefix = str_replace(['http://', 'HTTP://'], '', $url);
		}
		else if (str_starts_with(strtolower($url), 'https://'))
		{
			$urlPrefix = 'https://';
			$urlNoPrefix = str_replace(['https://', 'HTTPS://'], '', $url);
		}
		else if (str_starts_with(strtolower($url), 'www'))
		{
			$urlPrefix = 'www';
			$urlNoPrefix = str_replace(['www', 'WWW'], '', $url);
		}
		else
		{
			$urlNoPrefix = $url;
		}

		$urlSegments = explode('/', $urlNoPrefix);

		$urlHead = $urlNoPrefix;
		$urlTail =  '';

		if (count($urlSegments) > 1)
		{
			$urlHead = $urlSegments[0]; // Base URL where port needs to be added.
			for($c = 1; $c < count($urlSegments); $c++)
			{
				$urlTail .= $urlSegments[$c] . '/';
			}
			$urlTail = rtrim($urlTail, '/');
		}

		return $urlPrefix . $urlHead . ':' . $port . ($urlTail == '' && !$hasTrailingSlash ? '' : '/') . $urlTail . ($hasTrailingSlash && $urlTail != '' ? '/' : '');
	}

	public static function ExtractPort(string $url): int
	{
		if(str_contains($url, '.'))
		{
			$urlArray = explode('.', $url);
			$urlTail = $urlArray[count($urlArray) - 1];
			if (str_contains($urlTail, ':'))
			{
				$tailArray = explode(':', $urlTail);
				return intval($tailArray[count($tailArray) - 1]);
			}
		}
		else
		{
			if (str_contains($url, ':'))
			{
				$urlArray = explode(':', $url);
				return intval($urlArray[count($urlArray) - 1]);
			}
		}

		if (str_starts_with(strtolower($url), 'https://'))
		{
			return 443;
		}
		else if (str_starts_with(strtolower($url), 'http://'))
		{
			return 80;
		}

		return -1;
	}
}
