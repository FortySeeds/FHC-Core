<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 */
class ProfilUpdate extends Auth_Controller
{

	public function __construct(){
		parent::__construct([
			'index' => ['student/anrechnung_beantragen:r', 'user:r'], // TODO(chris): permissions?
			'getAllRequests' => ['student/anrechnung_beantragen:r', 'user:r'],
			'acceptProfilRequest'=>['user:r'],
			'denyProfilRequest'=>['user:r'],

		]);
		//? put the uid and pid inside the controller to reuse in controller
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

		$this->load->model('person/Profil_change_model','ProfilChangeModel');
	}


	public function index(){
		$this->load->view('Cis/ProfilUpdate');
	}

	public function getAllRequests(){
		$res = $this->ProfilChangeModel->getProfilUpdate();
		$res = hasData($res)? getData($res) : null;
		echo json_encode($res);
	}

	public function acceptProfilRequest(){
		$_POST = json_decode($this->input->raw_input_stream,true);

		$id = $this->input->post('requestID',true);
		
		if(isset($id)){
			$res =$this->ProfilChangeModel->update([$id], ["status"=>"accepted","status_timestamp"=>"NOW()"]);
			echo json_encode($res);
		}

	}

	public function denyProfilRequest(){
		$_POST = json_decode($this->input->raw_input_stream,true);

		$id = $this->input->post('requestID',true);
		
		if(isset($id)){
			var_dump($id);
			//! instead of deleting the rejected profil update, the status of the db entry is set to rejected
			//$res = $this->ProfilChangeModel->delete([$id]);


			$res = $this->ProfilChangeModel->update([$id],["status"=>"rejected","status_timestamp"=>"NOW()"]);
			echo json_encode($res);
			
		}
		

	}
}