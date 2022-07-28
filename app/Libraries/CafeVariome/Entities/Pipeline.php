<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Pipeline.php
 * Created 30/05/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class Pipeline extends Entity
{
	public string $name;

	public int $subject_id_location;

	public string $subject_id_attribute_name;

	public int $subject_id_assignment_batch_size;

	public string $subject_id_prefix;

	public string $expansion_columns;

	public ?int $expansion_policy;

	public ?string $expansion_attribute_name;

	public int $grouping;

	public ?string $group_columns;

	public ?int $dateformat;

	public ?string $internal_delimiter;

}
