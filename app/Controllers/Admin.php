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
use App\Models\Source;
use App\Helpers\AuthHelper;

use CodeIgniter\Config\Services;

class Admin extends CVUI_Controller{

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(false);
        parent::setIsAdmin(false);
        parent::initController($request, $response, $logger);

		$this->session = Services::session();
		$this->db = \Config\Database::connect();
        $this->setting =  Settings::getInstance($this->db);
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

    /**
     * 
     */
    function get_phenotype_attributes_for_network($network_key) {

        $token = $this->session->get('Token');

        $installation_urls = json_decode(AuthHelper::authPostRequest(array('installation_key' => $this->setting->settingData['installation_key'], 'network_key' => $network_key), $this->setting->settingData['auth_server'] . "network/get_all_installation_ips_for_network"), true);

        $postdata = http_build_query(
                array(
                    'network_key' => $network_key,
                    'modification_time' => @filemtime("resources/phenotype_lookup_data/local_" . $network_key . ".json")
                )
        );

        $opts = array('http' =>
            array(
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 1
            )
        );
        $context = stream_context_create($opts);

        $data = array();

        foreach ($installation_urls as $url) {
            $url = rtrim($url['installation_base_url'], "/") . "/admin/get_json_for_phenotype_lookup";
            try{
                $result = @file_get_contents($url, 1, $context);
            }
            catch (\Exception $ex) {
                return json_encode(var_dump($ex));
            }
            if ($result) {
                foreach (json_decode($result, 1) as $res) {

                    if (array_key_exists($res['attribute'], $data)) {
                        foreach (explode("|", strtolower($res['value'])) as $val) {
                            if (!in_array($val, $data[$res['attribute']]))
                                array_push($data[$res['attribute']], $val);
                        }
                    } else {
                        $data[$res['attribute']] = explode("|", strtolower($res['value']));
                    }
                }
            }
        }

        foreach(array_keys($data) as $key){
            sort($data[$key]);
        }
        ksort($data);

        if ($data) {
            file_put_contents("resources/phenotype_lookup_data/local_" . $network_key . ".json", json_encode($data));
        }

        // HPO ancestry
        $postdata = http_build_query(
            ['network_key' => $network_key,
                'modification_time' => @filemtime("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json")]
        );

        $opts = ['http' =>
            [
                'method' => 'POST',
                'header' => 'Content-type: application/x-www-form-urlencoded',
                'content' => $postdata,
                'timeout' => 1
            ]
        ];
        $context = stream_context_create($opts);
        $data = '';
        foreach ($installation_urls as $url) {
            $url = rtrim($url['installation_base_url'], "/") . "/admin/get_json_for_hpo_ancestry";
            $data = @file_get_contents($url, 1, $context);
        }

        if($data) {
            file_put_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json", json_encode($data));
        }

        $phen_data = json_decode(file_get_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . ".json"), 1);
        $hpo_data = json_decode(file_get_contents("resources/phenotype_lookup_data/" . "local_" . $network_key . "_hpo_ancestry.json"), 1);
        echo json_encode([$phen_data, $hpo_data]);
    }

    function get_json_for_phenotype_lookup() {
        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        if (file_exists('resources/phenotype_lookup_data/' . $network_key . ".json")) {
            error_log(file_get_contents("resources/phenotype_lookup_data/" . $network_key . ".json"));
            return (file_get_contents("resources/phenotype_lookup_data/" . $network_key . ".json"));
        } else {
            error_log("resources/phenotype_lookup_data/" . $network_key . ".json");
        }              
    }

    
    function get_json_for_hpo_ancestry() {
        $modification_time = $this->request->getVar('modification_time');
        $network_key = $this->request->getVar('network_key');

        if (file_exists('resources/phenotype_lookup_data/' . $network_key . "_hpo_ancestry.json")) {
            echo (file_get_contents("resources/phenotype_lookup_data/" . $network_key . "_hpo_ancestry.json"));
        } else {
            echo false;
        }              
    }


}