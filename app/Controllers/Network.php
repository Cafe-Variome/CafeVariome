<?php namespace App\Controllers;

/**
 * Name: Network.php
 * Created: 18/07/2019
 *
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Libraries\CafeVariome\Factory\NetworkAdapterFactory;
use App\Libraries\CafeVariome\Factory\NetworkFactory;
use App\Libraries\CafeVariome\Factory\UserAdapterFactory;
use App\Models\UIData;
use App\Libraries\CafeVariome\Net\NetworkInterface;
use CodeIgniter\Config\Services;

class Network extends CVUI_Controller
{

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
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
		$this->dbAdapter = (new NetworkAdapterFactory())->GetInstance();
    }

    public function Index()
	{
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    /**
     *
     */

    public function List()
    {
        $uidata = new UIData();
        $uidata->data['title'] = "Networks";
        $uidata->data['message'] = '';
        $networks = [];

        $networkInterface = new NetworkInterface();
        try
		{
            $response = $networkInterface->GetNetworksByInstallationKey($this->setting->GetInstallationKey());
            if ($response->status)
			{
                $networks = $response->data;
            }
        }
		catch (\Exception $ex)
		{
            $uidata->data['message'] = 'There was a problem retrieving networks: ' . $ex->getMessage();
        }

        $uidata->data['networks'] = $networks;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(JS.'cafevariome/network.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory."/List", $data);
    }

    /**
     * Create
     */
    public function Create()
	{
        $uidata = new UIData();
        $uidata->data['title'] = "Create Network";

        // Validate form input
        $this->validation->setRules([
            'name' => [
                'label'  => 'Network Name',
                'rules'  => 'required|alpha_dash|is_unique[networks.name]',
                'errors' => [
                    'required' => '{field} is required.',
                    'uniquename_check' => '{field} already exists.'
                ]
            ]
        ]
        );

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
            $name = strtolower($this->request->getVar('name')); // Convert the network name to lowercase

            $networkInterface = new NetworkInterface();
            $response = $networkInterface->CreateNetwork(['network_name' => $name, 'network_type' => 1, 'network_threshold' => 0, 'network_status' => 1]);
            if (!$response->status)
			{
                //something failed
                if ($response->message != null)
				{
                    $this->setStatusMessage($response->message, STATUS_ERROR);
                }
				else
				{
                    $this->setStatusMessage("There was a problem communicating with the network software.", STATUS_ERROR);
                }
            }
            else
			{
                //operation successful
                $network_key = $response->data->network_key;

                //Add Installation to Network
                $addInstallationResponse = $networkInterface->AddInstallationToNetwork(['network_key' => $network_key]);

                //create local replication of network
                if (!$addInstallationResponse->status)
				{
                    $this->setStatusMessage("There was a problem adding this installation to '$name'.", STATUS_ERROR);
                }
                else
				{
					try
					{
						$this->dbAdapter->Create((new NetworkFactory())->GetInstanceFromParameters($response->data->network_key, $name));
						$this->setStatusMessage("Network '$name' was created successfully.", STATUS_SUCCESS);

					}
					catch (\Exception $ex)
					{
						$this->setStatusMessage("There was a problem creating a local record of '$name':" . $ex->getMessage(), STATUS_ERROR);
					}

                    return redirect()->to(base_url($this->controllerName.'/List'));
                }
            }
       }
       else
	   {
            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
       }

        $uidata->data['name'] = array(
            'name' => 'name',
            'id' => 'name',
            'type' => 'text',
            'class' => 'form-control',
            'value' =>set_value('name'),
        );

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory.'/Create', $data);

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
                    $this->networkModel->deleteAllUsersFromNetworkGroup($group_id);
                    $network_key = $this->networkModel->getNetworkKeybyGroupId($group_id);

                    foreach ($this->request->getVar('users') as $user_id)
                    {
                        $this->networkModel->addUserToNetworkGroup($user_id, $group_id, $installation_key, $network_key);
                    }

                    $this->networkModel->deleteUserFromAllOtherNetworkGroups($network_key, $this->request->getVar('users'));
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

    public function Join()
	{
        $uidata = new UIData();
        $uidata->data['title'] = "Join Network";
        // Validate form input
        $this->validation->setRules([
			'network' => [
				'label' => 'Network',
				'rules' => 'required|integer|greater_than[0]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => '{field} must be an integer.',
					'greater_than' => 'Please select a network.',
				]
			],
            'justification' => [
                'label'  => 'Justification',
                'rules'  => 'required|alpha_dash',
                'errors' => [
                    'required' => '{field} is required.',
                ]
            ]
        ]
        );

        $uidata->javascript = array(JS.'cafevariome/network.js');

        $networkInterface = new NetworkInterface();

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
            $network_key = $this->request->getVar('network');
            $justification = $this->request->getVar('justification');

			$userAdapter = (new UserAdapterFactory())->GetInstance();
			$email = $userAdapter->ReadEmail($this->authenticator->getUserId());

            $join_response = $networkInterface->RequestToJoinNetwork($network_key, $email, $justification);
            if ($join_response->status)
			{
                $this->setStatusMessage("Your request to join the network was sent successfully.", STATUS_SUCCESS);
            }
            else
			{
                $this->setStatusMessage("There was a problem communicating with network software.", STATUS_ERROR);
            }
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else
		{
            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$networks_response = $networkInterface->GetAvailableNetworks();

			$networks = [0 => 'Please select a network to join.'];

			if ($networks_response->status == 1)
			{
				foreach ($networks_response->data as $network)
				{
					$networks[$network->network_key] = $network->network_name;
				}
			}

			$uidata->data['network'] = [
				'id' => 'network',
				'name' => 'network',
				'type' => 'dropdown',
				'options' => $networks,
				'class' => 'form-control'
			];

            $uidata->data['justification'] = array(
                'name' => 'justification',
                'id' => 'justification',
                'type' => 'text',
                'rows' => '5',
                'cols' => '3',
                'class' => 'form-control',
                'value' => set_value('justification'),
            );

            $data = $this->wrapData($uidata);

            return view($this->viewDirectory.'/Join', $data);
        }
    }

    /**
     * @deprecated
     */
    private function create_remote_user() {

        if (isset($_POST['rUser'])) {
            $remote_email = htmlentities(filter_var($_POST['rUser'], FILTER_VALIDATE_EMAIL), ENT_QUOTES);
            if (!$this->userModel->userExists($remote_email)) {
                $user_id = $this->userModel->createRemoteUser($remote_email);
                $user = ['status' => "success", 'data' => ['username' => $remote_email, 'id' => $user_id]];
                echo json_encode($user);
            }
            else {
                $user = ['status' => 'exists'];
                return json_encode($user);
            }
        }
        else {
            $user = ['status' => 'failure'];
            return json_encode($user);
        }
    }

	/**
	 * @deprecated
	 * @param $network_key
	 * @return \CodeIgniter\HTTP\RedirectResponse|string
	 */
    private function Update_Threshold($network_key) {

        $networkInterface = new NetworkInterface();
        $response = $networkInterface->GetNetworkThreshold((int)$network_key);

        $uidata = new UIData();
        $uidata->data['title'] = "Edit Network Threshold";

        // Validate form input
        $this->validation->setRules([
            'network_threshold' => [
                'label'  => 'Network Threshold',
                'rules'  => 'required|alpha_dash|is_natural',
                'errors' => [
                    'required' => '{field} is required.',
                    'is_natural' => '{field} must be a positive integer.'
                ]
            ]
        ]
        );

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $network_threshold = (int)$this->request->getVar('network_threshold');

            $thresholdResponse = $networkInterface->SetNetworkThreshold($network_key, $network_threshold);
            if ($thresholdResponse->status == 1) {
                return redirect()->to(base_url($this->controllerName.'/index'));
            }

        }
        else {
            if ($response->status == 0) {
                //something failed
                return redirect()->to(base_url($this->controllerName.'/index'));
            }
            else{
                $network_threshold = $response->data->network_threshold;
                //$uidata->data['network_threshold'] = $network_threshold;
                $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

                $uidata->data['network_threshold'] = array(
                    'name' => 'network_threshold',
                    'id' => 'network_threshold',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' =>set_value('network_threshold', $network_threshold),
                );
            }
        }

        $uidata->data['network_key'] = $network_key;

        $uidata->javascript = array(JS."/cafevariome/network.js");
        $data = $this->wrapData($uidata);

        return view($this->viewDirectory.'/Update_Threshold', $data);
    }

    public function Leave(int $network_key)
    {
        $networkInterface = new NetworkInterface();
        $networkResponse = $networkInterface->GetNetwork($network_key);

        $uidata = new UIData();
        $uidata->title = "Leave Network";

        if (!$networkResponse->status || $networkResponse->data == null)
		{
            $this->setStatusMessage("There was a problem communicating with network software.", STATUS_ERROR);
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else
		{
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
                    $name = $this->request->getVar('name');
                    $networkResponse = $networkInterface->LeaveNetwork($network_key);
                    $this->setStatusMessage("Your installation has successfully left '$name'.", STATUS_SUCCESS);

                    if ($networkResponse->status)
					{
                        //Left the network.
                        //Now delete the local replica if it exists.
                        try
						{
                            $this->dbAdapter->Delete($network_key);
                        }
						catch (\Exception $ex)
						{
                            $this->setStatusMessage("There was a problem removing local record of '$name': " . $ex->getMessage(), STATUS_ERROR, true);
                        }
                        return redirect()->to(base_url($this->controllerName.'/List'));
                    }
                }
                return redirect()->to(base_url($this->controllerName.'/List'));
            }

            $uidata->data['confirm'] = array(
                'name' => 'confirm',
                'type' => 'radio',
                'class' => 'form-control',
            );

            $uidata->data['network_key'] = $network_key;
            $uidata->data['network_name'] = $networkResponse->data->network_name;

            $data = $this->wrapData($uidata);
            return view($this->viewDirectory.'/Leave', $data);
        }
    }
}
