<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Cis4 extends FHC_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// Loads Libraries
		$this->load->library('AuthLib');
		$this->load->library('PermissionLib');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		$this->load->model('person/Person_model','PersonModel');
		$begrüsung = $this->PersonModel->getFirstName(getAuthUID());
		if(isError($begrüsung))
		{
			show_error("name couldn't be loaded for username ".getAuthUID());
		}
		$begrüsung = getData($begrüsung);
		$this->load->view('CisVue/Dashboard.php',["name"=> $begrüsung]);
	}
}
