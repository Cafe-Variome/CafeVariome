<?php namespace App\Controllers;

/**
 * Discover.php
 * Created: 16/07/2019
 *
 * @author Mehdi Mehtarizadeh
 *
 *
 */

use App\Libraries\CafeVariome\Core\DataPipeLine\Index\ElasticSearch;
use App\Libraries\CafeVariome\Entities\ViewModels\DiscoveryGroupList;
use App\Models\UIData;
use CodeIgniter\Config\Services;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory;

class Discover extends CVUI_Controller
{
    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
        parent::setProtected(true);
        parent::setIsAdmin(false);
        parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
    }

    public function Index()
	{
        return redirect()->to(base_url($this->controllerName. '/SelectNetwork'));
    }

    public function SelectNetwork()
	{
        $uidata = new UIData();
        $uidata->title = "Select Network";

        $networkInterface = new NetworkInterface();

        $user_id = $this->authenticator->GetUserId();

        $authorisedNetworks = [];
        $instalattionNetworks = [];

		$discoveryGroupAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();
		$discoveryGroupIds = $discoveryGroupAdapter->ReadByUserId($user_id);

		$discoveryGroups = $discoveryGroupAdapter->SetModel(DiscoveryGroupList::class)->ReadByIds($discoveryGroupIds);

        $instalattionNtworksResp = $networkInterface->GetNetworksByInstallationKey($this->setting->GetInstallationKey());

        if ($instalattionNtworksResp->status)
		{
            $instalattionNetworks = $instalattionNtworksResp->data;
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
            return redirect()->to(base_url($this->controllerName. '/QueryBuilder/' . $authorisedNetworks[0]->network_key));
        }

        $uidata->data['networks'] = $authorisedNetworks;

        $uidata->javascript = array(JS."cafevariome/discover.js");

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory.'/SelectNetwork', $data);
    }

    public function QueryBuilder(int $network_id)
	{
        $uidata = new UIData();
        $networkInterface = new NetworkInterface();

        if ($network_id)
		{
            $this->session->set(array('network_key' => $network_id));
        }
        else
		{
            return redirect()->to(base_url($this->controllerName. '/Select_Network'));
        }

        // Check if the user is in the master network group for this network

        $user_id = $this->authenticator->getUserId();

        $uidata->data['user_id'] = $user_id;
        $uidata->data['network_key'] = $network_key;

        error_log("User: " . $this->session->get('email') . " has chosen network: $network_key || " . date("Y-m-d H:i:s"));

        $installations = [];
        $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_key);

        if($response->status){
            $installations = $response->data;
        }

        $uidata->data["elasticSearchEnabled"] = true;
        $uidata->data["message"] = null;

		$elasticSearch = new ElasticSearch([$this->setting->GetElasticSearchUri()]);
        if (!$elasticSearch->ping())
		{
            $uidata->data["elasticSearchEnabled"] = false;
            $uidata->data["message"] = "The query builder interface is currently not accessible as Elasticsearch is not running. Please get an administrator to start Elasticsearch and then try again.";
        }

        $uidata->title = "Discover - Query Builder";
        $uidata->css = array(//VENDOR.'vakata/jstree/dist/themes/default/style.css',
                             VENDOR.'components/jqueryui/themes/base/jquery-ui.css',
                             CSS.'query_builder.css',
                             VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->stickyFooter = false;

        $uidata->javascript = array(//VENDOR.'vakata/jstree/dist/jstree.js',
                                    VENDOR.'components/jqueryui/jquery-ui.js',
                                    JS.'bootstrap-notify.js',
                                    JS.'mustache.min.js',
                                    JS.'query_builder_config.js',
                                    //JS.'cafevariome/query_builder_tree.js',
                                    JS.'cafevariome/query_builder.js',
									VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js'
                                );

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory. '/QueryBuilder', $data);
    }
}
