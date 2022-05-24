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
    private $fileStack;
    private $removeDuplicateFileOnUpload = true;
    protected string $basePath;
    protected array $files;

	private int $diskNameLength;

    public function __construct(string $basePath = null) {
        parent::__construct($basePath);
        if ($basePath == null) {
            $this->basePath = UPLOAD; // As set in Constants.php
        }
        else{
            $this->basePath = $basePath;
        }
        $this->fileStack = $_FILES;
        $this->files = [];
        $this->loadFiles();
    }

    public function loadFiles()
    {
        foreach ($this->fileStack as $fileSetKey => $fileSet) {
            if (is_countable($fileSet['name'])) { // We have more than one file
                for ($i=0; $i < count($fileSet['name']); $i++) {
                    $f = new File($fileSet['name'][$i], $fileSet['size'][$i], $fileSet['tmp_name'][$i], $fileSet['type'][$i], $fileSet['error'][$i]);
                    array_push($this->files, $f);
                }
            }
            else { // We have one file
                $f = new File($fileSet['name'], $fileSet['size'], $fileSet['tmp_name'], $fileSet['type'], $fileSet['error']);
                array_push($this->files, $f);
            }

        }
    }

    public function Save(File $file, string $path = '') : bool
    {
        return move_uploaded_file($file->getTempPath(), $this->getFullPath() . $path . basename($file->getName()));
    }

    public function SaveAll()
    {
        foreach ($this->fileStack as $tempKey => $tempFile) {
            if ($tempFile['error'] == 0) {
                $tmp_name = $tempFile['tmp_name'];
                $name = basename($tempFile["name"]);
                if ($this->Exists($name)) {
                    $this->Delete($name);
                }
                move_uploaded_file($tmp_name, $this->getFullPath() . $name);
            }
        }
    }

    public function getFileStack()
    {
        return ($this->fileStack != null ? $this->fileStack : null);
    }

    public function getFiles()
    {
        return $this->files;
    }

	public static function getMaximumAllowedUploadSize(): string
	{
		$max_size = -1;
		if ($max_size < 0) {
			$post_max_size = ini_get('post_max_size');
			if ($post_max_size > 0) {
				$max_size = $post_max_size;
			}

			$upload_max = ini_get('upload_max_filesize');
			if ($upload_max > 0 && $upload_max < $max_size) {
				$max_size = $upload_max;
			}
		}
		return $max_size;
	}

	public static function parseSizeToByte(string $size) : float
	{
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
		if ($unit) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		}
		else {
			return round($size);
		}
	}
}
