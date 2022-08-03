<?php namespace App\Controllers;


/**
 * DataFile.php
 * Created 17/06/2022
 *
 * This class offers CRUD operation for DataFile.
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Core\DataPipeLine\DataPipeLine;
use App\Libraries\CafeVariome\Core\DataPipeLine\Input\SpreadsheetDataInput;
use App\Libraries\CafeVariome\Core\IO\FileSystem\FileMan;
use App\Libraries\CafeVariome\Core\IO\FileSystem\UploadFileMan;
use App\Libraries\CafeVariome\Factory\DataFileAdapterFactory;
use App\Libraries\CafeVariome\Factory\DataFileFactory;
use App\Libraries\CafeVariome\Factory\PipelineAdapterFactory;
use App\Libraries\CafeVariome\Factory\SourceAdapterFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class DataFile extends CVUI_Controller
{

	private $validation;

	/**
	 * Validation list template.
	 *
	 * @var string
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
		$this->dbAdapter = (new DataFileAdapterFactory())->GetInstance();
	}

	public function Index()
	{
		return redirect()->to(base_url($this->controllerName . '/List'));
	}

	public function List(int $source_id)
	{
		$sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$source = $sourceAdapter->Read($source_id);
		if ($source->isNull())
		{
			$this->setStatusMessage("Source was not found.", STATUS_ERROR);
			return redirect()->to(base_url('Source'));
		}

		$pipelineAdapter = (new PipelineAdapterFactory())->GetInstance();
		$pipelines = $pipelineAdapter->ReadAll();
		$pipelineNames = [-1 => 'Please select a pipeline...'];

		foreach ($pipelines as $pipeline)
		{
			$pipelineNames[$pipeline->getID()] = $pipeline->name;
		}

		$uidata = new UIData();
		$uidata->title = 'Data Files';

		$dataFiles = $this->dbAdapter->ReadBySourceId($source_id);
		$uidata->data['dataFiles'] = $dataFiles;
		$uidata->data['source'] = $source;

		$uidata->data['pipeline'] = array(
			'name' => 'pipeline',
			'id' => 'pipeline',
			'type' => 'dropdown',
			'class' => 'form-control',
			'value' => set_value('pipeline'),
			'options' => $pipelineNames
		);

		$uidata->css = [VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css'];
		$uidata->javascript = [
			JS. 'cafevariome/datafile.js',
			VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js'
		];

		$data = $this->wrapData($uidata);
		return view($this->controllerName . '/List', $data);
	}

	public function Upload(int $source_id)
	{
		$sourceAdapter = (new SourceAdapterFactory())->GetInstance();
		$source = $sourceAdapter->Read($source_id);
		if ($source->isNull())
		{
			$this->setStatusMessage("Source was not found.", STATUS_ERROR);
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = "Upload Data File";

		$uidata->css = [VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css'];
		$uidata->javascript = [
			VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',
			JS. 'bootstrap-notify.js',
			JS.'cafevariome/datafile.js'
		];

		$maximumAllowedUploadSize = UploadFileMan::getMaximumAllowedUploadSize();
		$uidata->data['maxUploadSize'] = UploadFileMan::parseSizeToByte($maximumAllowedUploadSize);
		$uidata->data['maxUploadSizeH'] = $maximumAllowedUploadSize;
		$allowedFormats = UploadFileMan::GetAllowedDataFileFormats(false);
		$uidata->data['allowedFormats'] = $allowedFormats;
		$uidata->data['source'] = $source;

		$this->validation->setRules([
			'name' => [
				'label'  => 'Data File',
				'rules'  => "uploaded[name]",
				'errors' => [
					'uploaded' => '{field} is required.',
				]
			]
		]);

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$basePath = FCPATH . UPLOAD . UPLOAD_DATA;

				$fileMan = new UploadFileMan($basePath, true, 27);

				if (!$fileMan->Exists($source->getID()))
				{
					$fileMan->CreateDirectory($source->getID());
				}

				$files = $fileMan->getFiles();

				if (count($files) == 1)
				{
					$error = '';
					if ($fileMan->isValid($files[0], $error))
					{
						//Upload file
						if ($fileMan->Save($files[0], $source->getID()))
						{
							$name = $files[0]->getName();
							$disk_name = $files[0]->getDiskName();
							$size = $files[0]->getSize();
							$dataFileFactory = new DataFileFactory();

							$this->dbAdapter->Create($dataFileFactory->GetInstanceFromParameters(
								$name,
								$disk_name,
								$size,
								time(),
								0,
								$this->authenticator->GetUserId(),
								$source->getID(),
								DATA_FILE_STATUS_UPLOADED
							));

							$this->setStatusMessage("Data file '$name' was uploaded.", STATUS_SUCCESS);
						}
						else
						{
							$this->setStatusMessage("There was a problem uploading data file. File could not be saved on the server.", STATUS_ERROR);
						}
					}
					else
					{
						$this->setStatusMessage("There was a problem uploading data file. File is not valid: $error", STATUS_ERROR);
					}
				}
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem uploading data file: " . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List/' . $source_id));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

			$uidata->data['name'] = array(
				'name' => 'name',
				'id' => 'name',
				'type' => 'text',
				'class' => 'custom-file-input',
				'aria-describedby' => 'name',
				'value' =>set_value('name'),
			);
		}

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory . '/Upload', $data);
	}

	public function Create()
	{
		$uidata = new UIData();
		$uidata->title = '';

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$this->setStatusMessage("Data File '' was created.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem creating Data File: " . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List'));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Create', $data);
	}

	public function Update(int $id)
	{
		$uidata = new UIData();
		$uidata->title = '';

		if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
			try
			{
				$this->setStatusMessage("Data File '' was updated.", STATUS_SUCCESS);

			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem updating Data File: " . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List'));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

		}

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Update', $data);
	}

	public function Details(int $id)
	{
		$uidata = new UIData();
		$uidata->title = '';

		$data = $this->wrapData($uidata);

		return view($this->controllerName . '/Details', $data);
	}

	public function Delete(int $id)
	{
		$dataFile = $this->dbAdapter->Read($id);
		if ($dataFile->isNull())
		{
			$this->setStatusMessage("Data file was not found.", STATUS_ERROR);
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Data File';
		$uidata->data['dataFile'] = $dataFile;

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
			try
			{
				$confirm = $this->request->getVar('confirm');
				if ($confirm == 'yes')
				{
					$dataInput = new DataPipeLine($dataFile->source_id);
					$dataInput->DeleteExistingRecords($id);
					$this->dbAdapter->Delete($id);
					$this->setStatusMessage("Data file was deleted.", STATUS_SUCCESS);
				}
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem deleting data file: " . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List/' . $dataFile->source_id));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/Delete', $data);
	}

	public function DeleteRecords(int $id)
	{
		$dataFile = $this->dbAdapter->Read($id);
		if ($dataFile->isNull())
		{
			$this->setStatusMessage("Data file was not found.", STATUS_ERROR);
			return redirect()->to(base_url('Source'));
		}

		$uidata = new UIData();
		$uidata->title = 'Delete Data File Records';
		$uidata->data['dataFile'] = $dataFile;

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
			try
			{
				$confirm = $this->request->getVar('confirm');
				if ($confirm == 'yes')
				{
					$dataInput = new DataPipeLine($dataFile->source_id);
					$dataInput->DeleteExistingRecords($id);
					$this->dbAdapter->UpdateStatus($id, DATA_FILE_STATUS_UPLOADED);
					$this->setStatusMessage("Data file records were deleted.", STATUS_SUCCESS);
				}
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem deleting data file records: " . $ex->getMessage(), STATUS_ERROR);
			}

			return redirect()->to(base_url($this->controllerName . '/List/' . $dataFile->source_id));
		}
		else
		{
			$uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');
		}

		$data = $this->wrapData($uidata);

		return view($this->viewDirectory . '/DeleteRecords', $data);
	}
}
