<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * File.php
 * Created: 29/01/2020
 * @author Mehdi Mehtarizadeh
 * 
 */

class File
{
    private $name;
    private $extension;
    private $size;
    private $error;
    private $type;
    private $tempPath;

    public function __construct(string $name, float $size, string $tempPath, string $type, int $error) {
        $this->name = preg_replace('/\s+/', '_', $name);
        $this->extension = $this->findExtension();
        $this->size = $size;
        $this->tempPath = $tempPath;
        $this->type = $type;
        $this->error = $error;

    }

    private function findExtension()
    {
        $fn = $this->name;

        if (strpos($fn, '.') != false) {
            $fnArr = explode('.', $fn);
            return $fnArr[count($fnArr) - 1]; 
        }
        return '';
    }

    public function getName()
    {
        return $this->name;
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getTempPath()
    {
        return $this->tempPath;
    }

    public function getType()
    {
        return $this->type;
    }
}
