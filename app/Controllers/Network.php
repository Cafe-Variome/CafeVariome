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
    
    private $authAdapter;

    private $validation;

    private $networkModel;
    /**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);

		$this->validation = Services::validation();

        $this->authAdapterConfig = config('AuthAdapter');
        $this->authAdapter = new AuthAdapter($this->authAdapterConfig->authRoutine);
        
        $this->networkModel = new \App\Models\Network($this->db);

        if (!$this->authAdapter->loggedIn() /*|| !$this->ion_auth->is_admin()*/) {
            redirect('auth');
        }
    }

    function index(){
        return redirect()->to(base_url("network/networks"));
    }

    /**
     * 
     */

    function networks()
    {

        $uidata = new UIData();
        $uidata->data['title'] = "Networks";
        $data = AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key']),  $this->setting->settingData['auth_server'] . "network/get_networks_installation_member_of_with_other_installation_details");
        $installations_for_networks = json_decode($data, true);
        $master_groups = $this->networkModel->getMasterGroups();

        $uidata->data['groups'] = $master_groups;
        $uidata->data['networks'] = $installations_for_networks;

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

        if ($this->validation->withRequest($this->request)->run()== false) {
            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $uidata->data['name'] = array(
                'name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'style' => 'width:50%',
                'value' =>set_value('name'),
            );
            $uidata->data['validation'] = $this->validation;
            $data = $this->wrapData($uidata);

            return view('Network/create_network', $data);
        } else {
            $name = strtolower($this->request->getVar('name')); // Convert the network name to lowercase

            $base_url = base_url();
            error_log($base_url);
            $network = json_decode(AuthHelper::authPostRequest(array('installation_base_url' => $base_url, 'network_name' => $name, 'installation_key' => $this->setting->settingData['installation_key']), $this->setting->settingData['auth_server'] . "network/create_network"),1);
            error_log(print_r($network,1));


			$this->networkModel->createNetwork(array ('network_name' => $network['network_name'],'network_key' => $network['network_key'],'network_type' => 'federated'));
            $network_master_group_data = array (    'name' => $network['network_name'],
                                        'description' => $network['network_name'],
                                        'network_key' => $network['network_key'],
                                        'group_type' => "master",
                                        'url' => $this->setting->settingData['installation_key']
                                    );
            $network_group_id = $this->networkModel->createNetworkGroup($network_master_group_data);

            $this->session->setFlashdata('message', "Successfully created network $name");


            redirect("networks", 'refresh');
        }
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
            $uidata->javascript = array('cafevariome/network.js');

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
}