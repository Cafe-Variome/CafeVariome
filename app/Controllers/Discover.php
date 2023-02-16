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

class Discover extends CVUIController
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
        $uidata->data['network_key'] = $network_id;

        error_log("User: " . $this->session->get('email') . " has chosen network: $network_id || " . date("Y-m-d H:i:s"));

        $installations = [];
        $response = $networkInterface->GetInstallationsByNetworkKey((int)$network_id);

        if($response->status)
		{
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

		$uidata->IncludeCSS(VENDOR.'components/jqueryui/themes/base/jquery-ui.css');
		$uidata->IncludeCSS(CSS.'query_builder.css');
		//$uidata->IncludeCSS(VENDOR.'vakata/jstree/dist/themes/default/style.css');

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

        return view($this->viewDirectory. '/QueryBuilder', $data);
    }
}
