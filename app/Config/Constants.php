<?php

/*
 | --------------------------------------------------------------------
 | App Namespace
 | --------------------------------------------------------------------
 |
 | This defines the default Namespace that is used throughout
 | CodeIgniter to refer to the Application directory. Change
 | this constant to change the namespace that all application
 | classes should use.
 |
 | NOTE: changing this will require manually modifying the
 | existing namespaces of App\* namespaced-classes.
 */
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
 | --------------------------------------------------------------------------
 | Composer Path
 | --------------------------------------------------------------------------
 |
 | The path that Composer's autoload file is expected to live. By default,
 | the vendor folder is in the Root directory, but you can customize that here.
 */
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
 |--------------------------------------------------------------------------
 | Timing Constants
 |--------------------------------------------------------------------------
 |
 | Provide simple ways to work with the myriad of PHP functions that
 | require information to be in seconds.
 */
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2592000);
defined('YEAR')   || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

/*
 | --------------------------------------------------------------------------
 | Exit Status Codes
 | --------------------------------------------------------------------------
 |
 | Used to indicate the conditions under which the script is exit()ing.
 | While there is no universal standard for error codes, there are some
 | broad conventions.  Three such conventions are mentioned below, for
 | those who wish to make use of them.  The CodeIgniter defaults were
 | chosen for the least overlap with these conventions, while still
 | leaving room for others to be defined in future versions and user
 | applications.
 |
 | The three main conventions used for determining exit status codes
 | are as follows:
 |
 |    Standard C/C++ Library (stdlibc):
 |       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
 |       (This link also contains other GNU-specific conventions)
 |    BSD sysexits.h:
 |       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
 |    Bash scripting:
 |       http://tldp.org/LDP/abs/html/exitcodes.html
 |
 */
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

/**
*--------------------------------------------------------------------------
* Cafe Variome Path Constants
*--------------------------------------------------------------------------
* @author Mehdi Mehtarizadeh
* date 11/06/2019
* This section contains base folder paths for running Cafe Variome user interface. 
* The code is extracted from the Cafe Variome 2.
* 
*/

define('IMAGES','resources/images/');
define('JS','resources/js/');
define('CSS','resources/css/');
define('UPLOAD','upload' . DIRECTORY_SEPARATOR);
define('UPLOAD_DATA','UploadData' . DIRECTORY_SEPARATOR);
define('UPLOAD_JSON','json' . DIRECTORY_SEPARATOR);
define('UPLOAD_PAIRINGS', 'pairings'. DIRECTORY_SEPARATOR);
define('VENDOR','vendor/');

define('RESOURCES_DIR', "resources". DIRECTORY_SEPARATOR);
define('JSON_DATA_DIR', RESOURCES_DIR . "phenotype_lookup_data" . DIRECTORY_SEPARATOR);
define('CV_BIN', RESOURCES_DIR . 'bin' . DIRECTORY_SEPARATOR);
define('STATIC_DIR', 'static' . DIRECTORY_SEPARATOR);
define('ORPHATERMS_SOURCE', 'orphaterms.txt');


define('CV_CONVERT_BIN', CV_BIN . 'cv-convert');
define('CV_CONVERT_SETTINGS_DIR', UPLOAD . 'settings' . DIRECTORY_SEPARATOR);


define('PRECAN', false); // Set/Unset to enable/disable Precanned Queries feature in Query Builder
define('PHENOTYPE_CATEGORIES', true); // Set/Unset to enable/disable categories wise split in phenotype attributes

define('PHP_BIN_PATH', '/usr/bin/php');

// UI status message types
define('STATUS_SUCCESS', 1);
define('STATUS_ERROR', 0);
define('STATUS_INFO', 2);
define('STATUS_WARNING', 3);

//Uploader Actions
define('UPLOADER_DELETE_NONE', 00);
define('UPLOADER_DELETE_ALL', 1);
define('UPLOADER_DELETE_FILE', 2);

// PHP INI Values
ini_set('memory_limit', "1G");

//Data Pipeline
define('SUBJECT_ID_WITHIN_FILE', 0);
define('SUBJECT_ID_IN_FILE_NAME', 1);
define('GROUPING_COLUMNS_ALL', 0);
define('GROUPING_COLUMNS_CUSTOM', 1);

define('EAV_BATCH_SIZE', 30000);



