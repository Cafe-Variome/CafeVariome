<?php namespace App\Controllers;

/**
 * Elastic.php
 * 
 * Created 08/08/2019
 * 
 * @author Mehdi Mehtarizadeh
 * 
 * This controller makes it possible for users to contact elastic search server.
 */

 
use App\Models\UIData;
use App\Models\Settings;
use App\Models\Source;

use CodeIgniter\Config\Services;

class Elatsic extends CVUI_Controller{

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);
    }
	public function index()
	{
		$udata = new UIData();
		$udata->title = "Home";
		$data = $this->wrapData($udata);

		var_dump(shell_exec("php " . getcwd() . "/index.php Task phenoPacketInsert 7"));
		exit;
		echo view('home/index', $data);
	}
    public function Status(){
        $uidata = new UIData();
        $uidata->data['elastic_update'] = $sourceModel->getSourceElasticStatus();
        $uidata->data['isRunning'] = $this->checkElasticSearch();

        // Check the status of maintenance cron job file, if it's empty then cron job won't run
        if (file_exists(FCPATH . '/resources/cron/crontab')) {
            if (filesize(FCPATH . '/resources/cron/crontab') != 0) {
                $uidata->data['isCronEnabled'] = TRUE;
            }
        }


        $data = $this->wrapData($uidata);
        return view('Elastic/status', $data);
    }

    function checkElasticSearch() {
        $hosts = (array)$this->setting->settingData['elastic_url'];
        $client = Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    
        try {
            $indices = $client->cat()->indices(array('index' => '*'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}