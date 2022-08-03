<?php namespace App\Controllers;

use App\Libraries\CafeVariome\CafeVariome;

/**
 * UserInterfaceAPI.php
 *
 * Created 05/04/2022
 *
 * @author Mehdi Mehtraizadeh
 *
 * This controller contains endpoints that generate UI JavaScript code.
 */

class UserInterfaceAPI extends BaseController
{
	private $setting;

	public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	{
		parent::initController($request, $response, $logger);
		$this->setting = CafeVariome::Settings();
	}

	public function getUIConstants()
	{
		$this->response->setHeader("Content-Type", "text/javascript");

		$script = '';

		$baseURL = base_url(); // Load base URL
		$hpoAutocompleteURL = $this->setting->getHPOAutoCompleteURL();
		$orphaAutocompleteURL = $this->setting->getORPHAAutoCompleteURL();
		$snomedAutocompleteURL = $this->setting->getSNOMEDAutoCompleteURL();
		$geneAutocompleteURL = $this->setting->getGeneAutoCompleteURL();

		$script .= "var baseurl = '$baseURL/';";
		$script .= "var hpo_autocomplete_url = '$hpoAutocompleteURL';";
		$script .= "var orpha_autocomplete_url = '$orphaAutocompleteURL';";
		$script .= "var snomed_autocomplete_url = '$snomedAutocompleteURL';";
		$script .= "var gene_autocomplete_url = '$geneAutocompleteURL';";

		return $script;

	}

}
