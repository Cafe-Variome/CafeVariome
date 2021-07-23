<?php namespace App\Libraries\CafeVariome\Query;


/**
 * AbstractResult.php
 * Created 23/07/2021
 *
 * @author Mehdi Mehtarizadeh
 *
 */


abstract class AbstractResult
{
	public abstract function extract(array $ids, string $attribute, int $source_id): array;
}
