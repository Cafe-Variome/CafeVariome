<?php namespace App\Controllers;

use App\Models\UIData;
use App\Models\Page;

class Home extends CVUI_Controller
{

	public function Index(int $page_id = 0)
	{
		$uidata = new UIData();
		$uidata->title = 'Home';
		$uidata->stickyFooter = false;
		$pageModel = new Page();
		$uidata->data['pageContent'] = '';
		if (is_numeric($page_id)) {
			($page_id == 0) ? $page_id = 1 : $page_id = $page_id;
			$page = $pageModel->getActivePage($page_id);

			if ($page != null)
			{
				$uidata->title = $page['Title'];
				$uidata->data['pageContent'] = $page['Content'];
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
		$uidata->javascript = [JS . 'cafevariome/portal.js'];

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory. '/Portal', $data);
	}
}
