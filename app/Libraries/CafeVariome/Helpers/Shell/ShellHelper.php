<?php namespace App\Libraries\CafeVariome\Helpers\Shell;

/**
 * ShellHelper.php
 * Created 28/01/2020
 *
 * @author Mehdi Mehtarizadeh
 * @author Farid Yavari Dizjikan
 *
 *
 * This is a helper class to run the background tasks in php.
 * It detects the OS and runs the proper command in a synchronous/asynchronous way.
 *
 */

abstract class ShellHelper
{
    // Force extending class to define this method to prevent RCE vulnerabilities
    abstract public function run(string $cmd);
    abstract public function runAsync(string $cmd);

    protected function isWindows() : bool
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }
}
