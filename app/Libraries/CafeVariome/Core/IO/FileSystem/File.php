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

    private int $error;

    private string $type;

    private string $tempPath;

	private int $diskNameLength;

    public function __construct(
		string $name,
		float $size,
		string $tempPath,
		string $type,
		int $error,
		bool $useDiskName = false,
		int $diskNameLength = 32
	)
	{
        $this->name = preg_replace('/\s+/', '_', $name);
        $this->extension = $this->findExtension();
        $this->size = $size;
        $this->tempPath = $tempPath;
        $this->type = $type;
        $this->error = $error;

		if ($useDiskName)
		{
			$this->diskNameLength = $diskNameLength;
			$this->diskName = $this->generateDiskName() . '.' . $this->extension;
		}
    }

    private function findExtension(): string
	{
        $fn = $this->name;

        if (strpos($fn, '.') != false)
		{
            $fnArr = explode('.', $fn);
            return $fnArr[count($fnArr) - 1];
        }
        return '';
    }

    public function getName()
	{
        return $this->name;
    }

	public function getDiskName(): string
	{
		return $this->diskName;
	}

    public function getExtension(): string
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

	/**
	 * @throws \Exception
	 */
	private function generateDiskName(): string
	{
		return bin2hex(random_bytes((int)$this->diskNameLength/2));
	}
}
