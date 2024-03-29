<?php namespace App\Controllers;

/**
 * NetworkRequest.php
 *
 * Created: 20/12/2019
 *
 * @author Mehdi Mehtarizadeh
 * @author Sadegh Abadijou
 *
 */

use App\Libraries\CafeVariome\Factory\NetworkRequestAdapterFactory;
use App\Models\UIData;
use App\Libraries\CafeVariome\Net\NetworkInterface;


class NetworkRequest extends CVUIController
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
		$this->session = \Config\Services::session();
		$this->providerID = $this->session->get(self::AUTHENTICATOR_SESSION);
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

        $uidata->IncludeJavaScript(JS. 'cafevariome/networkrequest.js');
		$uidata->IncludeDataTables();

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/List', $data);
    }

	public function Accept(int $id)
	{
		$networkRequest = $this->dbAdapter->Read($id);

		if ($networkRequest->isNull())
		{
			$this->setStatusMessage("Network request was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$networkInterface = new NetworkInterface('', $this->providerID);

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

	public function Reject(int $id)
	{
		$networkRequest = $this->dbAdapter->Read($id);

		if ($networkRequest->isNull())
		{
			$this->setStatusMessage("Network request was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$networkInterface = new NetworkInterface('', $this->providerID);

		$response = $networkInterface->DenyRequest($networkRequest->token);

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
