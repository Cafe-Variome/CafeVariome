<?php namespace App\Controllers;

/**
 * Elastic.php
 * 
 * Created 08/08/2019
 * 
 * @author Mehdi Mehtarizadeh
 * @author Farid Yavari Dizjikan
 * 
 * This controller makes it possible for users to contact elastic search server.
 */

 
use App\Models\UIData;
use App\Models\Settings;
use App\Models\Source;
use App\Models\Network;
use App\Libraries\ElasticSearch;
use App\Libraries\CafeVariome\PHPShellHelper;
use CodeIgniter\Config\Services;

class Elastic extends CVUI_Controller{


    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);
    }

    public function Status(){

        $networkModel = new Network();

        $uidata = new UIData();
        $uidata->title = "Index Status";

        $elasticSearch = new ElasticSearch((array)$this->setting->getElasticSearchUri());
        $elasticModel = new \App\Models\Elastic($this->db);
        $sourceModel = new Source($this->db);
        $sources = $sourceModel->getSources('source_id, name, elastic_status');

        $networkAssignedSources = $networkModel->getNetworkSourcesForCurrentInstallation();

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
            
            $sources[$i]['network_assigned'] = false;

            foreach ($networkAssignedSources as $networkSourcePair) {
                if ($networkSourcePair['source_id'] == $sources[$i]['source_id']) {
                    $sources[$i]['network_assigned'] = true;
                    break;
                }
            }
        }

        $uidata->data['elastic_update'] = $sources;

        $indexPrefix = $elasticModel->getTitlePrefix();
        $uidata->data['indexPrefix'] = $indexPrefix;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',
                                JS."cafevariome/elastic.js",
                                JS."/bootstrap-notify.js"
        ];

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory.'/Status', $data);
    }
}