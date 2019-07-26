<?php namespace App\Controllers;

/**
 * Source.php
 * Created 22/07/2019
 * 
 * This class offers CRUD operation for data sources.
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 */

use App\Models\UIData;
use App\Models\Settings;
use App\Libraries\AuthAdapter;
//use App\Models\Source;
use CodeIgniter\Config\Services;

class Source extends CVUI_Controller{

    /**
	 * Validation list template.
	 *
	 * @var string
	 */
    protected $validationListTemplate = 'list';

    private $authAdapter;

    private $sourceModel;


    /**
	 * Constructor
	 *
	 * @return void
	 */
    public function __construct(){
        $this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);

		$this->validation = Services::validation();

        $this->authAdapterConfig = config('AuthAdapter');
        $this->authAdapter = new AuthAdapter($this->authAdapterConfig->authRoutine);

        $this->sourceModel = new \App\Models\Source($this->db);

    }

    public function index() {
        if (!$this->authAdapter->loggedIn() /*|| !$this->ion_auth->is_admin()*/) {
			return redirect()->to(base_url("auth/login"));
        }
        $uidata = new UIData();

        $uidata->title = "Sources";

        $uidata->data['variant_counts'] = $this->sourceModel->countSourceEntries();
        $sources = $this->sourceModel->getSourcesFull();
        $uidata->data['sources'] = $sources;
        $source_groups = array();

        $source_ids_array = array(); // Array for storing all source IDs for this install
        foreach ($sources as $source) {
            $source_ids_array[] = $source['source_id'];
        }

        // Create pipe separated string of source IDs to post to API call 
        $source_ids = implode("|", $source_ids_array);

		// Pass all the source IDs for this install to auth server in one call and get all the network groups for each source 

        $networkModel = new \App\Models\Network($this->db);
        $source_ids_exploded = explode('|', $source_ids);
        $groups_for_source_ids;
        foreach ( $source_ids_exploded as $source_id ) {
            $groups = $networkModel->getCurrentNetworkGroupsForSourceInInstallation($source_id);
            $groups_for_source_ids[$source_id] = $groups;
        }
        if ( $groups_for_source_ids ) {
           $returned_groups = json_decode(json_encode($groups_for_source_ids), TRUE);
        }
        else {
            $this->response(array("error" => "Unable to get current network groups for sources in this installation"));
        }
        // Loop through each source
        foreach ($returned_groups as $source_id => $selected_groups) {
            if (!empty($selected_groups)) { // If there's groups assigned to this source then pass to the view
                $this->data['source_network_groups'][$source_id] = $selected_groups;
            }
        }                                                
        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(JS.'cafevariome/source.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);
        var_dump(preg_replace("/\s.+/", '', "Cafe Variome"));
        return view('source/sources', $data);
    }

    function add_source() {
        if (!$this->authAdapter->loggedIn() /*|| !$this->ion_auth->is_admin()*/) {
			return redirect()->to(base_url("auth/login"));
        }
        $uidata = new UIData();

        $uidata->data['title'] = "Add Source";

        $this->validation->setRules([
            'name' => [
                'label'  => 'Source Name',
                'rules'  => 'required|alpha_dash|is_unique[sources.name]',
                'errors' => [
                    'required' => '{field} is required.',
                    'uniquename_check' => '{field} already exists.'
                ]
            ],
            'owner_name' => [
                    'label'  => 'Owner Name',
                    'rules'  => 'required',
                    'errors' => [
                        'required' => '{field} is required.'
                    ]
            ],
            'email' => [
                'label'  => 'Owner Email',
                'rules'  => 'valid_email|required',
                'errors' => [
                    'required' => '{field} is required.',
                    'valid_email' => 'Please check the Email field. It does not appear to be valid.'
                ]
            ],
            'uri' => [
                'label'  => 'Source URI',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],            
            'desc' => [
                'label'  => 'Source Description',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ], 
            'long_description' => [
                'label'  => 'Long Source Description',
                'rules'  => 'string',

            ],  
            'status' => [
                'label'  => 'Source Status',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ]                             
        ]
        );

        // Get all available groups for the networks this installation is a member of from auth central for multi select list
        $networkModel = new \App\Models\Network($this->db);

        // Temporary section from network model
        $network_groups_for_installation = array();
		$installation_key = $this->setting->settingData['installation_key'];
		$url = base_url();
		foreach ( $networkModel->getNetworkGroupsForInstallation() as $network_group ) {
			$number_sources = $networkModel->countSourcesForNetworkGroup($network_group['id']);
			$network_group['number_of_sources'] = $number_sources;
			$network_groups_for_installation[] = $network_group;
		}
        //$groups = $networkModel->get_network_groups_for_installation();
        $groups = $network_groups_for_installation;
        //End temporary section

        $uidata->data['groups'] = json_decode(json_encode($groups), TRUE);

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $name = strtolower(str_replace(' ', '_', $this->request->getVar('name'))); // Convert the source name to lowercase and replace whitespace with underscore
            $uri = $this->request->getVar('uri');
            $owner_name = $this->request->getVar('owner_name');
            $email = $this->request->getVar('email');
            $description = $this->request->getVar('desc');
            $long_description = $this->request->getVar('long_description');
            $status = $this->request->getVar('status');
            $type = $this->request->getVar('type');

            $source_data = array("name" => $name, "owner_name" => $owner_name, "email" => $email, "uri" => $uri, "description" => $description, "long_description" => $long_description, "type" => "mysql", "status" => $status);
            $insert_id = $this->sourceModel->createSource($source_data);
            $this->data['insert_id'] = $insert_id;

            if ($this->request->getVar('groups')) {

                $group_data_array = array();
                foreach ($this->request->getVar('groups') as $group_data) {
                    // Need to explode the group multi select to get the group_id and the network_key since the value is comma separated as I needed to pass both in the value
                    $group_data_array[] = $group_data;
                }
                // Create the post string that will get sent
                // Each group will be a comma separated variable (first the group ID and then the network_key)
                // if multiple groups are selected then they'll be delimited by a | which will be exploded auth server side

                // Make API to auth central for the source for this installation for the network groups
                $groups = $networkModel->addSourceFromInstallationToMultipleNetworkGroups($insert_id,$group_data_array);
            }

			return redirect()->to(base_url('source/index'));            
        } else {
            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $uidata->data['name'] = array(
                'name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'class' => 'form-control',
                'value' => set_value('name'),
            );
            $uidata->data['owner_name'] = array(
                'name' => 'owner_name',
                'id' => 'owner_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' => set_value('owner_name'),
            );
            $uidata->data['email'] = array(
                'name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'class' => 'form-control',
                'value' => set_value('email'),
            );
            $uidata->data['uri'] = array(
                'name' => 'uri',
                'id' => 'uri',
                'type' => 'text',
                'class' => 'form-control',
                'value' => set_value('uri'),
            );
            $uidata->data['desc'] = array(
                'name' => 'desc',
                'id' => 'desc',
                'type' => 'text',
                'class' => 'form-control',
                'value' => set_value('desc'),
            );

            $uidata->data['long_description'] = array(
                'name' => 'long_description',
                'id' => 'long_description',
                'type' => 'text',
                'rows' => '5',
                'cols' => '3',
                'class' => 'form-control',
                'value' => set_value('long_description'),
            );

            $uidata->data['status'] = array(
                'name' => 'status',
                'id' => 'status',
                'type' => 'select',
                'class' => 'form-control',
                'value' => set_value('status'),
            );

            $uidata->data['type'] = array(
                'name' => 'type',
                'id' => 'type',
                'type' => 'select',
                'class' => 'form-control',
                'value' => set_value('type'),
            );

            $uidata->javascript = array(JS.'cafevariome/source.js');

            $data = $this->wrapData($uidata);
            return view('source/add_source', $data);
        }
    }

    public function edit_source($source_id = NULL) {

        if (!$this->authAdapter->loggedIn() /*|| !$this->ion_auth->is_admin()*/) {
			return redirect()->to(base_url("auth/login"));
        }

        $uidata = new UIData();

        $uidata->data['source_id'] = $source_id;
        $uidata->data['title'] = "Edit Source";

        $networkModel = new \App\Models\Network($this->db);

        //validate form input

        $this->validation->setRules([
            'name' => [
                'label'  => 'Source Name',
                'rules'  => 'required|alpha_dash',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
            'email' => [
                'label'  => 'Owner Email',
                'rules'  => 'valid_email|required',
                'errors' => [
                    'required' => '{field} is required.',
                    'valid_email' => 'Please check the Email field. It does not appear to be valid.'
                ]
            ],
            'uri' => [
                'label'  => 'Source URI',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],            
            'desc' => [
                'label'  => 'Source Description',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ], 
            'long_description' => [
                'label'  => 'Long Source Description',
                'rules'  => 'string'
            ],   
            'status' => [
                'label'  => 'Source Status',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ]                             
        ]
        );

        
        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            //check to see if we are creating the user
            //redirect them back to the admin page
            
            $update_data['source_id'] = $this->request->getVar('source_id');
            $update_data['name'] = $this->request->getVar('name');
            $update_data['email'] = $this->request->getVar('email');
            $update_data['uri'] = $this->request->getVar('uri');
            $update_data['description'] = $this->request->getVar('desc');
            $update_data['long_description'] = $this->request->getVar('long_description');
            $update_data['type'] = $this->request->getVar('type');
            $update_data['status'] = $this->request->getVar('status');

            $this->sourceModel->updateSource($update_data);

            // Check if there any groups selected
            if ($this->request->getVar('groups')) {
                $group_data_array = array();
                foreach ($this->request->getVar('groups') as $group_data) {
                    // Need to explode the group multi select to get the group_id and the network_key since the value is comma separated as I needed to pass both in the value
                    $group_data_array[] = $group_data;
                }
                // Create the post string that will get sent
                // Each group will be a comma separated variable (first the group ID and then the network_key)
                // if multiple groups are selected then they'll be delimited by a | which will be exploded auth server side
                $group_post_data = implode("|", $group_data_array);
                // Make API to auth central for the source for this installation for the network groups
                $groups = $networkModel->modify_current_network_groups_for_source_in_installation($update_data['source_id'],$group_post_data);
            } else {
                // All groups were deselected so make API call to delete this source from all groups
                 $groups = $networkModel->modify_current_network_groups_for_source_in_installation($update_data['source_id'],null);
            }
            if (file_exists("resources/elastic_search_status_complete"))
                unlink("resources/elastic_search_status_complete");
            file_put_contents("resources/elastic_search_status_incomplete", "");

			return redirect()->to(base_url('source/index'));
        } else {

            $groups = $networkModel->get_network_groups_for_installation();

            $uidata->data['groups'] = json_decode(json_encode($groups), TRUE);

            // Get all the network groups that this source from this installation is currently in so that these can be pre selected in the multiselect list
            $returned_groups = $networkModel->getCurrentNetworkGroupsForSourceInInstallation($source_id);
            $tmp_selected_groups = json_decode(json_encode($returned_groups), TRUE);
            $selected_groups = array();
            if (!array_key_exists('error', $tmp_selected_groups)) {
                foreach ($tmp_selected_groups as $tmp_group) {
                    $selected_groups[$tmp_group['group_id']] = "group_description";
                }
            }
            $uidata->data['selected_groups'] = $selected_groups;

            // Get all the data for this source
            $source_data = $this->sourceModel->getSourceSingleFull($source_id);
            $uidata->data['source_data'] = $source_data;
            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
            var_dump($uidata->data['message']);
            $uidata->data['name'] = array(
                'name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'class' => 'form-control',
                'readonly' => 'true', // Don't allow the user to edit the source name
                'value' => set_value('name', $source_data['name']),
            );
            $uidata->data['uri'] = array(
                'name' => 'uri',
                'id' => 'uri',
                'type' => 'text',
                'class' => 'form-control',
                'value' => set_value('uri', $source_data['uri']),
            );
            $uidata->data['desc'] = array(
                'name' => 'desc',
                'id' => 'desc',
                'type' => 'text',
                'class' => 'form-control',
                'value' => set_value('desc', $source_data['description']),
            );
            $uidata->data['long_description'] = array(
                'name' => 'long_description',
                'id' => 'long_description',
                'type' => 'text',
                'class' => 'form-control',
                'rows' => '5',
                'cols' => '3',
                'value' => set_value('long_description', $source_data['long_description']),
            );
            $uidata->data['email'] = array(
                'name' => 'email',
                'id' => 'email',
                'type' => 'text',
                'class' => 'form-control',
                'value' => set_value('email', $source_data['email']),
            );
            $uidata->data['status'] = array(
                'name' => 'status',
                'id' => 'status',
                'type' => 'select',
                'value' => set_value('status'),
            );
            $uidata->data['type'] = array(
                'name' => 'type',
                'id' => 'type',
                'type' => 'dropdown',
                'value' => set_value('type', $source_data['type']),
            );

            $data = $this->wrapData($uidata);

            return view('source/edit_source', $data);
        }
    }

    function delete_source($source_id = NULL, $source = NULL) {
        if (!$source) {
            return redirect()->to(base_url("source/sources"));          
        }
        if (!$this->authAdapter->loggedIn() /*|| !$this->ion_auth->is_admin()*/) {
			return redirect()->to(base_url("auth/login"));
        }

        
        $elasticModel = new \App\Models\Elastic();
        $this->form_validation->set_rules('confirm', 'confirmation', 'required');
        $this->form_validation->set_rules('source', 'Source Name', 'required|alpha_dash');

        if ($this->form_validation->run() == FALSE) {
            // insert csrf check
            $this->data['source_id'] = $source_id;
            $this->data['source'] = $source;
            $this->_render('sources/delete_source');
        } else {
            // do we really want to delete?
            if ($this->input->post('confirm') == 'yes') {
                // do we have a valid request?
                if ($source != $this->input->post('source')) {
                    show_error('This form post did not pass our security checks.');
                }

                // do we have the right userlevel?
                if ($this->ion_auth->logged_in() && $this->ion_auth->is_admin()) {
                    error_log("deleting source");
                    error_log(print_r($_POST,1));
                    $source_id = $_POST['source_id'];
                    if ($this->input->post('variants') == 'yes') { // also delete variants for the source
                        $is_deleted = $this->sources_model->delete_variants_and_phenotypes($source);
                    }
                    $this->sources_model->deleteSource($source_id);
                    $this->elastic_data_model->deleteElasticIndex($source_id);
                    
                }
                if (file_exists("resources/elastic_search_status_complete"))
                    unlink("resources/elastic_search_status_complete");
                file_put_contents("resources/elastic_search_status_incomplete", "");
            }
            //redirect them back to the auth page
            redirect('sources', 'refresh');
        }
    }
}