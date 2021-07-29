<?php namespace App\Libraries\CafeVariome\Helpers\Shell;

/**
 * PHPShellHelper.php
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

class PHPShellHelper extends ShellHelper
{

    private function getPHPPath(): string
    {
        if (defined('PHP_BIN_PATH')) {
            return PHP_BIN_PATH;
        }
        return PHP_BINDIR . '/php';
    }

    // This method is overriden from the parent class to adapt only PHP commands in a synchronous way
    public function run(string $cmd)
    {
        $escaped_command = escapeshellcmd($cmd);

        if(!$cmd){
            throw new \Exception("No command to run!");
        }

        $cmdToRun = $this->getPHPPath(). " ".$escaped_command;

        return shell_exec($cmdToRun);
    }

    // This method is overriden from the parent class to adapt only PHP commands in an asynchronous way
    public function runAsync(string $cmd)
    {
        $escaped_command = escapeshellcmd($cmd);

        if(!$cmd){
            throw new \Exception("No command to run!");
        }

        $cmdToRun = $this->getPHPPath(). " ".$escaped_command;

        if ($this->isWindows()) {
            shell_exec($cmdToRun . " > NUL");
        }
        else {
            shell_exec($cmdToRun . " >/dev/null 2>&1 &");
        }
    }

}
