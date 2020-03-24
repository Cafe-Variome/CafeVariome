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

    private $basePath = UPLOAD; // As set in Constants.php
    private $files;

    public function __construct(string $basePath = null) {
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

    private function getFullPath()
    {
        return $this->basePath;
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

    public function CreateDirectory(string $path, $mode = 777)
    {
        mkdir($this->getFullPath() . $path);
    }

    public function Exists(string $path): bool
    {
        return file_exists($this->getFullPath() . $path);
    }

    public function Delete(string $path)
    {
        unlink($this->getFullPath() . $path);
    }

    public function countFiles(): int
    {
        if ($this->files != null) {
            return count($this->files);
        }
        return 0;
    }

    public function getFileStack()
    {
        return ($this->fileStack != null ? $this->fileStack : null);
    }

    public function getFiles()
    {
        return $this->files;
    }
}
 