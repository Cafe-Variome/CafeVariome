<?php
//ini_set('error_reporting', E_ALL);
//
//ini_set('display_errors', '1');
//ini_set('display_startup_errors', '1');

// Make sure it recognizes that we're testing.
$_SERVER['CI_ENVIRONMENT'] = 'testing';
define('ENVIRONMENT', 'testing');

// Load our paths config file
require __DIR__ . '/../../app/Config/Paths.php';
require __DIR__ . '/../../app/Config/Constants.php';

// path to the directory that holds the front controller (index.php)
define('FCPATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);

define('ROOTPATH', realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR);

// The path to the "tests" directory
define('TESTPATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

define('SUPPORTPATH', realpath(TESTPATH . '_support/') . DIRECTORY_SEPARATOR);

define('CI_DEBUG', true);

$paths = new Config\Paths();

// Location of the framework bootstrap file.
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

// Load environment settings from .env files into $_SERVER and $_ENV
require_once SYSTEMPATH . 'Config/DotEnv.php';
(new CodeIgniter\Config\DotEnv(ROOTPATH))->load();

// Set environment values that would otherwise stop the framework from functioning during tests.
if (! isset($_SERVER['app.baseURL']))
{
	$_SERVER['app.baseURL'] = 'http://example.com';
}

