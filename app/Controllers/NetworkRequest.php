<?php namespace App\Controllers;

/**
 * NetworkRequest.php
 *
 * Created: 20/12/2019
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Factory\NetworkRequestAdapterFactory;
use App\Models\UIData;
use App\Libraries\CafeVariome\Net\NetworkInterface;


class NetworkRequest extends CVUI_Controller
{
    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);
		$this->dbAdapter = (new NetworkRequestAdapterFactory())->GetInstance();
	}

    public function Index()
	{
        return redirect()->to(base_url($this->controllerName . '/List'));
    }

    public function List()
    {
        $uidata = new UIData();
        $uidata->data['title'] = "Network Requests";

        $uidata->data['networkRequests'] = $this->dbAdapter->ReadAll();

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js', JS.'cafevariome/components/datatable.js',JS. 'cafevariome/networkrequest.js');
        
        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/List', $data);
    }

    public function acceptrequest(int $id)
    {
		$networkRequest = $this->dbAdapter->Read($id);

		if ($networkRequest->isNull())
		{
			$this->setStatusMessage("Network request was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$networkInterface = new NetworkInterface();

		$response = $networkInterface->AcceptRequest($networkRequest->token);

		if ($response->status)
		{
			$networkRequest->status = NETWORKREQUEST_ACCEPTED;
			$this->dbAdapter->Update($id, $networkRequest);
		}
		else
		{
			$this->setStatusMessage("There was a problem in accepting network request.", STATUS_ERROR);
		}

        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function denyrequest(int $id)
    {
		$networkRequest = $this->dbAdapter->Read($id);

		if ($networkRequest->isNull())
		{
			$this->setStatusMessage("Network request was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$networkInterface = new NetworkInterface();

		$response = $networkInterface->DenyRequest($networkRequest[0]['token']);

		if ($response->status)
		{
			$networkRequest->status = NETWORKREQUEST_REJECTED;
			$this->dbAdapter->Update($id, $networkRequest);
		}
		else
		{
			$this->setStatusMessage("There was a problem in rejecting network request.", STATUS_ERROR);
		}

        return redirect()->to(base_url($this->controllerName.'/List'));
    }
}
