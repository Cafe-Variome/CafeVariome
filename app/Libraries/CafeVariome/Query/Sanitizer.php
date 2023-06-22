<?php namespace App\Libraries\CafeVariome\Query;


/**
 * Sanitizer.php
 * Created 14/06/2023
 *
 * @author Sadegh Abadijou
 * @author Farid Yavari Dizjikan
 *
 * This is a helper class that makes sure no illegal characters exist in the query.
 *
 */

class Sanitizer {

	public static string $flag;

	public function __construct()
	{
		self::$flag = false;
	}
	public static function getFlag()
	{
		return self::$flag;
	}
	public static function Sanitize($data)
	{

		if (is_array($data))
		{
			$sanitizedValue = self::sanitizeArray($data);

			if ($sanitizedValue == $data)
			{
				self::$flag = false;
			}
			else
			{
				self::$flag = true;
			}
			return $sanitizedValue;
		}
		elseif (is_object($data))
		{
			$sanitizedObject = self::sanitizeObject($data);
			if ($sanitizedObject == $data)
			{
				self::$flag = false;
			}
			else
			{
				self::$flag = true;
			}
			return $sanitizedObject;
		}
		else
		{
			$sanitizedValue = self::sanitizeValue($data);
			if ($sanitizedValue === $data)
			{
				self::$flag = false;
			}
			else
			{
				self::$flag = true;
			}
			return $sanitizedValue;
		}
	}

	private static function sanitizeArray($array) {
		$sanitizedArray = [];
		foreach ($array as $key => $value) {
			$sanitizedArray[self::sanitizeKey($key)] = self::sanitize($value);
		}
		return $sanitizedArray;
	}

	private static function sanitizeObject($object) {
		$sanitizedObject = new stdClass();
		foreach ($object as $key => $value) {
			$sanitizedObject->{self::sanitizeKey($key)} = self::sanitize($value);
		}
		return $sanitizedObject;
	}

	private static function sanitizeKey($key) {
		$sanitizedKey = preg_replace('/[^a-zA-Z0-9_-]/', '', $key);
		return $sanitizedKey;
	}

	private static function sanitizeValue($value) {
		$exclude_chars = array('<', '=', '>', '"');
		$sanitizedValue = self::customEntities($value, $exclude_chars);
		return $sanitizedValue;
	}

	private static function customEntities($value, $ex_chars)
	{
		$value = htmlentities($value, ENT_QUOTES, 'UTF-8');
		foreach ($ex_chars as $char)
		{
			$encodedChar = htmlentities($char, ENT_QUOTES, 'UTF-8');
			$value = str_replace($encodedChar, $char, $value);
		}
		return $value;
	}

}
