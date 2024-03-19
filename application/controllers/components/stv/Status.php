<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Status extends FHC_Controller
{
	public function __construct()
	{
		parent::__construct();

		// Load Libraries
		$this->load->library('AuthLib');
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('PermissionLib');

		// Load language phrases
		/*		$this->loadPhrases([
					'ui'
				]);*/
	}

	public function getHistoryPrestudent($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$result = $this->PrestudentModel->getHistoryPrestudent($prestudent_id);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson(getData($result) ?: []);
	}

	public function getStatusgruende()
	{
		$this->load->model('crm/Statusgrund_model', 'StatusgrundModel');

		$result = $this->StatusgrundModel->load();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function getLastBismeldestichtag()
	{
		$this->load->model('codex/Bismeldestichtag_model', 'BismeldestichtagModel');

		$result = $this->BismeldestichtagModel->getLastReachedMeldestichtag();
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
		}
		$this->outputJson($result);
	}

	public function isLastStatus($prestudent_id)
	{
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$result = $this->PrestudentstatusModel->checkIfLastStatusEntry($prestudent_id);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		if($result->retval == "1")
		{
				return $this->outputJson($result->retval);
		}
		$this->outputJson($result);
	}

	public function addNewStatus($prestudent_id)
	{
		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->PrestudentModel->addJoin('public.tbl_person p', 'ON (p.person_id = public.tbl_prestudent.person_id)');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		//Variablen für Statuscheck
		$stg = $result->studiengang_kz;
		$reihungstest_angetreten = $result->reihungstestangetreten;
		$name = trim($result->vorname . " ". $result->nachname);
		$zgv_code = $result->zgv_code;

		$isStudent = false;

		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg) )
		{
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$uid = getAuthUID();
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$datum = $this->input->post('datum');
		$bestaetigtam = $this->input->post('bestaetigtam');
		$bewerbung_abgeschicktamum = $this->input->post('bewerbung_abgeschicktamum');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$anmerkung = $this->input->post('anmerkung');
		$statusgrund_id = $this->input->post('statusgrund_id');
		$rt_stufe = $this->input->post('rt_stufe');
		$bestaetigtvon = $uid;

		//GET lastStatus
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		elseif(!hasData($result))
		{
			$lastStatusData = [];
		}
		else
			$lastStatusData = current(getData($result));

		//Different handling depending on newStatus
		//Todo(manu) check if these checks makes sense?
		/*		if($status_kurzbz == 'Absolvent' || $status_kurzbz == 'Diplomand')
				{
					//$studiensemester = $semester_aktuell; //TODO(Manu) oder ist hier defaultsemester gemeint?
					$ausbildungssemester = $lastStatusData->ausbildungssemester;
				}*/

		/*		if($status_kurzbz != 'Student')
				{
					$ausbildungssemester = $lastStatusData->ausbildungssemester;
				}*/

		//check if Rolle already exists
		$result = $this->PrestudentstatusModel->checkIfExistingPrestudentRolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if($result->retval == '1')
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result->code);
		}


		//Check Reihungstest
		if(REIHUNGSTEST_CHECK)
		{
			if($status_kurzbz=='Bewerber' && !$reihungstest_angetreten)
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($name . ": Um einen Interessenten zum Bewerber zu machen, muss die Person das Reihungstestverfahren abgeschlossen haben");
			}
		}

		//Check ZGV
		if(!defined("ZGV_CHECK") || ZGV_CHECK)
		{
			if($status_kurzbz=='Bewerber' && $zgv_code=='')
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($name . ": Um einen Interessenten zum Bewerber zu machen, muss die Zugangsvoraussetzung eingetragen sein.");
			}
		}

		//Check ZGV-Master
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$result = $this->StudiengangModel->load([
			'studiengang_kz'=> $stg
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));
		$typ = $result->typ;

		if(!defined("ZGV_CHECK") || ZGV_CHECK)
		{
			if($status_kurzbz=='Bewerber' && $zgv_code=='' && $typ=='m')
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($name . ": Um einen Interessenten zum Bewerber zu machen, muss die Zugangsvoraussetzung Master eingetragen sein.");
			}
		}

		//check if bewerberstatus exists
		if($status_kurzbz == 'Aufgenommener' || $status_kurzbz == 'Wartender')
		{
			//TODO(manu) erst später für multiactions releveant
			//FAS: Aufnahme ist möglich: Beispiel prestudent_id = 129629

			$result = $this->PrestudentstatusModel->checkIfExistingBewerberstatus($prestudent_id, $name);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if($result->retval == "0")
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
		}

		//check if studentrolle already exists
		//TODO(manu) eigener Code für Diplomand?
		if($status_kurzbz == 'Student' || $status_kurzbz == 'Diplomand' )
		{
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfExistingStudentRolle($prestudent_id);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if($result->retval == "0")
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
			if($result->retval != "0")
			{
				$isStudent = true;
			}
		}

		$isBerechtigtNoStudstatusCheck =  $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung');
		/*		var_dump(isBerechtigtNoStudstatusCheck);

				$basis =  $this->permissionlib->isBerechtigt('basis/prestudent');
				var_dump($basis);*/
		if(!$isBerechtigtNoStudstatusCheck)
		{
			//Block STATUSCHECKS
			$new_status_datum = isset($datum) ? $datum  : date('Y-m-d');
			$result = $this->PrestudentstatusModel->checkDatumNewStatus($new_status_datum);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}

			$result = $this->PrestudentstatusModel->checkIfValidStatusHistory($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $new_status_datum, $ausbildungssemester);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}

			//check if Bismeldestichtag erreicht
			$this->load->model('codex/Bismeldestichtag_model', 'BismeldestichtagModel');
			$result = $this->BismeldestichtagModel->checkIfMeldestichtagErreicht($new_status_datum);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if($result->retval == "1")
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result->code));
			}
		}


		// Start DB transaction
		$this->db->trans_begin(); // Beginnen der Transaktion

		$result = $this->PrestudentstatusModel->insert(
			[
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'bewerbung_abgeschicktamum' => $bewerbung_abgeschicktamum,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'studienplan_id' => $lastStatusData->studienplan_id,
				'ausbildungssemester' => $ausbildungssemester,
				'anmerkung' => $anmerkung,
				'statusgrund_id' => $statusgrund_id,
				'insertvon' => $uid,
				'insertamum' => date('c'),
				'bestaetigtam' => $bestaetigtam,
				'bestaetigtvon' => $bestaetigtvon,
				'datum' => $datum,
				'rt_stufe' => $rt_stufe
			]
		);

		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}



		if($isStudent)
		{
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfUid($prestudent_id);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			$student_uid = $result->retval;

			//load student
			$result = $this->StudentModel->loadWhere(
				array(
					'student_uid' => $student_uid
				)
			);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson($result);
			}
			if (!hasData($result)) {
				$this->outputJson($result);
			}

			$studentData = current(getData($result));
			$verband = $studentData->verband == '' ? '' : $studentData->verband;
			$gruppe = $studentData->gruppe == '' ? '' : $studentData->gruppe;
			$studiengang_kz = $studentData->studiengang_kz;

			//Handle Abbrecher and Unterbrecher
			//TODO (manu) implement later with multiactions
			// not active yet: works only for "status ändern" in FAS
			/*			if($status_kurzbz == 'Abbrecher' || $status_kurzbz == 'Unterbrecher')
						{
							$ausbildungssemester = 0;
							$gruppe = '';
							$verband = $status_kurzbz == 'Abbrecher' ? 'A' : 'B';
						}*/

			//process studentlehrverband
			$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
			$result = $this->StudentlehrverbandModel->processStudentlehrverband($student_uid, $studiengang_kz, $ausbildungssemester, $verband, $gruppe, $studiensemester_kurzbz);
			if ($this->db->trans_status() === false || isError($result))
			{
				$this->db->trans_rollback();
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}

			//Student updaten (fuer Abbrecher und Unterbrecher)
			//not active yet: works only for "status ändern" in FAS
			//Later for multiactions

			/*
			if($status_kurzbz == 'Abbrecher' || $status_kurzbz == 'Unterbrecher')
			{
				$result = $this->StudentModel->update(
								[
									'student_uid' => $student_uid
								],
								[
									'studiengang_kz' => $studiengang_kz,
									'semester' => $ausbildungssemester,
									'verband' => $verband,
									'gruppe' => $gruppe,
									'updateamum' => date('c'),
									'updatevon' => $uid
							]);
							if ($this->db->trans_status() === false || isError($result))
							{
								$this->db->trans_rollback();
								$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
								return $this->outputJson($result->code);
							}
				}
			*/
		}

		$this->db->trans_commit();

		return $this->outputJsonSuccess(true);
	}



	public function loadStatus()
	{
		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');

		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		else
		{
			$this->outputJsonSuccess(current(getData($result)));
		}
	}

	public function deleteStatus()
	{
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);

		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$deletePrestudent = false;

		$isBerechtigtAdmin = $this->permissionlib->isBerechtigt('admin', null, 'suid');
		$isBerechtigtNoStudstatusCheck = $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung', null, 'suid');


		if($status_kurzbz=="Student" && !$isBerechtigtAdmin && !$isBerechtigtNoStudstatusCheck)
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson("Studentenrolle kann nur durch den Administrator geloescht werden");
		}

		//check if last status
		$result = $this->PrestudentstatusModel->checkIfLastStatusEntry($prestudent_id);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		if($result->retval == "1")
		{
			//Berechtigungen nach Check prüfen!
			if(!$isBerechtigtAdmin && !$isBerechtigtNoStudstatusCheck)
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
			else
			{
				$deletePrestudent = true;
			}
		}

		// Start DB transaction
		$this->db->trans_begin();

		//load rolle für LOG sqlundo
		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		if (!hasData($result)) {
			return $this->outputJson($result);
		}

		//Data of current Rolle
		$statusData = current(getData($result));
		//var_dump($statusData);

		$datum = $statusData->datum == '' ? 'null' : $statusData->datum;
		$insertamum = $statusData->insertamum == '' ? 'null' : $statusData->insertamum;
		$insertvon = $statusData->insertvon == '' ? 'null' : $statusData->insertvon;
		$updateamum = $statusData->updateamum == '' ? 'null' : $statusData->updateamum;
		$updatevon = $statusData->updatevon == '' ? 'null' : $statusData->updatevon;
		$ext_id = $statusData->ext_id == '' ? 'null' : $statusData->ext_id;
		$orgform_kurzbz = $statusData->orgform_kurzbz == '' ? 'null' : $statusData->orgform_kurzbz;
		$bestaetigtam = $statusData->bestaetigtam == '' ? 'null' : $statusData->bestaetigtam;
		$bestaetigtvon = $statusData->bestaetigtvon == '' ? 'null' : $statusData->bestaetigtvon;
		$anmerkung = $statusData->anmerkung == '' ? 'null' : $statusData->anmerkung;
		$bewerbung_abgeschicktamum = $statusData->bewerbung_abgeschicktamum == '' ? 'null' : $statusData->bewerbung_abgeschicktamum;
		$studienplan_id = $statusData->studienplan_id == '' ? 'null' : $statusData->studienplan_id;
		$rt_stufe = $statusData->rt_stufe == '' ? 'null' : $statusData->rt_stufe;
		$statusgrund_id = $statusData->statusgrund_id == '' ? 'null' : $statusData->statusgrund_id;

		$quotes_datum = $datum == "null" ? " " : "'";
		$quotes_insertamum = $insertamum == "null" ? " " : "'";
		$quotes_insertvon = $insertvon == "null" ? " " : "'";
		$quotes_updateamum = $updateamum == "null" ? " " : "'";
		$quotes_updatevon = $updatevon == "null" ? " " : "'";
		$quotes_ext_id = $ext_id == "null" ? " " : "'";
		$quotes_orgform_kurzbz = $orgform_kurzbz == "null" ? " " : "'";
		$quotes_bestaetigtam = $bestaetigtam == "null" ? " " : "'";
		$quotes_bestaetigtvon = $bestaetigtvon == "null" ? " " : "'";
		$quotes_anmerkung = $anmerkung == "null" ? " " : "'";
		$quotes_bewerbung_abgeschicktamum = $bewerbung_abgeschicktamum == "null" ? " " : "'";
		$quotes_studienplan_id = $studienplan_id == "null" ? " " : "'";
		$quotes_rt_stufe = $rt_stufe == "null" ? " " : "'";
		$quotes_statusgrund_id = $statusgrund_id == "null" ? " " : "'";

$sqlundo =
	"
	INSERT INTO public.tbl_prestudentstatus(prestudent_id, status_kurzbz, studiensemester_kurzbz, ausbildungssemester, datum, insertamum, insertvon, updateamum, updatevon, ext_id, orgform_kurzbz, bestaetigtam, bestaetigtvon, anmerkung, bewerbung_abgeschicktamum, studienplan_id,  rt_stufe, statusgrund_id) 
	VALUES('" . $prestudent_id . "','" . $status_kurzbz . "','" . $studiensemester_kurzbz . "','" . $ausbildungssemester . "',"
	. $quotes_datum . $datum . $quotes_datum . ","
	. $quotes_insertamum . $insertamum . $quotes_insertamum . ","
	. $quotes_insertvon . $insertvon . $quotes_insertvon . ","
	. $quotes_updateamum . $updateamum . $quotes_updateamum . ","
	. $quotes_updatevon . $updatevon . $quotes_updatevon . ","
	. $quotes_ext_id . $ext_id . $quotes_ext_id . ","
	. $quotes_orgform_kurzbz . $orgform_kurzbz . $quotes_orgform_kurzbz . ","
	. $quotes_bestaetigtam . $bestaetigtam . $quotes_bestaetigtam . ","
	. $quotes_bestaetigtvon . $bestaetigtvon . $quotes_bestaetigtvon . ","
	. $quotes_anmerkung . $anmerkung . $quotes_anmerkung . ","
	. $quotes_bewerbung_abgeschicktamum . $bewerbung_abgeschicktamum . $quotes_bewerbung_abgeschicktamum . ","
	. $quotes_studienplan_id . $studienplan_id . $quotes_studienplan_id . ","
	. $quotes_rt_stufe . $rt_stufe . $quotes_rt_stufe . ","
	. $quotes_statusgrund_id . $statusgrund_id . $quotes_statusgrund_id . ");
	";

		//Delete Status
		$result = $this->PrestudentstatusModel->delete(
			[
				'prestudent_id' => $prestudent_id,
				'status_kurzbz' => $status_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'studiensemester_kurzbz' => $studiensemester_kurzbz
			]
		);

		if ( $this->db->trans_status() === false || isError($result))
		//if (isError($result))
		{
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}
		if(!hasData($result))
		{
			$this->outputJson($result);
		}

		//save log delete prestudentstatus
		$uid = getAuthUID();
		$beschreibung = 'Loeschen der Rolle ' . $status_kurzbz . " bei " . $prestudent_id;
		$sql = "DELETE FROM public.tbl_prestudentstatus
                WHERE prestudent_id = " . $prestudent_id . "
			  AND status_kurzbz = '" . $status_kurzbz . "'
			  AND ausbildungssemester ='" . $ausbildungssemester . "'
			  AND studiensemester_kurzbz = '" . $studiensemester_kurzbz . "'";

		$this->load->model('system/Log_model', 'LogModel');
		$result = $this->LogModel->insert([
			'mitarbeiter_uid' => $uid,
			'beschreibung' => $beschreibung,
			'sql' => $sql,
			'sqlundo' => $sqlundo
			]);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if (!hasData($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$logId = $result->retval;

		//Delete Studentlehrverband if no Status left
		$result = $this->PrestudentstatusModel->getLastStatus($prestudent_id, $studiensemester_kurzbz);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if (!hasData($result))
		{
			//get student_uid
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfUid($prestudent_id);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if (!hasData($result))
			{
				$this->outputJson($result);
			}
			$student_uid = $result->retval;

			$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
			$result = $this->StudentlehrverbandModel->delete(
				array(
					'student_uid' => $student_uid,
					'studiensemester_kurzbz' => $studiensemester_kurzbz
				)
			);
			if ($this->db->trans_status() === false || isError($result))
			{
				$this->db->trans_rollback();
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson("Fehler beim Löschen der Lehrverbandszuordnung");
			}
			if (!hasData($result))
			{
				$this->outputJson($result);
				var_dump("no data Löschen der Lehrverbandszuordnung");
			}
			$this->outputJsonSuccess(true);
		}

		//Delete Prestudent if no data is left

		if($deletePrestudent && $isBerechtigtAdmin)
		{
			//TODO(manu) check all connected tables

			//check if existing dokumentprestudent
			$output = "";
			$this->load->model('crm/Dokumentprestudent_model', 'DokumentprestudentModel');
			$result = $this->DokumentprestudentModel->loadWhere(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson($result);
			}
			if (hasData($result))
			{
				$documentPrestudent = $result->retval;
				foreach ($documentPrestudent as $doc)
				{
					$output .=  ' '. $doc->dokument_kurzbz;
				}
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return 	$this->outputJson("Es sind noch zu loeschende Dokumente vorhanden: " . $output);
			}

			//check if Anrechnungen tbl_anrechnung
			$output_anrechnungen = '';
			$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
			$result = $this->AnrechnungModel->loadWhere(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson($result);
			}
			if (hasData($result))
			{
				$anrechnungPrestudent = $result->retval;
				foreach ($anrechnungPrestudent as $anr)
				{
					$output_anrechnungen .=  ' '. $anr->anrechnung_id;
				}
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return 	$this->outputJson("Mit dieser Prestudent_id sind verbundene Anrechnungen vorhanden (id) : " . $output_anrechnungen);
			}

			//DELETE Prestudent

			//save log delete prestudent
			$uid = getAuthUID();
			$beschreibung = 'Loeschen der Prestudent ID ' . $prestudent_id;
			$sql = "DELETE FROM public.tbl_prestudent
                WHERE prestudent_id = " . $prestudent_id ;

			//load prestudent für LOG sqlundo
			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->loadWhere(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result);
			}
			if (!hasData($result)) {
				return $this->outputJson($result);
			}

			//Data of current Prestudent
			$prestudentData = current(getData($result));

			$aufmerksamdurch_kurzbz = $prestudentData->aufmerksamdurch_kurzbz == '' ? 'null' : $prestudentData->aufmerksamdurch_kurzbz;
			$studiengang_kz = $prestudentData->studiengang_kz == '' ? 'null' : $prestudentData->studiengang_kz;
			$berufstaetigkeit_code = $prestudentData->berufstaetigkeit_code == '' ? 'null' : $prestudentData->berufstaetigkeit_code;
			$ausbildungcode = $prestudentData->ausbildungcode == '' ? 'null' : $prestudentData->ausbildungcode;
			$zgv_code = $prestudentData->zgv_code == '' ? 'null' : $prestudentData->zgv_code;
			$zgvort = $prestudentData->zgvort == '' ? 'null' : $prestudentData->zgvort;
			$zgvdatum = $prestudentData->zgvdatum == '' ? 'null' : $prestudentData->zgvdatum;
			$zgvnation = $prestudentData->zgvnation == '' ? 'null' : $prestudentData->zgvnation;
			$zgv_erfuellt = $prestudentData->zgv_erfuellt == '' ? 'null' : $prestudentData->zgv_erfuellt;
			$zgvmas_code = $prestudentData->zgvmas_code == '' ? 'null' : $prestudentData->zgvmas_code;
			$zgvmaort = $prestudentData->zgvmaort == '' ? 'null' : $prestudentData->zgvmaort;
			$zgvmadatum = $prestudentData->zgvmadatum == '' ? 'null' : $prestudentData->zgvmadatum;
			$zgvmanation = $prestudentData->zgvmanation == '' ? 'null' : $prestudentData->zgvmanation;
			$zgvmas_erfuellt = $prestudentData->zgvmas_erfuellt == '' ? 'null' : $prestudentData->zgvmas_erfuellt;
			$aufnahmeschluessel = $prestudentData->aufnahmeschluessel == '' ? 'null' : $prestudentData->aufnahmeschluessel;
			$facheinschlberuf = $prestudentData->facheinschlberuf == '' ? 'null' : $prestudentData->facheinschlberuf;
			$anmeldungreihungstest = $prestudentData->anmeldungreihungstest == '' ? 'null' : $prestudentData->anmeldungreihungstest;
			$reihungstestangetreten = $prestudentData->reihungstestangetreten == 1 ? 'true' : 'false';
			$reihungstest_id = $prestudentData->reihungstest_id == '' ? 'null' : $prestudentData->reihungstest_id;
			$rt_gesamtpunkte = $prestudentData->rt_gesamtpunkte == '' ? 'null' : $prestudentData->rt_gesamtpunkte;
			$rt_punkte1 = $prestudentData->rt_punkte1 == '' ? 'null' : $prestudentData->rt_punkte1;
			$rt_punkte2 = $prestudentData->rt_punkte2 == '' ? 'null' : $prestudentData->rt_punkte2;
			$rt_punkte3 = $prestudentData->rt_punkte3 == '' ? 'null' : $prestudentData->rt_punkte3;
			$bismelden = $prestudentData->bismelden == 1 ? 'true' : 'false';
			$person_id = $prestudentData->person_id == '' ? 'null' : $prestudentData->person_id;
			$anmerkung = $prestudentData->anmerkung == '' ? 'null' : $prestudentData->anmerkung;
			$mentor = $prestudentData->mentor == '' ? 'null' : $prestudentData->mentor;
			$ext_id = $prestudentData->ext_id == '' ? 'null' : $prestudentData->ext_id;
			$dual = $prestudentData->dual == 1 ? 'true' : 'false';
			$ausstellungsstaat = $prestudentData->ausstellungsstaat == '' ? 'null' : $prestudentData->ausstellungsstaat;
			$zgvdoktor_code = $prestudentData->zgvdoktor_code == '' ? 'null' : $prestudentData->zgvdoktor_code;
			$zgvdoktorort = $prestudentData->zgvdoktorort == '' ? 'null' : $prestudentData->zgvdoktorort;
			$zgvdoktordatum = $prestudentData->zgvdoktordatum == '' ? 'null' : $prestudentData->zgvdoktordatum;
			$zgvdoktornation = $prestudentData->zgvdoktornation == '' ? 'null' : $prestudentData->zgvdoktornation;
			$gsstudientyp_kurzbz = $prestudentData->gsstudientyp_kurzbz == '' ? 'null' : $prestudentData->gsstudientyp_kurzbz;
			$aufnahmegruppe_kurzbz = $prestudentData->aufnahmegruppe_kurzbz == '' ? 'null' : $prestudentData->aufnahmegruppe_kurzbz;
			$priorisierung = $prestudentData->priorisierung == '' ? 'null' : $prestudentData->priorisierung;
			$zgvdoktor_erfuellt= $prestudentData->zgvdoktor_erfuellt == '' ? 'null' : $prestudentData->zgvdoktor_erfuellt;

			$quotes_aufmerksamdurch_kurzbz = $aufmerksamdurch_kurzbz == "null" ? " " : "'";
			$quotes_studiengang_kz = $studiengang_kz == "null" ? " " : "'";
			$quotes_berufstaetigkeit_code = $berufstaetigkeit_code == "null" ? " " : "'";
			$quotes_ausbildungcode = $ausbildungcode == "null" ? " " : "'";
			$quotes_zgv_code = $zgv_code == "null" ? " " : "'";
			$quotes_zgvort = $zgvort == "null" ? " " : "'";
			$quotes_zgvdatum = $zgvdatum == "null" ? " " : "'";
			$quotes_zgvnation = $zgvnation == "null" ? " " : "'";
			$quotes_zgv_erfuellt = $zgv_erfuellt == "null" ? " " : "'";
			$quotes_zgvmas_code = $zgvmas_code == "null" ? " " : "'";
			$quotes_zgvmaort = $zgvmaort == "null" ? " " : "'";
			$quotes_zgvmadatum = $zgvmadatum == "null" ? " " : "'";
			$quotes_zgvmanation = $zgvmanation == "null" ? " " : "'";
			$quotes_zgvmas_erfuellt = $zgvmas_erfuellt == "null" ? " " : "'";
			$quotes_aufnahmeschluessel = $aufnahmeschluessel == "null" ? " " : "'";
			$quotes_facheinschlberuf = $facheinschlberuf == "null" ? " " : "'";
			$quotes_anmeldungreihungstest = $anmeldungreihungstest == "null" ? " " : "'";
			$quotes_reihungstestangetreten = $reihungstestangetreten == "null" ? " " : "'";
			$quotes_reihungstest_id = $reihungstest_id == "null" ? " " : "'";
			$quotes_rt_gesamtpunkte = $rt_gesamtpunkte == "null" ? " " : "'";
			$quotes_rt_punkte1 = $rt_punkte1 == "null" ? " " : "'";
			$quotes_rt_punkte2 = $rt_punkte2 == "null" ? " " : "'";
			$quotes_rt_punkte3 = $rt_punkte3 == "null" ? " " : "'";
			$quotes_bismelden = $bismelden == "null" ? " " : "'";
			$quotes_person_id = $person_id == "null" ? " " : "'";
			$quotes_anmerkung = $anmerkung == "null" ? " " : "'";
			$quotes_mentor = $mentor == "null" ? " " : "'";
			$quotes_ext_id = $ext_id == "null" ? " " : "'";
			$quotes_dual = $dual == "null" ? " " : "'";
			$quotes_ausstellungsstaat = $ausstellungsstaat == "null" ? " " : "'";
			$quotes_zgvdoktor_code = $zgvdoktor_code == "null" ? " " : "'";
			$quotes_zgvdoktorort = $zgvdoktorort == "null" ? " " : "'";
			$quotes_zgvdoktordatum = $zgvdoktordatum == "null" ? " " : "'";
			$quotes_zgvdoktornation = $zgvdoktornation == "null" ? " " : "'";
			$quotes_gsstudientyp_kurzbz = $gsstudientyp_kurzbz == "null" ? " " : "'";
			$quotes_aufnahmegruppe_kurzbz = $aufnahmegruppe_kurzbz == "null" ? " " : "'";
			$quotes_priorisierung = $priorisierung == "null" ? " " : "'";
			$quotes_zgvdoktor_erfuellt = $zgvdoktor_erfuellt == "null" ? " " : "'";


			$sqlundo =
				"
	INSERT INTO public.tbl_prestudent(prestudent_id, aufmerksamdurch_kurzbz, studiengang_kz, berufstaetigkeit_code, ausbildungcode,
				zgv_code, zgvort, zgvdatum, zgvnation,zgv_erfuellt, zgvmas_code, zgvmaort, zgvmadatum, zgvmanation,zgvmas_erfuellt,
				aufnahmeschluessel, facheinschlberuf, anmeldungreihungstest, reihungstestangetreten, reihungstest_id,
				rt_gesamtpunkte, rt_punkte1, rt_punkte2, rt_punkte3, bismelden, person_id, anmerkung, mentor, ext_id,
				dual, ausstellungsstaat, zgvdoktor_code, zgvdoktorort, zgvdoktordatum, zgvdoktornation,
				gsstudientyp_kurzbz, aufnahmegruppe_kurzbz, priorisierung, zgvdoktor_erfuellt) 
	VALUES('" . $prestudent_id . "',"
				. $quotes_aufmerksamdurch_kurzbz . $aufmerksamdurch_kurzbz . $quotes_aufmerksamdurch_kurzbz . ","
				. $quotes_studiengang_kz . $studiengang_kz . $quotes_studiengang_kz . ","
				. $quotes_berufstaetigkeit_code . $berufstaetigkeit_code . $quotes_berufstaetigkeit_code . ","
				. $quotes_ausbildungcode . $ausbildungcode . $quotes_ausbildungcode . ","
				. $quotes_zgv_code . $zgv_code . $quotes_zgv_code . ","
				. $quotes_zgvort . $zgvort . $quotes_zgvort . ","
				. $quotes_zgvdatum . $zgvdatum . $quotes_zgvdatum . ","
				. $quotes_zgvnation . $zgvnation . $quotes_zgvnation . ","
				. $quotes_zgv_erfuellt . $zgv_erfuellt . $quotes_zgv_erfuellt . ","
				. $quotes_zgvmas_code . $zgvmas_code . $quotes_zgvmas_code . ","
				. $quotes_zgvmaort . $zgvmaort . $quotes_zgvmaort . ","
				. $quotes_zgvmadatum . $zgvmadatum . $quotes_zgvmadatum . ","
				. $quotes_zgvmanation . $zgvmanation . $quotes_zgvmanation . ","
				. $quotes_zgvmas_erfuellt . $zgvmas_erfuellt . $quotes_zgvmas_erfuellt . ","
				. $quotes_aufnahmeschluessel . $aufnahmeschluessel . $quotes_aufnahmeschluessel . ","
				. $quotes_facheinschlberuf . $facheinschlberuf . $quotes_facheinschlberuf . ","
				. $quotes_anmeldungreihungstest . $anmeldungreihungstest . $quotes_anmeldungreihungstest . ","
				. $quotes_reihungstestangetreten . $reihungstestangetreten . $quotes_reihungstestangetreten . ","
				. $quotes_reihungstest_id . $reihungstest_id . $quotes_reihungstest_id . ","
				. $quotes_rt_gesamtpunkte . $rt_gesamtpunkte . $quotes_rt_gesamtpunkte . ","
				. $quotes_rt_punkte1 . $rt_punkte1 . $quotes_rt_punkte1 . ","
				. $quotes_rt_punkte2 . $rt_punkte2 . $quotes_rt_punkte2 . ","
				. $quotes_rt_punkte3 . $rt_punkte3 . $quotes_rt_punkte3 . ","
				. $quotes_bismelden . $bismelden . $quotes_bismelden . ","
				. $quotes_person_id . $person_id . $quotes_person_id . ","
				. $quotes_anmerkung . $anmerkung . $quotes_anmerkung . ","
				. $quotes_mentor . $mentor . $quotes_mentor . ","
				. $quotes_ext_id . $ext_id . $quotes_ext_id . ","
				. $quotes_dual . $dual . $quotes_dual . ","
				. $quotes_ausstellungsstaat . $ausstellungsstaat . $quotes_ausstellungsstaat . ","
				. $quotes_zgvdoktor_code . $zgvdoktor_code . $quotes_zgvdoktor_code . ","
				. $quotes_zgvdoktorort . $zgvdoktorort . $quotes_zgvdoktorort . ","
				. $quotes_zgvdoktordatum . $zgvdoktordatum . $quotes_zgvdoktordatum . ","
				. $quotes_zgvdoktornation . $zgvdoktornation . $quotes_zgvdoktornation . ","
				. $quotes_gsstudientyp_kurzbz . $gsstudientyp_kurzbz . $quotes_gsstudientyp_kurzbz . ","
				. $quotes_aufnahmegruppe_kurzbz . $aufnahmegruppe_kurzbz . $quotes_aufnahmegruppe_kurzbz . ","
				. $quotes_priorisierung . $priorisierung . $quotes_priorisierung . ","
				. $quotes_zgvdoktor_erfuellt . $zgvdoktor_erfuellt . $quotes_zgvdoktor_erfuellt . ");
	";

			$this->load->model('system/Log_model', 'LogModel');
			$result = $this->LogModel->insert([
				'mitarbeiter_uid' => $uid,
				'beschreibung' => $beschreibung,
				'sql' => $sql,
				'sqlundo' => $sqlundo
			]);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if (!hasData($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}

			$logId = $result->retval;

			//independent transaction
			$this->db->trans_start();

			$this->load->model('crm/Prestudent_model', 'PrestudentModel');
			$result = $this->PrestudentModel->delete(
				array(
					'prestudent_id' => $prestudent_id
				)
			);
			if ($this->db->trans_status() === FALSE)
			{
				$this->db->trans_rollback();
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson($result);
			}
			if(!hasData($result))
			{
				$this->outputJson($result);
			}
			$this->db->trans_commit();
			$this->outputJson($result);

		}
		$this->db->trans_commit();
		return $this->outputJsonSuccess(true);
	}

	public function updateStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		$isStudent = false;
		$isBerechtigtNoStudstatusCheck =  $this->permissionlib->isBerechtigt('student/keine_studstatuspruefung');

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg) )
		{
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$_POST = json_decode(utf8_encode($this->input->raw_input_stream), true);
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$uid = getAuthUID();
		$prestudent_id = $this->input->post('prestudent_id');
		$status_kurzbz = $this->input->post('status_kurzbz');
		$ausbildungssemester = $this->input->post('ausbildungssemester');
		$datum = $this->input->post('datum');
		$bestaetigtam = $this->input->post('bestaetigtam');
		$bewerbung_abgeschicktamum = $this->input->post('bewerbung_abgeschicktamum');
		$studiensemester_kurzbz = $this->input->post('studiensemester_kurzbz');
		$studienplan_id = $this->input->post('studienplan_id');
		$anmerkung  = $this->input->post('anmerkung');
		$statusgrund_id = $this->input->post('statusgrund_id');
		$rt_stufe = $this->input->post('rt_stufe');
		$bestaetigtvon = $uid;

		//check if Bismeldestichtag erreicht
		if(!$isBerechtigtNoStudstatusCheck)
		{
			//Fas only check datum
			$this->load->model('codex/Bismeldestichtag_model', 'BismeldestichtagModel');
/*			$result = $this->BismeldestichtagModel->checkIfMeldestichtagErreicht($datum, $studiensemester_kurzbz);*/
			$result = $this->BismeldestichtagModel->checkIfMeldestichtagErreicht($datum);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if ($result->retval == "1") {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				//return $this->outputJson($result->code);
				//Fehlermeldung überschrieben, passender
				return $this->outputJson("Meldestichtag erreicht - Bearbeiten nicht mehr möglich");
			}
		}

		//check if Rolle already exists
		if(($key_studiensemester_kurzbz != $studiensemester_kurzbz)
			|| ($key_ausbildungssemester != $ausbildungssemester))
		{
			$result = $this->PrestudentstatusModel->checkIfExistingPrestudentRolle($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $ausbildungssemester);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if($result->retval == '1')
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
		}

		//check if studentrolle already exists
		if($status_kurzbz == 'Student' || $status_kurzbz == 'Diplomand')
		{
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfExistingStudentRolle($prestudent_id);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if($result->retval == "0")
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
			if($result->retval != "0")
			{
				$isStudent = true;
			}
		}


		if(!isBerechtigtNoStudstatusCheck)
		{
			//Block STATUSCHECKS

			$result = $this->PrestudentstatusModel->checkIfValidStatusHistory($prestudent_id, $status_kurzbz, $studiensemester_kurzbz, $datum, $ausbildungssemester);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
		}


		// Start DB transaction
		$this->db->trans_begin();

		//update status
		var_dump($ausbildungssemester, $studiensemester_kurzbz);
		$result = $this->PrestudentstatusModel->update(
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
			],
			[
				'status_kurzbz' => $status_kurzbz,
				'studiensemester_kurzbz' => $studiensemester_kurzbz,
				'ausbildungssemester' => $ausbildungssemester,
				'bewerbung_abgeschicktamum' => $bewerbung_abgeschicktamum,
				'studienplan_id' => $studienplan_id,
				'anmerkung' => $anmerkung,
				'statusgrund_id' => $statusgrund_id,
				'updatevon' => $uid,
				'updateamum' => date('c'),
				'bestaetigtam' => $bestaetigtam,
				'bestaetigtvon' => $bestaetigtvon,
				'datum' => $datum,
				'rt_stufe' => $rt_stufe
			]
		);
		if (isError($result))
		{
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}


		if($isStudent)
		{
			//check Studentlehrverband
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfUid($prestudent_id);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			$student_uid = $result->retval;

			//process studentlehrverband
			$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
			$result = $this->StudentlehrverbandModel->loadWhere(
				array(
					'student_uid' => $student_uid,
					'studiensemester_kurzbz' => $studiensemester_kurzbz
				)
			);
			if (isError($result)) {
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				$this->outputJson($result);
			}
			if (!hasData($result)) {
				$this->outputJson($result);
			}

			//Data of current Semester
			$studentlvbData = current(getData($result));

			$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
			$result = $this->StudentlehrverbandModel->processStudentlehrverband($student_uid,  $studentlvbData->studiengang_kz, $ausbildungssemester, $studentlvbData->verband, $studentlvbData->gruppe, $studiensemester_kurzbz);
			if ($this->db->trans_status() === false || isError($result))
			{
				$this->db->trans_rollback();
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
		}

		$this->db->trans_commit();
		return $this->outputJsonSuccess(true);
	}

	public function advanceStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		$isStudent = false;

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg) )
		{
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}


		//Data Vorrücken
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz
			)
		);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		else
		{
			$statusData = current(getData($result));
		}

		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$result = $this->StudiensemesterModel->getNextFrom($key_studiensemester_kurzbz);

		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$studiensem_next = current(getData($result));
		$studiensem_next = $studiensem_next->studiensemester_kurzbz;

		$ausbildungssem_next = $key_ausbildungssemester+1;

		//check if Rolle already exists
		$result = $this->PrestudentstatusModel->checkIfExistingPrestudentRolle($key_prestudent_id, $key_status_kurzbz, $studiensem_next, $ausbildungssem_next);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if($result->retval == '1')
		{
			$isStudent = true;
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result->code);
		}

		//check if studentrolle already exists
		if($key_status_kurzbz == 'Student')
		{
			$this->load->model('crm/Student_model', 'StudentModel');
			$result = $this->StudentModel->checkIfExistingStudentRolle($key_prestudent_id);
			if (isError($result))
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson(getError($result));
			}
			if ($result->retval == "0")
			{
				$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				return $this->outputJson($result->code);
			}
		}

		// Start DB transaction
		$this->db->trans_begin();

		//insert prestudentstatus
		$uid = getAuthUID();
		$result = $this->PrestudentstatusModel->insert(
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $studiensem_next,
				'ausbildungssemester' => $ausbildungssem_next,
				'insertamum' => date('c'),
				'insertvon' => $uid,
				'bestaetigtam' => date('c'),
				'bestaetigtvon' => $uid,
				'studienplan_id' => $statusData->studienplan_id,
				'datum' => date('c'),
				'anmerkung' => $statusData->anmerkung
			]
		);

		if (isError($result))
		{
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}

		$this->load->model('crm/Student_model', 'StudentModel');
		$result = $this->StudentModel->checkIfUid($key_prestudent_id);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$student_uid = $result->retval;

		//process studentlehrverband
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->loadWhere(
			array(
				'student_uid' => $student_uid,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz
			)
		);
		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}
		if (!hasData($result)) {
			$this->outputJson($result);
		}

		//Data of current Semester
		$result = getData($result) ? : [];
		$studentlvbData = current($result);

		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$result = $this->StudentlehrverbandModel->processStudentlehrverband($student_uid, $studentlvbData->studiengang_kz, $ausbildungssem_next, $studentlvbData->verband, $studentlvbData->gruppe, $studiensem_next);
		if ($this->db->trans_status() === false || isError($result))
		{
			$this->db->trans_rollback();
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result->code);
		}

		$this->db->trans_commit();

		return $this->outputJsonSuccess(true);
	}

	public function confirmStatus($key_prestudent_id, $key_status_kurzbz, $key_studiensemester_kurzbz, $key_ausbildungssemester)
	{
		$uid = getAuthUID();

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $key_prestudent_id,
		]);
		if(isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		if (!$this->permissionlib->isBerechtigt('admin', 'suid', $stg) && !$this->permissionlib->isBerechtigt('assistenz', 'suid', $stg) )
		{
			$result = "Sie haben keine Schreibrechte fuer diesen Studiengang!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');

		$result = $this->PrestudentstatusModel->loadWhere(
			array(
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz
			)
		);

		if (isError($result)) {
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			$this->outputJson($result);
		}

		elseif (!hasData($result)) {
			$this->outputJson($result);
		}
		else
		{
			$statusData = current(getData($result));
		}

		//check if Status is unconfirmed.. TODO(manu) check further conditions? Status < Student
		if($statusData->bestaetigtam != NULL)
		{
			$result = "Der Status ist bereits bestätigt!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		//check if Bewerbung abgeschickt
		if($statusData->bewerbung_abgeschicktamum == NULL)
		{
			$result = "Die Bewerbung wurde noch nicht abgeschickt und kann deshalb nicht bestaetigt werden!";
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson($result);
		}

		$result = $this->PrestudentstatusModel->update(
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
			],
			[
				'prestudent_id' => $key_prestudent_id,
				'status_kurzbz' => $key_status_kurzbz,
				'studiensemester_kurzbz' => $key_studiensemester_kurzbz,
				'ausbildungssemester' => $key_ausbildungssemester,
				'bestaetigtam' => date('c'),
				'bestaetigtvon' => $uid,
				'updateamum' => date('c'),
				'updatevon' => $uid
			]
		);
		if (isError($result))
		{
			$this->output->set_status_header(REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			return $this->outputJson(getError($result));
		}
		if (!hasData($result)) {
			return error('No Statusdata vorhanden');
		}
		return $this->outputJsonSuccess(true);

	}
}
