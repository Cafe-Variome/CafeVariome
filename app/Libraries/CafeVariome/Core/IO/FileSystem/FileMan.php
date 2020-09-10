<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * FileMan.php 
 * Created: 13/08/2020
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * File Manager Class 
 */

 class FileMan implements IFileMan
 {
    protected $basePath;
    protected $files;

    public function __construct(string $basePath)
    {
        if ($basePath != null) {
            $this->basePath = $basePath;
        }
    }

    public function CreateDirectory(string $path, $mode = 777)
    {
        mkdir($this->getFullPath() . $path);
    }

    public function Delete(string $path)
    {
        unlink($this->getFullPath() . $path);
    }

    public function Exists(string $path): bool
    {
        return file_exists($this->getFullPath() . $path);
    }

    public function countFiles(): int
    {
        if ($this->files != null) {
            return count($this->files);
        }
        return 0;
    }

    protected function getFullPath()
    {
        return $this->basePath;
    }
 }
 
