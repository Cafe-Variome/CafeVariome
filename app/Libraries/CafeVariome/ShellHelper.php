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

    public function run(string $cmd, bool $isPHP = true)
    {
        if(!$cmd){
            throw new Exception("No command to run!");
        }

        $cmdToRun = '';
        if ($isPHP) {
            $cmdToRun = $this->getPHPPath(). " ".$cmd;
        }
        else {
            $cmdToRun = $cmd;
        }

        return shell_exec($cmdToRun);        
    }

    public function runAsync(string $cmd, bool $isPHP = true)
    {
        if(!$cmd){
            throw new Exception("No command to run!");
        }

        $cmdToRun = '';
        if ($isPHP) {
            $cmdToRun = $this->getPHPPath(). " ".$cmd;
        }
        else {
            $cmdToRun = $cmd;
        }

        if ($this->isWindows()) {
            shell_exec($cmdToRun . " > NUL");
        }
        else {
            shell_exec($cmdToRun . " >/dev/null 2>&1 &");
        }
    }

    private function isWindows() : bool
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
    }
}
