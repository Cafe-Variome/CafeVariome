<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * SysFileMan.php 
 * Created: 30/09/2020
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * System File Manager Class 
 */

 class SysFileMan extends FileMan
 {
    protected $basePath;
    protected $files;

    public function __construct(string $basePath = null) {
        parent::__construct($basePath);

        $this->files = [];
        $this->loadFiles();
    }

    private function loadFiles(string $path = null)
    {
        $this->files = scandir($this->getFullPath() . $path);
    }

    public function getFiles(string $path = null): array
    {
        if (count($this->files) == 0 || $path != null) {
            $this->loadFiles($path);
        }
        return $this->files;
    }
 }
 