<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Reihungstest extends Auth_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'index' => 'basis/mitarbeiter:r'
			)
		);
	}
	
	// -----------------------------------------------------------------------------------------------------------------
	// Public methods
	
	public function index()
	{
		$this->load->view('system/reihungstests/reihungstestOverview.php');
	}
}

