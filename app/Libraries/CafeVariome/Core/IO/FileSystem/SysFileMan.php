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
    protected $basePath;
    protected $files;
    private $extension_filter;

    public function __construct(string $basePath = null, bool $recursive = false, array $extension_filter = []) {
        parent::__construct($basePath);

        $this->files = [];
        $this->extension_filter = $this->toLowerCase($extension_filter);
        $this->loadFiles('', $recursive);
    }

    private function toLowerCase(array $arr): array
    {
        $new_arr = [];
        foreach ($arr as $element) {
            $new_arr[] = strtolower($element);
        }
        return $new_arr;
    }

    private function loadFiles(string $path = '', bool $recursive = false)
    {
        if(!$recursive){
            if ($this->Exists($path)) {
                if (is_dir($this->getFullPath() . $path)) {
                    $this->files = scandir($this->getFullPath() . $path);
                }
            }
        }
        else{
            $cdir = @scandir($this->getFullPath() . $path);
            if (is_array($cdir)) {

                foreach ($cdir as $dir)
                {
                    if (!in_array($dir, [".",".."]))
                    {
                        if (is_dir($this->getFullPath() . $path . DIRECTORY_SEPARATOR . $dir))
                        {
                            //Another directory that needs to be explored
                            $this->loadFiles($path . DIRECTORY_SEPARATOR . $dir, true);
                        }
                        else
                        {
                            //List of files
                            if ($dir != null && count($this->extension_filter) > 0 && in_array($this->getExtension($dir), $this->extension_filter)) {
                                $path_to_file = $path . DIRECTORY_SEPARATOR . $dir;
                                $f = new File($dir, $this->getSize($path_to_file), $this->getFullPath() . $path_to_file, $this->getMimeType($path_to_file), 0);
                                $this->files[] = $f;
                            }
                        }
                    }
                }
            }
        }
    }

    public function getFiles(string $path = '', bool $recursive = false, array $extension_filter = []): array
    {
        if (count($extension_filter) > 0) {
            $this->extension_filter = $this->toLowerCase($extension_filter);
        }

        if (count($this->files) == 0 || $path != null) {
            $this->loadFiles($path, $recursive);
        }

        return $this->files;
    }

    public function Save(File $file, string $path = '') : bool
    {
        return copy($file->getTempPath(), $this->getFullPath() . $path . basename($file->getName()));
    }
 }
 