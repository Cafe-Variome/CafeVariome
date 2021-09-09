<?php namespace App\Controllers;


/**
 * Name: Upload.php
 * Created: 31/07/2019
 *
 * @author Gregory Warren
 * @author Mehdi Mehtarizadeh
 *
 */

use App\Models\UIData;
use App\Models\Source;
use App\Models\Pipeline;
use CodeIgniter\Config\Services;


 class Upload extends CVUI_Controller{

    protected $sourceModel;

    protected $uploadModel;

    /**
	 * Constructor
	 *
	 */
    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger){
        parent::setProtected(true);
        parent::setIsAdmin(true);
        parent::initController($request, $response, $logger);

        $this->sourceModel = new Source($this->db);
        $this->uploadModel = new \App\Models\Upload($this->db);
    }

    /**
     * Json - render the upload json view
     *
     * @param string $source - The source name we will be uploading to
     * @return N/A
     */
    public function Phenopacket($source_id) {
        // Check if user is logged in and admin
        // Since this is a shared function for curators and admin check that the curator is a curator for this source
        $user_id = $this->authAdapter->getUserId();
        //$source_id = $this->sourceModel->getSourceIDByName($source);

        // data for hidden input for source
        $uidata = new UIData();
        $uidata->title = "Upload Phenopacket Files";
        $uidata->data['user_id'] = $user_id;
        $uidata->data['source_id'] = $source_id;
        $uidata->data['source_name'] = $this->sourceModel->getSourceNameByID($source_id);

        $piplineModel = new Pipeline();
        $pipelines = $piplineModel->getPipelines();

        $uidata->data['pipelines'] = $pipelines;

        // preparing webpage
        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js',JS.'cafevariome/phenopacket.js',JS.'cafevariome/status.js'];

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Phenopacket', $data);
    }

    /**
     * VCF - Render the Stand-Alone view to upload VCF's
     *
     */
    public function VCF($source_id) {

        $uidata = new UIData();
        $uidata->title = "Upload VCF";
        // Since this is a shared function for curators and admin check that the curator is a curator for this source
        $user_id = $this->authAdapter->getUserId();

        $uidata->data['source_name'] = $this->sourceModel->getSourceNameByID($source_id);
        $uidata->data['source_id'] = $source_id;
        $uidata->data['user_id'] = $user_id;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js', JS.'cafevariome/status.js', JS.'cafevariome/vcf.js'];

		$piplineModel = new Pipeline();
		$pipelines = $piplineModel->getPipelines();
		$uidata->data['pipelines'] = $pipelines;

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/VCF', $data);
    }

    function Spreadsheet(int $source_id) {

        $uidata = new UIData();
        $uidata->title = "Upload Spreadsheet Files";

        // Since this is a shared function for curators and admin check that the curator is a curator for this source
        $user_id = $this->authAdapter->getUserID();

        $uidata->data['source_name'] = $this->sourceModel->getSourceNameByID($source_id);
        $uidata->data['user_id'] = $user_id;
        $uidata->data['source_id'] = $source_id;

        $uidata->data['uploadedFiles'] = $this->sourceModel->getSourceStatus($source_id);
        $uidata->data['uploadedFilesErrors'] = $this->sourceModel->getErrorForSource($source_id);

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js', JS.'cafevariome/status.js', JS.'cafevariome/spreadsheet.js'];

        $piplineModel = new Pipeline();
        $pipelines = $piplineModel->getPipelines();

        $uidata->data['pipelines'] = $pipelines;

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Spreadsheet', $data);
    }

    public function Import(int $source_id)
    {
        $uidata = new UIData();
        $uidata->title = "Import Files";
        $user_id = $this->authAdapter->getUserId();

        $uidata->data['source_name'] = $this->sourceModel->getSourceNameByID($source_id);
        $uidata->data['user_id'] = $user_id;
        $uidata->data['source_id'] = $source_id;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');
        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js', JS.'cafevariome/import.js', JS.'cafevariome/status.js'];

        $piplineModel = new Pipeline();
        $pipelines = $piplineModel->getPipelines();

        $uidata->data['pipelines'] = $pipelines;

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Import', $data);
    }

    public function Universal(int $source_id) {

        $uidata = new UIData();
        $uidata->title = "Universal Upload";
        // Since this is a shared function for curators and admin check that the curator is a curator for this source
        $user_id = $this->authAdapter->getUserId();

        $uidata->data['source_name'] = $this->sourceModel->getSourceNameByID($source_id);
        $uidata->data['user_id'] = $user_id;
        $uidata->data['source_id'] = $source_id;

        $uidata->css = array(VENDOR.'datatables/datatables/media/css/jquery.dataTables.min.css');

        $uidata->javascript = [VENDOR.'datatables/datatables/media/js/jquery.dataTables.js',JS. 'bootstrap-notify.js',JS.'cafevariome/univ.js',JS.'cafevariome/status.js'];


        $uidata->data['configs'] = array_diff(scandir(FCPATH. UPLOAD . "/settings/"), array('..', '.'));;

        $data = $this->wrapData($uidata);
        return view($this->viewDirectory . '/Universal', $data);
    }

 }
