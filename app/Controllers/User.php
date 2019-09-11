<?php namespace App\Controllers;

/**
 * User.php
 * 
 * Created : 11/09/2019
 * 
 * User controller class
 * 
 */

use App\Models\UIData;
use App\Models\Settings;
use App\Models\Network;
use App\Models\Source;
use App\Helpers\AuthHelper;
use CodeIgniter\Config\Services;

class User extends CVUI_Controller{

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
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);

        $this->validation = Services::validation();

    }

    function index(){
        return redirect()->to(base_url("user/users"));
    }

    function create_user(){

        $uidata = new UIData();
        $uidata->title = "Create New User";

        $networkModel = new Network($this->db);

        $groups = $networkModel->getNetworkGroupsForInstallation();

        $uidata->data['groups'] = $groups;
        //display the create user form
        //set the flash data error message if there is one
        $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');


        $uidata->data['username'] = array(
                'name' => 'username',
                'id' => 'username',
                'type' => 'text',
                'value' => set_value('email'),
        );
        $uidata->data['first_name'] = array(
                'name'  => 'first_name',
                'id'    => 'first_name',
                'type'  => 'text',
                'value' => set_value('first_name'),
        );
        $uidata->data['last_name'] = array(
                'name'  => 'last_name',
                'id'    => 'last_name',
                'type'  => 'text',
                'value' => set_value('last_name'),
        );
        $uidata->data['email'] = array(
                'name'  => 'email',
                'id'    => 'email',
                'type'  => 'text',
                'value' => set_value('email'),
        );
        $uidata->data['company'] = array(
                'name'  => 'company',
                'id'    => 'company',
                'type'  => 'text',
                'value' => set_value('company'),
        );
        $uidata->data['password'] = array(
                'name'  => 'password',
                'id'    => 'password',
                'type'  => 'password',
                'value' => set_value('password'),
        );
        $uidata->data['password_confirm'] = array(
                'name'  => 'password_confirm',
                'id'    => 'password_confirm',
                'type'  => 'password',
                'value' => set_value('password_confirm'),
        );
        $uidata->data['orcid'] = array(
                'name' => 'orcid',
                'id' => 'orcid',
                'type' => 'text',
                'value' => set_value('orcid'),
        );

        $data = $this->wrapData($uidata);
        return view("user/create_user", $data);
    }

    function users(){
        $uidata = new UIData();
        $uidata->title = "Users";

        $userModel = new \App\Models\User($this->db);
        $networkModel = new Network($this->db);

		$uidata->data['message'] = $this->session->getFlashdata('activation_email_unsuccessful');

        $uidata->data['users'] = $userModel->getUsers();

    
		$users_groups_data = $networkModel->getCurrentNetworkGroupsForUsers();

        $users_groups = array();
		// If there were groups fetch from auth server for users then add them to the view
		if (! array_key_exists('error', $users_groups_data)) {
			foreach ( $users_groups_data as $group ) {
				$users_groups[$group['user_id']][] = array('network_name' => $group['network_name'], 'group_id' => $group['group_id'], 'group_name' => $group['name'], 'group_description' => $group['description']);
			}
			$uidata->data['users_groups'] = $users_groups;
		}
        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->javascript = array(JS."cafevariome/components/datatable.js", JS."cafevariome/admin.js", VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);
        return view("user/users", $data);
    }

    function edit_user(int $id){

    }

    function delete_user(int $id){

    }

    function user(int $id){

    }
}