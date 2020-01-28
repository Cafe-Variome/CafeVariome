<?php namespace App\Libraries\CafeVariome;

/**
 * ShellHelper.php 
 * Created 28/01/2020
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * This is a helper class to run the background tasks in php.
 * It detects the OS and runs the proper command in a synchronous/asynchronous way.
 * 
 */

class ShellHelper
{
    private function getPHPPath(): string
    {
        if (defined('PHP_BIN_PATH')) {
            return PHP_BIN_PATH;
        }
        return PHP_BINDIR . '/php';
    }

    public function run(string $cmd)
    {
        if(!$cmd){
            throw new Exception("No command to run!");
        }

        return shell_exec($this->getPHPPath(). " ".$cmd);        
    }

    public function runAsync(string $cmd)
    {
        if(!$cmd){
            throw new Exception("No command to run!");
        }

        if ($this->isWindows()) {
            shell_exec($this->getPHPPath() . " " .$cmd." > NUL");
        }
        else {
            shell_exec($this->getPHPPath(). " ".$cmd." >/dev/null 2>&1 &");
        }
    }

    private function isWindows() : bool
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }
}
