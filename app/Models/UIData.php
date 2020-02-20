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
    protected $helpers = [ ];

    //Page info
    public $data = Array();
    public $pageName = FALSE;
    
    //Page contents
    public $javascript = array();
    public $css = array();
    public $fonts = array();
    
    //Page Meta
    public $title = FALSE;
    public $description = FALSE;
    public $keywords = FALSE;
    public $author = FALSE;

    public $stickyFooter = true;

    public function __construct()
    {

    }

}

