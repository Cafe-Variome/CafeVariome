<?php namespace App\Controllers;

/**
 * User.php
star *
 * Created : 11/09/2019
 *
 * User controller class
 *
 */

use App\Libraries\CafeVariome\Factory\UserAdapterFactory;
use App\Libraries\CafeVariome\Factory\UserFactory;
use App\Models\UIData;
use App\Models\Settings;
use App\Models\Network;
use App\Models\Source;
use App\Helpers\AuthHelper;
use CodeIgniter\Config\Services;

class User extends CVUI_Controller
{

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
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);
		$this->dbAdapter = (new UserAdapterFactory())->GetInstance();

        $this->validation = Services::validation();

    }

    public function Index()
	{
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function Create()
	{
        $uidata = new UIData();
        $uidata->title = "Create User";
        $uidata->stickyFooter = false;

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

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
            $email    = $this->request->getVar('email');
            $is_admin = ($this->request->getVar('is_admin') != null) ? 1 : 0;
            $remote = ($this->request->getVar('remote') != null) ? 1 : 0;
            $first_name = $this->request->getVar('first_name');
            $last_name = $this->request->getVar('last_name');
            $company = $this->request->getVar('company');

            try
			{
				$this->dbAdapter->Create(
					(new UserFactory())->getInstanceFromParameters(
						$email,
						$email,
						$first_name,
						$last_name,
						$this->request->getIPAddress(),
						time(),
						null,
						$company,
						$is_admin,
						$remote
				));
                $this->setStatusMessage("User '$email' was created.", STATUS_SUCCESS);
            }
			catch (\Exception $ex)
			{
                $this->setStatusMessage("There was a problem creating '$email': " . $ex->getMessage(), STATUS_ERROR);
            }
            return redirect()->to(base_url($this->controllerName.'/List'));

        }
        else
		{
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
            $uidata->data['is_admin'] = array(
                'name'  => 'is_admin[]',
                'id'    => 'is_admin',
                'class' => 'custom-control-input',
                'value' => 1,
            );
            $uidata->data['remote'] = array(
                'name'  => 'remote[]',
                'id'    => 'remote',
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

    public function List()
	{
        $uidata = new UIData();
        $uidata->title = "Users";
        $uidata->stickyFooter = false;

        $networkModel = new Network($this->db);

		$uidata->data['message'] = $this->session->getFlashdata('activation_email_unsuccessful');

        $uidata->data['users'] = $this->dbAdapter->ReadAll();

		$users_groups_data = $networkModel->getCurrentNetworkGroupsForUsers();

        $users_groups = array();
		// If there were groups fetch from auth server for users then add them to the view
		if (! array_key_exists('error', $users_groups_data))
		{
			foreach ( $users_groups_data as $group )
			{
				$users_groups[$group['user_id']][] = array('network_name' => $group['network_name'], 'group_id' => $group['group_id'], 'group_name' => $group['name'], 'group_description' => $group['description']);
			}
			$uidata->data['users_groups'] = $users_groups;
		}
        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->javascript = array(JS."cafevariome/components/datatable.js", JS."cafevariome/user.js", VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/List', $data);
    }

    public function Update(int $id)
	{
		$user = $this->dbAdapter->Read($id);
		if ($user->isNull())
		{
			$this->setStatusMessage("User was not found.", STATUS_WARNING);
			return redirect()->to(base_url($this->controllerName.'/List'));
		}

        $uidata = new UIData();
        $uidata->title = "Edit User";

		$uidata->data['id'] = $user->getID();

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

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$email    = $this->request->getVar('uemail');
			$is_admin = ($this->request->getVar('is_admin') != null) ? 1 : 0;
			$remote = ($this->request->getVar('remote') != null) ? 1 : 0;
			$first_name = $this->request->getVar('first_name');
			$last_name = $this->request->getVar('last_name');
			$company = $this->request->getVar('company');
			$active = ($this->request->getVar('active') != null) ? 1 : 0;

			try
			{
				$this->dbAdapter->Update($id, (new UserFactory)->getInstanceFromParameters(
					$email,
					$email,
					$first_name,
					$last_name,
					$this->request->getIPAddress(),
					$user->created_on,
					null,
					$company,
					$is_admin,
					$remote,
					$active
				));

				$this->setStatusMessage("User '$email' was updated.", STATUS_SUCCESS);

			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating '$email'.", STATUS_WARNING);
			}
			return redirect()->to(base_url($this->controllerName.'/List'));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['first_name'] = array(
					'name'  => 'first_name',
					'id'    => 'first_name',
					'type'  => 'text',
					'class' => 'form-control',
					'value' => set_value('first_name', $user->first_name)
			);
			$uidata->data['last_name'] = array(
					'name'  => 'last_name',
					'id'    => 'last_name',
					'type'  => 'text',
					'class' => 'form-control',
					'value' => set_value('last_name',  $user->last_name)
			);
			$uidata->data['email'] = array(
					'name'  => 'email',
					'id'    => 'email',
					'type'  => 'text',
					'class' => 'form-control',
					'value' => set_value('email', $user->email)
			);
			$uidata->data['company'] = array(
					'name'  => 'company',
					'id'    => 'company',
					'type'  => 'text',
					'class' => 'form-control',
					'value' => set_value('company', $user->company)
			);
			$uidata->data['is_admin'] = array(
				'name'  => 'is_admin[]',
				'id'    => 'is_admin',
				'class' => 'custom-control-input',
				'value' => set_value('is_admin', $user->is_admin),
				'checked' => (bool)$user->is_admin
			);
			$uidata->data['remote'] = array(
				'name'  => 'remote[]',
				'id'    => 'remote',
				'class' => 'custom-control-input',
				'value' => set_value('remote', $user->remote),
				'checked' => (bool)$user->remote
			);
			$uidata->data['active'] = array(
				'name'  => 'active[]',
				'id'    => 'active',
				'class' => 'custom-control-input',
				'value' => set_value('active', $user->active),
				'checked' => (bool)$user->active
			);

			$uidata->data['uemail'] = $user->email;
		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory. '/Update', $data);

    }

    public function Delete(int $id)
	{
		$user = $this->dbAdapter->Read($id);
		if ($user->isNull())
		{
			$this->setStatusMessage("User was not found.", STATUS_WARNING);
			return redirect()->to(base_url($this->controllerName.'/List'));
		}

        $uidata = new UIData();
        $uidata->title = "Delete User";

		$uidata->data['user'] = $user;

		$this->validation->setRules([
			'confirm' => [
				'label'  => 'confirmation',
				'rules'  => 'required',
				'errors' => [
					'required' => '{field} is required.'
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			if ($this->request->getVar('confirm') == 'yes')
			{
				try
				{
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("User was removed.", STATUS_SUCCESS);
				}
				catch (\Exception $ex)
				{
					$this->setStatusMessage("There was a problem removing user: " . $ex->getMessage(), STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List'));
		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory. '/Delete', $data);
    }

    public function Details(int $id)
	{
		$user = $this->dbAdapter->Read($id);
		if ($user->isNull())
		{
			$this->setStatusMessage("User was not found.", STATUS_WARNING);
			return redirect()->to(base_url($this->controllerName.'/List'));
		}

		$uidata = new UIData();
        $uidata->title = "User Details";

        $uidata->data['user'] = $user;

        $uidata->css = array(VENDOR . 'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->javascript = array(JS . "cafevariome/components/datatable.js", JS . "cafevariome/user.js", VENDOR . 'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Details', $data);
    }
}
