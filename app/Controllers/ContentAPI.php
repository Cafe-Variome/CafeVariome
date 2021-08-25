<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use App\Libraries\CafeVariome\Net\HPONetworkInterface;

/**
 * ContentAPI.php
 *
 * Created 25/08/2021
 *
 * @author Mehdi Mehtraizadeh
 * @author Gregory Warren
 *
 * This controller contains endpoints that serve (static) content.
 */

class ContentAPI extends BaseController
{
	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);
	}
}
