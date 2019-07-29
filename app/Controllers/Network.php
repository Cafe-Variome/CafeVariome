<?php namespace App\Controllers;

/**
 * Name: Network.php
 * Created: 18/07/2019
 * 
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 * 
 */

use App\Models\User;
use App\Models\UIData;
use App\Models\Settings;
use App\Helpers\AuthHelper;
use App\Libraries\AuthAdapter;
use CodeIgniter\Config\Services;

class Network extends CVUI_Controller{

    /**
	 * Validation list template.
	 *
	 * @var string
	 * @see https://bcit-ci.github.io/CodeIgniter4/libraries/validation.html#configuration
	 */
    protected $validationListTemplate = 'list';
    
    private $networkModel;

    private $userModel;
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
        $this->userModel = new \App\Models\User($this->db);
    }

    public function index(){
        return redirect()->to(base_url("network/networks"));
    }

    /**
     * 
     */

    public function networks()
    {

        $uidata = new UIData();
        $uidata->data['title'] = "Networks";
        $data = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']),  $this->setting->settingData['auth_server'] . "network/get_networks_installation_member_of_with_other_installation_details");
        $installations_for_networks = json_decode($data, true);
        $master_groups = $this->networkModel->getMasterGroups();

        $uidata->data['groups'] = $master_groups;
        $uidata->data['networks'] = $installations_for_networks;
            
        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(JS.'cafevariome/network.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);
        return view("network/networks", $data);
    }

    /**
     * create_network
     */
    function create_network() {

        $uidata = new UIData();
        $uidata->data['title'] = "Create Network";


        // Validate form input
        $this->validation->setRules([
            'name' => [
                'label'  => 'Network Name',
                'rules'  => 'required|alpha_dash|is_unique[networks.network_name]',
                'errors' => [
                    'required' => '{field} is required.',
                    'uniquename_check' => '{field} already exists.'
                ]
            ]
        ]
        );

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {

            $name = strtolower($this->request->getVar('name')); // Convert the network name to lowercase

            $network = json_decode(AuthHelper::authPostRequest(array('installation_base_url' => $base_url(), 'network_name' => $name, 'installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "network/create_network"),1);

			$this->networkModel->createNetwork(array ('network_name' => $network['network_name'],'network_key' => $network['network_key'],'network_type' => 'federated'));
            $network_master_group_data = array (    'name' => $network['network_name'],
                                        'description' => $network['network_name'],
                                        'network_key' => $network['network_key'],
                                        'group_type' => "master",
                                        'url' => $this->setting->settingData['installation_key']
                                    );
            $network_group_id = $this->networkModel->createNetworkGroup($network_master_group_data);

            $this->session->setFlashdata('message', "Successfully created network $name");


			return redirect()->to(base_url('network/index'));            
        } else {

            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $uidata->data['name'] = array(
                'name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('name'),
            );
            $uidata->data['validation'] = $this->validation;

            $data = $this->wrapData($uidata);

            return view('network/create_network', $data);
        }
    }

    function edit_user_network_groups($id, $isMaster = false) {

        $uidata = new UIData();
        $uidata->title = "Edit User Network Groups";


        $uidata->data['user_id'] = $id;
        //display the edit user form
        $uidata->data['csrf'] = $this->_get_csrf_nonce();

        //set the flash data error message if there is one
        $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
        $users = json_decode(json_encode($this->userModel()),1);
        $group_users = json_decode(json_encode($this->networkModel->getNetworkGroupUsers($id)),1);
        $group_details = json_decode(json_encode($this->network_model->get_network_name_and_type_for_id($id)),1);
        $uidata->data['name'] = $group_details[0]['name'];   
        $uidata->data['group_type'] = $group_details[0]['group_type']; 
        if(!$isMaster) {
            $this->load->model('federated_model');
            $sources = $this->federated_model->get_sources();
            $group_sources = json_decode(json_encode($this->network_model->get_sources_for_group($id)), TRUE);
            // error_log(print_r($sources, 1));
            // error_log(print_r($group_sources, 1));

            $ids = [];
            foreach ($group_sources as $key => $value)
                $ids[] = $value['source_id'];

            if($ids)
                $group_sources = $this->federated_model->add_source_name_to_ids($ids);

            // error_log(print_r($group_sources, 1));

            $sources_left = []; 
            $sources_right = [];

            foreach ($sources as $key => $value)
                $sources_left[$value['source_id']] = $value['name'];

            foreach ($group_sources as $key => $value)
                $sources_right[$value['source_id']] = $value['name'];

            foreach ($sources_right as $key => $value) {
                if(array_key_exists($key, $sources_left))
                    unset($sources_left[$key]);
            }

            // error_log(print_r($sources_left, 1));
            // error_log(print_r($sources_right, 1));

            $uidata->data['sources_left'] = $sources_left;
            $uidata->data['sources_right'] = $sources_right;
        }

        if($isMaster) {
            for($i = 0; $i < count($users); $i++) {
                if($users[$i]['username'] == "admin@cafevariome") {
                    unset($users[$i]);
                    $users = array_values($users);
                }
            }
        }

        if (!array_key_exists('error', $group_users)) {
            foreach ($group_users as $group_user) {
                for($i = 0; $i < count($users); $i++) {
                    if($group_user == $users[$i]) {
                        unset($users[$i]);
                        $users = array_values($users);
                    }
                }
            }
            $uidata->data['group_users'] = $group_users;   
        }

        if($isMaster)
            $uidata->data['users'] = $users;
        else {
            if(!array_key_exists('error', $users))  $uidata->data['users'] = $users;
        }
        
        $uidata->data['isMaster'] = $isMaster;
        $uidata->data['user_id'] = $id;
        $uidata->data['installation_key'] = $this->setting->settingData['installation_key'];

        $data = $this->wrapData($uidata);
        return view('federated/auth/edit_network_groups_users', $data);

    }

    /**
     * 
     */
    function join_network(){

        $uidata = new UIData();
        $uidata->data['title'] = "Join Network";

        if (!isset($_POST['network'])) {
            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $uidata->data['justification'] = array(
                'name' => 'justification',
                'id' => 'justification',
                'type' => 'text',
                'rows' => '5',
                'cols' => '3',
                'style' => 'width:50%',
                'value' => set_value('justification'),
            );
            $networks = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "/network/get_networks_installation_not_a_member_of");
            $data = json_decode($networks, true);

            $uidata->data['networks'] = $data;
            
            $uidata->javascript = array(JS.'cafevariome/network.js');

            $data = $this->wrapData($uidata);

            return view('network/join_network', $data);
        }
    }

    /**
     * 
     */
    function process_network_join_request() {
        error_log("original admin");
        $result['network_key'] = $this->request->getVar('networks');
        $result['justification'] = $this->request->getVar('justification');

        $result['installation_key'] = $this->setting->settingData['installation_key'];

        $user_id = $this->authAdapter->getUserId();
        $userModel = new User($this->db);
        $user = $userModel->getUserById($user_id);

        $result['username'] = $user[0]->username;
        $result['email'] = $user[0]->email;
        $result['auth_server'] =  $this->setting->settingData['auth_server'];
        $networks = AuthHelper::authPostRequest($result,  $this->setting->settingData['auth_server'] . "/network/join_network_request");
        error_log("networks -> $networks");
        $data = json_decode($networks, 1);

        if (array_key_exists('network_request_id', $data)) {
            echo json_encode(array('success' => 'Network join request has been sent!'));
        } else {
            echo json_encode(array('error' => 'Sorry, unable to process request. Retry!'));
        }
    }



    function _get_csrf_nonce()
	{
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}
}