<?php namespace App\Libraries\CafeVariome\Entities\ViewModels;

/**
 * PageList.php
 * Created 17/08/2022
 *
 * @author Mehdi Mehtarizadeh
 */

class PageList extends BaseViewModel
{
	public string $title;

	public string $content;

	public int $user_id;

	public string $user_first_name;

	public string $user_last_name;

	public bool $active;

	public bool $removable;

	public function __construct(object $input = null)
	{
		if (!is_null($input))
		{
			parent::__construct($input);
			$this->content = substr(strip_tags($this->content), 0, 100);
		}

	}
}
