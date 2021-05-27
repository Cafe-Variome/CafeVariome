<?php namespace App\Controllers;

use App\Models\UIData;
use App\Models\Page;

class Home extends CVUI_Controller
{

	public function Index(int $page_id = 0)
	{
		$uidata = new UIData();
		$uidata->title = 'Home';

		$pageModel = new Page();
		$uidata->data['pageContent'] = '';
		if (is_numeric($page_id)) {
			($page_id == 0) ? $page_id = 1 : $page_id = $page_id;
			$page = $pageModel->getPages(Null, ['id' => $page_id]);

			if (count($page) == 1) {
				$uidata->title = $page[0]['Title'];
				$uidata->data['pageContent'] = $page[0]['Content'];
			}
		}

		$data = $this->wrapData($uidata);
		return view($this->viewDirectory. '/Index', $data);
	}
}
