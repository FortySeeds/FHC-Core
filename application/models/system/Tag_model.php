<?php
class Tag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_tag';
		$this->pk = 'tag';
	}
}
