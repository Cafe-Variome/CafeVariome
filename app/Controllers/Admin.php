<?php namespace App\Controllers;

/**
 * Admin.php
 * Created 18/07/2019
 * 
 * @author Owen Lancaster
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 */

use App\Models\UIData;
use App\Models\Settings;
use App\Models\Network;
use App\Models\Source;
use App\Models\User;
use App\Helpers\AuthHelper;

use CodeIgniter\Config\Services;

class Admin extends CVUI_Controller{

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
    }

    function index(){
        $uidata = new UIData();
        $uidata->title = "Administrator Dashboard";


        $data = $this->wrapData($uidata);
        return view("Admin/Index", $data);
    }

    function user(){
        $uidata = new UIData();
        $uidata->title = "Users";

        $userModel = new User($this->db);
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
        return view("Admin/User", $data);
    }

    function settings($message = NULL) {

        $sourceModel = new Source($this->db);
        if ($message = "regenerate_elastic_search") {
            $this->session->set(array('settings_tab' => "maintenance"));
            $this->session->set(array('maintenance_tab' => "regenerate"));
        }

        if (!$this->session->get('settings_tab')) { // Set tab to settings if it's not already set
            $this->session->set('settings_tab', 'settings');
        }

        if (!$this->session->get('fields_tab')) { // Set tab to settings if it's not already set
            $this->session->set('fields_tab', 'search_result');
        }

        if (!$this->session->get('maintenance_tab')) { // Set tab to settings if it's not already set
            $this->session->set('maintenance_tab', 'regenerate');
        }

        // Get variant table structure and display fields table structure (used for database structure edit and editing display fields tabs)

        $uidata = new UIData();
        
        $uidata->data['elastic_update'] = $sourceModel->getSourceElasticStatus();
        
        $title = $this->setting->settingData["title"];
        $host = strtolower(preg_replace("/\s.+/", '', $title)); 

        $uidata->data['host'] = $host;

        $uidata->data['table_structure'] = $this->general_model->describeTable("variants");
        $uidata->load->model('settings_model');
        $uidata->data['display_fields'] = $this->settings_model->getDisplayFields();
        $uidata->data['display_fields_grouped'] = $this->settings_model->getDisplayFieldsGroupBySharingPolicy();
        $uidata->data['individual_record_display_fields'] = $this->settings_model->getIndividualRecordDisplayFields();
         $uidata->javascript = ['maintenance.js'];  
            $uidata->css = ['maintenance.css'];

        // Get search fields
        $uidata->data['search_fields'] = $this->settings_model->getSearchFields();

        // Check if ElasticSearch is running and pass result to view
        $this->load->library('elasticsearch');
        $check_if_running = $this->elasticsearch->check_if_running();
        if (array_key_exists('ok', $check_if_running)) {
            $is_elastic_search_running = $check_if_running['ok'];
            $uidata->data['is_elastic_search_running'] = $is_elastic_search_running;
        }

        // Check the status of maintenance cron job file, if it's empty then cron job won't run
        if (file_exists(FCPATH . '/resources/cron/crontab')) {
            if (filesize(FCPATH . '/resources/cron/crontab') != 0) {
                $uidata->data['is_maintenance_cron_enabled'] = TRUE;
            }
        }


        if ($this->config->item('federated_head')) {
            $this->load->model('federated_model');
            $node_list = $this->federated_model->getNodeList();
            $node_statuses = array();
            foreach ($node_list as $node_name => $node) {
                $node_status = $this->node_ping($node['node_uri']); // Get the status of each node by pinging them
                $node_statuses[$node['node_name']] = $node_status;
                if (!$node_status) { // If the node is down then update the node record in db
                    $this->federated_model->updateNodeList(array('node_name' => $node_name, 'node_status' => 'offline'));
                } else {
                    if ($node['node_status'] == "offline") { // If the node is up and currently marked as offline in db then update the record and set it as online
                        $this->federated_model->updateNodeList(array('node_name' => $node_name, 'node_status' => 'online'));
                    }
                }
            }
            $uidata->data['node_statuses'] = $node_statuses;
            $uidata->data['node_list'] = $node_list;
        }

        // Settings tab
        $settings = $this->_get_settings();
        $this->data['settings'] = $settings;
        // Dynamically create the validation rule for each setting based on the validation_rules field in the settings table in the db
        foreach ($settings as $setting) {
            $this->form_validation->set_rules($setting->name, $setting->name, $setting->validation_rules);
        }
        if ($this->form_validation->run() == FALSE) { // Form didn't validate - render the view and the validation errors get printed there
            $this->_render('admin/settings');
        } else { // Form validated, go through each setting and update 
            $this->load->model('settings_model');
            foreach ($settings as $setting) {
                if (array_key_exists($setting->name, $_POST)) { // Need this since checkboxes do not get posted by a form if they are unchecked
                    if ($_POST[$setting->name] != $setting->value) { // Only update the setting in the db if it has been changed
                        $update['name'] = $setting->name;
                        $update['value'] = $_POST[$setting->name];
                        if ($setting->name == "federated") {
                            if (!$this->config->item('cafevariome_central')) {
                                $this->send_federated_switch('on');
                                error_log("on -> " . base_url());
                            }
                        }
                        $this->settings_model->updateSetting($update);
                    }
                } else { // Must be a unchecked checkbox so need to deal with this here (set it to off if it isn't already set as off
                    if ($setting->value != "off") {
                        $update['name'] = $setting->name;
                        $update['value'] = "off";
                        if ($setting->name == "federated") {
                            if (!$this->config->item('cafevariome_central')) {
                                $this->send_federated_switch('off');
                                error_log("off -> " . base_url());
                            }
                        }
                        $this->settings_model->updateSetting($update);
                    }

                }
            }

            // Fetch the updated settings from the database (TODO: just repopulated the array with the new setting instead of doing another query)
            $settings = $this->_get_settings();
            $this->data['settings'] = $settings;
            $uidata->data['success_message'] = true;
           
            header("refresh: 0;");
            $this->_render('admin/settings');
        }
    }



}