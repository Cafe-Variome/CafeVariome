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
use App\Libraries\ElasticSearch;
use App\Libraries\CafeVariome\ShellHelper;
use CodeIgniter\Config\Services;

class Elastic extends CVUI_Controller{

    private $shellHelperInstance;

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

        $this->shellHelperInstance = new ShellHelper();
    }

    public function Status(){
        $uidata = new UIData();
        $uidata->title = "Index Status";

        $elasticSearch = new ElasticSearch((array)$this->setting->getElasticSearchUri());
        $elasticModel = new \App\Models\Elastic($this->db);
        $sourceModel = new Source($this->db);
        $sources = $sourceModel->getSources('source_id, name, elastic_status');
        //ping elasticsearch
        $uidata->data['isRunning'] = $elasticSearch->ping();

        $title_prefix = $elasticModel->getTitlePrefix();
        
        for ($i=0; $i < count($sources); $i++) { 
            if($elasticSearch->indexExists($title_prefix . "_" .$sources[$i]['source_id']) != null){
                $sources[$i]['elastic_index'] = true;
            }
            else {
                $sources[$i]['elastic_index'] = false;
            }
        }

        $uidata->data['elastic_update'] = $sources;

        $title = $this->setting->settingData["site_title"];
        $host = strtolower(preg_replace("/\s.+/", '', $title)); 
        $uidata->data['host'] = $host;

        $uidata->javascript = [JS."cafevariome/elastic.js", JS."/bootstrap-notify.js"];

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory.'/Status', $data);
    }


    /**
     * Elastic Check - Checking function prior to update to determine type of update desired and whether it is needed.
     *
     * @param int $force     - Are we forcing the regnerate? 1 if so and 0 if not
     * @param int $id        - The source id for the elasticsearch index
     * @param int $add       - 1 if we are adding to index instead of fully regenerating
     * @return array $result - Various parameters to allow front end decision
     */
    public function elastic_check() {	   
            
        $elasticModel = new \App\Models\Elastic($this->db); 

        $data = json_decode($_POST['u_data']);
        $force = $data->force;
        $source_id = $data->id;
        $add = $data->add;

        $unprocessedFilesCount = $elasticModel->getUnprocessedFilesForSource($source_id);
        error_log($unprocessedFilesCount);
        if (!$unprocessedFilesCount) {
            $result = ['Status' => 'Empty'];
            echo json_encode($result);
            return;
        }
        if ($add) {
            $unaddedEAVsCount = $elasticModel->getUnaddedEAVs($source_id);
            error_log("add");
            error_log($unaddedEAVsCount);
            if (!$unaddedEAVsCount) {
                $result = ['Status' => 'Fully Updated'];
                echo json_encode($result);
                return;
            }
            else {
                $time = $unaddedEAVsCount/2786;
                $result = ['Status' => 'Success','Time'=> $time];
                echo json_encode($result);
            }
        }
        else {
            if ($force) {
                $count = $elasticModel->getEAVsCountForSource($source_id);
                $time = $count/2786;
                $result = ['Status' => 'Success','Time'=> $time];
                echo json_encode($result);

            }
            else {
                $result = ['Status' => 'Fully Updated'];
                echo json_encode($result);
            }
        } 	
    }


    /**
     * Elastic Start - Begin ElasticSearch regeneration
     *
     * @param int $force     - Are we forcing the regnerate? 1 if so and 0 if not
     * @param int $id        - The source id for the elasticsearch index
     * @param int $add       - 1 if we are adding to index instead of fully regenerating
     * @return N/A
     */
    public function elastic_start() {
        $elasticModel = new \App\Models\Elastic($this->db); 

        $data = json_decode($_POST['u_data']);
        $force = $data->force;
        $source_id = $data->id;
        $add = (int)$data->add;

        if ($force) {
            // if the regenerate was forced set the elastic state for all eav data rows
            error_log("as its forced we are setting elastic to 0");
            $unprocessedFilesCount = $elasticModel->getUnprocessedFilesForSource($source_id);
            error_log("pre: ".$unprocessedFilesCount);
            $elasticModel->resetElasticFlagForSourceEAVs($source_id);
            $unprocessedFilesCount = $elasticModel->getUnprocessedFilesForSource($source_id);
            error_log("post: ".$unprocessedFilesCount);
        }
        
        // rebuild the json list for interface
        //$r = $elasticModel->regenerateFederatedPhenotypeAttributeValueList($source_id);
        $this->shellHelperInstance->runAsync(getcwd() . "/index.php Task regenerateFederatedPhenotypeAttributeValueList $source_id $add");
        //$task = New Task();
        //$res = $task->regenerateElasticsearchIndex($source_id, $add);
        // Call in background the regenerate function
        //$this->shellHelperInstance->runAsync(getcwd() . "/index.php Task regenerateElasticsearchIndex $source_id $add");
    }

    /**
     * @deprecated
    */
    private function checkElasticSearch() {
        $hosts = (array)$this->setting->settingData['elastic_url'];
        $client = \Elasticsearch\ClientBuilder::create()->setHosts($hosts)->build();
    
        try {
            $indices = $client->cat()->indices(array('index' => '*'));
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}