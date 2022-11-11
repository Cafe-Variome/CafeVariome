<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * FileMan.php
 * Created: 13/08/2020
 *
 * @author Mehdi Mehtarizadeh
 * @author Farid Yavari Dizjikan
 *
 * File Manager Class
 */

class FileMan implements IFileMan
{
    protected string $basePath;

    protected array $files;

    protected $handle;

    protected string $filePath;

    private string $mode;

    public function __construct(string $basePath)
    {
        if ($basePath != null)
		{
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

    public function Exists(string $path = ''): bool
    {
        return file_exists($this->getFullPath() . $path);
    }

    public function GetModificationTimeStamp(string $path): int
    {
        return $this->Exists($path) ? filemtime($this->getFullPath() . $path) : -1;
    }

    public function Read(string $path, int $length = -1, $isRelative = true)
    {
        $readMode = 'r';

        if (!$this->handle || $this->mode != $readMode || $this->filePath != $path)
		{
            $this->getHandle($path, $readMode, $isRelative);
        }

		return ($length != -1) ? fread($this->handle, $length) : fread($this->handle, $this->getSize($path, $isRelative));
    }

    public function ReadCSV(string $path, int $length = -1, string $delimiter = ',', string $enclosure = '"', string $escape = '\\')
    {
        $readMode = 'r';

        if (!$this->handle || $this->mode != $readMode || $this->filePath != $path)
		{
            $this->getHandle($path);
        }

		return ($length != -1) ? fgetcsv($this->handle, $length, $delimiter, $enclosure, $escape) : fgetcsv($this->handle, $this->getSize($path), $delimiter, $enclosure, $escape);
    }

    public function ReadLine(string $path, int $length = -1, $isRelative = true)
    {
        $readMode = 'r';

        if (!$this->handle || $this->mode != $readMode || $this->filePath != $path)
		{
            $this->getHandle($path, $readMode, $isRelative);
        }

		return ($length != -1) ? fgets($this->handle, $length) : fgets($this->handle);
    }

    public function Write(string $path, string $content, bool $append = false, int $length = -1): int
    {
        $writeOrAppend = $append ? 'a+' : 'w+';

        if (!$this->handle || $this->mode != $writeOrAppend || !$append)
		{
            $this->getHandle($path, $writeOrAppend);
        }

		return ($length != -1) ? fwrite($this->handle, $content, $length) : fwrite($this->handle, $content);
    }

    protected function getHandle(string $path, string $mode = 'r', bool $isRelative = true)
    {
        if ($this->handle)
		{
            $this->destroyHandle();
        }
        $absPath = $isRelative ? $this->getFullPath() . $path : $path;
        $this->handle = fopen($absPath, $mode);
        $this->mode = $mode;
        $this->filePath = $path;
    }

    protected function destroyHandle(): bool
    {
		$closed = fclose($this->handle);
		$this->handle = null;
        return $closed;
    }

    public function countFiles(): int
    {
		return count($this->files);
    }

    protected function getFullPath(): string
    {
        return $this->basePath;
    }

    /**
     * isValid(File $path)
     * @param File : File object to be valiadated
     * @return bool
     *
     * This function validates uploaded files before they are moved to software directories.
     * Currently, XLSX, XLS, VCF, and CSV files are supported.
     */
	 public function isValid(File $file, string & $error): bool
	 {
		 $fExt = strtolower($file->getExtension());
		 $path = $file->getTempPath();
		 if ($fExt === 'csv')
		 {
			 $this->getHandle($path, 'r', false);

			 $heading = $this->ReadCSV($path, 0);
			 $columnCount = count($heading);
			 $i = 1;
			 while (($line = $this->ReadCSV($path, 0)) !== false )
			 {
				 if($columnCount != count($line))
				 {
					 $error = "Number of cells do not match number of columns at line: $i";
					 return false;
				 }
				 $i++;
			 }

            return true;
        }
        elseif ($fExt === 'phenopacket' || $fExt === 'json')
		{
            $this->getHandle($path, 'r', false);

            $fcontent = $this->Read($path, -1, false);

            if( json_decode($fcontent) != null)
			{
				return true;
			}
			else
			{
				$error = 'File is not in a JSON format.';
				return false;
			}
        }
        else if($fExt === 'xls' || $fExt === 'xlsx' || $fExt === 'vcf')
		{
            // Signatures of allowed files as follows: XLSX, XLS and VCF
            $allowed = array('504B0304',         // XLSX File signature
                            '504B030414000600',  // XLSX File signature
                            'D0CF11E0A1B11AE1',  // XLS File signature
                            '0908100000060500',  // XLS File signature
                            'FDFFFFFF10',        // XLS File signature
                            'FDFFFFFF1F',        // XLS File signature
                            'FDFFFFFF22',        // XLS File signature
                            'FDFFFFFF23',        // XLS File signature
                            'FDFFFFFF28',        // XLS File signature
                            'FDFFFFFF29',        // XLS File signature
                            '424547494E3A5643'   // VCF File signature
                            );

            for($j = 0; $j < count($allowed); $j++)
			{
                if(strlen($allowed[$j]) == 8)
				{
                    $this->getHandle($path, 'r', false);
                    $bytes = strtoupper($this->bin2Hex($this->Read($path, 4)));
                    if(in_array($bytes, $allowed))
					{
						$this->destroyHandle();
						return true;
                    }
                }
				else if (strlen($allowed[$j]) == 10)
				{
                    $this->getHandle($path, 'r', false);
                    $bytes = strtoupper($this->bin2Hex($this->Read($path, 5)));
                    if(in_array($bytes, $allowed))
					{
						$this->destroyHandle();
						return true;
                    }
                }
				else if (strlen($allowed[$j]) == 16)
				{
                    $this->getHandle($path, 'r', false);
                    $bytes = strtoupper($this->bin2Hex($this->Read($path, 8)));
                    if(in_array($bytes, $allowed))
					{
						$this->destroyHandle();
						return true;
                    }
                }
            }

			$error = 'File signature is not valid.';
		}

        return false;
    }


    public function getSize(string $path, bool $isRelative = true): int
    {
        clearstatcache();
        return filesize(($isRelative ? $this->getFullPath() : "") . $path);
    }

    protected function bin2Hex(string $bin): string
    {
        return bin2hex($bin);
    }

    public function getExtension(string $path): string
	{
        $file_name = $path;
        if (str_contains($path, DIRECTORY_SEPARATOR))
		{
            $path_array = explode(DIRECTORY_SEPARATOR, $path);
            $file_name = $path_array[count($path_array) - 1];
        }

        if (str_contains($file_name, '.'))
		{
            $file_name_array = explode('.', $file_name);
            return $file_name_array[count($file_name_array) - 1];
        }

        return '';
    }

    public function getMimeType(string $path = ''): string
    {
		$mimetype = '';
        $guessedMime = mime_content_type($this->getFullPath() . $path);

        if ($guessedMime != false)
		{
			$mimetype =  $guessedMime;
        }

        return $mimetype;
    }

	public static function IsFile(string $path):bool
	{
		return is_file($path);
	}

	public static function GetFileName(string $path): string
	{
		return pathinfo($path)['basename'];
	}

	public static function GetFileExtension(string $path): string
	{
		$pathInfo = pathinfo($path);
	 	return array_key_exists('extension', $pathInfo) ? $pathInfo['extension'] : '';
	}

	public static function GetFileSize(string $path): int
	{
		 clearstatcache();
		 return filesize($path);
	}

	public static function GetFileMimeType(string $path): string
	{
		$mimetype = '';
		$guessedMime = mime_content_type($path);

		if ($guessedMime != false)
		{
			$mimetype = $guessedMime;
		}

		return $mimetype;
	}

	public function GetImageSize(string $path)
	{
		return getimagesize($this->getFullPath() . $path);
	}

	public function ResizeImage(string $data, int $width, ?int $height)
	{
		$sourceImage = imagecreatefromstring($data);
		if ($sourceImage != false)
		{
			$sourceWidth = imagesx($sourceImage);
			$sourceHeight = imagesy($sourceImage);

			if (is_null($height))
			{
				$height = ($sourceHeight/$sourceWidth) * $width;
			}
			$destinationImage = imagecreatetruecolor($width, $height);
			imagecopyresized($destinationImage, $sourceImage,  0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

			$background = imagecolorallocate($destinationImage , 0, 0, 0);
			imagecolortransparent($destinationImage, $background);

			imagealphablending($destinationImage, false);

			//imagesavealpha($destinationImage, true);

			imagepng($destinationImage, null, 0);

			imagedestroy($destinationImage);
		}
	}
}
