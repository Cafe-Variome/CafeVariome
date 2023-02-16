<?php namespace App\Models;

/**
 * Class UIData
 * @author: Mehdi Mehtarizadeh
 * Created: 18/06/2019
 * This class contains data passed to views from controllers.
 *
 *
 */

class UIData
{
	/*
	 * @var array
	 * associative array that will pass extra values to views
	 * array keys can be used inside views as variables
	 */
    public array $data;

	/**
	 * @var array
	 * list of javascript files to be loaded in a view
	 */
    private array $javascript;

	/**
	 * @var array
	 * list of css files to be loaded in a view
	 */
    private array $css;

	/**
	 * @var string
	 * page title as it appears on the browser
	 */
    public string $title;

	/**
	 * @var string
	 * page meta description
	 */
    public string $description;

	/**
	 * @var string
	 * page meta keywords
	 */
    public string $keywords;

	/**
	 * @var string
	 * page author
	 */
    public string $author;

	/**
	 * @var bool
	 * flag to make footer sticky or not in templates that need it
	 */
    public bool $stickyFooter;

    public function __construct()
    {
		$this->data = [];
		$this->javascript = [];
		$this->css = [];
		$this->author = '';
		$this->title = '';
		$this->description = '';
		$this->keywords = '';
		$this->stickyFooter = true;
    }

}

