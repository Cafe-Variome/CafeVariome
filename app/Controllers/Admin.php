<?php namespace App\Controllers;

/**
 * Admin.php
 * Created 18/07/2019
 *
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * @author Sadegh Abadijou
 *
 */

use App\Libraries\CafeVariome\Factory\NetworkRequestAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\UserAdapterFactory;
use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;
use App\Models\UIData;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use CodeIgniter\Config\Services;

class Admin extends CVUIController
{

    /**
	 * Validation list template.
	 *
	 * @var string
	 */
    protected $validationListTemplate = 'list';


    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->validation = Services::validation();
		$this->providerID = $this->session->get(self::AUTHENTICATOR_SESSION);
    }

    public function Index()
	{
        $uidata = new UIData();
        $uidata->title = "Administrator Dashboard";
        $uidata->stickyFooter = false;
        $uidata->IncludeCSS(CSS.'dashboard/chartjs/Chart.min.css');
        $uidata->IncludeJavaScript(JS.'dashboard/chartjs/Chart.min.js');
		$uidata->IncludeJavaScript(JS . 'cafevariome/admin.js');

		$networkInterface = new NetworkInterface('', $this->providerID);
		$networkRequestAdapter = (new NetworkRequestAdapterFactory())->GetInstance();

        $neo4j = new Neo4J();

		$sourceAdapter = (new SourceAdapterFactory())->GetInstance();
        $sourceList = $sourceAdapter->ReadAll();

        $sc = 0;
        $maxSourcesToDisplay = 12;
        $sourceCountList = [];
        $sourceNameLabels = '';
        foreach ($sourceList as $source)
		{
            if ($sc > $maxSourcesToDisplay)
			{
                break;
            }
            if ($sc == count($sourceList) - 1 || $sc == $maxSourcesToDisplay)
			{
                $sourceNameLabels .= "'" . $source->display_name. "'";
            }
            else
			{
                $sourceNameLabels .= "'" . $source->display_name. "',";
            }

            $sourceCountList[$source->name] = 0;
            $sc++;
        }

        $uidata->data['sourceCount'] = count($sourceList);
        $uidata->data['sourceNames'] = $sourceNameLabels;

        $networks = $networkInterface->GetNetworksByInstallationKey($this->setting->getInstallationKey());
        if ($networks->status)
		{
            $uidata->data['networksCount'] = count($networks->data);
            $uidata->data['networkMsg'] = null;
        }
        else
		{
            //Problem contacting network server
            $uidata->data['networksCount'] = "-";
            $uidata->data['networkMsg'] = "There was a problem in communicating with network software. Please fix it as the system is unable to function correctly.";
        }

        $uidata->data['usersCount'] = count((new UserAdapterFactory())->GetInstance()->ReadAll());
        $uidata->data['networkRequestCount'] = $networkRequestAdapter->CountAllPending();

        $elasticStatus = ElasticsearchHelper::ping();
        $uidata->data['elasticStatus'] = $elasticStatus;
        $uidata->data['elasticMsg'] = null;
        if (!$elasticStatus)
		{
            $uidata->data['elasticMsg'] = "Elasticsearch is not running. The query interface is not accessible. Please ask the server administrator to start it.";
        }

        $neo4jStatus = $neo4j->ping();
        $uidata->data['neo4jStatus'] = $neo4jStatus;
        $uidata->data['neo4jMsg'] = null;
        if (!$neo4jStatus)
		{
            $uidata->data['neo4jMsg'] = "Neo4J is not running. Some capabilities of the system are disabled because of this. Please ask the server administrator to start it.";
        }

        $uidata->data['openIDStatus'] = $this->authenticator->Ping();
        $uidata->data['serviceStatus'] = $this->serviceInterface->ping();

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Index', $data);
    }
}
