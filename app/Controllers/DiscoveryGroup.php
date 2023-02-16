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
use App\Models\UIData;
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

        $uidata->IncludeJavaScript(JS. 'cafevariome/discoverygroup.js');
		$uidata->IncludeDataTables();

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/List', $data);
	}

    public function Create()
	{
        $uidata = new UIData();
		$uidata->title = "Create Discovery Group";

		$uidata->IncludeJavaScript(JS. 'cafevariome/discoverygroup.js');

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
				'rules' => 'permit_empty|text_validator[Description]|max_length[512]',
				'errors' => [
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
				'class' => 'form-select',
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
				'class' => 'form-select',
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
		$uidata->title = "Edit Discovery Group";
		$uidata->data['discovery_group_id'] = $discoveryGroup->getID();
		$uidata->IncludeJavaScript(JS. 'cafevariome/discoverygroup.js');

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
					'rules' => 'permit_empty|text_validator[Description]|max_length[512]',
					'errors' => [
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

				$this->setStatusMessage("Discovery group '$name' was updated.", STATUS_SUCCESS);
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

			$selectedUsers = $this->dbAdapter->ReadAssociatedUserIds([$id]);

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
				'class' => 'form-select',
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
				'class' => 'form-select',
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
}
