<?php namespace App\Controllers;

/**
 * NetworkRequest.php
 * 
 * Created: 20/12/2019
 * 
 * @author Mehdi Mehtarizadeh
 */

use App\Models\UIData;
use App\Libraries\CafeVariome\Net\NetworkInterface;


class NetworkRequest extends CVUI_Controller 
{

    private $networkRequestModel;

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

        $this->networkRequestModel = new \App\Models\NetworkRequest();
    }

    public function Index(){
        return redirect()->to(base_url($this->controllerName . '/List'));
    }

    public function List()
    {
        $uidata = new UIData();
        $uidata->data['title'] = "Network Requests";

        $networkRequests = $this->networkRequestModel->getNetworkRequests();
        $uidata->data['networkRequests'] = $networkRequests;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js', JS.'cafevariome/components/datatable.js',JS. 'cafevariome/networkrequest.js');
        
        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/List', $data);
    }

    public function acceptrequest(int $id)
    {
        $networkRequest = $this->networkRequestModel->getNetworkRequests(null, ['id' => $id]);

        if (count($networkRequest) == 1) {
            $networkInterface = new NetworkInterface();

            $response = $networkInterface->AcceptRequest($networkRequest[0]['token']);

            if ($response->status) {
                $data = ['status' =>  1]; // Status 1 indicates an accepted request.
                $this->networkRequestModel->updateNetworkRequests($data ,['id' => $id]);// Update status in local database.
            }
            else {
                
            }
        }

        return redirect()->to(base_url($this->controllerName.'/List'));

    }

    public function denyrequest(int $id)
    {
        $networkRequest = $this->networkRequestModel->getNetworkRequests(null, ['id' => $id]);

        if (count($networkRequest) == 1) {
            $networkInterface = new NetworkInterface();

            $response = $networkInterface->DenyRequest($networkRequest[0]['token']);

            if ($response->status) {
                $data = ['status' =>  0]; // Status 0 indicates denied request.
                $this->networkRequestModel->updateNetworkRequests($data ,['id' => $id]);// Update status in local database.
            }
            else {
                
            } 
        }

        return redirect()->to(base_url($this->controllerName.'/List'));
    }
}
