<?php namespace App\Controllers;

/**
 * User.php
star *
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

    function Index(){
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    function Create(){

        $uidata = new UIData();
        $uidata->title = "Create New User";
        $uidata->stickyFooter = false;
        $networkModel = new Network($this->db);

        $groups = $networkModel->getNetworkGroupsForInstallation();

        $this->validation->setRules([
            'email' => [
                'label'  => 'Email',
                'rules'  => 'required|valid_email|is_unique[users.username]',
                'errors' => [
                    'required' => '{field} is required.',
                    'is_unique' => '{field} already exists.',
                    'valid_email' => 'Please check the Email field. It does not appear to be valid.'
                ]
            ],
            'first_name' => [
                    'label'  => 'First Name',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} is required.'
                    ]
            ],
            'last_name' => [
                'label'  => 'Last Name',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.',
                ]
            ],
            'company' => [
                'label'  => 'Institute/Laboratory/Company Name',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ]
        ]
        );

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {

            $email    = $this->request->getVar('email');
            //$groups = ($this->request->getVar('groups') != null) ? $this->request->getVar('groups') : [];
            $groups = ($this->request->getVar('isadmin') != null) ? [1] : [2];
            $is_admin = ($this->request->getVar('isadmin') != null) ? 1 : 0;
            $remote = ($this->request->getVar('isremote') != null) ? 1 : 0;
            $first_name = $this->request->getVar('first_name');
            $last_name = $this->request->getVar('last_name');
            $company = $this->request->getVar('company');

            $data = [
                    "installation_key" => $this->setting->getInstallationKey(),
                    "first_name" => $first_name,
                    "last_name" => $last_name,
                    "company" => $company,
                    "is_admin" => $is_admin,
                    "remote" => $remote
            ];

            $userModel = new \App\Models\User();

            try {
                $userModel->createUser($email, $email, $this->authAdapter, $groups, $data);
                $this->setStatusMessage("User '$email' was created.", STATUS_SUCCESS);
            } catch (\Exception $ex) {
                $this->setStatusMessage("There was a problem creating '$email'.", STATUS_ERROR);
            }
            return redirect()->to(base_url($this->controllerName.'/List'));

        }
        else {
            $uidata->data['groups'] = $groups;
            //display the create user form
            //set the flash data error message if there is one
            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $uidata->data['first_name'] = array(
                    'name'  => 'first_name',
                    'id'    => 'first_name',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => set_value('first_name'),
            );
            $uidata->data['last_name'] = array(
                    'name'  => 'last_name',
                    'id'    => 'last_name',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => set_value('last_name'),
            );
            $uidata->data['email'] = array(
                    'name'  => 'email',
                    'id'    => 'email',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => set_value('email'),
            );
            $uidata->data['company'] = array(
                    'name'  => 'company',
                    'id'    => 'company',
                    'type'  => 'text',
                    'class' => 'form-control',
                    'value' => set_value('company'),
            );
            $uidata->data['isadmin'] = array(
                'name'  => 'isadmin',
                'id'    => 'isadmin',
                'class' => 'custom-control-input',
                'value' => 1,
            );
            $uidata->data['isremote'] = array(
                'name'  => 'isremote',
                'id'    => 'isremote',
                'class' => 'custom-control-input',
                'value' => 1,
            );
            // Uncomment in order to add orcid field
            /*
            $uidata->data['orcid'] = array(
                    'name' => 'orcid',
                    'id' => 'orcid',
                    'type' => 'text',
                    'value' => set_value('orcid'),
            );
            */
        }
        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Create', $data);
    }

    function List(){
        $uidata = new UIData();
        $uidata->title = "Users";
        $uidata->stickyFooter = false;

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
        return view($this->viewDirectory. '/List', $data);
    }

    function Update(int $id){
        $uidata = new UIData();
        $uidata->title = "Edit User";
        $uidata->stickyFooter = false;

        $userModel = new \App\Models\User();
        $networkModel = new Network();

        $user = $userModel->getUsers(null, ["id" => $id]);
        if (count($user) != 1) {
            $this->setStatusMessage("User was not found.", STATUS_WARNING);
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else {
            $user = $user[0];
            $uidata->data['user_id'] = $user['id'];
            $groups = $networkModel->getNetworkGroupsForInstallation();

            $this->validation->setRules([
                'first_name' => [
                        'label'  => 'First Name',
                        'rules'  => 'required',
                        'errors' => [
                            'required' => '{field} is required.'
                        ]
                ],
                'last_name' => [
                    'label'  => 'Last Name',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} is required.',
                    ]
                ],
                'company' => [
                    'label'  => 'Institute/Laboratory/Company Name',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} is required.'
                    ]
                ]
            ]
            );

            if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {

                $email    = $this->request->getVar('uemail');
                $groups = ($this->request->getVar('isadmin') != null) ? [1] : [0];
                $is_admin = ($this->request->getVar('isadmin') != null) ? 1 : 0;
                $remote = ($this->request->getVar('isremote') != null) ? 1 : 0;
                $first_name = $this->request->getVar('first_name');
                $last_name = $this->request->getVar('last_name');
                $company = $this->request->getVar('company');
                $active = ($this->request->getVar('active') != null) ? 1 : 0;

                $data = [
                        "first_name" => $first_name,
                        "last_name" => $last_name,
                        "company" => $company,
                        "is_admin" => $is_admin,
                        "remote" => $remote,
                        "active" => $active
                ];

                try {
                    $userModel->updateUser($id, $this->authAdapter, $groups, $data);
                    $this->setStatusMessage("User '$email' was updated.", STATUS_SUCCESS);

                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem updating '$email'.", STATUS_WARNING);
                }
                return redirect()->to(base_url($this->controllerName.'/List'));
            }
            else {
                $uidata->data['groups'] = $groups;

                $user_groups = $networkModel->getNetworkGroupsForInstallationForUser((int)$user['id']);

                $selected_groups = [];
                foreach ($groups as $g) {
                    foreach ($user_groups as $ug) {
                        if ($g['id'] == $ug['group_id']) {
                            array_push($selected_groups, $g['id']);
                        }
                    }
                }

                $uidata->data['selected_groups'] = $selected_groups;
                //display the create user form
                //set the flash data error message if there is one
                $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

                $uidata->data['first_name'] = array(
                        'name'  => 'first_name',
                        'id'    => 'first_name',
                        'type'  => 'text',
                        'class' => 'form-control',
                        'value' => set_value('first_name', ($user['first_name']) ? $user['first_name'] : '')
                );
                $uidata->data['last_name'] = array(
                        'name'  => 'last_name',
                        'id'    => 'last_name',
                        'type'  => 'text',
                        'class' => 'form-control',
                        'value' => set_value('last_name',  ($user['last_name']) ? $user['last_name'] : '')
                );
                $uidata->data['email'] = array(
                        'name'  => 'email',
                        'id'    => 'email',
                        'type'  => 'text',
                        'class' => 'form-control',
                        'value' => set_value('email', $user['email'])
                );
                $uidata->data['company'] = array(
                        'name'  => 'company',
                        'id'    => 'company',
                        'type'  => 'text',
                        'class' => 'form-control',
                        'value' => set_value('company', ($user['company']) ? $user['company'] : '')
                );
                $uidata->data['isadmin'] = array(
                    'name'  => 'isadmin',
                    'id'    => 'isadmin',
                    'class' => 'custom-control-input',
                    'value' => set_value('isadmin', $user['is_admin']),
                    'checked' => ($user['is_admin'] == 1) ? true : false
                );
                $uidata->data['isremote'] = array(
                    'name'  => 'isremote',
                    'id'    => 'isremote',
                    'class' => 'custom-control-input',
                    'value' => set_value('isremote', $user['remote']),
                    'checked' => ($user['remote'] == 1) ? true : false
                );
                $uidata->data['active'] = array(
                    'name'  => 'active',
                    'id'    => 'active',
                    'class' => 'custom-control-input',
                    'value' => set_value('active', $user['active']),
                    'checked' => ($user['active'] == 1) ? true : false
                );

                $uidata->data['uemail'] = $user['email'];
            }
            $data = $this->wrapData($uidata);
            return view($this->viewDirectory. '/Update', $data);
        }
    }

    function Delete(int $id){
        $uidata = new UIData();
        $userModel = new \App\Models\User($this->db);

        $uidata->title = "Delete User";

        $user = $userModel->getUsers('id, email, first_name, last_name', ["id" => $id]);
        if (count($user) != 1) {
            $this->setStatusMessage("User was not found.", STATUS_WARNING);
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else {
            $user = $user[0];
            $email = $user['email'];
            $uidata->data['id'] = $user['id'];
            $uidata->data['first_name'] = $user['first_name'];
            $uidata->data['last_name'] = $user['last_name'];

            $this->validation->setRules([
                'confirm' => [
                    'label'  => 'confirmation',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} is required.'
                    ]
                ]
            ]);
            if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
                if ($this->request->getVar('confirm') == 'yes') {
                    try {
                        //delete user
                        $userModel->deleteUser((int)$user['id'], $this->authAdapter);
                        $this->setStatusMessage("User '$email' was removed.", STATUS_SUCCESS);

                    } catch (\Exception $ex) {
                        $this->setStatusMessage("There was a problem removing '$email'.", STATUS_ERROR);
                    }
                }
                return redirect()->to(base_url($this->controllerName.'/List'));
            }
            $data = $this->wrapData($uidata);
            return view($this->viewDirectory. '/Delete', $data);
        }
    }

    function Details(int $id){
	$uidata = new UIData();
        $uidata->title = "User Details";
        $uidata->stickyFooter = false;

        $userModel = new \App\Models\User($this->db);
        $networkModel = new Network($this->db);

        $user = $userModel->getUsers('id, email, first_name, last_name,company,active,remote,phone,last_login,created_on,is_admin,ip_address', ["id" => $id]);
        $user = $user[0];
        $email = $user['email'];
        $uidata->data['uemail'] = $user['email'];
        $uidata->data['id'] = $user['id'];
        $uidata->data['first_name'] = $user['first_name'];
        $uidata->data['last_name'] = $user['last_name'];
        $uidata->data['user_id'] = $user['id'];
        $uidata->data['company'] = $user['company'];
        $uidata->data['active'] = $user['active'];
        $uidata->data['remote'] = $user['remote'];
        $uidata->data['phone'] = $user['phone'];
        $uidata->data['last_login'] = $user['last_login'];
        //$uidata->data['last_login'] = Time::createFromTimestamp($user['last_login'],'Europe/London','en_US');
        $uidata->data['created_on'] = $user['created_on'];
        //$uidata->data['created_on'] = Time::createFromTimestamp($user['created_on'],'Europe/London','en_US');
        $uidata->data['is_admin'] = $user['is_admin'];
        $uidata->data['ip_address'] = $user['ip_address'];
        $users_groups_data = $networkModel->getCurrentNetworkGroupsForUsers();

        $users_groups = array();
        // If there were groups fetch from auth server for users then add them to the view
        if (!array_key_exists('error', $users_groups_data)) {
            foreach ($users_groups_data as $group) {
                $users_groups[$group['user_id']][] = array('network_name' => $group['network_name'], 'group_id' => $group['group_id'], 'group_name' => $group['name'], 'group_description' => $group['description']);
            }
            $uidata->data['users_groups'] = $users_groups;
        }
        $uidata->css = array(VENDOR . 'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->javascript = array(JS . "cafevariome/components/datatable.js", JS . "cafevariome/admin.js", VENDOR . 'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Details', $data);
    }
}
