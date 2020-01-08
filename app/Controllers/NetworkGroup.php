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

class Networkgroup extends CVUI_Controller{

	/**
	 * Validation list template.
	 *
	 * @var string
	 * @see https://bcit-ci.github.io/CodeIgniter4/libraries/validation.html#configuration
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
		$this->networkGroupModel = new \App\Models\NetworkGroup($this->db);

	}
	
	public function index(){
        return redirect()->to(base_url("networkgroup/networkgroups"));
    }

	function networkgroups(){
		$uidata = new UIData();
		$uidata->title = "Network Groups";

		$networkGroups = $this->networkGroupModel->getNetworkGroups(null, null, array('name'), true);
		foreach ( $networkGroups as $network_group ) {
			$number_sources = $this->networkModel->countSourcesForNetworkGroup($network_group['id']);
			$network_group['number_of_sources'] = $number_sources;
			$network_groups_for_installation[] = $network_group;
		}
		if (!empty($network_groups_for_installation)) {
			$uidata->data["groups"] = $network_groups_for_installation;
		}
		else {
			$uidata->data["groups"] = array("error" => "No network groups are available for this installation");
		}

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js', JS.'cafevariome/components/datatable.js',JS. 'cafevariome/networkgroup.js');

		$data = $this->wrapData($uidata);

		return view("Networkgroup/Networkgroups", $data);
	}

    public function create_networkgroup(){

        $uidata = new UIData();
		$uidata->title = "Create Group";

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
			$data = array ( 'name' => $this->request->getVar('group_name'),
							'description' =>$this->request->getVar('desc'),
							'group_type' => $this->request->getVar('group_type'),
							'network_key' => $this->request->getVar('network'),
							'url'		=> base_url()
			);
			
			$network_group_id = $this->networkGroupModel->createNetworkGroup($data);
			if ( $network_group_id ) {
				$this->session->setFlashdata('message', 'Network group created successfully.');
                return redirect()->to(base_url('networkgroup/index'));            
			}
			else {
				$uidata->data['group_name'] = "";
				$uidata->data['desc'] = "";
                $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
                
                $data = $this->wrapData($uidata);
				return view('Networkgroup/Create_Networkgroup', $data);
			}
		}
		else {
			$networks_installation_member_of = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "network/get_networks_installation_member_of_with_other_installation_details");
			//$networks_installation_member_of = json_decode($networks_installation_member_of, 1);
            if ( ! empty($networks_installation_member_of) ) {
					$uidata->data['networks'] = json_decode($networks_installation_member_of, TRUE);
			}

            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
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

			return view('Networkgroup/Create_Networkgroup', $data);
		}		

    }

    function delete_networkgroup(int $id){
		$uidata = new UIData();
		$uidata->title = "Delete Network Group";
		// insert csrf check
		$uidata->data['group_id'] = $id;
		$uidata->data['csrf'] = $this->_get_csrf_nonce();

		$uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

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
				// do we have a valid request?
				if ($id != $this->request->getVar('id'))
				{
					show_error('This form post did not pass our security checks.');
				}
				// do we have the right userlevel?
				$has_got_network_sources_assigned = $this->networkGroupModel->hasSource($id);
				if (!$has_got_network_sources_assigned) {
					$this->networkGroupModel->deleteNetworkGroup($id);		
					return redirect()->to(base_url('networkgroup/index'));            	
				}
				else {
					$this->session->setFlashdata('message', "Unable to delete network group as sources from another installation are present in the group.");
				}
			}
			return redirect()->to(base_url('networkgroup/index'));            	
		}
		$data = $this->wrapData($uidata);
		return view("Networkgroup/Delete_Networkgroup", $data);
		
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