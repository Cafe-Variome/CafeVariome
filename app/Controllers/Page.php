<?php namespace App\Controllers;

/**
 * Name: Page.php
 * 
 * Created: 19/02/2020
 * 
 * @author Mehdi Mehtarizadeh
 */

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
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
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
        $uidata->title = "Pages";

        $pageModel = new \App\Models\Page();

        $pagesList = $pageModel->getPages();

        $uidata->data['pagesList'] = $pagesList;

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

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $pageTitle = $this->request->getVar('ptitle');
            $pageContent = $this->request->getVar('pcontent');
            $user_id = $this->authAdapter->getUserId();

            $pageData = ['Title' => $pageTitle, 'Content' => $pageContent, 'Author' => $user_id, 'Removable' => 1, 'Active' => 1];

            $pageModel = new \App\Models\Page();
            try {
                $pageModel->createPage($pageData);
                $this->setStatusMessage("Page '$pageTitle' was created.", STATUS_SUCCESS);
            } catch (\Exception $ex) {
                $this->setStatusMessage("There was a problem creating '$pageTitle'.", STATUS_ERROR);
            }
            return redirect()->to(base_url($this->controllerName.'/List'));

        }
        else {
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

    public function Update(int $page_id)
    {
        $uidata = new UIData();
        $uidata->title = "Edit Page";

        $pageModel = new \App\Models\Page();

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


        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {
            $pageTitle = $this->request->getVar('ptitle');
            $pageContent = $this->request->getVar('pcontent');
            $user_id = $this->authAdapter->getUserId();

            $updateData = ['Title' => $pageTitle, 'Content' => $pageContent, 'Author' => $user_id];

            try {
                $pageModel->updatePage($updateData, ['id' => $page_id]);
                $this->setStatusMessage("Page '$pageTitle' was updated.", STATUS_SUCCESS);
            } catch (\Exception $ex) {
                $this->setStatusMessage("There was a problem updating '$pageTitle'.", STATUS_ERROR);
            }
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else {
            $uidata->data['message'] = $this->validation->getErrors() ? $this->validation->listErrors($this->validationListTemplate) : $this->session->getFlashdata('message');

            $page = $pageModel->getPages(NULL, ['id' => $page_id]);

            if (count($page) == 1) {
                $uidata->data['page_id'] = $page[0]['id'];

                $uidata->data['ptitle'] = array(
                    'name' => 'ptitle',
                    'id' => 'ptitle',
                    'type' => 'text',
                    'class' => 'form-control',
                    'value' => set_value('ptitle', $page[0]['Title']),
                ); 

                $uidata->data['pcontent'] = array(
                    'name' => 'pcontent',
                    'id' => 'pcontent',
                    'value' =>set_value('pcontent', $page[0]['Content'], false),
                );
            }
            else {
                $this->setStatusMessage("Page was not found.", STATUS_WARNING);
                return redirect()->to(base_url($this->controllerName.'/List'));
            }
        }



        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Update.php', $data);

    }
    
    public function Activate(int $page_id)
    {
        $pageModel = new \App\Models\Page();
        $page = $pageModel->getPages('Title, Active', ['id' => $page_id]);

        if (count($page) == 1) {
            $pageTitle = $page[0]['Title'];

            if (!$page[0]['Active']) {
                $updateData = ['Active' => 1];
                try {
                    $pageModel->updatePage($updateData, ['id' => $page_id]);
                    $this->setStatusMessage("Page '$pageTitle' was activated.", STATUS_SUCCESS);
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem activating '$pageTitle'.", STATUS_ERROR);
                }
            }
            else {
                $this->setStatusMessage("Page '$pageTitle' is already active.", STATUS_INFO);
            }
        }
        else {
            $this->setStatusMessage("Page was not found.", STATUS_ERROR);
        }
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function Deactivate(int $page_id)
    {
        $pageModel = new \App\Models\Page();
        $page = $pageModel->getPages('Title, Active', ['id' => $page_id]);

        if (count($page) == 1) {
            $pageTitle = $page[0]['Title'];
            
            if ($page[0]['Active']) {
                $updateData = ['Active' => 0];
                try {
                    $pageModel->updatePage($updateData, ['id' => $page_id]);
                    $this->setStatusMessage("Page '$pageTitle' was deactivated.", STATUS_SUCCESS);
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem deactivating '$pageTitle'.", STATUS_ERROR);
                }
            }
            else {
                $this->setStatusMessage("Page '$pageTitle' is already deactive.", STATUS_INFO);
            }
        }
        else {
            $this->setStatusMessage("Page was not found.", STATUS_ERROR);
        }
        return redirect()->to(base_url($this->controllerName.'/List'));
    }

    public function Delete(int $page_id)
    {
        $uidata = new UIData();
        $uidata->title = "Delete Page";

        $pageModel = new \App\Models\Page();

        $this->validation->setRules([
            'confirm' => [
                'label'  => 'confirmation',
                'rules'  => 'required',
                'errors' => [
                    'required' => '{field} is required.'
                ]
            ],
            
            'page_id' => [
                'label'  => 'Page Id',
                'rules'  => 'required|alpha_dash',
                'errors' => [
                    'required' => '{field} is required.',
                    'alpha_dash' => '{field} must only contain alpha-numeric characters, underscores, or dashes.'
                ]
            ]            
        ]);

        if ($this->request->getPost() && $this->validation->withRequest($this->request)->run()) {      
            $pageId = $this->request->getVar('page_id'); 
            $confirm = $this->request->getVar('confirm');

            if ($confirm == 'yes') {
                try {
                    $page = $pageModel->getPages('Title, Removable', ['id' => $page_id]);
                    if (count($page) == 1) {
                        $pageTitle = $page[0]['Title'];
                        if ($page[0]['Removable']) {
                            $pageModel->deletePage($page_id);
                            $this->setStatusMessage("Page '$pageTitle' was deleted.", STATUS_SUCCESS);
                        }
                        else {
                            $this->setStatusMessage("Page '$pageTitle' is not removable.", STATUS_WARNING);
                        }
                    }
                    else{
                        $this->setStatusMessage("Page does not exist.", STATUS_ERROR);
                    }
                } catch (\Exception $ex) {
                    $this->setStatusMessage("There was a problem deleting the page.", STATUS_ERROR);
                }
            }
            return redirect()->to(base_url($this->controllerName.'/List'));
        }
        else {
            $page = $pageModel->getPages('Title, Removable', ['id' => $page_id]);
            if (count($page) == 1) {
                $pageTitle = $page[0]['Title'];
    
                if (!$page[0]['Removable']) {
                    $this->setStatusMessage("Page '$pageTitle' is not removable.", STATUS_WARNING);
                    return redirect()->to(base_url($this->controllerName.'/List'));
                }
                else {
                    $uidata->data['page_id'] = $page_id;
                    $uidata->data['pageTitle'] = $pageTitle;
                 }
            }
            else {
                $this->setStatusMessage("Page was not found.", STATUS_ERROR);
                return redirect()->to(base_url($this->controllerName.'/List'));
            }
            $data = $this->wrapData($uidata);
            return view($this->viewDirectory.'/Delete', $data);
        }
    }
}
 