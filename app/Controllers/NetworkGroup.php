<?php namespace App\Controllers;

/**
 * Name: NetworkGroup.php
 * Created: 31/07/2019
 * 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 */

use App\Models\User;
use App\Models\UIData;
//use App\Models\Source;
use App\Helpers\AuthHelper;
use App\Libraries\AuthAdapter;
use CodeIgniter\Config\Services; 

class NetworkGroup extends CVUI_Controller{

	/**
	 * Validation list template.
	 *
	 * @var string
	 * @see https://bcit-ci.github.io/CodeIgniter4/libraries/validation.html#configuration
	 */
	protected $validationListTemplate = 'list';
	

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
        $this->networkModel = new \App\Models\Network($this->db);
        //$this->userModel = new \App\Models\User($this->db);
    }

    public function create_networkgroup(){

        $uidata = new UIData();
		$uidata->title = "Create Group";

        //validate form input
        
        $this->validation->setRules([
            'group_name' => [
                'label'  => 'Group name',
                'rules'  => 'required|alpha_dash|unique_network_group_name_check['.$this->request->getVar('name').','. $this->request->getVar('network') . ']',
                'errors' => [
                    'required' => '{field} is required.',
                    'callback_unique_network_group_name_check' => '{field} already exists.'
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
		//$this->validation->set_rules('group_name', 'Group name', 'required|alpha_dash|xss_clean|callback_unique_network_group_name_check['.$this->input->post('network').']');
		//$this->form_validation->set_rules('desc', 'Description', 'required|xss_clean');
		//$this->form_validation->set_rules('network', 'Network', 'xss_clean');
		//$this->form_validation->set_rules('group_type', 'Group type', 'xss_clean');
		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
			// Create the new group
			$data = array ( 'name' => $this->request->getVar('group_name'),
							'description' =>$this->request->getVar('desc'),
							'group_type' => $this->request->getVar('group_type'),
							'network_key' => $this->request->getVar('network'),
							'url'		=> base_url()
			);
			
			$network_group_id = $this->networkModel->createNetworkGroup($data);
			if ( $network_group_id ) {
				$this->session->setFlashdata('message', 'Network group created successfully.');
                return redirect()->to(base_url('networkgroup/index'));            
			}
			else {
				$uidata->data['group_name'] = "";
				$uidata->data['desc'] = "";
                $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
                
                $data = $this->wrapData($uidata);
				return view('networkgroup/create_networkgroup', $data);
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

			return view('networkgroup/create_networkgroup', $data);
		}		

    }



    function delete_networkgroup($id = NULL){}

}