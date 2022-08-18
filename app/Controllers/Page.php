<?php namespace App\Controllers;

/**
 * Name: Page.php
 *
 * Created: 19/02/2020
 *
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Entities\ViewModels\PageList;
use App\Libraries\CafeVariome\Factory\PageAdapterFactory;
use App\Libraries\CafeVariome\Factory\PageFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class Page extends CVUI_Controller
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
		$this->dbAdapter = (new PageAdapterFactory())->GetInstance();
    }

    public function Index()
    {
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function List()
    {
        $uidata = new UIData();
        $uidata->title = "Pages";

        $uidata->data['pages'] = $this->dbAdapter->SetModel(PageList::class)->ReadAll();

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = array(JS.'cafevariome/page.js', VENDOR.'datatables/datatables/media/js/jquery.dataTables.min.js');

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/List.php', $data);
    }

    public function Create()
    {
        $uidata = new UIData();
        $uidata->title = "Create Page";

        $uidata->javascript = [VENDOR.'tinymce/tinymce/tinymce.min.js', JS.'cafevariome/page.js'];

        // Validate form input
        $this->validation->setRules([
            'ptitle' => [
                'label'  => 'Page Title',
                'rules'  => 'required|alpha_dash|max_length[50]',
                'errors' => [
                    'required' => '{field} is required.',
                    'uniquename_check' => '{field} already exists.',
                    'max_length' => 'Maximum length is 50 characters.'
                ]
                ]
            ],
        [
            'pcontent' => [
                'label' => 'Page Content',
                'rules' => 'required|alpha_dash|max_length[65535]',
                'errors' => [
                    'required' => '{field} is required.',
                    'max_length' => 'Maximum length is 65,535 characters.'
                ]
            ]
        ]

        );

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
            $pageTitle = $this->request->getVar('ptitle');
            $pageContent = $this->request->getVar('pcontent');
            $user_id = $this->authenticator->GetUserId();

            try
			{
				$this->dbAdapter->Create((new PageFactory())->GetInstanceFromParameters($pageTitle, $pageContent, $user_id, true, true));
                $this->setStatusMessage("Page '$pageTitle' was created.", STATUS_SUCCESS);
            }
			catch (\Exception $ex)
			{
                $this->setStatusMessage("There was a problem creating '$pageTitle'.", STATUS_ERROR);
            }

            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else
		{
            $uidata->data['statusMessage'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $uidata->data['ptitle'] = array(
                'name' => 'ptitle',
                'id' => 'ptitle',
                'type' => 'text',
                'class' => 'form-control',
                'value' =>set_value('ptitle'),
            );

            $uidata->data['pcontent'] = array(
                'name' => 'pcontent',
                'id' => 'pcontent',
                'value' =>set_value('pcontent', '', false),
            );

            $uidata->data['validation'] = $this->validation;
        }

        $data = $this->wrapData($uidata);

        return view($this->viewDirectory . '/Create.php', $data);
    }

    public function Update(int $id)
    {
		$page = $this->dbAdapter->Read($id);

		if ($page->isNull())
		{
			$this->setStatusMessage("Page was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

        $uidata = new UIData();
        $uidata->title = "Edit Page";
		$uidata->data['id'] = $page->getID();

        $uidata->javascript = [VENDOR.'tinymce/tinymce/tinymce.min.js', JS.'cafevariome/page.js'];

        // Validate form input
        $this->validation->setRules([
            'ptitle' => [
                'label'  => 'Page Title',
                'rules'  => 'required|alpha_dash|max_length[50]',
                'errors' => [
                    'required' => '{field} is required.',
                    'uniquename_check' => '{field} already exists.',
                    'max_length' => 'Maximum length is 50 characters.'
                ]
                ]
            ],
        [
            'pcontent' => [
                'label' => 'Page Content',
                'rules' => 'required|alpha_dash|max_length[65535]',
                'errors' => [
                    'required' => '{field} is required.',
                    'max_length' => 'Maximum length is 65,535 characters.'
                ]
            ]
        ]);


        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run())
		{
            $pageTitle = $this->request->getVar('ptitle');
            $pageContent = $this->request->getVar('pcontent');
            $user_id = $this->authenticator->getUserId();

            try
			{
				$this->dbAdapter->Update($id, (new PageFactory())->GetInstanceFromParameters($pageTitle, $pageContent, $user_id, $page->active, $page->removable));
                $this->setStatusMessage("Page '$pageTitle' was updated.", STATUS_SUCCESS);
            }
			catch (\Exception $ex)
			{
                $this->setStatusMessage("There was a problem updating '$pageTitle'.", STATUS_ERROR);
            }
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else
		{
            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            if ($page != null)
			{
                $uidata->data['page_id'] = $page->getID();

                $uidata->data['ptitle'] = array(
                    'name' => 'ptitle',
                    'id' => 'ptitle',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => set_value('ptitle', $page->title),
                );

                $uidata->data['pcontent'] = array(
                    'name' => 'pcontent',
                    'id' => 'pcontent',
                    'value' =>set_value('pcontent', $page->content, false),
                );
            }
            else
			{
                $this->setStatusMessage("Page was not found.", STATUS_WARNING);
                return redirect()->to(base_url($this->controllerName.'/List'));
            }
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Update.php', $data);

    }

    public function Activate(int $id)
    {
		$page = $this->dbAdapter->Read($id);

		if ($page->isNull())
		{
			$this->setStatusMessage("Page was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$pageTitle = $page->title;

		if (!$page->active)
		{
			try
			{
				$this->dbAdapter->Activate($id);
				$this->setStatusMessage("Page '$pageTitle' was activated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem activating '$pageTitle'.: " . $ex->getMessage(), STATUS_ERROR);
			}
		}
		else
		{
			$this->setStatusMessage("Page '$pageTitle' is already active.", STATUS_INFO);
		}

        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function Deactivate(int $id)
    {
		$page = $this->dbAdapter->Read($id);

		if ($page->isNull())
		{
			$this->setStatusMessage("Page was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

		$pageTitle = $page->title;

		if ($page->active)
		{
			try
			{
				$this->dbAdapter->Deactivate($id);
				$this->setStatusMessage("Page '$pageTitle' was deactivated.", STATUS_SUCCESS);
			}
			catch (\Exception $ex)
			{
				$this->setStatusMessage("There was a problem deactivating '$pageTitle': " . $ex->getMessage(), STATUS_ERROR);
			}
		}
		else
		{
			$this->setStatusMessage("Page '$pageTitle' is already deactive.", STATUS_INFO);
		}

        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function Delete(int $id)
    {
		$page = $this->dbAdapter->Read($id);

		if ($page->isNull())
		{
			$this->setStatusMessage("Page was not found.", STATUS_ERROR);
			return redirect()->to(base_url($this->controllerName . '/List'));
		}

        $uidata = new UIData();
        $uidata->title = "Delete Page";
		$uidata->data['page'] = $page;

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
					$pageTitle = $page->title;
					if ($page->removable)
					{
						$this->dbAdapter->Delete($id);
						$this->setStatusMessage("Page '$pageTitle' was deleted.", STATUS_SUCCESS);
					}
					else
					{
						$this->setStatusMessage("Page '$pageTitle' is not removable.", STATUS_WARNING);
					}
                }
				catch (\Exception $ex)
				{
                    $this->setStatusMessage("There was a problem deleting the page.", STATUS_ERROR);
                }
            }
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else
		{
			$pageTitle = $page->title;

			if (!$page->removable)
			{
				$this->setStatusMessage("Page '$pageTitle' is not removable.", STATUS_WARNING);
				return redirect()->to(base_url($this->controllerName.'/List'));
			}


        }

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory.'/Delete', $data);
    }
}
