<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * IFileMan.php 
 * Created: 23/01/2020
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * File Manager Interface 
 */

interface IFileMan {

    public function Save();

    public function Exists(string $path): bool;
    public function Delete(string $path);


}