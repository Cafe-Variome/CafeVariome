<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * File.php
 * Created: 29/01/2020
 * @author Mehdi Mehtarizadeh
 *
 */

class File
{
    private string $name;

	private string $diskName;

    private string $extension;

    private float $size;

    public function __construct(string $name, float $size, string $tempPath, string $type, int $error) {
        $this->name = preg_replace('/\s+/', '_', $name);
        $this->extension = $this->findExtension();
        $this->size = $size;
        $this->tempPath = $tempPath;
        $this->type = $type;
        $this->error = $error;

    }

    private function findExtension(): string
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

    public function getSize(): float
	{
        return $this->size;
    }

    public function getTempPath(): string
	{
        return $this->tempPath;
    }

    public function getType(): string
	{
        return $this->type;
    }
}
