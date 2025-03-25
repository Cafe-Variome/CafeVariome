<?php namespace App\Controllers;

/**
 * Discover.php
 * Created: 16/07/2019
 *
 * @author Mehdi Mehtarizadeh
 * @author Sadegh Abadijou
 *
 */

use App\Libraries\CafeVariome\Core\DataPipeLine\Index\ElasticSearch;
use App\Libraries\CafeVariome\Entities\ViewModels\DiscoveryGroupList;
use App\Models\UIData;
use CodeIgniter\Config\Services;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory;
use App\Models\DatasetModel;
use APP\Controllers\AjaxApi;

class Discover extends CVUIController
{
    /**
	 * Constructor
	 *
	 */
    protected $datasetModel;
    protected $encryption;
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
        parent::setProtected(false);
        parent::setIsAdmin(false);
        parent::initController($request, $response, $logger);

		$this->validation = Services::validation();

		$this->session = \Config\Services::session();
		$this->providerID = $this->session->get(self::AUTHENTICATOR_SESSION);
        $this->datasetModel = new DatasetModel();
        $this->encryption = Services::encrypter();
    }

    public function Index()
	{
        return redirect()->to(base_url($this->controllerName. '/SelectNetwork'));
    }

    public function SelectNetwork()
	{
        $uidata = new UIData();
        $uidata->title = "Select Network";

        $networkInterface = new NetworkInterface('', $this->providerID);

        $user_id = $this->authenticator->GetUserId();

        $authorisedNetworks = [];
        $instalattionNetworks = [];

		$discoveryGroupAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$discoveryGroupIds = $discoveryGroupAdapter->ReadByUserId($user_id);

		$discoveryGroups = $discoveryGroupAdapter->SetModel(DiscoveryGroupList::class)->ReadByIds($discoveryGroupIds);

        $instalationNtworksResp = $networkInterface->GetNetworksByInstallationKey($this->setting->GetInstallationKey());

        if ($instalationNtworksResp->status)
		{
            $instalattionNetworks = $instalationNtworksResp->data;
        }

        foreach ($instalattionNetworks as $iNetwork)
		{
            foreach ($discoveryGroups as $discoveryGroup)
			{
                if ($iNetwork->network_key == $discoveryGroup->network_id)
				{
                    array_push($authorisedNetworks, $discoveryGroup);
                }
            }
        }

        if (count($authorisedNetworks) == 1)
		{
            return redirect()->to(base_url($this->controllerName. '/QueryBuilder/' . $authorisedNetworks[0]->network_id));
        }

        $uidata->data['networks'] = $authorisedNetworks;

        $uidata->IncludeJavaScript(JS."cafevariome/discover.js");

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory.'/SelectNetwork', $data);
    }

    public function QueryBuilder()
	{
        $uidata = new UIData();
        // $networkInterface = new NetworkInterface('', $this->providerID);

        // if ($network_id)
		// {
        //     $this->session->set(array('network_key' => $network_id));
        // }
        // else
		// {
        //     return redirect()->to(base_url($this->controllerName. '/Select_Network'));
        // }

        // Check if the user is in the master network group for this network

        // $user_id = $this->authenticator->getUserId();

        // $uidata->data['user_id'] = $user_id;
        // $uidata->data['network_key'] = $network_id;

        // error_log("User: " . $this->session->get('email') . " has chosen network: $network_id || " . date("Y-m-d H:i:s"));

        // $installations = [];
        // $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_id);

        // if($response->status)
		// {
        //     $installations = $response->data;
        // }

        $uidata->data["elasticSearchEnabled"] = true;
        $uidata->data["message"] = null;

		$elasticSearch = new ElasticSearch([$this->setting->GetElasticSearchUri()]);
        if (!$elasticSearch->ping())
		{
            $uidata->data["elasticSearchEnabled"] = false;
            $uidata->data["message"] = "The query builder interface is currently not accessible as Elasticsearch is not running. Please get an administrator to start Elasticsearch and then try again.";
        }

        $uidata->title = "Discover - Query Builder";

		$uidata->IncludeCSS(VENDOR.'components/jqueryui/themes/base/jquery-ui.css');
		$uidata->IncludeCSS(CSS.'query_builder.css');
		$uidata->IncludeCSS(VENDOR.'vakata/jstree/dist/themes/default/style.css');

        $uidata->stickyFooter = false;

		$uidata->IncludeJavaScript(VENDOR.'components/jqueryui/jquery-ui.js');
		$uidata->IncludeJavaScript(JS.'bootstrap-notify.js');
		$uidata->IncludeJavaScript(JS.'mustache.min.js');
		$uidata->IncludeJavaScript(JS.'query_builder_config.js');
		$uidata->IncludeJavaScript(JS.'cafevariome/query_builder.js');
		//$uidata->IncludeJavaScript(VENDOR.'vakata/jstree/dist/jstree.js');
		//$uidata->IncludeJavaScript(JS.'cafevariome/query_builder_tree.js');

		$uidata->IncludeDataTables();

        $data = $this->wrapData($uidata);
        $perPage = 10; // Number of records per page
        // Active datasets pagination
        $activeDatasets = $this->datasetModel->where('d_approved', 1)->where('archived', 0)
        ->paginate($perPage, 'active');

        $activePaginationLinks = $this->datasetModel->pager->links('active', 'default_full');

        // Encrypt dataset IDs for secure frontend usage
        foreach ($activeDatasets as &$dataset) {
        $dataset['encrypted_id'] = $this->encrypt($dataset['d_id']);
        }
        $model = new DatasetModel();

        $data['activeDatasets'] = $activeDatasets;
        $data['activePaginationLinks'] = $activePaginationLinks;


        $data['titles'] = $model->getUniqueTitles();
        $data['keywords'] = $model->getUniqueKeywords();

        return view($this->viewDirectory. '/QueryBuilder', $data);
    }


    public function fetchData()
    {
        $start = intval($this->request->getVar('start') ?? 0); // Ensure it's an integer
        $length = intval($this->request->getVar('length') ?? 10); // Ensure it's an integer
        $searchValue = htmlspecialchars(trim($this->request->getVar('search')['value'] ?? ''), ENT_QUOTES, 'UTF-8'); // Sanitize search value
        $orderColumnIndex = intval($this->request->getVar('order')[0]['column'] ?? 0); // Ensure it's an integer
        $orderDir = strtolower($this->request->getVar('order')[0]['dir'] ?? 'asc'); // Ensure it's 'asc' or 'desc'
    
        // Whitelist valid columns for ordering
        $columns = [
            'd_title',
            'd_abstract',
            'd_datatypes',
            'd_conpoint'
        ];
    
        // Validate order column and direction
        $orderColumn = $columns[$orderColumnIndex] ?? $columns[0];
        $orderDir = in_array($orderDir, ['asc', 'desc']) ? $orderDir : 'asc';
    
        // Dynamically process filters
        $filters = [];
    
        // Handle d_datatitle filter
        $d_datatitle = $this->request->getVar('d_datatitle') ?? [];
        $d_datatitle = array_map(function ($value) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8'); // Sanitize each title
        }, $d_datatitle);
    
        if (!empty($d_datatitle)) {
            $filters['d_datatitle'] = [
                'fields' => ['d_title', 'd_keywords'], // Check both fields
                'values' => $d_datatitle,
                'operator' => 'LIKE'
            ];
        }
    
        // Handle d_datatype filter
        $d_datatype = $this->request->getVar('d_datatype') ?? [];
        $d_datatype = array_map(function ($value) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8'); // Sanitize each datatype
        }, $d_datatype);
    
        if (!empty($d_datatype)) {
            $filters['d_datatype'] = [
                'fields' => ['d_datatypes'], // Single field
                'values' => $d_datatype,
                'operator' => 'IN_LIST' // Special handling for semicolon-separated lists
            ];
        }
    
        // Handle d_datatheme filter
        $d_datatheme = $this->request->getVar('d_datatheme') ?? [];
        $d_datatheme = array_map(function ($value) {
            return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8'); // Sanitize each theme
        }, $d_datatheme);
    
        if (!empty($d_datatheme)) {
            $filters['d_datatheme'] = [
                'fields' => ['d_theme'],
                'values' => $d_datatheme,
                'operator' => 'LIKE'
            ];
        }
    
        // Handle d_studysize filter
        $d_studysize = htmlspecialchars(trim($this->request->getVar('d_studysize') ?? ''), ENT_QUOTES, 'UTF-8');
        if (!empty($d_studysize) && is_numeric($d_studysize)) {
            $filters['d_studysize'] = [
                'fields' => ['d_studysize'],
                'values' => [$d_studysize], // Convert single value to array for consistency
                'operator' => '>='
            ];
        }
    
        // Compile Query
        $compiler = new \App\Libraries\CafeVariome\Query\Compiler();
        $queryLogic = $compiler->buildQueryLogic($filters, $searchValue);
    
        // Fetch Results
        $results = $compiler->execute($queryLogic, $start, $length, $orderColumn, $orderDir);
    
        // Prepare response
        return $this->response->setJSON([
            "draw" => intval($this->request->getVar('draw')),
            "recordsTotal" => $results['recordsTotal'],
            "recordsFiltered" => $results['recordsFiltered'],
            "data" => $results['data'],
        ]);
    }
    
    
    

    public function getDatasetDetails()
    {
        $datasetId = $this->request->getPost('id');
        $d_id = $this->decrypt($datasetId);
        // Fetch dataset details from the database
        $datasetModel = new DatasetModel();
        $data = $datasetModel->getDatasetWithDetails($d_id);
        $data['id'] = $d_id;

        if ($data) {
            return $this->response->setJSON(['success' => true, 'data' => $data]);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Dataset not found']);
        }
    }


    // public function QueryBuilder(){

    //     $uidata = new UIData();

    //     $uidata->title = "Discover - Query Builder";

    // 	$uidata->IncludeCSS(VENDOR.'components/jqueryui/themes/base/jquery-ui.css');
    // 	$uidata->IncludeCSS(CSS.'query_builder.css');
    // 	//$uidata->IncludeCSS(VENDOR.'vakata/jstree/dist/themes/default/style.css');

    //     $uidata->stickyFooter = false;

    // 	$uidata->IncludeJavaScript(VENDOR.'components/jqueryui/jquery-ui.js');
    // 	$uidata->IncludeJavaScript(JS.'bootstrap-notify.js');
    // 	$uidata->IncludeJavaScript(JS.'mustache.min.js');
    // 	$uidata->IncludeJavaScript(JS.'query_builder_config.js');
    // 	$uidata->IncludeJavaScript(JS.'cafevariome/query_builder.js');
    // 	//$uidata->IncludeJavaScript(VENDOR.'vakata/jstree/dist/jstree.js');
    // 	//$uidata->IncludeJavaScript(JS.'cafevariome/query_builder_tree.js');

    // 	$uidata->IncludeDataTables();
    //     $data = $this->wrapData($uidata);

        
    //     $perPage = 10; // Number of records per page
    //     // Active datasets pagination
    //     $activeDatasets = $this->datasetModel->where('d_approved', 1)->where('archived', 0)
    //     ->paginate($perPage, 'active');

    //     $activePaginationLinks = $this->datasetModel->pager->links('active', 'default_full');

    //     // Encrypt dataset IDs for secure frontend usage
    //     foreach ($activeDatasets as &$dataset) {
    //         $dataset['encrypted_id'] = $this->encrypt($dataset['d_id']);
    //     }

    //     return view($this->viewDirectory. '/QueryBuilder', [
    //         'activeDatasets' => $activeDatasets,
    //         'activePaginationLinks' => $activePaginationLinks,
    //         'message' => empty($activeDatasets)  ? 'No records found.' : '',
    //         'data' => $data
    //     ]);

    // }

    private function encrypt($data)
    {
        return bin2hex($this->encryption->encrypt($data));
    }

    private function decrypt($data)
    {
        log_message('error', 'Failed to decrypt id: ' . $data );
        return $this->encryption->decrypt(hex2bin($data));
    }
    
}
