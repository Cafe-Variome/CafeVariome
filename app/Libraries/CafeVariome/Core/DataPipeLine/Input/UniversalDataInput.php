<?php namespace App\Libraries\CafeVariome\Core\DataPipeLine\Input;

/**
 * Name EAVDataInput.php
 *
 * Created 19/08/2020
 * @author Samuel Balco
 * @author Mehdi Mehtarizadeh
 * @author Gregory Warren
 * @author Farid Yavari Dizjikan
 *
 */

use App\Libraries\CafeVariome\Helpers\Shell\UniversalUploaderShellHelper;

class UniversalDataInput extends DataInput
{
    private $delete;
    private $universalUploaderShellHelperInstance;
    private $filePath;
    private $settingFile;

    public function __construct(int $source_id, int $delete, string $setting_file) {
        parent::__construct($source_id);
        $this->delete = $delete;
        $this->settingFile = $setting_file;
        $this->universalUploaderShellHelperInstance = new UniversalUploaderShellHelper();
    }

    public function absorb(int $file_id){

        $files = $this->getSourceFiles($file_id); //Get a list of files for source
        foreach ($files as $key => $fname) {
            $fileId = $file_id != -1 ? $file_id : $key;
            $file = $fname['FileName'];

            if ($this->fileMan->Exists($file)) {
                $this->uploadModel->clearErrorForFile($fileId);

                if ($this->delete == 1) {
                    $this->eavModel->deleteRecordsBySourceId($this->sourceId);
                }

                $this->filePath = $this->basePath . $file;
            }
        }

    }

    public function save(int $file_id){
        $dbConfig = config('Database')->default;
        $mysql_host = $dbConfig['hostname'];
        $mysql_username = $dbConfig['username'];
        $mysql_password = $dbConfig['password'];
        $mysql_database = $dbConfig['database'];
        $mysql_port = $dbConfig['port'];

        $cmd = getcwd() . DIRECTORY_SEPARATOR . CV_CONVERT_BIN . ' -s ' . getcwd() . DIRECTORY_SEPARATOR . CV_CONVERT_SETTINGS_DIR . "'$this->settingFile' -i '$this->filePath' --source-id  $this->sourceId --log db -o db --db-config mysql://$mysql_username:$mysql_password@$mysql_host:$mysql_port/$mysql_database";
        $this->universalUploaderShellHelperInstance->runAsync($cmd);
        $this->uploadModel->markEndOfUpload($file_id, $this->sourceId);
    }
}
