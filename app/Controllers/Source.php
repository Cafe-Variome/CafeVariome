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
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceFactory;
use App\Libraries\CafeVariome\Helpers\Core\ElasticsearchHelper;
use App\Libraries\CafeVariome\Helpers\Core\Neo4JHelper;
use App\Libraries\CafeVariome\Helpers\UI\SourceHelper;
use App\Models\Attribute;
use App\Models\EAV;
use App\Models\UIData;
use App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J;
use \App\Libraries\CafeVariome\Core\IO\FileSystem\SysFileMan;
use CodeIgniter\Config\Services;

class Source extends CVUI_Controller
{

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
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->validation = Services::validation();
		$this->dbAdapter = (new SourceAdapterFactory())->GetInstance();
        $this->sourceModel = new \App\Models\Source();
        helper('filesystem');
    }

    public function Index()
	{
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function List()
	{
        $uidata = new UIData();
        $uidata->title = "Sources";

        $sources = $this->dbAdapter->ReadAll();
        $uidata->data['sources'] = $sources;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(JS.'cafevariome/source.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory.'/List', $data);
    }

    public function Create()
	{
        $uidata = new UIData();
        $uidata->stickyFooter = false;

        $uidata->data['title'] = "Create a Source";

        $this->validation->setRules([
            'name' => [
                'label'  => 'Source Name',
                'rules'  => 'required|alpha_numeric_space|max_length[30]|is_unique[sources.name]',
                'errors' => [
                    'required' => '{field} is required.',
					'max_length' => 'Maximum length for {field} is 30 characters.',
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
            'owner_email' => [
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
            'description' => [
                'label'  => 'Source Description',
                'rules'  => 'required|alpha_numeric_space',
                'errors' => [
                    'required' => '{field} is required.'
                ]
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
            $name = $this->request->getVar('name');
            $display_name = $this->request->getVar('display_name');
            $uri = $this->request->getVar('uri');
            $owner_name = $this->request->getVar('owner_name');
            $owner_email = $this->request->getVar('owner_email');
            $description = $this->request->getVar('description');
            $status = $this->request->getVar('status');

			$source = (new SourceFactory())->GetInstanceFromParameters(
				$name,
				bin2hex(random_bytes(5)),
				$display_name == '' ? $name : $display_name,
				$description,
				$owner_name,
				$owner_email,
				$uri,
				time(),
				0,
				false,
				$status
			);

            try
			{
				$source_id = $this->dbAdapter->Create($source);

                if ($this->request->getVar('source_display'))
				{
                    $group_data_array = array();
                    foreach ($this->request->getVar('source_display') as $src_group_data)
					{
                        // Need to explode the group multi select to get the group_id and the network_key since the value is comma separated as I needed to pass both in the value
                        $group_data_array[] = $src_group_data;
                    }
                    $networkModel->addSourceFromInstallationToMultipleNetworkGroups($source_id, $group_data_array);
                }

                if ($this->request->getVar('count_display'))
				{
                    $group_data_array = array();
                    foreach ($this->request->getVar('count_display') as $count_group_data)
					{
                        $group_data_array[] = $count_group_data;
                    }
                    $networkModel->addSourceFromInstallationToMultipleNetworkGroups($source_id, $group_data_array);
                }

                $this->setStatusMessage("Source '$name' was created successfully.", STATUS_SUCCESS);

            }
			catch (\Exception $ex)
			{
                $this->setStatusMessage("There was a problem creating '$name'." . $ex->getMessage(), STATUS_ERROR);
            }

            return redirect()->to(base_url($this->controllerName.'/List'));
        }
		else
		{
            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('name'),
			);
			$uidata->data['display_name'] = array(
				'name' => 'display_name',
				'id' => 'display_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('display_name'),
			);
			$uidata->data['owner_name'] = array(
				'name' => 'owner_name',
				'id' => 'owner_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('owner_name'),
			);
			$uidata->data['uri'] = array(
				'name' => 'uri',
				'id' => 'uri',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('uri'),
			);
			$uidata->data['description'] = array(
				'name' => 'description',
				'id' => 'description',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('description'),
			);
			$uidata->data['owner_email'] = array(
				'name' => 'owner_email',
				'id' => 'owner_email',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('owner_email'),
			);
			$uidata->data['status'] = array(
				'name' => 'status',
				'type' => 'dropdown',
				'class' => 'form-control',
				'value' =>set_value('status'),
				'options' => [
					SOURCE_STATUS_ONLINE => SourceHelper::getSourceStatus(SOURCE_STATUS_ONLINE),
					SOURCE_STATUS_OFFLINE => SourceHelper::getSourceStatus(SOURCE_STATUS_OFFLINE)
				]
			);

            $uidata->data['selected_source_display'] = $this->request->getVar('source_display') ? $this->request->getVar('source_display') :[];
            $uidata->data['selected_count_display'] = $this->request->getVar('count_display') ? $this->request->getVar('count_display') :[];

            $uidata->javascript = array(JS.'cafevariome/components/transferbox.js', JS.'cafevariome/source.js');

            $data = $this->wrapData($uidata);
            return view($this->viewDirectory.'/Create', $data);
        }
    }

    public function Update(int $id = null)
	{
		$source = $this->dbAdapter->Read($id);

		if ($source->isNull())
		{
			$this->setStatusMessage("Source was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

        $uidata = new UIData();
		$uidata->data['title'] = "Edit Source";
		$uidata->data['id'] = $source->getID();

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
                'rules'  => 'required|alpha_numeric_space|max_length[30]',
                'errors' => [
					'required' => '{field} is required.',
					'max_length' => 'Maximum length for {field} is 30 characters.',
                ]
            ],
			'owner_name' => [
				'label'  => 'Owner Name',
				'rules'  => 'required|alpha_numeric_space',
				'errors' => [
					'required' => '{field} is required.'
				]
			],
            'owner_email' => [
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
            'description' => [
                'label'  => 'Source Description',
                'rules'  => 'required|alpha_numeric_space',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
            'status' => [
                'label'  => 'Source Status',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
        ]
        );

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
            //check to see if we are creating the user
            //redirect them back to the admin page
            $name = $this->request->getVar('name');
			$display_name = $this->request->getVar('display_name');
			$owner_name =  $this->request->getVar('owner_name');
			$owner_email = $this->request->getVar('owner_email');
            $uri = $this->request->getVar('uri');
            $description = $this->request->getVar('description');
            $status = $this->request->getVar('status');

			try
			{
				$source = (new SourceFactory())->GetInstanceFromParameters(
					$name,
					$source->uid,
					$display_name,
					$description,
					$owner_name,
					$owner_email,
					$uri,
					$source->date_created,
					$source->record_count,
					$source->locked,
					$status
				);

				$this->dbAdapter->Update($id, $source);

                $group_data_array = array();
                // Check if there any groups selected
                if ($this->request->getVar('source_display'))
				{
                    foreach ($this->request->getVar('source_display') as $src_group_data)
					{
                        // Need to explode the group multi select to get the group_id and the network_key since the value is comma separated as I needed to pass both in the value
                        $group_data_array[] = $src_group_data;
                    }
                }
                if ($this->request->getVar('count_display'))
				{
                    foreach ($this->request->getVar('count_display') as $count_group_data) {
                        $group_data_array[] = $count_group_data;
                    }
                }
                if (count($group_data_array) > 0)
				{
                    $group_post_data = implode("|", $group_data_array);
                    $networkModel->updateNetworkGroupsBySourceId($id, $group_post_data);

                }
                else
				{
                    $networkModel->updateNetworkGroupsBySourceId($id);
                }
                $this->setStatusMessage("Source '$name' was updated.", STATUS_SUCCESS);

            } catch (\Exception $ex) {
                $this->setStatusMessage("There was a problem updating '$name'.", STATUS_ERROR);
            }

            return redirect()->to(base_url($this->controllerName.'/List'));

        }
		else
		{
            // Get all the network groups that this source from this installation is currently in so that these can be pre selected in the multiselect list
            $networkGroups = $networkModel->getCurrentNetworkGroupsForSourceInInstallation($id);
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

			$uidata->data['source'] = $source;

			$uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('name', $source->name),
			);
			$uidata->data['display_name'] = array(
				'name' => 'display_name',
				'id' => 'display_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('display_name', $source->display_name),
			);
			$uidata->data['owner_name'] = array(
				'name' => 'owner_name',
				'id' => 'owner_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('owner_name', $source->owner_name),
			);
			$uidata->data['uri'] = array(
				'name' => 'uri',
				'id' => 'uri',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('uri', $source->uri),
			);
			$uidata->data['description'] = array(
				'name' => 'description',
				'id' => 'description',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('description', $source->description),
			);
			$uidata->data['owner_email'] = array(
				'name' => 'owner_email',
				'id' => 'owner_email',
				'type' => 'text',
				'class' => 'form-control',
				'value' => set_value('owner_email', $source->owner_email),
			);
			$uidata->data['status'] = array(
				'name' => 'status',
				'type' => 'dropdown',
				'class' => 'form-control',
				'value' =>set_value('status'),
				'options' => [
					SOURCE_STATUS_ONLINE => SourceHelper::getSourceStatus(SOURCE_STATUS_ONLINE),
					SOURCE_STATUS_OFFLINE => SourceHelper::getSourceStatus(SOURCE_STATUS_OFFLINE)
				],
				'selected' => (int)$source->status,
			);

			$uidata->javascript = array(JS.'cafevariome/components/transferbox.js',JS.'cafevariome/source.js');

			$data = $this->wrapData($uidata);

			return view($this->viewDirectory.'/Update', $data);
        }
    }

    public function Delete(int $id)
	{
		$source = $this->dbAdapter->Read($id);

		if ($source->isNull())
		{
			$this->setStatusMessage("Source was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

        $uidata = new UIData();
        $uidata->title = "Delete Source";
		$uidata->data['id'] = $id;

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
            $error_flag = false;
            if ($this->request->getVar('confirm') == 'yes')
			{
                //delete Elasticsearch index associated with the source
                try
				{
					$hosts = [$this->setting->GetElasticSearchUri()];
                    $elasticModel = new ElasticSearch($hosts);
                    $elasticModel->deleteIndex(ElasticsearchHelper::getSourceIndexName($id));
                }
				catch (\Exception $ex)
				{
                    $this->setStatusMessage("There was an error in deleting Elasticsearch index.", STATUS_ERROR);
                    $error_flag = true;
                }

                //delete the associated node from neo4j database
                try
				{
                    $neo4jInterface = new Neo4J();
                    $neo4jInterface->deleteSource($id);
                }
				catch (\Exception $ex)
				{
                    $this->setStatusMessage("There was an error in deleting Neo4J data of the source.", STATUS_ERROR, true);
                    $error_flag = true;
                }

                //delete files on system
                try
				{
                    $dirPath = FCPATH . UPLOAD . UPLOAD_DATA . $id;
                    if (file_exists($dirPath))
					{
                        delete_files($dirPath, true);
                    }
                } catch (\Exception $ex)
				{
                    $this->setStatusMessage("There was an error in deleting files of the source.", STATUS_ERROR, true);
                    $error_flag = true;
                }

                if (!$error_flag)
				{
                    try
					{
                        $this->sourceModel->deleteSourceFromEAVs($id);
						$this->dbAdapter->Delete($id);
                        $this->setStatusMessage("Source was deleted.", STATUS_SUCCESS, true);
                    }
					catch (\Exception $ex)
					{
                        $this->setStatusMessage("There was an error in deleting source records from database.", STATUS_ERROR, true);
                    }
                }
			}
			return redirect()->to(base_url($this->controllerName.'/List'));
		}
        else
        {
			$uidata->data['source_name'] = $source->name;
			$uidata->data['confirm'] = array(
				'name' => 'confirm',
				'type' => 'radio',
				'class' => 'form-control',
			);
        }

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory.'/Delete', $data);
	}

	public function Elasticsearch(int $id)
	{
		$source = $this->dbAdapter->Read($id);

		if ($source->isNull())
		{
			$this->setStatusMessage("Source was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = "Elasticsearch Index";

		$indexName = '-';
		$indexUUID = '-';
		$indexSize = '-';
		$indexDocIndexed = '-';
		$indexDocDeleted = '-';
		$elasticStatus = ElasticsearchHelper::ping();
		$indexStatus = ELASTICSEARCH_INDEX_STATUS_UNKNOWN;
		if ($elasticStatus){
			$elasticsearch = new ElasticSearch([$this->setting->GetElasticSearchUri()]);
			$indexName = ElasticsearchHelper::getSourceIndexName($id);
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

		$esAttributeIds = $attributeModel->getAttributeIdsBySourceIdAndStorageLocation($id, ATTRIBUTE_STORAGE_ELASTICSEARCH);
		$dataStatus = ELASTICSEARCH_DATA_STATUS_UNKNOWN;
		if(count($esAttributeIds) && $eavModel->recordsExistBySourceId($id, $esAttributeIds)){
			$indexedRecordsExist = $eavModel->indexedRecordsExistBySourceId($id, $esAttributeIds);
			$unindexedRecordsExist = $eavModel->unindexedRecordsExistBySourceId($id, $esAttributeIds);
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

		$uidata->data['sourceName'] = $source->name;
		$uidata->data['sourceId'] = $id;
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

	public function Neo4J(int $id)
	{
		$source = $this->dbAdapter->Read($id);

		if ($source->isNull())
		{
			$this->setStatusMessage("Source was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = "Neo4J Index";

		$neo4jStatus = Neo4JHelper::ping();

		$indexedSubjectsCount = '-';
		$relationshipsCount = '-';
		$indexStatus = NEO4J_INDEX_STATUS_UNKNOWN;

		if ($neo4jStatus)
		{
			$neo4j = new \App\Libraries\CafeVariome\Core\DataPipeLine\Index\Neo4J();
			$indexedSubjectsCount = $neo4j->countSubjectsBySourceId($id, $source->uid);
			if ($indexedSubjectsCount > 0)
			{
				$indexStatus = NEO4J_INDEX_STATUS_CREATED;
			}
			else
			{
				$indexStatus = NEO4J_INDEX_STATUS_NOT_CREATED;
			}
			$relationshipsCount = $neo4j->countRelationshipsBySourceId($id, $source->uid);
		}

		$attributeModel = new Attribute();
		$eavModel = new EAV();

		$n4jAttributeIds = $attributeModel->getAttributeIdsBySourceIdAndStorageLocation($id, ATTRIBUTE_STORAGE_NEO4J);
		$dataStatus = NEO4J_DATA_STATUS_UNKNOWN;
		if(count($n4jAttributeIds) > 0 && $eavModel->recordsExistBySourceId($id, $n4jAttributeIds))
		{
			$indexedRecordsExist = $eavModel->indexedRecordsExistBySourceId($id, $n4jAttributeIds);
			$unindexedRecordsExist = $eavModel->unindexedRecordsExistBySourceId($id, $n4jAttributeIds);
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

		$uidata->data['sourceName'] = $source->name;
		$uidata->data['sourceId'] = $id;
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

	public function UserInterface(int $id)
	{
		$source = $this->dbAdapter->Read($id);

		if ($source->isNull())
		{
			$this->setStatusMessage("Source was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
		$uidata->title = "User Interface Index";

		$indexName = $id . '_' . $source->uid . '.json';
		$uiIndexPath = getcwd() . DIRECTORY_SEPARATOR . USER_INTERFACE_INDEX_DIR;

		$fileMan = new SysFileMan($uiIndexPath);
		$indexSize = '-';
		$indexCreationDate = '-';

		if ($fileMan->Exists($indexName))
		{
			$indexSize = $fileMan->getSize($indexName);
			$indexCreationDate = date("D M j G:i:s T Y", $fileMan->GetModificationTimeStamp($indexName));
			$indexStatus = USER_INTERFACE_INDEX_STATUS_CREATED;
		}
		else
		{
			$indexStatus = USER_INTERFACE_INDEX_STATUS_NOT_CREATED;
		}

		$uidata->data['sourceName'] = $source->name;
		$uidata->data['sourceId'] = $id;
		$uidata->data['indexName'] = $indexName;
		$uidata->data['indexSize'] = $indexSize == '-' ? $indexSize : SourceHelper::formatSize($indexSize);
		$uidata->data['indexCreationDate'] = $indexCreationDate;
		$uidata->data['indexStatusText'] = SourceHelper::getUserInterfaceIndexStatus($indexStatus);
		$uidata->data['lastTaskId'] = (new TaskAdapterFactory())->GetInstance()->ReadLastProcessingTaskIdBySourceIdAndType($source->getID(), TASK_TYPE_SOURCE_INDEX_ELASTICSEARCH);

		$uidata->javascript = [JS."cafevariome/userinterfaceindex.js"];

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory.'/UserInterface', $data);
	}
}
