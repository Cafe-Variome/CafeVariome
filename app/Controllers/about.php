<?php namespace App\Controllers;
use App\Models\UIData;
//if ( ! defined('BASEPATH')) exit('No direct script access allowed!');

class About extends CVUI_Controller {
	
	public function index(){
		$udata = new UIData();
		$udata->title = "Home";
		$data = $this->wrapData($udata);

		return view('pages/share', $data);
	}

	function api(){
		$this->title = "Cafe Variome - API";
		$this->_render('pages/api');
	}

	function cafevariome(){
		$this->title = "Cafe Variome - About";
		$this->_render('pages/cafevariome');
	}
	
	function contact(){
		$this->title = "Cafe Variome - Contact";		
		$this->form_validation->set_rules('fullname', 'Full Name', 'required|xss_clean');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		
		if ($this->form_validation->run() == FALSE) {
			$this->_render('pages/contact');
		}
		else {
			$fullname = $_POST['fullname'];
//			$institute = $_POST['institute'];
			$date = date('d-m-Y H:i:s');
			$ip = getRealIpAddr();
			$email = $_POST['email'];
			$data = array(
				'fullname' => $fullname,
				'email' => $email,
				'date' => $date,
				'ip' => $ip
			);
			$this->load->model('general_model');
			$this->general_model->insertMailingListData($data);
			// Should clear the form data
			$this->session->set_flashdata('message', 'Mailing List');
			$this->data['success_message'] = true;
			$this->_render('pages/contact');
//			redirect(current_url());
		}	
//		$this->_render('pages/contact');
	}
	
	function disclaimer() {
		$this->title = "Cafe Variome - Disclaimer";
		$this->_render('pages/disclaimer');
	}
	
	function faq(){
		$this->title = "Cafe Variome - Frequently Asked Questions";
		$this->_render('pages/faq');
	}
	
	function features(){
		$this->title = "Cafe Variome - Features";
		$this->_render('pages/features');
	}
	
	function get(){
		$this->title = "Cafe Variome - Get";
		$this->_render('pages/get');
	}

	function gensearch(){
		$this->title = "Cafe Variome - Gensearch";
		$this->_render('pages/gensearch');
	}
	
	function inabox(){
		$this->title = "Cafe Variome in-a-box";
		
		$this->form_validation->set_rules('fullname', 'Full Name', 'required|xss_clean');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('institute', 'Institute', 'required|xss_clean');
		$this->form_validation->set_rules('description', 'Conditions', 'xss_clean');
//		$this->form_validation->set_rules('conditions', 'Conditions', 'required');
		
		if ($this->form_validation->run() == FALSE) {
//			$this->load->view('myform');
			$this->_render('pages/inabox');
		}
		else {
			$this->load->helper('download');
			$fullname = $_POST['fullname'];
			$institute = $_POST['institute'];
			$description = $_POST['description'];
			$date = date('d-m-Y H:i:s');
			$ip = getRealIpAddr();
			$email = $_POST['email'];
			$data = array(
				'fullname' => $fullname,
				'institute' => $institute,
				'email' => $email,
				'date' => $date,
				'description' => $description,
				'ip' => $ip
			);
			$this->load->model('general_model');
			$this->general_model->insertInABoxData($data);
//			$data = 'Here is some text!';
//			$name = 'cafevariome.txt';
//			$data = file_get_contents("/Library/WebServer/Documents/cafevariome/downloads/cafevariome_ci_v6.tar.gz"); // Read the file's contents
//			$name = 'cafevariome_ci_v6.tar.gz';
//			force_download($name, $data);
			// Should clear the form data
//			$this->session->set_flashdata('message', 'In-a-box success');
//			redirect(current_url());
			cafevariomeEmail($this->config->item('email'), "Cafe Variome Admin", $this->config->item('email'), "Cafe Variome Install Enquiry", "New registration of interest $fullname $institute $email $description");
			redirect('about/inabox_success', 'refresh');
		}
	}
	
	function inabox_success(){
//		$this->title = "Cafe Variome - Gensearch";
		$this->_render('pages/inabox-success');
	}

	function scenarios(){
		$this->title = "Cafe Variome - Sharing Scenarios";
		$this->_render('pages/scenarios');
	}
	
	function varioml(){
		$this->title = "Cafe Variome - VarioML";
		$this->_render('pages/varioml');
	}
	

	
	
}