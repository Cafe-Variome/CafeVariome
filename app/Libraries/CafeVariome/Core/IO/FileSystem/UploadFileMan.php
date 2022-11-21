<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * UploadFileMan.php
 * Created: 23/01/2020
 *
 * @author Mehdi Mehtarizadeh
 *
 * File Manager Class
 */

class UploadFileMan extends FileMan
{
    protected string $basePath;
    protected array $files;

	private int $diskNameLength;

	private bool $useDiskName;

    public function __construct(?string $basePath = null, bool $useDiskName = false, $diskNameLength = 16)
	{
        if (is_null($basePath))
		{
			$basePath = UPLOAD; // As set in Constants.php
        }
		parent::__construct($basePath);

		$this->useDiskName = $useDiskName;
		$this->diskNameLength = $diskNameLength;
        $this->loadFiles($_FILES);
    }

    private function loadFiles(array $files)
    {
        foreach ($files as $fileSetKey => $fileSet)
		{
            if (is_countable($fileSet['name']))
			{
				// We have more than one file
                for ($i=0; $i < count($fileSet['name']); $i++)
				{
					if ($fileSet['error'][$i] == UPLOAD_ERR_OK)
					{
						$f = new File(
							$fileSet['name'][$i],
							$fileSet['size'][$i],
							$fileSet['tmp_name'][$i],
							$fileSet['type'][$i],
							$fileSet['error'][$i],
							$this->useDiskName,
							$this->diskNameLength
						);
						array_push($this->files, $f);
					}
                }
            }
            else
			{
				// We have one file
				if ($fileSet['error'] == UPLOAD_ERR_OK)
				{
					$f = new File(
						$fileSet['name'],
						$fileSet['size'],
						$fileSet['tmp_name'],
						$fileSet['type'],
						$fileSet['error'],
						$this->useDiskName,
						$this->diskNameLength
					);
					array_push($this->files, $f);
				}
            }
        }
    }

    public function Save(File $file, string $path = '') : bool
    {
		$file_name_to_write = $this->useDiskName ? $file->getDiskName() : basename($file->getName());

        return move_uploaded_file($file->getTempPath(), $this->getFullPath() . $path . DIRECTORY_SEPARATOR . $file_name_to_write);
    }

    public function getFiles(): array
    {
        return $this->files;
    }

	public static function getMaximumAllowedUploadSize(): string
	{
		$max_size = -1;
		$post_max_size = ini_get('post_max_size');
		if ($post_max_size > 0)
		{
			$max_size = $post_max_size;
		}

		$upload_max = ini_get('upload_max_filesize');
		if ($upload_max > 0 && $upload_max < $max_size)
		{
			$max_size = $upload_max;
		}

		return $max_size;
	}

	public static function parseSizeToByte(string $size) : float
	{
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
		if ($unit)
		{
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}
		else
		{
			return round($size);
		}
	}

	public static function GetAllowedDataFileFormats(bool $array = true): array | string
	{
		$allowedFormats = ['csv', 'xls', 'xlsx', 'phenopacket'];

		if($array)
		{
			return $allowedFormats;
		}
		else
		{
			return implode(',', $allowedFormats);
		}
	}
}
