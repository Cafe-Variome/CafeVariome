<?php namespace App\Controllers;

use App\Libraries\CafeVariome\Factory\PageAdapterFactory;
use App\Models\UIData;

class Home extends CVUI_Controller
{

	public function Index(int $page_id = 0)
	{
		$uidata = new UIData();
		$uidata->title = 'Home';
		$uidata->stickyFooter = false;
		$pageAdapter = (new PageAdapterFactory())->GetInstance();
		$uidata->data['pageContent'] = '';
		if (is_numeric($page_id))
		{
			($page_id == 0) ? $page_id = 1 : $page_id = $page_id;
			$page = $pageAdapter->ReadActive($page_id);

			if (!$page->isNull())
			{
				$uidata->title = $page->title;
				$uidata->data['pageContent'] = htmlspecialchars_decode($page->content, ENT_QUOTES | ENT_SUBSTITUTE);
			}
			else
			{
				return redirect()->to(base_url());
			}
		}

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory. '/Index', $data);
	}

	public function Portal()
	{
		$uidata = new UIData();
		$uidata->title = 'Portal';
		$uidata->stickyFooter = false;
		$uidata->IncludeJavaScript(JS . 'cafevariome/portal.js');

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory. '/Portal', $data);
	}
}
