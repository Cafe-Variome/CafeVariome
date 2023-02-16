<?php namespace App\Controllers;

/**
 * Name: Pipeline.php
 *
 * Created: 15/05/2021
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\ViewModels\PipelineDetails;
use App\Libraries\CafeVariome\Entities\ViewModels\PipelineList;
use App\Libraries\CafeVariome\Factory\PipelineAdapterFactory;
use App\Libraries\CafeVariome\Factory\PipelineFactory;
use App\Libraries\CafeVariome\Helpers\UI\PipelineHelper;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class Pipeline extends CVUIController
{
    private $validation;
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
		$this->dbAdapter = (new PipelineAdapterFactory())->GetInstance();
    }

    public function Index()
    {
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function List()
    {
        $uidata = new UIData();
        $uidata->title = "Data Pipelines";

        $uidata->IncludeDataTables();
        $uidata->IncludeJavaScript(JS.'cafevariome/pipeline.js');

        $pipelines = $this->dbAdapter->SetModel(PipelineList::class)->ReadAll();

        $uidata->data['pipelines'] = $pipelines;

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/List.php', $data);
    }

    public function Delete(int $id)
    {
		$pipeline = $this->dbAdapter->SetModel(PipelineDetails::class)->Read($id);

		if ($pipeline->isNull())
		{
			$this->setStatusMessage("Pipeline was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

        $uidata = new UIData();
        $uidata->title = "Delete Pipeline";

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
            $confirm = $this->request->getVar('confirm');
            if ($confirm == 'yes')
			{
                try
				{
					$pipelineName = $pipeline->name;
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Pipeline '$pipelineName' was deleted.", STATUS_SUCCESS);
                }
				catch (\Exception $ex)
				{
                    $this->setStatusMessage("There was a problem deleting the pipeline.", STATUS_ERROR);
                }
            }
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else
		{
			$uidata->data['pipeline'] = $pipeline;

            $data = $this->wrapData($uidata);

            return view($this->viewDirectory.'/Delete', $data);
        }
    }

    public function Create()
    {
        $uidata = new UIData();
        $uidata->title = "Create Data Pipeline";
        $uidata->IncludeJavaScript(JS.'cafevariome/pipeline.js');

        $this->validation->setRules([
            'name' => [
                'label'  => 'Name',
                'rules'  => 'required|alpha_space|is_unique[pipelines.name]|max_length[50]',
                'errors' => [
                    'required' => '{field} is required.',
                    'alpha_space' => 'The only valid characters for {field} are alphabetical characters and spaces.',
                    'uniquename_check' => '{field} already exists.',
                    'max_length' => 'Maximum length of {field} is 50 characters.'
                ]
            ],
            'subject_id_location' => [
                'label' => 'Subject ID Location',
                'rules' => 'required|integer|max_length[3]',
                'errors' => [
                    'required' => '{field} is required.',
                    'integer' => 'The only valid type for {field} is integer.',
                    'max_length' => 'Maximum length of {field} is 3 digits.'
                ]
            ],
            'subject_id_attribute_name' => [
                'label' => 'Subject ID Attribute Name',
                'rules' => 'subject_id_required_with[subject_id_location]|max_length[100]',
                'errors' => [
                    'subject_id_required_with' => '{field} cannot be empty when Subject ID Location is set to `Attribute in File`.',
                    'max_length' => 'Maximum length of {field} is 100 characters.'
                ]
            ],
			'subject_id_prefix' => [
				'label' => 'Subject ID Prefix',
				'rules' => 'permit_empty|alpha_dash|max_length[16]',
				'errors' => [
					'alpha_dash' => '{field} can only accept alphanumeric characters, dashes, and underscores.',
					'max_length' => 'Maximum length of {field} is 16 characters.'
				]
			],
			'subject_id_batch_size' => [
				'label' => 'Subject ID Batch Size',
				'rules' => 'required|integer|max_length[4]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => 'The only valid type for {field} is integer.',
					'max_length' => 'Maximum length of {field} is 4 digits.'
				]
			],
			'subject_id_expansion_columns' => [
				'label' => 'Subject ID Expansion Columns',
				'rules' => 'expansion_columns_required_with[subject_id_location]|max_length[50]',
				'errors' => [
					'expansion_columns_required_with' => '{field} cannot be empty when Subject ID Location is set to `No Subject ID Given - Assign by Expansion of Column(s)`.',
					'max_length' => 'Maximum length of {field} is 50 characters.'
				]
			],
			'subject_id_expansion_policy' => [
				'label' => 'Policy of Expansion',
				'rules' => 'expansion_policy_required_with[subject_id_location]',
				'errors' => [
				]
			],
			'expansion_attribute_name' => [
				'label' => 'Expansion Attribute Name',
				'rules' => 'expansion_attribute_name_required_with[subject_id_location]|max_length[200]',
				'errors' => [
					'max_length' => 'Maximum length of {field} is 200 characters.'
				]
			],
            'grouping' => [
                'label' => 'Grouping',
                'rules' => 'required|integer|max_length[3]',
                'errors' => [
                    'required' => '{field} is required.',
                    'integer' => 'The only valid type for {field} is integer.',
                    'max_length' => 'Maximum length of {field} is 3 digits.'
                ]
            ],
            'group_columns' => [
                'label' => 'Group Columns',
                'rules' => 'group_columns_required_with[grouping]|max_length[200]',
                'errors' => [
                    'integer' => 'The only valid type for {field} is integer.',
                    'max_length' => 'Maximum length of {field} is 200 characters.'
                ]
            ],
            'internal_delimiter' => [
                'label' => 'Internal Delimiter',
                'rules' => 'permit_empty|valid_delimiter[' . $this->request->getVar('internal_delimiter') . ']|max_length[1]',
                'errors' => [
                    'valid_delimiter' => 'The only valid inputs for {field} are (,), (/), (;), (:), (|), (*), (&), (%), ($), (!), (~), (#), (-), (_), (+), (=), (^), and (.).',
                    'max_length' => 'Maximum length of {field} is 1 character.'
                ]
            ]
        ]);

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			$name = $this->request->getVar('name');
			$subject_id_location = $this->request->getVar('subject_id_location');
			$subject_id_attribute_name = $this->request->getVar('subject_id_attribute_name');
			$subject_id_prefix = $this->request->getVar('subject_id_prefix');
			$subject_id_assignment_batch_size = $this->request->getVar('subject_id_batch_size');
			$subject_id_expansion_policy = $this->request->getVar('subject_id_expansion_policy');
			$subject_id_expansion_columns = $this->request->getVar('subject_id_expansion_columns');
			$expansion_attribute_name = $this->request->getVar('expansion_attribute_name');
			$grouping = $this->request->getVar('grouping');
			$group_columns = $this->request->getVar('group_columns');
			$internal_delimiter = $this->request->getVar('internal_delimiter');

            try
			{
				$this->dbAdapter->Create((
					new PipelineFactory())->GetInstanceFromParameters(
						$name,
						$subject_id_location,
						$subject_id_attribute_name,
						$subject_id_prefix,
						$subject_id_assignment_batch_size,
						$subject_id_expansion_policy,
						$subject_id_expansion_columns,
						$expansion_attribute_name,
						$grouping,
						$group_columns,
						$internal_delimiter
				));

                $this->setStatusMessage("Pipeline '$name' was created.", STATUS_SUCCESS);
            }
			catch (\Exception $ex)
			{
                $this->setStatusMessage("There was a problem creating '$name'."  . $ex->getMessage(), STATUS_ERROR);
            }

            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else
		{
            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $uidata->data['name'] = array(
                'name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('name'),
            );

            $uidata->data['subject_id_location'] = array(
                'name' => 'subject_id_location',
                'id' => 'subject_id_location',
                'type' => 'dropdown',
                'class' => 'form-select',
                'value' =>set_value('subject_id_location'),
                'options' => [
					SUBJECT_ID_WITHIN_FILE => PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_WITHIN_FILE),
					SUBJECT_ID_IN_FILE_NAME => PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_IN_FILE_NAME),
					SUBJECT_ID_PER_BATCH_OF_RECORDS => PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_PER_BATCH_OF_RECORDS),
					SUBJECT_ID_PER_FILE => PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_PER_FILE),
					SUBJECT_ID_BY_EXPANSION_ON_COLUMNS => PipelineHelper::GetSubjectIDLocation(SUBJECT_ID_BY_EXPANSION_ON_COLUMNS)
				]
            );

            $uidata->data['subject_id_attribute_name'] = array(
                'name' => 'subject_id_attribute_name',
                'id' => 'subject_id_attribute_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('subject_id_attribute_name'),
            );

			$uidata->data['subject_id_prefix'] = array(
				'name' => 'subject_id_prefix',
				'id' => 'subject_id_prefix',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('subject_id_prefix'),
			);

			$uidata->data['subject_id_batch_size'] = array(
				'name' => 'subject_id_batch_size',
				'id' => 'subject_id_batch_size',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('subject_id_batch_size'),
			);

			$uidata->data['subject_id_expansion_policy'] = array(
				'name' => 'subject_id_expansion_policy',
				'id' => 'subject_id_expansion_policy',
				'type' => 'subject_id_expansion_policy',
				'class' => 'form-select',
				'value' =>set_value('subject_id_expansion_policy'),
				'options' => [
					SUBJECT_ID_EXPANDSION_POLICY_INDIVIDUAL => PipelineHelper::GetExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_INDIVIDUAL),
					SUBJECT_ID_EXPANDSION_POLICY_MAXIMUM => PipelineHelper::GetExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_MAXIMUM),
					SUBJECT_ID_EXPANDSION_POLICY_MINIMUM => PipelineHelper::GetExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_MINIMUM)
				]
			);

			$uidata->data['subject_id_expansion_columns'] = array(
				'name' => 'subject_id_expansion_columns',
				'id' => 'subject_id_expansion_columns',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('subject_id_expansion_columns'),
			);

			$uidata->data['expansion_attribute_name'] = array(
				'name' => 'expansion_attribute_name',
				'id' => 'expansion_attribute_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('expansion_attribute_name'),
			);

            $uidata->data['grouping'] = array(
                'name' => 'grouping',
                'id' => 'grouping',
                'type' => 'dropdown',
                'class' => 'form-select',
                'value' =>set_value('grouping'),
                'options' => [
					GROUPING_COLUMNS_ALL => PipelineHelper::GetGrouping(GROUPING_COLUMNS_ALL),
					GROUPING_COLUMNS_CUSTOM => PipelineHelper::GetGrouping(GROUPING_COLUMNS_CUSTOM)
				]
            );

            $uidata->data['group_columns'] = array(
                'name' => 'group_columns',
                'id' => 'group_columns',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('group_columns'),
            );

            $uidata->data['internal_delimiter'] = array(
                'name' => 'internal_delimiter',
                'id' => 'internal_delimiter',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('internal_delimiter'),
            );
        }

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory . '/Create.php', $data);
    }

    public function Details(int $id)
    {
		$pipeline = $this->dbAdapter->SetModel(PipelineDetails::class)->Read($id);

		if ($pipeline->isNull())
		{
			$this->setStatusMessage("Pipeline was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$uidata = new UIData();
        $uidata->title = "Data Pipeline Details";

        $uidata->data['pipeline'] = $pipeline;

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory . '/Details.php', $data);
    }

    public function Update(int $id)
    {
		$pipeline = $this->dbAdapter->Read($id);

		if ($pipeline->isNull())
		{
			$this->setStatusMessage("Pipeline was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

        $uidata = new UIData();
        $uidata->title = "Edit Data Pipeline";
        $uidata->IncludeJavaScript(JS.'cafevariome/pipeline.js');

        $this->validation->setRules([
            'name' => [
                'label'  => 'Name',
                'rules'  => 'required|alpha_space|is_unique[pipelines.name,id,{id}]|max_length[50]',
                'errors' => [
                    'required' => '{field} is required.',
                    'alpha_space' => 'The only valid characters for {field} are alphabetical characters and spaces.',
                    'is_unique' => '{field} already exists.',
                    'max_length' => 'Maximum length of {field} is 50 characters.'
                ]
            ],
            'subject_id_location' => [
                'label' => 'Subject ID Location',
                'rules' => 'required|integer|max_length[3]',
                'errors' => [
                    'required' => '{field} is required.',
                    'integer' => 'The only valid type for {field} is integer.',
                    'max_length' => 'Maximum length of {field} is 3 digits.'
                ]
            ],
            'subject_id_attribute_name' => [
                'label' => 'Subject ID Attribute Name',
                'rules' => 'subject_id_required_with[subject_id_location]|max_length[100]',
                'errors' => [
                    'subject_id_required_with' => '{field} cannot be empty when Subject ID Location is set to Attribute in File.',
                    'max_length' => 'Maximum length of {field} is 100 characters.'
                ]
            ],
			'subject_id_prefix' => [
				'label' => 'Subject ID Prefix',
				'rules' => 'permit_empty|alpha_dash|max_length[16]',
				'errors' => [
					'alpha_dash' => '{field} can only accept alphanumeric characters, dashes, and underscores.',
					'max_length' => 'Maximum length of {field} is 16 characters.'
				]
			],
			'subject_id_batch_size' => [
				'label' => 'Subject ID Batch Size',
				'rules' => 'required|integer|max_length[4]',
				'errors' => [
					'required' => '{field} is required.',
					'integer' => 'The only valid type for {field} is integer.',
					'max_length' => 'Maximum length of {field} is 4 digits.'
				]
			],
			'subject_id_expansion_columns' => [
				'label' => 'Subject ID Expansion Columns',
				'rules' => 'expansion_columns_required_with[subject_id_location]|max_length[50]',
				'errors' => [
					'expansion_columns_required_with' => '{field} cannot be empty when Subject ID Location is set to `No Subject ID Given - Assign by Expansion of Column(s)`.',
					'max_length' => 'Maximum length of {field} is 50 characters.'
				]
			],
			'subject_id_expansion_policy' => [
				'label' => 'Policy of Expansion',
				'rules' => 'expansion_policy_required_with[subject_id_location]',
				'errors' => [
				]
			],
			'expansion_attribute_name' => [
				'label' => 'Expansion Attribute Name',
				'rules' => 'expansion_attribute_name_required_with[subject_id_location]|max_length[200]',
				'errors' => [
					'max_length' => 'Maximum length of {field} is 200 characters.'
				]
			],
            'grouping' => [
                'label' => 'Grouping',
                'rules' => 'required|integer|max_length[3]',
                'errors' => [
                    'required' => '{field} is required.',
                    'integer' => 'The only valid type for {field} is integer.',
                    'max_length' => 'Maximum length of {field} is 3 digits.'
                ]
            ],
            'group_columns' => [
                'label' => 'Group Columns',
                'rules' => 'group_columns_required_with[grouping]|max_length[200]',
                'errors' => [
                    'integer' => 'The only valid type for {field} is integer.',
                    'max_length' => 'Maximum length of {field} is 200 digits.'
                ]
            ],
            'internal_delimiter' => [
                'label' => 'Internal Delimiter',
                'rules' => 'permit_empty|valid_delimiter[' . $this->request->getVar('internal_delimiter') . ']|max_length[1]',
                'errors' => [
                    'valid_delimiter' => 'The only valid inputs for {field} are (,), (/), (;), (:), (|), (*), (&), (%), ($), (!), (~), (#), (-), (_), (+), (=), (^), and (.).',
                    'max_length' => 'Maximum length of {field} is 1 character.'
                ]
            ]
        ]);

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
            try
			{
                $name = $this->request->getVar('name');
                $subject_id_location = $this->request->getVar('subject_id_location');
                $subject_id_attribute_name = $this->request->getVar('subject_id_attribute_name');
				$subject_id_prefix = $this->request->getVar('subject_id_prefix');
				$subject_id_assignment_batch_size = $this->request->getVar('subject_id_batch_size');
				$subject_id_expansion_policy = $this->request->getVar('subject_id_expansion_policy');
				$subject_id_expansion_columns = $this->request->getVar('subject_id_expansion_columns');
				$expansion_attribute_name = $this->request->getVar('expansion_attribute_name');
				$grouping = $this->request->getVar('grouping');
                $group_columns = $this->request->getVar('group_columns');
                $internal_delimiter = $this->request->getVar('internal_delimiter');

				$this->dbAdapter->Update($id, (new PipelineFactory())->GetInstanceFromParameters(
					$name,
					$subject_id_location,
					$subject_id_attribute_name,
					$subject_id_prefix,
					$subject_id_assignment_batch_size,
					$subject_id_expansion_policy,
					$subject_id_expansion_columns,
					$expansion_attribute_name,
					$grouping,
					$group_columns,
					$internal_delimiter
				));

                $this->setStatusMessage("Pipeline '$name' was updated.", STATUS_SUCCESS);
            }
			catch (\Exception $ex)
			{
                $this->setStatusMessage("There was a problem updating ' '.", STATUS_ERROR);
            }
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else
		{
            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $uidata->data['pipeline_id'] = $pipeline->getID();

            $uidata->data['name'] = array(
                'name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('name', $pipeline->name),
            );

            $uidata->data['subject_id_location'] = array(
                'name' => 'subject_id_location',
                'id' => 'subject_id_location',
                'type' => 'dropdown',
                'class' => 'form-select',
				'options' => [
					SUBJECT_ID_WITHIN_FILE => PipelineHelper::getSubjectIDLocation(SUBJECT_ID_WITHIN_FILE),
					SUBJECT_ID_IN_FILE_NAME => PipelineHelper::getSubjectIDLocation(SUBJECT_ID_IN_FILE_NAME),
					SUBJECT_ID_PER_BATCH_OF_RECORDS => PipelineHelper::getSubjectIDLocation(SUBJECT_ID_PER_BATCH_OF_RECORDS),
					SUBJECT_ID_PER_FILE => PipelineHelper::getSubjectIDLocation(SUBJECT_ID_PER_FILE),
					SUBJECT_ID_BY_EXPANSION_ON_COLUMNS => PipelineHelper::getSubjectIDLocation(SUBJECT_ID_BY_EXPANSION_ON_COLUMNS)
				],
                'value' =>set_value('subject_id_location', $pipeline->subject_id_location),
                'selected' => $pipeline->subject_id_location
            );

            $uidata->data['subject_id_attribute_name'] = array(
                'name' => 'subject_id_attribute_name',
                'id' => 'subject_id_attribute_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('subject_id_attribute_name', $pipeline->subject_id_attribute_name),
            );

			$uidata->data['subject_id_prefix'] = array(
				'name' => 'subject_id_prefix',
				'id' => 'subject_id_prefix',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('subject_id_prefix',  $pipeline->subject_id_prefix),
			);

			$uidata->data['subject_id_batch_size'] = array(
				'name' => 'subject_id_batch_size',
				'id' => 'subject_id_batch_size',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('subject_id_batch_size', $pipeline->subject_id_assignment_batch_size),
			);

			$uidata->data['subject_id_expansion_policy'] = array(
				'name' => 'subject_id_expansion_policy',
				'id' => 'subject_id_expansion_policy',
				'type' => 'dropdown',
				'class' => 'form-select',
				'options' => [
					SUBJECT_ID_EXPANDSION_POLICY_INDIVIDUAL => PipelineHelper::getExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_INDIVIDUAL),
					SUBJECT_ID_EXPANDSION_POLICY_MAXIMUM => PipelineHelper::getExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_MAXIMUM),
					SUBJECT_ID_EXPANDSION_POLICY_MINIMUM => PipelineHelper::getExpansionPolicy(SUBJECT_ID_EXPANDSION_POLICY_MINIMUM)
				],
				'value' =>set_value('subject_id_expansion_policy', $pipeline->expansion_policy),
				'selected' => $pipeline->expansion_policy
			);

			$uidata->data['subject_id_expansion_columns'] = array(
				'name' => 'subject_id_expansion_columns',
				'id' => 'subject_id_expansion_columns',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('subject_id_expansion_columns',  $pipeline->expansion_columns),
			);

			$uidata->data['expansion_attribute_name'] = array(
				'name' => 'expansion_attribute_name',
				'id' => 'expansion_attribute_name',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('expansion_attribute_name', $pipeline->expansion_attribute_name),
			);

            $uidata->data['grouping'] = array(
                'name' => 'grouping',
                'id' => 'grouping',
                'type' => 'dropdown',
                'class' => 'form-select',
				'options' => [
					GROUPING_COLUMNS_ALL => PipelineHelper::getGrouping(GROUPING_COLUMNS_ALL),
					GROUPING_COLUMNS_CUSTOM => PipelineHelper::getGrouping(GROUPING_COLUMNS_CUSTOM)
				],
                'value' =>set_value('grouping',  $pipeline->grouping),
                'selected' => $pipeline->grouping
            );

            $uidata->data['group_columns'] = array(
                'name' => 'group_columns',
                'id' => 'group_columns',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('group_columns',  $pipeline->group_columns),
            );

            $uidata->data['internal_delimiter'] = array(
                'name' => 'internal_delimiter',
                'id' => 'internal_delimiter',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('internal_delimiter', $pipeline->internal_delimiter),
            );
        }

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory . '/Update.php', $data);
    }
}
