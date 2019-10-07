<?php namespace App\Models;

/**
 * URISegment.php
 * 
 * Created: 07/10/2019
 * @author Mehdi Mehtarizadeh
 * 
 * This class contains data for uri segments. It is useful for front-end interface.
 */

class URISegment 
{
    public $controllerName;
    public $methodName;

    public $params = [];
}
