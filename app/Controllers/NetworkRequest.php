<?php namespace App\Controllers;

/**
 * NetworkRequest.php
 * 
 * Created: 20/12/2019
 * 
 * @author Mehdi Mehtarizadeh
 */

use App\Models\UIData;


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

    public function index(){
        return redirect()->to(base_url("networkrequest/networkrequests"));
    }

    public function networkrequests()
    {
        $uidata = new UIData();
        $uidata->data['title'] = "Network Requests";

        $networkRequests = $this->networkRequestModel->getNetworkRequests();
        $uidata->data['networkRequests'] = $networkRequests;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js', JS.'cafevariome/components/datatable.js',JS. 'cafevariome/networkrequest.js');
        
        $data = $this->wrapData($uidata);
        return view('NetworkRequest/NetworkRequests', $data);
    }
}
