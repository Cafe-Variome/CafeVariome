<?php namespace App\Libraries\CafeVariome\Entities;

/**
 * Page.php
 * Created 17/08/2022
 *
 * This class extends Entity and implements IEntity.
 * @author Mehdi Mehtarizadeh
 */

class Page extends Entity
{
	public string $title;

	public string $content;

	public int $user_id;

	public bool $active;

	public bool $removable;
}
