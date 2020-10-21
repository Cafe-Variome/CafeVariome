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
    private $fileStack;
    private $removeDuplicateFileOnUpload = true;
    protected $basePath;
    protected $files;
    protected $handle;
    private $mode;

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

    public function Read(string $path, int $length = -1)
    {
        $readMode = 'r';

        if (!$this->handle || $this->mode != $readMode) {
            $this->getHandle($path);
        }
        $buffer = ($length != -1) ? fgets($this->handle, $length) : fgets($this->handle);

        return $buffer;
    }

    public function Write(string $path, string $content, bool $append = false, int $length = -1): int
    {
        $writeOrAppend = $append ? 'a+' : 'w+';

        if (!$this->handle || $this->mode != $writeOrAppend || !$append) {
            $this->getHandle($path, $writeOrAppend);
        }

        $bytesWritten = ($length != -1) ? fwrite($this->handle, $content, $length) : fwrite($this->handle, $content);

        return $bytesWritten;
    }

    private function getHandle(string $path, string $mode = 'r')
    {
        $this->handle = fopen($this->getFullPath() . $path, $mode);
        $this->mode = $mode;
    }
 }
 