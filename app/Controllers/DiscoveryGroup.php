<?php namespace App\Controllers;

/**
 * Name: DiscoveryGroup.php
 * Created: 31/07/2019
 *
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Entities\ViewModels\DiscoveryGroupDetails;
use App\Libraries\CafeVariome\Entities\ViewModels\DiscoveryGroupList;
use App\Libraries\CafeVariome\Entities\ViewModels\SourceDropDown;
use App\Libraries\CafeVariome\Entities\ViewModels\UserDropDown;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupAdapterFactory;
use App\Libraries\CafeVariome\Factory\DiscoveryGroupFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\UserAdapterFactory;
use App\Libraries\CafeVariome\Helpers\UI\DiscoveryGroupHelper;
use App\Models\Network;
use App\Models\UIData;
use App\Models\User;
use App\Models\Source;
use App\Helpers\AuthHelper;
use CodeIgniter\Config\Services;
use App\Libraries\CafeVariome\Net\NetworkInterface;

class DiscoveryGroup extends CVUI_Controller
{

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
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
        parent::setProtected(true);
        parent::setIsAdmin(true);
		parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
		$this->dbAdapter = (new DiscoveryGroupAdapterFactory())->GetInstance();

		$this->networkModel = new Network();
		$this->networkGroupModel = new \App\Models\NetworkGroup();
        $this->userModel = new User();
	}

	public function Index()
	{
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

	public function List()
	{
		$uidata = new UIData();
		$uidata->title = "Discovery Groups";

		$uidata->data["discoveryGroups"] = $this->dbAdapter->SetModel(DiscoveryGroupList::class)->ReadAll();

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(
			VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js',
			JS.'cafevariome/components/datatable.js',
			JS. 'cafevariome/discoverygroup.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/List', $data);
	}

    public function Create()
	{
        $uidata = new UIData();
		$uidata->title = "Create Discovery Group";

		$uidata->javascript = array(JS. 'cafevariome/discoverygroup.js');


		$networkInterface = new NetworkInterface();

        //validate form input
        $this->validation->setRules([
			'name' => [
				'label'  => 'Name',
				'rules'  => 'required|alpha_numeric_space|max_length[128]|unique_discovery_group_name_check['. $this->request->getVar('network') . ']',
				'errors' => [
					'required' => '{field} is required.',
					'max_length' => 'Maximum length of {field} is 128 characters.',
					'unique_discovery_group_name_check' => '{field} already exists.'
				]
			],
			'description' => [
				'label'  => 'Description',
				'rules' => 'permit_empty|alpha_numeric_punct|max_length[512]',
				'errors' => [
					'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetic characters, numbers, spaces, and some punctuation marks.',
					'max_length' => 'Maximum length of {field} is 128 characters.'
				]
			],
            'network' => [
				'label'  => 'Network',
				'rules' => 'required|integer|greater_than[0]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} must be an integer.',
					'greater_than' => 'Please select a network.'
				]
            ],
			'policy' => [
				'label' => 'Policy',
				'rules' => 'required|integer|greater_than[0]|less_than[256]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} must be an integer.',
					'greater_than' => 'Please select a policy.',
					'less_than' => 'Invalid policy selected.',
				]
			]
        ]
        );

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			// Create the new group
			$name =  $this->request->getVar('name');
			$description = $this->request->getVar('description');
			$policy = $this->request->getVar('policy');
			$network_id = $this->request->getVar('network');

			$users = $this->request->getVar('users[]');
			$sources =	$this->request->getVar('sources[]');

			try
			{
				$id = $this->dbAdapter->Create((new DiscoveryGroupFactory())->GetInstanceFromParameters($name, $description, $network_id, $policy));

				if (is_array($users))
				{
					$this->dbAdapter->CreateUserAssociations($id, $users);
				}

				if(is_array($sources))
				{
					$this->dbAdapter->CreateSourceAssociations($id, $sources);
				}

				$this->setStatusMessage("Discovery group '$name' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating discovery group: " . $ex->getMessage(), STATUS_ERROR);
			}
			return redirect()->to(base_url($this->controllerName . '/List'));

		}
		else
		{
			$networks_response = $networkInterface->GetNetworksByInstallationKey($this->setting->GetInstallationKey());

			$networks = [0 => 'Please select a network'];

			if ($networks_response->status == 1)
			{
				foreach ($networks_response->data as $network)
				{
					$networks[$network->network_key] = $network->network_name;
				}
			}

			$userAdapter = (new UserAdapterFactory())->GetInstance();
			$users = $userAdapter->SetModel(UserDropDown::class)->ReadAll();

			$userList = [];

			foreach ($users as $user)
			{
				$userList[$user->getID()] = $user->first_name . ' ' . $user->last_name . ' (' . $user->email . ')';
			}

			$sourceAdapter = (new SourceAdapterFactory())->GetInstance();
			$sources = $sourceAdapter->SetModel(SourceDropDown::class)->ReadAll();

			$sourceList = [];

			foreach ($sources as $source)
			{
				$sourceList[$source->getID()] = $source->name;
			}

            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
			$uidata->data['name'] = array(
				'name'  => 'name',
				'id'    => 'name',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => set_value('name')
			);

			$uidata->data['description'] = array(
				'name'  => 'description',
				'id'    => 'description',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => set_value('description')
			);

			$uidata->data['network'] = array(
				'name'  => 'network',
				'id'    => 'network',
				'type'  => 'dropdown',
				'class' => 'form-control',
				'options' => $networks,
				'value' => set_value('network')
			);

			$uidata->data['users'] = array(
				'name' => 'users[]',
				'id'    => 'users',
				'class' => 'form-control',
				'multiselect' => 'multiselect',
				'options' => $userList,
				'selected' => set_value('users[]')
			);

			$uidata->data['sources'] = array(
				'name' => 'sources[]',
				'id'    => 'sources',
				'class' => 'form-control',
				'multiselect' => 'multiselect',
				'options' => $sourceList,
				'selected' => set_value('sources[]')
			);

			$uidata->data['policy'] = array(
				'name'  => 'policy',
				'id'    => 'policy',
				'type'  => 'dropdown',
				'class' => 'form-control',
				'options' => [
					0 => 'Please select a policy',
					DISCOVERY_GROUP_POLICY_EXISTENCE => DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_EXISTENCE),
					DISCOVERY_GROUP_POLICY_BOOLEAN => DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_BOOLEAN),
					DISCOVERY_GROUP_POLICY_COUNT => DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_COUNT),
					DISCOVERY_GROUP_POLICY_LIST_WITH_ATTRIBUTES => DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_LIST_WITH_ATTRIBUTES)
				],
				'value' => set_value('policy')
            );

            $data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Create', $data);
		}

    }

	public function Update(int $id)
	{
		$discoveryGroup = $this->dbAdapter->Read($id);

		if ($discoveryGroup->isNull())
		{
			$this->setStatusMessage("Discovery group was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = "Create Discovery Group";
		$uidata->data['discovery_group_id'] = $discoveryGroup->getID();
		$uidata->javascript = array(JS. 'cafevariome/discoverygroup.js');

		$networkInterface = new NetworkInterface();

		//validate form input
		$this->validation->setRules([
				'name' => [
					'label'  => 'Name',
					'rules'  => 'required|alpha_numeric_space|max_length[128]|unique_discovery_group_name_check['. $this->request->getVar('network') . ',' . $id . ']',
					'errors' => [
						'required' => '{field} is required.',
						'max_length' => 'Maximum length of {field} is 128 characters.',
						'unique_discovery_group_name_check' => '{field} already exists.'
					]
				],
				'description' => [
					'label'  => 'Description',
					'rules' => 'permit_empty|alpha_numeric_punct|max_length[512]',
					'errors' => [
						'alpha_numeric_punct' => 'The only valid characters for {field} are alphabetic characters, numbers, spaces, and some punctuation marks.',
						'max_length' => 'Maximum length of {field} is 128 characters.'
					]
				],
				'network' => [
					'label'  => 'Network',
					'rules' => 'required|integer|greater_than[0]',
					'errors' => [
						'required' => '{field} is required.',
						'integer' => '{field} must be an integer.',
						'greater_than' => 'Please select a network.'
					]
				],
				'policy' => [
					'label' => 'Policy',
					'rules' => 'required|integer|greater_than[0]|less_than[256]',
					'errors' => [
						'required' => '{field} is required.',
						'integer' => '{field} must be an integer.',
						'greater_than' => 'Please select a policy.',
						'less_than' => 'Invalid policy selected.',
					]
				]
			]
		);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			// Create the new group
			$name =  $this->request->getVar('name');
			$description = $this->request->getVar('description');
			$policy = $this->request->getVar('policy');
			$network_id = $this->request->getVar('network');

			$users = $this->request->getVar('users[]');
			$sources =	$this->request->getVar('sources[]');

			try
			{
				$this->dbAdapter->Update($id, (new DiscoveryGroupFactory())->GetInstanceFromParameters($name, $description, $network_id, $policy));

				if (is_array($users))
				{
					$this->dbAdapter->DeleteUserAssociations($id);
					$this->dbAdapter->CreateUserAssociations($id, $users);
				}

				if(is_array($sources))
				{
					$this->dbAdapter->DeleteSourceAssociations($id);
					$this->dbAdapter->CreateSourceAssociations($id, $sources);
				}

				$this->setStatusMessage("Discovery group '$name' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating discovery group: " . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List'));
		}
		else
		{
			$networks_response = $networkInterface->GetNetworksByInstallationKey($this->setting->GetInstallationKey());
			$networks = [0 => 'Please select a network'];

			if ($networks_response->status == 1)
			{
				foreach ($networks_response->data as $network)
				{
					$networks[$network->network_key] = $network->network_name;
				}
			}

			$userAdapter = (new UserAdapterFactory())->GetInstance();
			$users = $userAdapter->SetModel(UserDropDown::class)->ReadAll();

			$userList = [];

			foreach ($users as $user)
			{
				$userList[$user->getID()] = $user->first_name . ' ' . $user->last_name . ' (' . $user->email . ')';
			}

			$selectedUsers = $this->dbAdapter->ReadAssociatedSourceIds([$id]);

			$sourceAdapter = (new SourceAdapterFactory())->GetInstance();
			$sources = $sourceAdapter->SetModel(SourceDropDown::class)->ReadAll();

			$sourceList = [];

			foreach ($sources as $source)
			{
				$sourceList[$source->getID()] = $source->name;
			}

			$selectedSources = $this->dbAdapter->ReadAssociatedSourceIds([$id]);

			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
			$uidata->data['name'] = array(
				'name'  => 'name',
				'id'    => 'name',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => set_value('name', $discoveryGroup->name)
			);

			$uidata->data['description'] = array(
				'name'  => 'description',
				'id'    => 'description',
				'type'  => 'text',
				'class' => 'form-control',
				'value' => set_value('description', $discoveryGroup->description)
			);

			$uidata->data['network'] = array(
				'name'  => 'network',
				'id'    => 'network',
				'type'  => 'dropdown',
				'class' => 'form-control',
				'options' => $networks,
				'selected' => $discoveryGroup->network_id,
				'value' => set_value('network')
			);

			$uidata->data['users'] = array(
				'name' => 'users[]',
				'id'    => 'users',
				'class' => 'form-control',
				'multiselect' => 'multiselect',
				'options' => $userList,
				'selected' => set_value('users[]', $selectedUsers)
			);

			$uidata->data['sources'] = array(
				'name' => 'sources[]',
				'id'    => 'sources',
				'class' => 'form-control',
				'multiselect' => 'multiselect',
				'options' => $sourceList,
				'selected' => set_value('sources[]', $selectedSources)
			);

			$uidata->data['policy'] = array(
				'name'  => 'policy',
				'id'    => 'policy',
				'type'  => 'dropdown',
				'class' => 'form-control',
				'options' => [
					0 => 'Please select a policy',
					DISCOVERY_GROUP_POLICY_EXISTENCE => DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_EXISTENCE),
					DISCOVERY_GROUP_POLICY_BOOLEAN => DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_BOOLEAN),
					DISCOVERY_GROUP_POLICY_COUNT => DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_COUNT),
					DISCOVERY_GROUP_POLICY_LIST_WITH_ATTRIBUTES => DiscoveryGroupHelper::GetPolicy(DISCOVERY_GROUP_POLICY_LIST_WITH_ATTRIBUTES)
				],
				'selected' => $discoveryGroup->policy,
				'value' => set_value('policy')
			);

			$data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Update', $data);
		}
	}

    public function Delete(int $id)
	{
		$discoveryGroup = $this->dbAdapter->Read($id);

		if ($discoveryGroup->isNull())
		{
			$this->setStatusMessage("Discovery group was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = "Delete Discovery Group";
		$uidata->data['discoveryGroup'] = $discoveryGroup;

		$this->validation->setRules([
            'confirm' => [
                'label'  => 'Confirmation',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.',
                ]
            ]
        ]
        );

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			// do we really want to delete?
			if ($this->request->getVar('confirm') == 'yes')
			{
				try
				{
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Discovery group '$discoveryGroup->name' was deleted.", STATUS_SUCCESS);
				}
				catch (\Exception $ex)
				{
					$this->setStatusMessage("There was a problem deleting discovery group: " . $ex->getMessage(), STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List'));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Delete', $data);
		}
	}

	public function Details(int $id)
	{
		$discoveryGroup = $this->dbAdapter->SetModel(DiscoveryGroupDetails::class)->Read($id);

		if ($discoveryGroup->isNull())
		{
			$this->setStatusMessage("Discovery group was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = 'Discovery Group Details';
		$uidata->data['discoveryGroup'] = $discoveryGroup;

		$sourceIds = $this->dbAdapter->ReadAssociatedSourceIds([$id]);
		$sourceAdapter = (new SourceAdapterFactory())->GetInstance();

		$userIds = $this->dbAdapter->ReadAssociatedSourceIds([$id]);
		$userAdapter = (new UserAdapterFactory())->GetInstance();

		$uidata->data['sources'] = $sourceAdapter->SetModel(SourceDropDown::class)->ReadByIds($sourceIds);
		$uidata->data['users'] = $userAdapter->SetModel(UserDropDown::class)->ReadByIds($userIds);

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Details', $data);
	}
	/**
	 * @deprecated
	 * @param int $id
	 * @param $isMaster
	 * @return \CodeIgniter\HTTP\RedirectResponse|string
	 */
	private function Update_Users(int $id, $isMaster = false) {

        $uidata = new UIData();
        $uidata->title = "Edit User Network Groups";
        $uidata->stickyFooter = false;
        if ($this->request->getPost()){

            $uidata->data['selectedUsers'] = $this->request->getVar('users') ? $this->request->getVar('users') :[];
            $uidata->data['selectedSources'] = $this->request->getVar('sources') ? $this->request->getVar('sources') :[];
            $name = $this->request->getVar('name');

            if ($this->request->getVar('users')) {

                $group_id = $this->request->getVar('id');
                $installation_key = $this->request->getVar('installation_key');

                try {
                    $this->networkModel->deleteAllUsersFromNetworkGroup($group_id, false);
                    $network_key = $this->networkModel->getNetworkKeybyGroupId($group_id);

                    foreach ($this->request->getVar('users') as $user_id)
                    {
                        $this->networkModel->addUserToNetworkGroup($user_id, $group_id, $installation_key, $network_key);
                    }

                    $this->setStatusMessage("Granted users list was updated for '$name'.", STATUS_SUCCESS);
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem updating granted users list for '$name'.", STATUS_ERROR);
                }
            }
            else {
                // No groups selected so remove the user from all network groups
                $group_id = $this->request->getVar('id');
                $isMaster = $this->request->getVar('isMaster');
                $installation_key =  $this->request->getVar('installation_key');
                try {
                    $this->networkModel->deleteAllUsersFromNetworkGroup($group_id, $isMaster);
                    $this->setStatusMessage("Granted users list was updated for '$name'.", STATUS_SUCCESS);
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem removing all users from the list of granted users for '$name'.", STATUS_ERROR);
                }
            }
            if ($this->request->getVar('sources')) {
                $group_id = $this->request->getVar('id');
				$network_key = $this->networkModel->getNetworkKeybyGroupId($group_id);

				$installation_key = $this->request->getVar('installation_key');

                try {
                    $this->networkModel->deleteAllSourcesFromNetworkGroup($group_id, $installation_key);

                    foreach ($this->request->getVar('sources') as $source_id)
                    {
                        $this->networkModel->addSourceToNetworkGroup($source_id, $group_id, $network_key, $installation_key);
                    }
                    $this->setStatusMessage("Granted sources list was updated for '$name'.", STATUS_SUCCESS, true);

                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem updating granted sources list for '$name'.", STATUS_ERROR, true);
                }
            }
            else {
                // No groups selected so remove the user from all network groups
                $group_id = $this->request->getVar('id');
                $installation_key = $this->request->getVar('installation_key');
                try {
                    $this->networkModel->deleteAllSourcesFromNetworkGroup($group_id, $installation_key);
                    $this->setStatusMessage("Granted sources list was updated for '$name'.", STATUS_SUCCESS, true);

                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem removing all sources from the list of granted sources for '$name'.", STATUS_ERROR, true);
                }
            }

			return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else{

            $uidata->data['user_id'] = $id;

            //set the flash data error message if there is one
            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
            $users = $this->userModel->getUsers();
            $selectedUsers = $this->networkModel->getNetworkGroupUsers($id);
            $group_details = $this->networkModel->getNetworkGroup($id);
            $uidata->data['name'] = $group_details['name'];
            $uidata->data['group_type'] = $group_details['group_type'];

            $sourceModel = new Source();

            $sources = $sourceModel->getSources();
            $selectedSources = $sourceModel->getSourceId($id);

            $sourcesList = [];
            $selectedSourcesList = [];

            foreach ($sources as $source) {
                $sourcesList[$source['source_id']] = $source['name'];
            }

            foreach ($selectedSources as $selectedSource) {
                array_push($selectedSourcesList, $selectedSource['source_id']);
            }

            $uidata->data['sources'] = $sourcesList;
            $uidata->data['selectedSources'] = $selectedSourcesList;

            $usersList = [];
            $selectedUsersList = [];

            foreach ($users as $user) {
                $usersList[$user['id']] = $user['email'];
            }

            foreach ($selectedUsers as $user) {
                for($i = 0; $i < count($users); $i++) {
                    if($user['id'] == $users[$i]['id']) {
                        array_push($selectedUsersList, $user['id']);
                    }
                }
            }

            $uidata->data['users'] = $usersList;
            $uidata->data['selectedUsers'] = $selectedUsersList;

            $uidata->data['remote_user_email'] = array(
                'name' => 'remote_user_email',
                'id' => 'remote_user_email',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('remote_user_email'),
            );

            $uidata->data['isMaster'] = ($isMaster ? $isMaster : 0);
            $uidata->data['user_id'] = $id;
            $uidata->data['installation_key'] = $this->setting->getInstallationKey();

            $uidata->javascript = array(JS."cafevariome/network.js", JS."cafevariome/components/transferbox.js");

            $data = $this->wrapData($uidata);
            return view($this->viewDirectory.'/Update_Users', $data);
        }
    }

}
