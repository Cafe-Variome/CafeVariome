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

use App\Libraries\CafeVariome\Core\DataPipeLine\Index\ElasticSearch;
use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;
use App\Libraries\CafeVariome\Helpers\Core\Neo4JHelper;
use App\Libraries\CafeVariome\Helpers\UI\SourceHelper;
use App\Models\Attribute;
use App\Models\EAV;
use App\Models\UIData;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J;
use \App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use CodeIgniter\Config\Services;

class Source extends CVUI_Controller{

    /**
	 * Validation list template.
	 *
	 * @var string
	 */
    protected $validationListTemplate = 'list';


    private $sourceModel;

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
        $this->sourceModel = new \App\Models\Source();
        helper('filesystem');

    }

    public function Index(){
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function List() {

        $uidata = new UIData();
        $uidata->title = "Sources";

        $sources = $this->sourceModel->getSources();
        $uidata->data['sources'] = $sources;

        $source_ids_array = array(); // Array for storing all source IDs for this install
        foreach ($sources as $source) {
            $source_ids_array[] = $source['source_id'];
        }

        // Create pipe separated string of source IDs to post to API call
        $source_ids = implode("|", $source_ids_array);

		// Pass all the source IDs for this install to auth server in one call and get all the network groups for each source

        $networkModel = new \App\Models\Network($this->db);
        $source_ids_exploded = explode('|', $source_ids);

        $groups_for_source_ids = [];
        foreach ( $source_ids_exploded as $source_id ) {
            $groups = $networkModel->getCurrentNetworkGroupsForSourceInInstallation($source_id);
            $groups_for_source_ids[$source_id] = $groups;
        }

        if ( $groups_for_source_ids ) {
           $returned_groups = json_decode(json_encode($groups_for_source_ids), TRUE);
        }

        // Loop through each source
        foreach ($returned_groups as $source_id => $selected_groups) {
            if (!empty($selected_groups)) { // If there's groups assigned to this source then pass to the view
                $uidata->data['source_network_groups'][$source_id] = $selected_groups;
            }
        }

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(JS.'cafevariome/source.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory.'/List', $data);
    }

    public function Create() {

        $uidata = new UIData();
        $uidata->stickyFooter = false;

        $uidata->data['title'] = "Create Source";

        $this->validation->setRules([
            'name' => [
                'label'  => 'Source Name',
                'rules'  => 'required|alpha_numeric_space|is_unique[sources.name]',
                'errors' => [
                    'required' => '{field} is required.',
                    'uniquename_check' => '{field} already exists.'
                ]
            ],
            'owner_name' => [
                    'label'  => 'Owner Name',
                    'rules'  => 'required|alpha_numeric_space',
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
                'rules'  => 'required|valid_url',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
            'desc' => [
                'label'  => 'Source Description',
                'rules'  => 'required|alpha_numeric_space',
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
            ],
			'username' => [
				'label'  => 'Username',
				'rules'  => 'string',
				'errors' => [
					'string' => '{field} must be a valid string.'
				]
			],
			'password' => [
				'label'  => 'Password',
				'rules'  => 'string',
				'errors' => [
					'string' => '{field} must be a valid string.'
				]
			],
        ]
        );

        // Get all available groups for the networks this installation is a member of from auth central for multi select list
        $networkModel = new \App\Models\Network();

        $networkGroups = $networkModel->getNetworkGroupsForInstallation();

        $srcDisplayGroups = [];
        $countDisplayGroups = [];

		foreach ( $networkGroups as $ng ) {
            if ($ng['group_type'] == 'source_display') {
                $srcDisplayGroups[$ng['id'] . ',' . $ng['network_key']] = $ng['name'] . '(' . $ng['network_name'] . ')';
            }
            elseif ($ng['group_type'] == 'count_display') {
                $countDisplayGroups[$ng['id'] . ',' . $ng['network_key']] = $ng['name'] . '(' . $ng['network_name'] . ')';
            }
        }
        $uidata->data['srcDSPGroups'] = $srcDisplayGroups;
        $uidata->data['countDSPGroups'] = $countDisplayGroups;

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $name = strtolower(str_replace(' ', '_', $this->request->getVar('name'))); // Convert the source name to lowercase and replace whitespace with underscore
            $uri = $this->request->getVar('uri');
            $owner_name = $this->request->getVar('owner_name');
            $email = $this->request->getVar('email');
            $description = $this->request->getVar('desc');
            $long_description = $this->request->getVar('long_description');
            $status = $this->request->getVar('status');
			$username = $this->request->getVar('username');
			$password = $this->request->getVar('password');

            $source_data = [
				'uid' => bin2hex(random_bytes(5)), // UID is used as a prefix in Elasticsearch index names, and in Neo4J subject node source attribute
				'name' => $name,
				'owner_name' => $owner_name,
				'email' => $email,
				'uri' => $uri,
				'description' => $description,
				'long_description' => $long_description,
				'status' => $status,
				'username' => $username,
				'password' => $password];
            try {
                $insert_id = $this->sourceModel->createSource($source_data);

                if ($this->request->getVar('source_display')) {
                    $group_data_array = array();
                    foreach ($this->request->getVar('source_display') as $src_group_data) {
                        // Need to explode the group multi select to get the group_id and the network_key since the value is comma separated as I needed to pass both in the value
                        $group_data_array[] = $src_group_data;
                    }
                    $networkModel->addSourceFromInstallationToMultipleNetworkGroups($insert_id, $group_data_array);
                }

                if ($this->request->getVar('count_display')) {
                    $group_data_array = array();
                    foreach ($this->request->getVar('count_display') as $count_group_data) {
                        $group_data_array[] = $count_group_data;
                    }
                    $networkModel->addSourceFromInstallationToMultipleNetworkGroups($insert_id, $group_data_array);
                }

                $this->setStatusMessage("Source '$name' was created successfully.", STATUS_SUCCESS);

            } catch (\Exception $ex) {
                $this->setStatusMessage("There was a problem creating '$name'.", STATUS_ERROR);
            }

            return redirect()->to(base_url($this->controllerName.'/List'));

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

			$uidata->data['username'] = array(
				'name' => 'username',
				'id' => 'username',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('username'),
			);

			$uidata->data['password'] = array(
				'name' => 'password',
				'id' => 'password',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('password'),
			);

            $uidata->data['selected_source_display'] = $this->request->getVar('source_display') ? $this->request->getVar('source_display') :[];
            $uidata->data['selected_count_display'] = $this->request->getVar('count_display') ? $this->request->getVar('count_display') :[];

            $uidata->javascript = array(JS.'cafevariome/components/transferbox.js', JS.'cafevariome/source.js');

            $data = $this->wrapData($uidata);
            return view($this->viewDirectory.'/Create', $data);
        }
    }

    public function Update(int $source_id = null) {

        $uidata = new UIData();
        $uidata->stickyFooter = false;

        $uidata->data['source_id'] = $source_id;
        $uidata->data['title'] = "Edit Source";

        $networkModel = new \App\Models\Network();

        $networkGroups = $networkModel->getNetworkGroupsForInstallation();

        $srcDisplayGroups = [];
        $countDisplayGroups = [];

		foreach ( $networkGroups as $ng ) {
            if ($ng['group_type'] == 'source_display') {
                $srcDisplayGroups[$ng['id'] . ',' . $ng['network_key']] = $ng['name'] . '(' . $ng['network_name'] . ')';
            }
            elseif ($ng['group_type'] == 'count_display') {
                $countDisplayGroups[$ng['id'] . ',' . $ng['network_key']] = $ng['name'] . '(' . $ng['network_name'] . ')';
            }
        }
        $uidata->data['srcDSPGroups'] = $srcDisplayGroups;
        $uidata->data['countDSPGroups'] = $countDisplayGroups;

        //validate form input

        $this->validation->setRules([
            'name' => [
                'label'  => 'Source Name',
                'rules'  => 'required|alpha_dash',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
			'owner_name' => [
				'label'  => 'Owner Name',
				'rules'  => 'required|alpha_numeric_space',
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
                'rules'  => 'required|valid_url',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
            'desc' => [
                'label'  => 'Source Description',
                'rules'  => 'required|alpha_numeric_space',
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
            ],
			'username' => [
				'label'  => 'Username',
				'rules'  => 'string',
				'errors' => [
					'string' => '{field} must be a valid string.'
				]
			],
			'password' => [
				'label'  => 'Password',
				'rules'  => 'string',
				'errors' => [
					'string' => '{field} must be a valid string.'
				]
			],
        ]
        );

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            //check to see if we are creating the user
            //redirect them back to the admin page
            $source_name = $this->request->getVar('name');
            $update_data['source_id'] = $this->request->getVar('source_id');
            $update_data['name'] = $source_name;
			$update_data['owner_name'] = $this->request->getVar('owner_name');
			$update_data['email'] = $this->request->getVar('email');
            $update_data['uri'] = $this->request->getVar('uri');
            $update_data['description'] = $this->request->getVar('desc');
            $update_data['long_description'] = $this->request->getVar('long_description');
            $update_data['status'] = $this->request->getVar('status');
			$update_data['username'] = $this->request->getVar('username');
			$update_data['password'] = $this->request->getVar('password');

			try {
                $this->sourceModel->updateSource($update_data, ["source_id" => $this->request->getVar('source_id')]);

                $group_data_array = array();
                // Check if there any groups selected
                if ($this->request->getVar('source_display')) {
                    foreach ($this->request->getVar('source_display') as $src_group_data) {
                        // Need to explode the group multi select to get the group_id and the network_key since the value is comma separated as I needed to pass both in the value
                        $group_data_array[] = $src_group_data;
                    }
                }
                if ($this->request->getVar('count_display')) {
                    foreach ($this->request->getVar('count_display') as $count_group_data) {
                        $group_data_array[] = $count_group_data;
                    }
                }
                if (count($group_data_array) > 0) {
                    $group_post_data = implode("|", $group_data_array);
                    $networkModel->updateNetworkGroupsBySourceId($update_data['source_id'], $group_post_data);

                }
                else {
                    $networkModel->updateNetworkGroupsBySourceId($update_data['source_id']);
                }
                $this->setStatusMessage("Source '$source_name' was updated.", STATUS_SUCCESS);

            } catch (\Exception $ex) {
                $this->setStatusMessage("There was a problem updating '$source_name'.", STATUS_ERROR);
            }

            return redirect()->to(base_url($this->controllerName.'/List'));

        }
		else
		{
            // Get all the network groups that this source from this installation is currently in so that these can be pre selected in the multiselect list
            $networkGroups = $networkModel->getCurrentNetworkGroupsForSourceInInstallation($source_id);
            $selected_source_display = [];
            $selected_count_display = [];

            foreach ($networkGroups as $ng) {
                if ($ng['group_type'] == 'source_display') {
                    $selected_source_display[] = $ng['id'] . ',' . $ng['network_key'];
                }
                elseif ($ng['group_type'] == 'count_display') {
                    $selected_count_display[] = $ng['id'] . ',' . $ng['network_key'];
                }
            }

            $uidata->data['selected_source_display'] = $selected_source_display ? $selected_source_display :[];
            $uidata->data['selected_count_display'] = $selected_count_display ? $selected_count_display :[];

            // Get all the data for this source
            $source_data = $this->sourceModel->getSource($source_id);

            if ($source_data != null) {
                $uidata->data['source_data'] = $source_data;
                $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
                $uidata->data['name'] = array(
                    'name' => 'name',
                    'id' => 'name',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => set_value('name', $source_data['name']),
                );
                $uidata->data['owner_name'] = array(
                    'name' => 'owner_name',
                    'id' => 'owner_name',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => set_value('owner_name', $source_data['owner_name']),
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

				$uidata->data['username'] = array(
					'name' => 'username',
					'id' => 'username',
					'type' => 'text',
					'class' => 'form-control',
					'value' => set_value('username', $source_data['username']),
				);

				$uidata->data['password'] = array(
					'name' => 'password',
					'id' => 'password',
					'type' => 'text',
					'class' => 'form-control',
					'value' => set_value('password', $source_data['password']),
				);

                $uidata->javascript = array(JS.'cafevariome/components/transferbox.js',JS.'cafevariome/source.js');

                $data = $this->wrapData($uidata);

                return view($this->viewDirectory.'/Update', $data);
            }
            else {
                $this->setStatusMessage("Source was not found.", STATUS_WARNING);
                return redirect()->to(base_url($this->controllerName.'/List'));
            }
        }
    }

    public function Delete(int $source_id = Null) {
        if ($source_id == Null) {
            return redirect()->to(base_url($this->controllerName.'/List'));
        }

        $uidata = new UIData();
        $uidata->title = "Delete Source";

        $this->validation->setRules([
            'confirm' => [
                'label'  => 'confirmation',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],

                'source' => [
                    'label'  => 'Source Name',
                    'rules'  => 'required|alpha_dash',
                    'errors' => [
                        'required' => '{field} is required.',
                        'alpha_dash' => '{field} must only contain alpha-numeric characters, underscores, or dashes.'
                    ]
                ]
            ]);


        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $error_flag = false;
            if ($this->request->getVar('confirm') == 'yes') {
                if ($source_id != $this->request->getVar('source_id')) {
                    $this->setStatusMessage("No source selected to delete.", STATUS_ERROR);
                    return redirect()->to(base_url($this->controllerName.'/List'));
                }

                $source_id = $this->request->getVar('source_id');
                $source = $this->sourceModel->getSourceNameByID($source_id);
                if ($source == Null) {
                    $this->setStatusMessage("Source was not found.", STATUS_ERROR);
                    return redirect()->to(base_url($this->controllerName.'/List'));
                }
                //delete Elasticsearch index associated with the source
                try {
					$hosts = [$this->setting->getElasticSearchUri()];
                    $elasticModel = new ElasticSearch($hosts);
                    $elasticModel->deleteIndex(ElasticsearchHelper::getSourceIndexName($source_id));
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was an error in deleting Elasticsearch index.", STATUS_ERROR);
                    $error_flag = true;
                }

                //delete the associated node from neo4j database
                try {
                    $neo4jInterface = new Neo4J();
                    $neo4jInterface->deleteSource($source_id);
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was an error in deleting Neo4J data of the source.", STATUS_ERROR, true);
                    $error_flag = true;
                }

                //delete files on system
                try {
                    $dirPath = FCPATH . UPLOAD . UPLOAD_DATA . $source_id;
                    if (file_exists($dirPath)) {
                        delete_files($dirPath, true);
                    }
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was an error in deleting files of the source.", STATUS_ERROR, true);
                    $error_flag = true;
                }

                if (!$error_flag) {
                    //delete rest of the data in database
                    try {
                        $this->sourceModel->deleteSourceFromEAVs($source_id);
                        $this->sourceModel->deleteSource($source_id);
                        $this->setStatusMessage("Source '$source' was deleted.", STATUS_SUCCESS, true);
                    } catch (\Exception $ex) {
                        $this->setStatusMessage("There was an error in deleting source records from database.", STATUS_ERROR, true);
                        $error_flag = true;
                    }
                }
            }
        }
        else
        {
            $source = $this->sourceModel->getSource($source_id);
            if ($source) {
                $uidata->data['source_id'] = $source_id;
                $uidata->data['source_name'] = $source['name'];
                $data = [
                    'name'    => 'newsletter',
                    'id'      => 'newsletter',
                    'value'   => 'accept',
                    'checked' => TRUE,
                    'style'   => 'margin:10px'
                ];
                $uidata->data['confirm'] = array(
                    'name' => 'confirm',
                    'type' => 'radio',
                    'class' => 'form-control',
                );

                $data = $this->wrapData($uidata);
                return view($this->viewDirectory.'/Delete', $data);
            }
        }

        return redirect()->to(base_url($this->controllerName.'/List'));
    }

	public function Elasticsearch(int $source_id)
	{
		$uidata = new UIData();
		$uidata->title = "Elasticsearch Index";

		$source = $this->sourceModel->getSource($source_id);
		if($source == null){
			$this->setStatusMessage('Source was not found.', STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName.'/List'));
		}

		$indexName = '-';
		$indexUUID = '-';
		$indexSize = '-';
		$indexDocIndexed = '-';
		$indexDocDeleted = '-';
		$elasticStatus = ElasticsearchHelper::ping();
		$indexStatus = ELASTICSEARCH_INDEX_STATUS_UNKNOWN;
		if ($elasticStatus){
			$elasticsearch = new ElasticSearch([$this->setting->getElasticSearchUri()]);
			$indexName = ElasticsearchHelper::getSourceIndexName($source_id);
			if ($elasticsearch->indexExists($indexName)){
				$indexStatus = ELASTICSEARCH_INDEX_STATUS_CREATED;
				$indexStats = $elasticsearch->getIndicesStats();
				$indexStats = $indexStats['indices'][$indexName];
				$indexUUID = $indexStats['uuid'];
				$indexSize = $indexStats['total']['store']['size_in_bytes'];
				$indexDocIndexed = $indexStats['total']['docs']['count'];
				$indexDocDeleted = $indexStats['total']['docs']['deleted'];
			}
			else{
				$indexStatus = ELASTICSEARCH_INDEX_STATUS_NOT_CREATED;
			}
		}

		$attributeModel = new Attribute();
		$eavModel = new EAV();

		$esAttributeIds = $attributeModel->getAttributeIdsBySourceIdAndStorageLocation($source_id, ATTRIBUTE_STORAGE_ELASTICSEARCH);
		$dataStatus = ELASTICSEARCH_DATA_STATUS_UNKNOWN;
		if(count($esAttributeIds) && $eavModel->recordsExistBySourceId($source_id, $esAttributeIds)){
			$indexedRecordsExist = $eavModel->indexedRecordsExistBySourceId($source_id, $esAttributeIds);
			$unindexedRecordsExist = $eavModel->unindexedRecordsExistBySourceId($source_id, $esAttributeIds);
			if($indexedRecordsExist && !$unindexedRecordsExist){
				$dataStatus = ELASTICSEARCH_DATA_STATUS_FULLY_INDEXED;
			}
			else if($unindexedRecordsExist && !$indexedRecordsExist){
				$dataStatus = ELASTICSEARCH_DATA_STATUS_NOT_INDEXED;
			}
			else if($unindexedRecordsExist && $indexedRecordsExist){
				$dataStatus = ELASTICSEARCH_DATA_STATUS_PARTIALLY_INDEXED;
			}
		}
		else{
			$dataStatus = ELASTICSEARCH_DATA_STATUS_EMPTY;
		}

		$uidata->data['sourceName'] = $source['name'];
		$uidata->data['sourceId'] = $source_id;
		$uidata->data['isRunning'] = $elasticStatus;
		$uidata->data['indexName'] = $indexName;
		$uidata->data['indexStatus'] = $indexStatus;
		$uidata->data['dataStatus'] = $dataStatus;
		$uidata->data['indexStatusText'] = SourceHelper::getElasticsearchIndexStatus($indexStatus);
		$uidata->data['dataStatusText'] = SourceHelper::getElasticsearchDataStatus($dataStatus);
		$uidata->data['indexUUID'] = $indexUUID;
		$uidata->data['indexSize'] = $indexSize == '-' ? $indexSize : SourceHelper::formatSize($indexSize);
		$uidata->data['indexDocIndexed'] = $indexDocIndexed;
		$uidata->data['indexDocDeleted'] = $indexDocDeleted;

		$uidata->javascript = [JS."cafevariome/elasticsearch.js"];

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/Elasticsearch', $data);
	}

	public function Neo4J(int $source_id)
	{
		$uidata = new UIData();
		$uidata->title = "Neo4J Index";

		$source = $this->sourceModel->getSource($source_id);
		if($source == null){
			$this->setStatusMessage('Source was not found.', STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName.'/List'));
		}

		$neo4jStatus = Neo4JHelper::ping();

		$indexedSubjectsCount = '-';
		$relationshipsCount = '-';
		$indexStatus = NEO4J_INDEX_STATUS_UNKNOWN;

		if ($neo4jStatus){
			$neo4j = new \App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J();
			$indexedSubjectsCount = $neo4j->countSubjectsBySourceId($source_id, $source['uid']);
			if ($indexedSubjectsCount > 0){
				$indexStatus = NEO4J_INDEX_STATUS_CREATED;
			}
			else{
				$indexStatus = NEO4J_INDEX_STATUS_NOT_CREATED;
			}
			$relationshipsCount = $neo4j->countRelationshipsBySourceId($source_id, $source['uid']);
		}

		$attributeModel = new Attribute();
		$eavModel = new EAV();

		$n4jAttributeIds = $attributeModel->getAttributeIdsBySourceIdAndStorageLocation($source_id, ATTRIBUTE_STORAGE_NEO4J);
		$dataStatus = NEO4J_DATA_STATUS_UNKNOWN;
		if(count($n4jAttributeIds) > 0 && $eavModel->recordsExistBySourceId($source_id, $n4jAttributeIds)){
			$indexedRecordsExist = $eavModel->indexedRecordsExistBySourceId($source_id, $n4jAttributeIds);
			$unindexedRecordsExist = $eavModel->unindexedRecordsExistBySourceId($source_id, $n4jAttributeIds);
			if($indexedRecordsExist && !$unindexedRecordsExist){
				$dataStatus = NEO4J_DATA_STATUS_FULLY_INDEXED;
			}
			else if($unindexedRecordsExist && !$indexedRecordsExist){
				$dataStatus = NEO4J_DATA_STATUS_NOT_INDEXED;
			}
			else if($unindexedRecordsExist && $indexedRecordsExist){
				$dataStatus = NEO4J_DATA_STATUS_PARTIALLY_INDEXED;
			}
		}
		else{
			$dataStatus = NEO4J_DATA_STATUS_EMPTY;
		}

		$uidata->data['sourceName'] = $source['name'];
		$uidata->data['sourceId'] = $source_id;
		$uidata->data['isRunning'] = $neo4jStatus;
		$uidata->data['dataStatus'] = $dataStatus;
		$uidata->data['dataStatusText'] = SourceHelper::getNeo4JDataStatus($dataStatus);
		$uidata->data['indexStatusText'] = SourceHelper::getNeo4JIndexStatus($indexStatus);
		$uidata->data['indexedSubjectsCount'] = $indexedSubjectsCount;
		$uidata->data['relationshipsCount'] = $relationshipsCount;

		$uidata->javascript = [JS."cafevariome/neo4j.js"];

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/Neo4J', $data);
	}

	public function UserInterface(int $source_id)
	{
		$uidata = new UIData();
		$uidata->title = "User Interface Index";

		$source = $this->sourceModel->getSource($source_id);
		if($source == null){
			$this->setStatusMessage('Source was not found.', STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName.'/List'));
		}

		$indexName = $source_id . '_' . $source['uid'] . '.json';
		$uiIndexPath = getcwd() . DIRECTORY_SEPARATOR . USER_INTERFACE_INDEX_DIR;

		$fileMan = new SysFileMan($uiIndexPath);
		$indexSize = '-';
		$indexCreationDate = '-';

		if ($fileMan->Exists($indexName)){
			$indexSize = $fileMan->getSize($indexName);
			$indexCreationDate = date("D M j G:i:s T Y", $fileMan->GetModificationTimeStamp($indexName));
			$indexStatus = USER_INTERFACE_INDEX_STATUS_CREATED;
		}
		else{
			$indexStatus = USER_INTERFACE_INDEX_STATUS_NOT_CREATED;
		}

		$uidata->data['sourceName'] = $source['name'];
		$uidata->data['sourceId'] = $source_id;
		$uidata->data['indexName'] = $indexName;
		$uidata->data['indexSize'] = $indexSize == '-' ? $indexSize : SourceHelper::formatSize($indexSize);
		$uidata->data['indexCreationDate'] = $indexCreationDate;
		$uidata->data['indexStatusText'] = SourceHelper::getUserInterfaceIndexStatus($indexStatus);

		$uidata->javascript = [JS."cafevariome/userinterfaceindex.js"];

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/UserInterface', $data);
	}
}
