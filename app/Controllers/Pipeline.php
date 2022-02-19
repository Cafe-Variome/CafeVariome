<?php namespace App\Controllers;

/**
 * Name: Pipeline.php
 *
 * Created: 15/05/2021
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Helpers\UI\PipelineHelper;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class Pipeline extends CVUI_Controller
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
    }

    public function Index()
    {
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function List()
    {
        $uidata = new UIData();
        $uidata->title = "Data Pipelines";

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(JS.'cafevariome/pipeline.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $pipelineModel = new \App\Models\Pipeline();

        $pipelines = $pipelineModel->getPipelines();

		foreach ($pipelines as &$pipeline)
		{
			$pipeline['subject_id_location'] = PipelineHelper::getSubjectIDLocation($pipeline['subject_id_location']);
			$pipeline['grouping'] = PipelineHelper::getGrouping($pipeline['grouping']);
		}
        $uidata->data['pipelinesList'] = $pipelines;

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/List.php', $data);
    }

    public function Delete(int $pipeline_id)
    {
        $uidata = new UIData();
        $uidata->title = "Delete Pipeline";

        $pipelineModel = new \App\Models\Pipeline();

        $this->validation->setRules([
            'confirm' => [
                'label'  => 'confirmation',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],

            'pipeline_id' => [
                'label'  => 'Pipeline Id',
                'rules'  => 'required|alpha_dash',
                'errors' => [
                    'required' => '{field} is required.',
                    'alpha_dash' => '{field} must only contain alpha-numeric characters, underscores, or dashes.'
                ]
            ]
        ]);

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $pipeline_id = $this->request->getVar('pipeline_id');
            $confirm = $this->request->getVar('confirm');
            if ($confirm == 'yes') {
                try {
                    $pipeline = $pipelineModel->getPipeline($pipeline_id);
                    if ($pipeline)  {
                        $pipelineName = $pipeline['name'];
                        $pipelineModel->deletePipeline($pipeline_id);
                        $this->setStatusMessage("Pipeline '$pipelineName' was deleted.", STATUS_SUCCESS);
                    }
                    else{
                        $this->setStatusMessage("Pipeline does not exist.", STATUS_ERROR);
                    }
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem deleting the pipeline.", STATUS_ERROR);
                }
            }
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else {
            $pipeline = $pipelineModel->getPipeline($pipeline_id);
            if ($pipeline) {
                $pipeline_name = $pipeline['name'];
                $uidata->data['pipeline_id'] = $pipeline_id;
                $uidata->data['pipeline_name'] = $pipeline_name;
            }
            else {
                $this->setStatusMessage("Pipeline was not found.", STATUS_ERROR);
                return redirect()->to(base_url($this->controllerName.'/List'));
            }

            $data = $this->wrapData($uidata);

            return view($this->viewDirectory.'/Delete', $data);
        }
    }

    public function Create()
    {
        $uidata = new UIData();
        $uidata->title = "Create Data Pipeline";
        $uidata->javascript = array(JS.'cafevariome/pipeline.js');

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
			$pipeline_name = $this->request->getVar('name');
			$subject_id_location = $this->request->getVar('subject_id_location');
			$subject_id_attribute_name = $this->request->getVar('subject_id_attribute_name');
			$subject_id_prefix = $this->request->getVar('subject_id_prefix');
			$subject_id_assignment_batch_size = $this->request->getVar('subject_id_batch_size');
			$grouping = $this->request->getVar('grouping');
			$group_columns = $this->request->getVar('group_columns');
			$internal_delimiter = $this->request->getVar('internal_delimiter');

            try
			{
                $pipelineModel = new \App\Models\Pipeline();

                $data = [
                    'name' => $pipeline_name,
                    'subject_id_location' => $subject_id_location,
                    'subject_id_attribute_name' => $subject_id_attribute_name,
					'subject_id_prefix' => $subject_id_prefix,
					'subject_id_assignment_batch_size' => $subject_id_assignment_batch_size,
                    'grouping' => $grouping,
                    'group_columns' => $group_columns,
                    'internal_delimiter' => $internal_delimiter
                ];

                $pipelineModel->createPipeline($data);

                $this->setStatusMessage("Pipeline '$pipeline_name' was created.", STATUS_SUCCESS);
            } catch (\Exception $ex) {
                $this->setStatusMessage("There was a problem creating ' '."  . $ex->getMessage(), STATUS_ERROR);
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
                'type' => 'subject_id_location',
                'class' => 'form-control',
                'value' =>set_value('subject_id_location'),
                'options' => ['0' => 'Attribute in File', '1' => 'File Name']
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

            $uidata->data['grouping'] = array(
                'name' => 'grouping',
                'id' => 'grouping',
                'type' => 'dropdown',
                'class' => 'form-control',
                'value' =>set_value('grouping'),
                'options' => ['0' => 'Group Individually', '1' => 'Custom']
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
        $uidata = new UIData();
        $uidata->title = "Data Pipeline Details";

        $pipelineModel = new \App\Models\Pipeline();
        $pipeline = $pipelineModel->getPipeline($id);

        if($pipeline == null)
		{
            $this->setStatusMessage("Pipeline not found.", STATUS_ERROR);
            return redirect()->to(base_url($this->controllerName.'/List'));
        }

        $uidata->data['pipeline'] = $pipeline;

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory . '/Details.php', $data);
    }

    public function Update(int $id)
    {
        $uidata = new UIData();
        $uidata->title = "Edit Data Pipeline";
        $uidata->javascript = array(JS.'cafevariome/pipeline.js');

        $pipelineModel = new \App\Models\Pipeline();

        $this->validation->setRules(        [
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
                $pipeline_name = $this->request->getVar('name');
                $subject_id_location = $this->request->getVar('subject_id_location');
                $subject_id_attribute_name = $this->request->getVar('subject_id_attribute_name');
				$subject_id_prefix = $this->request->getVar('subject_id_prefix');
				$subject_id_assignment_batch_size = $this->request->getVar('subject_id_batch_size');
                $grouping = $this->request->getVar('grouping');
                $group_columns = $this->request->getVar('group_columns');
                $internal_delimiter = $this->request->getVar('internal_delimiter');

                $data = [
                    'name' => $pipeline_name,
                    'subject_id_location' => $subject_id_location,
                    'subject_id_attribute_name' => $subject_id_attribute_name,
					'subject_id_prefix' => $subject_id_prefix,
					'subject_id_assignment_batch_size' => $subject_id_assignment_batch_size,
                    'grouping' => $grouping,
                    'group_columns' => $group_columns,
                    'internal_delimiter' => $internal_delimiter
                ];

                $pipelineModel->updatePipeline($data, ['id' => $id]);

                $this->setStatusMessage("Pipeline '$pipeline_name' was updated.", STATUS_SUCCESS);
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

            $pipeline = $pipelineModel->getPipeline($id);

            if($pipeline == null)
			{
                $this->setStatusMessage("Pipeline not found.", STATUS_ERROR);
                return redirect()->to(base_url($this->controllerName.'/List'));
            }

            $uidata->data['pipeline_id'] = $pipeline['id'];

            $uidata->data['name'] = array(
                'name' => 'name',
                'id' => 'name',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('name', $pipeline['name']),
            );

            $uidata->data['subject_id_location'] = array(
                'name' => 'subject_id_location',
                'id' => 'subject_id_location',
                'type' => 'subject_id_location',
                'class' => 'form-control',
                'options' => ['0' => 'Attribute in File', '1' => 'File Name'],
                'value' =>set_value('subject_id_location', $pipeline['subject_id_location']),
                'selected' => $pipeline['subject_id_location']
            );

            $uidata->data['subject_id_attribute_name'] = array(
                'name' => 'subject_id_attribute_name',
                'id' => 'subject_id_attribute_name',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('subject_id_attribute_name', $pipeline['subject_id_attribute_name']),
            );

			$uidata->data['subject_id_prefix'] = array(
				'name' => 'subject_id_prefix',
				'id' => 'subject_id_prefix',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('subject_id_prefix',  $pipeline['subject_id_prefix']),
			);

			$uidata->data['subject_id_batch_size'] = array(
				'name' => 'subject_id_batch_size',
				'id' => 'subject_id_batch_size',
				'type' => 'text',
				'class' => 'form-control',
				'value' =>set_value('subject_id_batch_size', $pipeline['subject_id_assignment_batch_size']),
			);

            $uidata->data['grouping'] = array(
                'name' => 'grouping',
                'id' => 'grouping',
                'type' => 'dropdown',
                'class' => 'form-control',
                'options' => ['0' => 'Group Individually', '1' => 'Custom'],
                'value' =>set_value('grouping',  $pipeline['grouping']),
                'selected' => $pipeline['grouping']
            );

            $uidata->data['group_columns'] = array(
                'name' => 'group_columns',
                'id' => 'group_columns',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('group_columns',  $pipeline['group_columns']),
            );

            $uidata->data['internal_delimiter'] = array(
                'name' => 'internal_delimiter',
                'id' => 'internal_delimiter',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('internal_delimiter', $pipeline['internal_delimiter']),
            );
        }

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory . '/Update.php', $data);
    }
}
