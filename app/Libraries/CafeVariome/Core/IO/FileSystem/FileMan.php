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
    protected $basePath;
    protected $files;
    protected $handle;
    protected $filePath;
    private $mode;

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

        if (!$this->handle || $this->mode != $readMode || $this->filePath != $path) {
            $this->getHandle($path, $readMode, $isRelative);
        }
        $buffer = ($length != -1) ? fread($this->handle, $length) : fread($this->handle, $this->getSize($path, $isRelative));

        return $buffer;
    }

    public function ReadCSV(string $path, int $length = -1, string $delimiter = ',', string $enclosure = '"', string $escape = '\\')
    {
        $readMode = 'r';

        if (!$this->handle || $this->mode != $readMode || $this->filePath != $path) {
            $this->getHandle($path);
        }
        $line = ($length != -1) ? fgetcsv($this->handle, $length, $delimiter, $enclosure, $escape) : fgetcsv($this->handle, $this->getSize($path), $delimiter, $enclosure, $escape);

        return $line;
    }

    public function ReadLine(string $path, int $length = -1, $isRelative = true)
    {
        $readMode = 'r';

        if (!$this->handle || $this->mode != $readMode || $this->filePath != $path) {
            $this->getHandle($path, $readMode, $isRelative);
        }
        $line = ($length != -1) ? fgets($this->handle, $length) : fgets($this->handle);

        return $line;
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

    protected function getHandle(string $path, string $mode = 'r', bool $isRelative = true)
    {
        if ($this->handle) {
            $this->destroyHandle();
        }
        $absPath = $isRelative ? $this->getFullPath() . $path : $path;
        $this->handle = fopen($absPath, $mode);
        $this->mode = $mode;
        $this->filePath = $path;
    }

    protected function destroyHandle(): bool
    {
        return fclose($this->handle);
    }

    protected function setFilePath($path)
    {
        $this->filePath = $path;
    }

    public function countFiles(): int
    {
        if ($this->files != null) {
            return count($this->files);
        }
        return 0;
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

    public function isValid(File $file): bool
    {
        $fExt = strtolower($file->getExtension());
        $path = $file->getTempPath();
        if ($fExt === 'csv') {
            $this->getHandle($path, 'r', false);

            $heading = $this->ReadCSV($path, 0);
            $columnCount = count($heading);
            while (($line = $this->ReadCSV($path, 0)) !== false ) {
                if($columnCount != count($line)){
                    return false;
                }
            }
            //$this->destroyHandle();
            return true;
        }
        elseif ($fExt === 'phenopacket' || $fExt === 'json') {
            $this->getHandle($path, 'r', false);

            $fcontent = $this->Read($path, -1, false);

            return json_decode($fcontent) != null; 
        }
        else if($fExt === 'xls' || $fExt === 'xlsx' || $fExt === 'vcf'){
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

            for($j = 0; $j < count($allowed); $j++){
                if(strlen($allowed[$j]) == 8){
                    $this->getHandle($path, 'r', false);
                    $bytes = strtoupper($this->bin2Hex($this->Read($path, 4)));
                    $this->destroyHandle();
                    if(in_array($bytes, $allowed)){
                        return true;
                    }
                } else if (strlen($allowed[$j]) == 10) {
                    $this->getHandle($path, 'r', false);
                    $bytes = strtoupper($this->bin2Hex($this->Read($path, 5)));
                    $this->destroyHandle();
                    if(in_array($bytes, $allowed)){
                        return true;
                    }
                } else if (strlen($allowed[$j]) == 16) {
                    $this->getHandle($path, 'r', false);
                    $bytes = strtoupper($this->bin2Hex($this->Read($path, 8)));
                    $this->destroyHandle();
                    if(in_array($bytes, $allowed)){
                        return true;
                    }
                }
            }
        }

        return false;
    }


    protected function getSize(string $path, bool $isRelative = true): int
    {
        clearstatcache();
        return filesize(($isRelative ? $this->getFullPath() : "") . $path);
    }

    protected function bin2Hex(string $bin): string
    {
        return bin2hex($bin);
    }

    public function getExtension(string $path = '')
    {
        $file_name = $path;
        if (strpos($path, DIRECTORY_SEPARATOR)) {
            $path_array = explode(DIRECTORY_SEPARATOR, $path);
            $file_name = $path_array[count($path_array) - 1];
        }

        $extension = $file_name;
        if (strpos($file_name, '.')) {
            $file_name_array = explode('.', $file_name);
            $extension = $file_name_array[count($file_name_array) - 1];
        }

        return strtolower($extension);
    }

    public function getMimeType(string $path = ''): string
    {
        $mimetype = mime_content_type($this->getFullPath() . $path);

        if ($mimetype != false) {
            return $mimetype;
        }

        return '';
    }
 }