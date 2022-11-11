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
    protected string $basePath;
    protected array $files;
    private array $extension_filter;

    public function __construct(string $basePath = null, bool $recursive = false, array $extension_filter = [], bool $useDiskName = false, $diskNameLength = 16)
	{
        parent::__construct($basePath);
		$this->useDiskName = $useDiskName;
		$this->diskNameLength = $diskNameLength;
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
        if(!$recursive)
		{
            if ($this->Exists($path))
			{
                if (is_dir($this->getFullPath() . $path))
				{
					$dir_content = @scandir($this->getFullPath() . $path);
					for($c = 0; $c < count($dir_content); $c++)
					{
						if ($dir_content[$c] == '.' || $dir_content[$c] == '..') continue;

						if(
							count($this->extension_filter) > 0 &&
							in_array($this->getExtension($dir_content[$c]), $this->extension_filter)
						)
						{
							$path_to_file = $path . DIRECTORY_SEPARATOR;
							$this->files[] = new File(
								$dir_content[$c],
								$this->getSize($path_to_file),
								$this->getFullPath() . $path_to_file,
								$this->getMimeType($path_to_file),
								0,
								$this->useDiskName,
								$this->diskNameLength
							);
						}
					}

                }
            }
        }
        else
		{
            $cdir = @scandir($this->getFullPath() . $path);
            if (is_array($cdir))
			{
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
                            if ($dir != null && count($this->extension_filter) > 0 && in_array($this->getExtension($dir), $this->extension_filter))
							{
                                $path_to_file = $path . DIRECTORY_SEPARATOR . $dir;
                                $f = new File(
									$dir,
									$this->getSize($path_to_file),
									$this->getFullPath() . $path_to_file,
									$this->getMimeType($path_to_file),
									0,
									$this->useDiskName,
									$this->diskNameLength
								);
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
        if (count($extension_filter) > 0)
		{
            $this->extension_filter = $this->toLowerCase($extension_filter);
        }

        if ($path != null)
		{
			$this->files = [];
            $this->loadFiles($path, $recursive);
        }

        return $this->files;
    }

    public function Save(File $file, string $path = '') : bool
    {
		$file_name_to_write = $this->useDiskName ? $file->getDiskName() : basename($file->getName());

		return copy($file->getTempPath(), $this->getFullPath() . $path . $file_name_to_write);
    }
 }
