<?php namespace App\Controllers;

/**
 * Name: NetworkGroup.php
 * Created: 31/07/2019
 * 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 */

use App\Models\Network;
use App\Models\UIData;
use App\Models\User;
use App\Models\Source;
use App\Helpers\AuthHelper;
use App\Libraries\AuthAdapter;
use CodeIgniter\Config\Services; 
use App\Libraries\CafeVariome\Net\NetworkInterface;

class NetworkGroup extends CVUI_Controller{

	/**
	 * Validation list template.
	 *
	 * @var string
	 */
	protected $validationListTemplate = 'list';
	
	private $networkModel;

	private $networkGroupModel;

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
		parent::initController($request, $response, $logger);
		
		$this->validation = Services::validation();
		$this->networkModel = new Network($this->db);
		$this->networkGroupModel = new \App\Models\NetworkGroup();

	}
	
	public function Index(){
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

	public function List(){
		$uidata = new UIData();
		$uidata->title = "Network Groups";

		$localNetworkGroups = $this->networkGroupModel->getNetworkGroups(null, null, ['name', 'id'], true); // Local network groups
		$remoteNetworkGroups = $this->networkGroupModel->getRemoteNetworkGroups(); // Remote network groups

		$network_groups_for_installation = [];
		foreach ( $localNetworkGroups as $network_group ) {
			$number_sources = $this->networkModel->countSourcesForNetworkGroup($network_group['id']);
			$network_group['number_of_sources'] = $number_sources;
			$network_groups_for_installation[] = $network_group;
		}

		$networkInterface = new NetworkInterface();

		foreach ( $remoteNetworkGroups as $network_group ) {
			$number_sources = $this->networkModel->countSourcesForNetworkGroup($network_group['id']);
			$network_group['number_of_sources'] = $number_sources;
			$netResp = $networkInterface->GetNetwork($network_group['network_key']);
			$network_group['network_name'] = $netResp->status ? $netResp->data->network_name : "-";
			$network_groups_for_installation[] = $network_group;
		}

		$uidata->data["groups"] = $network_groups_for_installation;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js', JS.'cafevariome/components/datatable.js',JS. 'cafevariome/networkgroup.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/List', $data);
	}

    public function Create(){

        $uidata = new UIData();
		$uidata->title = "Create Group";

		$networkInterface = new NetworkInterface();

        //validate form input
        
        $this->validation->setRules([
            'group_name' => [
                'label'  => 'Group name',
                'rules'  => 'required|alpha_dash|unique_network_group_name_check['. $this->request->getVar('network') . ']',
                'errors' => [
                    'required' => '{field} is required.',
                    'unique_network_group_name_check' => '{field} already exists.'
                ]
            ],
            'desc' => [
                'label'  => 'Description',
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
            'network' => [
				'label'  => 'Network',
				'rules' => 'string'
            ],
            'group_type' => [
				'label' => 'Group Type',
				'rules' => 'string'
            ]
        ]
        );
		
		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			// Create the new group
			$name =  $this->request->getVar('group_name');
			$data = array ( 'name' => $this->request->getVar('group_name'),
							'description' =>$this->request->getVar('desc'),
							'group_type' => $this->request->getVar('group_type'),
							'network_key' => $this->request->getVar('network'),
							'url' => base_url()
			);
			
			try {
				$this->networkGroupModel->createNetworkGroup($data);
				$this->setStatusMessage("Network group '$name' was created.", STATUS_SUCCESS);
			} catch (\Exception $ex) {
				$this->setStatusMessage("There was a problem creating '$name'.", STATUS_ERROR);
			}
			return redirect()->to(base_url($this->controllerName . '/List'));   

		}
		else {
			$response = $networkInterface->GetNetworksByInstallationKey($this->setting->settingData['installation_key']);
			$networks = [];
			if ($response->status) {
				$networks = $response->data;
			}

            if (!empty($networks) ) {
				$uidata->data['networks'] = $networks;
			}

            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
			$uidata->data['group_name'] = array(
				'name'  => 'group_name',
				'id'    => 'group_name',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => set_value('group_name'),
			);
			$uidata->data['desc'] = array(
				'name'  => 'desc',
				'id'    => 'desc',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => set_value('description'),
			);
			$uidata->data['network'] = array(
				'name'  => 'network',
				'id'    => 'network',
				'type'  => 'dropdown',
				'value' => set_value('network'),
			);
			$uidata->data['group_type'] = array(
				'name'  => 'group_type',
				'id'    => 'group_type',
				'type'  => 'group_type',
				'class' => 'form-control',
				'value' => set_value('group_type'),
            );
            
            $data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Create', $data);
		}		

    }

    public function Delete(int $id){
		$uidata = new UIData();
		$uidata->title = "Delete Network Group";
		// insert csrf check
		$uidata->data['group_id'] = $id;
		$uidata->data['csrf'] = $this->_get_csrf_nonce();

		$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

		$this->validation->setRules([
            'confirm' => [
                'label'  => 'Confirmation',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.',
                ]
            ],
            'id' => [
                'label'  => 'Group ID',
                'rules' => 'required|alpha_numeric',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ]
        ]
        );

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			// do we really want to delete?
			if ($this->request->getVar('confirm') == 'yes')
			{
				try {
					// do we have the right userlevel?
					$has_got_network_sources_assigned = $this->networkGroupModel->hasSource($id);
					if (!$has_got_network_sources_assigned) {
						$f = $this->networkGroupModel->deleteNetworkGroup($id);
						$this->setStatusMessage("Network group was removed.", STATUS_SUCCESS);

						return redirect()->to(base_url($this->controllerName.'/List'));            	
					}
					else {
						$this->setStatusMessage("Unable to delete network group as sources from another installation are present in the group.", STATUS_ERROR);					}
				} catch (\Exception $ex) {
					$this->setStatusMessage("There was a problem removing network group.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List'));            	
		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/Delete', $data);
		
	}

	function _get_csrf_nonce()
	{
		helper('text');

		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->setFlashdata('csrfkey', $key);
		$this->session->setFlashdata('csrfvalue', $value);

		return array($key => $value);
	}

}