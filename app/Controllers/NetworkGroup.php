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
use App\Libraries\CafeVariome\Auth\AuthAdapter;
use CodeIgniter\Config\Services;
use App\Libraries\CafeVariome\Net\NetworkInterface;

class NetworkGroup extends CVUI_Controller{

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
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
		parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
		$this->networkModel = new Network();
		$this->networkGroupModel = new \App\Models\NetworkGroup();
        $this->userModel = new User();
	}

	public function Index(){
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

	public function List(){
		$uidata = new UIData();
		$uidata->title = "Network Groups";

		$localNetworkGroups = $this->networkGroupModel->getNetworkGroups(null, ['group_type!=' => 'master'], ['name', 'id'], true); // Local network groups
		$remoteNetworkGroups = $this->networkGroupModel->getRemoteNetworkGroups(); // Remote network groups

		$network_groups_for_installation = [];
		foreach ( $localNetworkGroups as $network_group ) {
			$number_sources = $this->networkModel->countSourcesForNetworkGroup($network_group['id']);
			$network_group['number_of_sources'] = $number_sources;
			$network_groups_for_installation[] = $network_group;
		}

		$networkInterface = new NetworkInterface();

		foreach ( $remoteNetworkGroups as $network_group ) {
			$number_sources = $this->networkModel->countSourcesForNetworkGroup($network_group['id']);
			$network_group['number_of_sources'] = $number_sources;
			$netResp = $networkInterface->GetNetwork($network_group['network_key']);
			$network_group['network_name'] = $netResp->status ? $netResp->data->network_name : "-";
			$network_groups_for_installation[] = $network_group;
		}

		$uidata->data["groups"] = $network_groups_for_installation;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js', JS.'cafevariome/components/datatable.js',JS. 'cafevariome/networkgroup.js');

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/List', $data);
	}

    public function Create(){

        $uidata = new UIData();
		$uidata->title = "Create Group";

		$networkInterface = new NetworkInterface();

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
			$name =  $this->request->getVar('group_name');
			$data = array ( 'name' => $this->request->getVar('group_name'),
							'description' =>$this->request->getVar('desc'),
							'group_type' => $this->request->getVar('group_type'),
							'network_key' => $this->request->getVar('network'),
							'url' => base_url()
			);

			try {
				$this->networkGroupModel->createNetworkGroup($data);
				$this->setStatusMessage("Network group '$name' was created.", STATUS_SUCCESS);
			} catch (\Exception $ex) {
				$this->setStatusMessage("There was a problem creating '$name'.", STATUS_ERROR);
			}
			return redirect()->to(base_url($this->controllerName . '/List'));

		}
		else {
			$response = $networkInterface->GetNetworksByInstallationKey($this->setting->settingData['installation_key']);
			$networks = [];
			if ($response->status) {
				$networks = $response->data;
			}

            if (!empty($networks) ) {
				$uidata->data['networks'] = $networks;
			}

            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
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

			return view($this->viewDirectory.'/Create', $data);
		}

    }

    public function Delete(int $id){
		$uidata = new UIData();
		$uidata->title = "Delete Network Group";
		$uidata->data['group_id'] = $id;

		$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

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
				try {
					// do we have the right userlevel?
					$has_got_network_sources_assigned = $this->networkGroupModel->hasSource($id);
					if (!$has_got_network_sources_assigned) {
						$f = $this->networkGroupModel->deleteNetworkGroup($id);
						$this->setStatusMessage("Network group was removed.", STATUS_SUCCESS);

						return redirect()->to(base_url($this->controllerName.'/List'));
					}
					else {
						$this->setStatusMessage("Unable to delete network group as sources from another installation are present in the group.", STATUS_ERROR);					}
				} catch (\Exception $ex) {
					$this->setStatusMessage("There was a problem removing network group.", STATUS_ERROR);
				}
			}
			return redirect()->to(base_url($this->controllerName.'/List'));
		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/Delete', $data);

	}

	function Update_Users(int $id, $isMaster = false) {

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
                $installation_key = $this->request->getVar('installation_key');

                try {
                    $this->networkModel->deleteAllSourcesFromNetworkGroup($group_id, $installation_key);

                    foreach ($this->request->getVar('sources') as $source_id)
                    {
                        $this->networkModel->addSourceToNetworkGroup($source_id, $group_id, $installation_key);
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
