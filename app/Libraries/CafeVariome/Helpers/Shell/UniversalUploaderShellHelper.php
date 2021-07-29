<?php namespace App\Libraries\CafeVariome\Helpers\Shell;

/**
 * UnivShellHelper.php
 * Created 26/01/2021
 *
 * @author Mehdi Mehtarizadeh
 * @author Farid Yavari Dizjikan
 *
 *
 * This is a helper class to run the background tasks in php.
 * It detects the OS and runs the proper command in a synchronous/asynchronous way.
 *
 */

class UniversalUploaderShellHelper extends ShellHelper
{

    private function getUnivPath(): string
    {
        if (defined('CV_CONVERT_BIN')) {
            return CV_CONVERT_BIN;
        }
        return CV_BIN . '/cv-convert';
    }

    // This method is overriden from the parent class to adapt only PHP commands in a synchronous way
    public function run(string $cmd)
    {
        $escaped_command = escapeshellcmd($cmd);

        if(!$cmd){
            throw new \Exception("No command to run!");
        }

        $cmdToRun = $this->getUnivPath(). " ".$escaped_command;

        return shell_exec($cmdToRun);
    }

    // This method is overriden from the parent class to adapt only PHP commands in an asynchronous way
    public function runAsync(string $cmd, bool $isUniv = true)
    {
        $escaped_command = escapeshellcmd($cmd);

        if(!$cmd){
            throw new \Exception("No command to run!");
        }

        $cmdToRun = $this->getUnivPath(). " ".$escaped_command;

        if ($this->isWindows()) {
            shell_exec($cmdToRun . " > NUL");
        }
        else {
            shell_exec($cmdToRun . " >/dev/null 2>&1 &");
        }
    }

}
