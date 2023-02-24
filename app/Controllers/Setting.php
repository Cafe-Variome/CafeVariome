<?php namespace App\Controllers;

/**
 * Setting.php
 * Created 05/05/2021
 *
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 */

use App\Libraries\CafeVariome\Factory\SettingAdapterFactory;
use App\Libraries\CafeVariome\Factory\SettingFactory;
use App\Models\UIData;
use CodeIgniter\Config\Services;

class Setting extends CVUIController
{

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

		$this->session = Services::session();
		$this->dbAdapter = (new SettingAdapterFactory())->GetInstance()->Load();
	}

    public function Discovery()
    {
        $uidata = new UIData();
        $uidata->title = "Discovery Settings";
        $uidata->stickyFooter = false;

        $settings =  $this->dbAdapter->ReadByGroup('discovery');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost())
		{
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Discovery'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Discovery', $data);
    }

    public function Endpoint()
    {
        $uidata = new UIData();
        $uidata->title = "Endpoint Settings";
        $uidata->stickyFooter = false;

        $settings = $this->dbAdapter->ReadByGroup('endpoint');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost())
		{
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Endpoint'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Endpoint', $data);
    }

    public function Main()
    {
        $uidata = new UIData();
        $uidata->title = "Main System Settings";
        $uidata->stickyFooter = false;

        $settings =  $this->dbAdapter->ReadByGroup('main');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost())
		{
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Main'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Main', $data);
    }

    public function Elasticsearch()
    {
        $uidata = new UIData();
        $uidata->title = "Elastic Search Settings";
        $uidata->stickyFooter = false;

        $settings = $this->dbAdapter->ReadByGroup('elasticsearch');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost()) {
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Elasticsearch'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Elasticsearch', $data);
    }

    public function Neo4J()
    {
        $uidata = new UIData();
        $uidata->title = "Neo4J Settings";
        $uidata->stickyFooter = false;

        $settings = $this->dbAdapter->ReadByGroup('neo4j');
        $uidata->data['settings'] = $settings;

        if ($this->request->getPost())
		{
            $this->processPost($settings);

            return redirect()->to(base_url($this->controllerName.'/Neo4J'));
        }

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory. '/Neo4J', $data);
    }

    private function processPost(array $settings)
    {
		$currentSettings = $this->dbAdapter->ReadAll();

        $errorFlag = false;
        foreach ($settings as $s)
		{
            $settingName = $s->name;
            $settingKey = $s->key;
            $settingVal = trim($this->request->getVar($settingKey));
            if ($settingVal != $currentSettings[$settingKey]->value)
			{
                if ($settingKey == 'installation_key')
				{
                    $settingVal = trim($settingVal);
                }
                if ($settingKey == 'auth_server')
				{
                    $settingVal = trim($settingVal);
                    $valLen = strlen($settingVal);
                    if(substr($settingVal, $valLen-1, $valLen) != '/')
					{
                        $settingVal = $settingVal . '/';
                    }
                }
                if ($currentSettings[$settingKey]->value == 'on' || $currentSettings[$settingKey]->value == 'off')
				{
                    $settingVal = $settingVal == null ? 'off' : 'on';
                }
                try
				{
					$this->dbAdapter->Update(
						$s->getID(),
						(new SettingFactory())->GetInstanceFromParameters($s->key, $s->name, $settingVal, $s->group, $s->info)
					);
                }
				catch (\Exception $ex)
				{
                    $errorFlag = true;
                    $this->setStatusMessage("There was a problem updating '$settingName'.", STATUS_ERROR);
                }
            }
        }

        if (!$errorFlag) {
            $this->setStatusMessage("Settings were updated.", STATUS_SUCCESS);
        }
    }

}
