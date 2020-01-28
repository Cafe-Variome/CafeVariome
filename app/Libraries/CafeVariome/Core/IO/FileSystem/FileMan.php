<?php namespace App\Libraries\CafeVariome\Core\IO\FileSystem;

/**
 * FileMan.php 
 * Created: 23/01/2020
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * File Manager Class 
 */


class FileMan implements IFileMan
{
    private $fileStack;

    private $removeDuplicateFileOnUpload = true;

    private $baseUploadPath = UPLOAD;

    private $uploadPath;

    public function __construct(string $path) {
        $this->uploadPath = $path;
        $this->fileStack = $_FILES;
    }

    private function getFullUploadPath()
    {
        return $this->baseUploadPath . DIRECTORY_SEPARATOR .  $this->uploadPath;
    }

    public function Save()
    {
        foreach ($this->fileStack as $tempKey => $tempFile) {
            if ($tempFile['error'] == 0) {
                $tmp_name = $tempFile['tmp_name'];
                $name = basename($tempFile["name"]);
                if ($this->Exists($this->getFullUploadPath() . DIRECTORY_SEPARATOR . $name)) {
                    $this->Delete($this->getFullUploadPath() . DIRECTORY_SEPARATOR . $name);
                }
                move_uploaded_file($tmp_name, $this->getFullUploadPath() . DIRECTORY_SEPARATOR . $name);
            }
        }
    }

    public function Exists(string $path): bool
    {
        return file_exists($path);
    }

    public function Delete(string $path)
    {
        unlink($path);
    }
}
 