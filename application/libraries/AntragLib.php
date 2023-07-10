<?php

/**
 * FH-Complete
 *
 * @package             FHC-Helper
 * @author              FHC-Team
 * @copyright           Copyright (c) 2023 fhcomplete.net
 * @license             GPLv3
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;
use \DOMDocument as DOMDocument;
use \XSLTProcessor as XSLTProcessor;
use \Studierendenantragstatus_model as Studierendenantragstatus_model;
use \stdClass as stdClass;

class AntragLib
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance();

		// Configs
		$this->_ci->load->config('studierendenantrag');

		// Models
		$this->_ci->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');
		$this->_ci->load->model('education/Studierendenantragstatus_model', 'StudierendenantragstatusModel');
		$this->_ci->load->model('education/Studierendenantraglehrveranstaltung_model', 'StudierendenantraglehrveranstaltungModel');
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->_ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->_ci->load->model('person/Person_model', 'PersonModel');
		$this->_ci->load->model('education/Pruefung_model', 'PruefungModel');

		// Helper
		$this->_ci->load->helper('hlp_sancho_helper');

		// Libraries
		$this->_ci->load->library('PermissionLib');
		$this->_ci->load->library('PrestudentLib');
	}

	/**
	 * @param integer		$antrag_id
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function cancelAntrag($antrag_id, $insertvon)
	{
		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_CANCELLED,
			'insertvon' => $insertvon
		]);

		return $result;
	}

	/**
	 * NOTE(chris): permissions & verification must be handled outside
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz
	 * @param string		$insertvon
	 * @param string		$grund
	 *
	 * @return stdClass
	 */
	public function createAbmeldung($prestudent_id, $studiensemester_kurzbz, $insertvon, $grund)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (isError($result))
			return $result;
		if(!hasData($result))
			return error("Kein Prestudent gefunden: " . $prestudent_id);

		$prestudent = getData($result)[0];
		if($prestudent->person_id == getAuthPersonId())
			$typ = Studierendenantrag_model::TYP_ABMELDUNG;
		else
			$typ = Studierendenantrag_model::TYP_ABMELDUNG_STGL;

		$result = $this->_ci->StudierendenantragModel->insert([
			'prestudent_id' => $prestudent_id,
			'studiensemester_kurzbz'=> $studiensemester_kurzbz,
			'datum' => date('c'),
			'typ' => $typ,
			'insertvon' => $insertvon,
			'grund' => $grund
		]);

		if (isError($result))
			return $result;

		$antrag_id = getData($result);

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_CREATED,
			'insertvon' => $insertvon
		]);

		if (isError($result))
			return $result;

		return success($antrag_id);
	}

	/**
	 * @param array			$studierendenantrag_ids
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function approveAbmeldung($studierendenantrag_ids, $insertvon)
	{
		$errors = [];
		foreach ($studierendenantrag_ids as $studierendenantrag_id) {

			$result = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
			if (isError($result))
			{
				$errors[] = getError($result);
				continue;
			}
			if(!hasData($result))
			{
				$errors[] = 'no Antrag found for '. $studierendenantrag_id;
				continue;
			}
			$status = getData($result)[0];

			$result = $this->_ci->StudierendenantragstatusModel->insert([
				'studierendenantrag_id' => $studierendenantrag_id,
				'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
				'insertvon' => $insertvon
			]);
			if (isError($result))
				$errors[] = getError($result);
			else {
				$resultPrestudent = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
				if (isError($resultPrestudent))
				{
					$errors[] = getError($resultPrestudent);
					continue;
				}
				if ($status->typ == Studierendenantrag_model::TYP_ABMELDUNG)
				{
					$antrag = getData($resultPrestudent)[0];

					$resultPrestudentStatus = $this->_ci->PrestudentstatusModel->getLastStatusWithStgEmail($antrag->prestudent_id);
					if (isError($resultPrestudentStatus))
						$errors[] = getError($resultPrestudentStatus);

					else {
						$prestudent_status = getData($resultPrestudentStatus)[0];

						$vorlage ='Sancho_Mail_Antrag_A_Approve';
						$subject = 'Abmeldung freigegeben';

						$result = $this->_ci->prestudentlib->setAbbrecher($antrag->prestudent_id, $antrag->studiensemester_kurzbz, $insertvon);
						if (isError($result))
						{
							$errors[] = getError($result);
							return $errors;
						}

						$result = $this->_ci->PersonModel->loadPrestudent($antrag->prestudent_id);
						$data = [
							'nameStudent' => 'Student*in'
						];
						if (hasData($result)) {
							$person = current(getData($result));
							$data['nameStudent'] = trim($person->vorname . ' ' . $person->nachname);
						}
						// NOTE(chris): Sancho mail
						sendSanchoMail($vorlage, $data, $prestudent_status->email, $subject);
					}
				}
				if ($status->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL) {
					$res = $this->_ci->PrestudentModel->load($status->prestudent_id);
					if (hasData($res)) {
						$prestudent = current(getData($res));
						$res = $this->_ci->PersonModel->load($prestudent->person_id);
						if (hasData($res)) {
							$person = current(getData($res));
							$name = trim($person->vorname . ' ' . $person->nachname);
						} else {
							$name = 'Student*in';
						}
						$res = $this->_ci->KontaktModel->getZustellKontakt($prestudent->person_id, ['email']);
						if (hasData($res)) {
							$kontakt = current(getData($res));
							$email = $kontakt->kontakt;
							sendSanchoMail(
								'Sancho_Mail_Antrag_A_Stgl',
								[
									'name' => $name,
									'grund' => $status->grund
								],
								$email,
								'Abmeldung durch Studiengangsleitung'
							);
						}
					}
				}
			}
		}

		if (count($errors))
			return error(implode(',', $errors));

		return success();
	}

	/**
	 * NOTE(chris): permissions & verification must be handled outside
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz
	 * @param string		$insertvon
	 * @param string		$grund
	 * @param string		$datum_wiedereinstieg
	 *
	 * @return stdClass
	 */
	public function createUnterbrechung($prestudent_id, $studiensemester_kurzbz, $insertvon, $grund, $datum_wiedereinstieg, $dms_id)
	{
		$datum_wiedereinstieg = new DateTime($datum_wiedereinstieg);
		$datum_wiedereinstieg = $datum_wiedereinstieg->format("Y-m-d");
		$result = $this->_ci->StudierendenantragModel->insert([
			'prestudent_id' => $prestudent_id,
			'studiensemester_kurzbz'=> $studiensemester_kurzbz,
			'datum' => date('c'),
			'typ' => Studierendenantrag_model::TYP_UNTERBRECHUNG,
			'insertvon' => $insertvon,
			'grund' => $grund,
			'datum_wiedereinstieg' => $datum_wiedereinstieg,
			'dms_id' => $dms_id
		]);

		if (isError($result))
			return $result;

		$antrag_id = getData($result);

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_CREATED,
			'insertvon' => $insertvon
		]);

		if (isError($result))
			return $result;

		return success($antrag_id);
	}


	/**
	 * @param array			$studierendenantrag_ids
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function approveUnterbrechung($studierendenantrag_ids, $insertvon)
	{
		$this->_ci->load->model('person/Kontakt_model', 'KontaktModel');

		$errors = [];

		foreach ($studierendenantrag_ids as $studierendenantrag_id)
		{
			$data = $this->getDataForUnterbrechung($studierendenantrag_id);

			if (isError($data)) {
				$error_msg = getError($data);
				if (is_array($error_msg) && isset($error_msg['message']))
					$error_msg = $error_msg['message'];

				$errors['failed_' . $studierendenantrag_id] = 'Could not approve Unterbrechung for studierendenantrag_id: ' .
				$studierendenantrag_id .
				'<br>Details:<br>' .
				$error_msg;
			} else {
				$data = getData($data);

				$result = $this->_ci->StudierendenantragstatusModel->insert([
					'studierendenantrag_id' => $studierendenantrag_id,
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
					'insertvon' => $insertvon
				]);
				if (isError($result))
				{
					$errors['failed_' . $studierendenantrag_id] = 'Could not approve Unterbrechung for studierendenantrag_id: ' .
					$studierendenantrag_id .
					'<br>Details:<br>' .
					getError($result)['message'];
				}
				else
				{
					$studierendenantrag_status_id = getData($result);
					$resultAntrag = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
					if (isError($resultAntrag))
						return $resultAntrag;
					$resultAntrag = getData($resultAntrag);
					if (!$resultAntrag)
						return error('No antrag found with id: ' . $studierendenantrag_id);
					$resultAntrag = current($resultAntrag);

						// Prestudentstatus und Unterbrechungsfolgeaktionen setzen
					$result = $this->_ci->prestudentlib->setUnterbrecher($resultAntrag->prestudent_id, $resultAntrag->studiensemester_kurzbz, $studierendenantrag_id);

					if (isError($result)) {
						$this->_ci->StudierendenantragstatusModel->delete($studierendenantrag_status_id);
						return $result;
					}

							//Mail
					$subject = 'Unterbrechung freigegeben';
					$mail = [];

					if (isset($data['errors']['person_id']))
					{
								//send assistenz mit id
						$errors[] = 'Mail to student not sent and student name not found<br>Details:<br>' . $data['errors']['person_id'];
						$mail['ass'] = 'Student/in (' . $data['antrag']->prestudent_id . ')';
					}
					elseif (isset($data['errors']['email']))
					{
						if (isset($data['errors']['person']))
						{
									//send assistenz mit id
							$errors[] = 'Mail to student not sent and student name not found<br>Details:<br>' .
							$data['errors']['email'] .
							'<br>' .
							$data['errors']['person'];
							$mail['ass'] = 'Student/in (' . $data['antrag']->prestudent_id . ')';
						}
						else
						{
									//send assistenz mit name
							$errors[] = 'Mail to student not sent<br>Details:<br>' . $data['errors']['email'];
							$mail['ass'] = trim($data['person']->vorname . ' ' . $data['person']->nachname);
						}
					}
					else
					{
						if (isset($data['errors']['person']))
						{
									//send assistenz mit id & student mit "Student/in"
							$errors[] = 'Student name not found<br>Details:<br>' . $data['errors']['person'];
							$mail['ass'] = 'Student/in (' . $data['antrag']->prestudent_id . ')';
							$mail['stu'] = 'Student/in';
						}
						else
						{
									//send normal
							$mail['ass'] = $mail['stu'] = trim($data['person']->vorname . ' ' . $data['person']->nachname);
						}
					}
					$mailVorlage = 'Sancho_Mail_Antrag_U_Approve';
					if ($data['studienbeitrag'])
						$mailVorlage .= '_SB';
					if (isset($mail['ass'])) {
						// NOTE(chris): Sancho mail
						if (!sendSanchoMail(
							$mailVorlage,
							[
								'name' => $mail['ass']
							],
							$data['prestudent_status']->email,
							$subject
						)) {
							$errors[] = 'Failed to send email to ' . $data['prestudent_status']->email;
						}
					}
					if (isset($mail['stu'])) {
						// NOTE(chris): Sancho mail
						if (!sendSanchoMail(
							$mailVorlage,
							[
								'name' => $mail['stu']
							],
							$data['email'],
							$subject
						)) {
							$errors[] = 'Failed to send email to ' . $data['email'];
						}
					}
				}
			}
		}

		if (count($errors))
			return error($errors);

		return success();
	}

	/**
	 * @param array			$studierendenantrag_ids
	 * @param string		$insertvon
	 * @param string		$grund
	 *
	 * @return stdClass
	 */
	public function rejectUnterbrechung($studierendenantrag_ids, $insertvon, $grund)
	{
		$this->_ci->load->model('person/Kontakt_model', 'KontaktModel');

		$errors = [];

		foreach ($studierendenantrag_ids as $studierendenantrag_id) {
			$data = $this->getDataForUnterbrechung($studierendenantrag_id);

			if (isError($data)) {
				$error_msg = getError($data);
				if (is_array($error_msg) && isset($error_msg['message']))
					$error_msg = $error_msg['message'];

				$errors['failed_' . $studierendenantrag_id] = 'Could not reject Unterbrechung for studierendenantrag_id: ' .
					$studierendenantrag_id .
					'<br>Details:<br>' .
					$error_msg;
			} else {
				$data = getData($data);

				$result = $this->_ci->StudierendenantragstatusModel->insert([
					'studierendenantrag_id' => $studierendenantrag_id,
					'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_REJECTED,
					'insertvon' => $insertvon,
					'grund' => $grund
				]);
				if (isError($result)) {
					$errors['failed_' . $studierendenantrag_id] = 'Could not reject Unterbrechung for studierendenantrag_id: ' .
						$studierendenantrag_id .
						'<br>Details:<br>' .
						getError($result)['message'];
				} else {
					$name = '';

					if (isset($data['errors']['person_id']) || isset($data['errors']['email'])) {
						$error_msg = [];
						if (isset($data['errors']['person_id']))
							$error_msg[] = $data['errors']['person_id'];
						if (isset($data['errors']['email']))
							$error_msg[] = $data['errors']['email'];
						$error_msg = 'Mail to student not sent<br>Details:<br>' . implode('<br>', $error_msg);
						$errors[] = $error_msg;
					} else {
						if (isset($data['errors']['person'])) {
							//send student mit "Student/in"
							$errors[] = 'Student name not found<br>Details:<br>' . $data['errors']['person'];
							$name = 'Student/in';
						} else {
							//send normal
							$name = trim($data['person']->vorname . ' ' . $data['person']->nachname);
						}
					}
					if ($name)
						// NOTE(chris): Sancho mail
						if (!sendSanchoMail(
							'Sancho_Mail_Antrag_U_Reject',
							[
								'name' => $name,
								'grund' => $grund,
								'abmeldungLink' => site_url('lehre/Studierendenantrag/abmeldung/' . $data['prestudent_status']->prestudent_id)
							],
							$data['email'],
							'Unterbrechung abgelehnt'
						))
							$errors[] = 'Failed to send email to ' . $data['email'];
				}
			}
		}

		if (count($errors))
			return error($errors);

		return success();
	}

	/**
	 * @param integer		$studierendenantrag_id
	 *
	 * @return array
	 */
	private function getDataForUnterbrechung($studierendenantrag_id)
	{
		$result = [];
		$errors = [];

		$res = $this->_ci->StudierendenantragModel->load($studierendenantrag_id);
		if (isError($res))
			return $res;

		$res = getData($res);
		if (!$res)
			return error('No Studierendenantrag found for studierendenantrag_id: ' . $studierendenantrag_id);

		$result['antrag'] = $antrag = current($res);


		$res = $this->_ci->PrestudentstatusModel->getLastStatusWithStgEmail($antrag->prestudent_id);
		if (isError($res))
			return $res;

		$res = getData($res);
		if (!$res)
			return error('No Prestudentstatus found for prestudent_id: ' . $antrag->prestudent_id);

		$result['prestudent_status'] = current($res);


		$res = $this->_ci->PrestudentModel->load($antrag->prestudent_id);

		if (isError($res)) {
			$errors['person_id'] = getError($res);
		} else {
			$res = getData($res);
			if (!$res) {
				$errors['person_id'] = 'No Prestudent found for prestudent_id: ' . $antrag->prestudent_id;
			} else {
				$person_id = current($res)->person_id;

				$res = $this->_ci->PersonModel->load($person_id);
				if (isError($res)) {
					$errors['person'] = getError($res);
				} else {
					$res = getData($res);
					if (!$res) {
						$errors['person'] = 'No Person found for person_id: ' . $person_id;
					} else {
						$result['person'] = current($res);
					}
				}

				$res = $this->_ci->KontaktModel->getZustellKontakt($person_id, ['email']);
				if (isError($res)) {
					$errors['email'] = getError($res);
				} else {
					$res = getData($res);

					if (!$res) {
						$errors['email'] = 'No email contact found for person_id: ' . $person_id;
					} else {
						$result['email'] = current($res)->kontakt;
					}
				}
			}
		}

		$result['studienbeitrag'] = false;
		if (!isset($errors['person_id'])) {
			$date_target = new DateTime(
				$this->_ci->config->item('frist_rueckzahlung_studiengebuer_' . substr($result['antrag']->studiensemester_kurzbz, 0, 2)) .
				substr($result['antrag']->studiensemester_kurzbz, 2)
			);
			$date_created = new DateTime($result['antrag']->datum);
			if ($date_created < $date_target) {
				$this->_ci->load->model('crm/Konto_model', 'KontoModel');
				$result['studienbeitrag'] = $this->_ci->KontoModel->checkStudienbeitragFromPerson($person_id, $result['antrag']->studiensemester_kurzbz);
			}
		}

		$result['errors'] = $errors;

		return success($result);
	}

	/**
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz
	 * @param string		$insertvon
	 * @param boolean		$repeat
	 *
	 * @return stdClass
	 */
	public function createWiederholung($prestudent_id, $studiensemester_kurzbz, $insertvon, $repeat)
	{
		$result = $this->_ci->StudierendenantragModel->loadIdAndStatusWhere([
			'prestudent_id' => $prestudent_id,
			'studiensemester_kurzbz'=> $studiensemester_kurzbz,
			'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG
		]);

		$antrag_id = null;
		if (hasData($result)) {
			$antrag = current(getData($result));
			if ($antrag->status == Studierendenantragstatus_model::STATUS_REOPENED ||
				$antrag->status == Studierendenantragstatus_model::STATUS_REQUESTSENT_1 ||
				$antrag->status == Studierendenantragstatus_model::STATUS_REQUESTSENT_2)
			{
				$antrag_id = $antrag->studierendenantrag_id;
			}
			else
			{
				return error('Antrag bereits vorhanden!');
			}
		}

		if ($antrag_id === null) {
			$result = $this->_ci->StudierendenantragModel->insert([
				'prestudent_id' => $prestudent_id,
				'studiensemester_kurzbz'=> $studiensemester_kurzbz,
				'datum' => date('c'),
				'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG,
				'insertvon' => $insertvon
			]);

			if (isError($result))
				return $result;

			$antrag_id = getData($result);
		}

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => $repeat ? Studierendenantragstatus_model::STATUS_CREATED : Studierendenantragstatus_model::STATUS_PASS,
			'insertvon' => $insertvon
		]);

		if ($repeat) {
			$res = $this->_ci->PrestudentstatusModel->getLastStatusWithStgEmail($prestudent_id);
			if (isError($res))
				return $res;
			$res = getData($res);
			if (!$res)
				return error('No Prestudentstatus found for prestudent_id: ' . $prestudent_id);

			$prestudent_status = current($res);
			$email = $prestudent_status->email;
			// NOTE(chris): Sancho mail
			sendSanchoMail(
				'Sancho_Mail_Antrag_W_New',
				[
					'antrag_id' => $antrag_id,
					'lvzuweisungLink' => site_url('lehre/Antrag/Wiederholung/assistenz/' . $antrag_id)
				],
				$email,
				'Neue*r Wiederholer*in'
			);
		}

		if (isError($result))
			return $result;

		return success($antrag_id);
	}

	/**
	 * @param integer		$studierendenantrag_id
	 * @param string		$insertvon
	 *
	 * @return stdClass
	 */
	public function reopenWiederholung($studierendenantrag_id, $insertvon)
	{
		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $studierendenantrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_REOPENED,
			'insertvon' => $insertvon
		]);
		return $result;
	}

	/**
	 * @param integer		$studierendenantrag_id
	 * @param string		$objectedvon
	 *
	 * @return stdClass
	 */
	public function objectAbmeldung($studierendenantrag_id, $objectedvon)
	{
		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $studierendenantrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_OBJECTED,
			'insertvon' => $objectedvon
		]);
		return $result;
	}

	public function getWiederholungsAntraege($status)
	{
		$studiengaenge = $this->_ci->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');
		$result = $this->_ci->StudierendenantragModel->loadForStudiengaenge(
			$studiengaenge,
			Studierendenantrag_model::TYP_WIEDERHOLUNG,
			$status
		);
		if (!getData($result))
			return $result;
		$result = getData($result);
		$grouped = [];

		foreach ($result as $item) {
			if (!isset($grouped[$item->studiengang_kz])) {
				$grouped[$item->studiengang_kz] = [
					'bezeichnung' => $item->bezeichnung,
					'bezeichnung_mehrsprachig' => $item->bezeichnung_mehrsprachig,
					'antraege' => []
				];
			}
			$grouped[$item->studiengang_kz]['antraege'][] = $item;
		}

		return success($grouped);
	}

	public function getLvsForAntrag($antrag_id)
	{
		$this->_ci->load->model('education/Lehrveranstaltung_model', 'LehrveranstaltungModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->_ci->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$result = $this->_ci->StudierendenantragModel->load($antrag_id);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error('No antrag found with id: ' . $antrag_id);
		$antrag = current($result);


		$result = $this->_ci->StudierendenantragModel->getStgAndSem($antrag_id);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error('No studiengang and ausbildungssemester found for antrag with id: ' . $antrag_id);
		$result = current($result);
		$studiengang_kz = $result->studiengang_kz;
		$orgform_kurzbz = $result->orgform_kurzbz;
		$ausbildungssemester = $result->ausbildungssemester;

		// NOTE(chris): check permission
		$allowedStgs = $this->_ci->permissionlib->getSTG_isEntitledFor('student/studierendenantrag') ?: [];
		if (!in_array($studiengang_kz, $allowedStgs)) {
			$allowedStgs = $this->_ci->permissionlib->getSTG_isEntitledFor('student/antragfreigabe') ?: [];
			if (!in_array($studiengang_kz, $allowedStgs)) {
				if(!$this->isOwnAntrag($antrag_id))
					return error('Forbidden');
			}
		}


		$result = $this->_ci->StudiensemesterModel->getNextFrom($antrag->studiensemester_kurzbz);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error('No studiensemster found after: ' . $antrag->studiensemester_kurzbz);
		$semA = current($result)->studiensemester_kurzbz;

		$result = $this->_ci->StudiensemesterModel->getNextFrom($semA);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error('No studiensemster found after: ' . $semA);
		$semB = current($result)->studiensemester_kurzbz;

		$result = $this->_ci->StudierendenantraglehrveranstaltungModel->loadWhere(['studierendenantrag_id' => $antrag_id]);
		if (isError($result))
			return $result;
		$result = getData($result) ?: [];

		$lvszugewiesen = array();
		foreach ($result as $lv)
		{
			$lvszugewiesen[$lv->lehrveranstaltung_id] = $lv;
		}

		$result = $this->getLvsByStgStsemAndSem(
			$studiengang_kz,
			$orgform_kurzbz,
			$semA,
			$ausbildungssemester + 1,
			$antrag->prestudent_id,
			$antrag->studiensemester_kurzbz
		);
		if (isError($result))
			return $result;
		$lvsA = $result->retval; // NOTE(chris): don't use getData() because we want to differenciate [] and null
		if ($lvsA) {
			foreach($lvsA as $lv)
			{
				if (isset($lvszugewiesen[$lv->lehrveranstaltung_id]) &&
					($lvszugewiesen[$lv->lehrveranstaltung_id]->note == $this->_ci->config->item('wiederholung_note_nicht_zugelassen')))
				{
					$lv->antrag_zugelassen = true;
					$lv->antrag_anmerkung = $lvszugewiesen[$lv->lehrveranstaltung_id]->anmerkung;
				}
			}
		}

		$result = $this->getLvsByStgStsemAndSem(
			$studiengang_kz,
			$orgform_kurzbz,
			$semB,
			$ausbildungssemester,
			$antrag->prestudent_id,
			$antrag->studiensemester_kurzbz
		);
		if (isError($result))
			return $result;
		$lvsB = getData($result) ?: [];
		foreach($lvsB as $lv)
		{
			if(isset($lvszugewiesen[$lv->lehrveranstaltung_id]) && ($lvszugewiesen[$lv->lehrveranstaltung_id]->note == 0))
			{
				$lv->antrag_anmerkung = $lvszugewiesen[$lv->lehrveranstaltung_id]->anmerkung;
				$lv->antrag_zugelassen = true;
			}
			// TODO(manu): eventuelle Änderungen taggen
		}

		return success([
			'1' . $semA => $lvsA,
			'2' . $semB => $lvsB ?: []
		]);
	}

	public function getLvsByStgStsemAndSem(
		$studiengang_kz,
		$orgform_kurzbz,
		$studiensemester_kurzbz,
		$ausbildungssemester,
		$prestudent_id,
		$note_stsem
	) {
		$this->_ci->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$result = $this->_ci->StudienplanModel->getStudienplaeneBySemester(
			$studiengang_kz,
			$studiensemester_kurzbz,
			$ausbildungssemester,
			$orgform_kurzbz
		);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result) {
			$result = $this->_ci->StudiengangModel->load($studiengang_kz);
			if (isError($result))
				return $result;
			if (!hasData($result))
				return error('No Studiengang found with studiengang_kz: ' . $studiengang_kz);
			$stg = current(getData($result));
			
			if ($ausbildungssemester > $stg->max_semester)
				return success();
			return error('No studienplan found for stg: ' .
				$studiengang_kz .
				', studiensemester: ' .
				$studiensemester_kurzbz .
				', ausbildungssemester: ' .
				($ausbildungssemester));
		}
		if (count($result) > 1)
			return error('Multiple studienplaene found for stg: ' .
				$studiengang_kz .
				', studiensemester: ' .
				$studiensemester_kurzbz .
				', ausbildungssemester: ' .
				$ausbildungssemester);
		$studienplan = current($result);

		return $this->_ci->StudienplanModel->getStudienplanLehrveranstaltungForPrestudent(
			$studienplan->studienplan_id,
			$ausbildungssemester,
			$prestudent_id,
			$note_stsem
		);
	}

	/**
	 * Checks if a prestudent can submit an Antrag for Abmeldung
	 *
	 * @param integer		$prestudent_id
	 *
	 * @return \stdClass	on success retval 0 means not a student; retval 1 means Berechtigt; retval -1 means has already an Antrag pending; retval -2 means other Antrag pending; retval -3 means in blacklist stg
	 */
	public function getPrestudentAbmeldeBerechtigt($prestudent_id)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$stg_kz = $result->studiengang_kz;
		if (in_array($stg_kz, $this->_ci->config->item('stgkz_blacklist_abmeldung')))
			return success(-3);

		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$datumStatus = $result->datum;

		if (!in_array($result->status_kurzbz, $this->_ci->config->item('antrag_prestudentstatus_whitelist'))) {
			$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['prestudent_id' => $prestudent_id, 'typ IN' => [Studierendenantrag_model::TYP_ABMELDUNG, Studierendenantrag_model::TYP_ABMELDUNG_STGL], 'campus.get_status_studierendenantrag(studierendenantrag_id)' => Studierendenantragstatus_model::STATUS_APPROVED]);
			if (isError($result))
				return $result;
			if (hasData($result))
				return success(-1);

			return success(0);
		}

		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['prestudent_id' => $prestudent_id]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(1);
		$result= getData($result);
		foreach ($result as $antrag)
		{
			if ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG || $antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL)
			{
				if ($antrag->status == Studierendenantragstatus_model::STATUS_CREATED)
					return success(-1);
				elseif ($antrag->status == Studierendenantragstatus_model::STATUS_APPROVED && $antrag->datum > $datumStatus)
					return success(-1);
			}
			if ($antrag->typ == Studierendenantrag_model::TYP_WIEDERHOLUNG)
			{
				if($antrag->status == Studierendenantragstatus_model::STATUS_PASS)
					return success(-2);
			}
		}

		return success(1);
	}

	/**
	 * Checks if a prestudent can submit an Antrag for Unterbrechung
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz		(optional)
	 *
	 * @return \stdClass	on success retval 0 means not a student; retval 1 means Berechtigt; retval -1 means has already an Antrag pending; retval -2 means other Antrag pending; retval -3 means in blacklist stg
	 */
	public function getPrestudentUnterbrechungsBerechtigt($prestudent_id, $studiensemester_kurzbz = null)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$stg_kz = $result->studiengang_kz;
		if (in_array($stg_kz, $this->_ci->config->item('stgkz_blacklist_unterbrechung')))
			return success(-3);

		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$datumStatus = $result->datum;
		if (!in_array($result->status_kurzbz, $this->_ci->config->item('antrag_prestudentstatus_whitelist'))) {
			$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['prestudent_id' => $prestudent_id, 'typ' => Studierendenantrag_model::TYP_UNTERBRECHUNG, 'campus.get_status_studierendenantrag(studierendenantrag_id)' => Studierendenantragstatus_model::STATUS_APPROVED]);
			if (isError($result))
				return $result;
			if (hasData($result))
				return success(-1);

			return success(0);
		}
		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['prestudent_id' => $prestudent_id]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(1);
		$result= getData($result);
		foreach ($result as $antrag)
		{
			if ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG || $antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL)
			{
				if($antrag->status == Studierendenantragstatus_model::STATUS_CREATED)
					return success(-2);
				elseif($antrag->status == Studierendenantragstatus_model::STATUS_APPROVED && $antrag->datum > $datumStatus)
					return success(-2);
			}
			if ($studiensemester_kurzbz && $antrag->typ == Studierendenantrag_model::TYP_UNTERBRECHUNG)
			{
				// NOTE(chris): check if this is an old or canceled one
				if ($antrag->studiensemester_kurzbz == $studiensemester_kurzbz && $antrag->status != Studierendenantragstatus_model::STATUS_CANCELLED)
					return success(-1);
			}
			if ($antrag->typ == Studierendenantrag_model::TYP_WIEDERHOLUNG)
			{
				if($antrag->status == Studierendenantragstatus_model::STATUS_PASS)
					return success(-2);
			}
		}

		return success(1);
	}

	/**
	 * Checks if a prestudent can submit an Antrag for Wiederholung
	 *
	 * @param integer		$prestudent_id
	 *
	 * @return \stdClass	on success retval 0 means not a student; retval 1 means Berechtigt; retval -1 means has already an Antrag pending; retval -2 means other Antrag pending; retval -3 means in blacklist stg
	 */
	public function getPrestudentWiederholungsBerechtigt($prestudent_id)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);
		$result = current(getData($result));
		$stg_kz = $result->studiengang_kz;
		if (in_array($stg_kz, $this->_ci->config->item('stgkz_blacklist_wiederholung')))
			return success(-3);

		$result = $this->getFailedExamForPrestudent($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);

		$result = $this->_ci->PrestudentstatusModel->getLastStatus($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(0);

		$result = current(getData($result));
		$datumStatus = $result->datum;
		if (!in_array($result->status_kurzbz, $this->_ci->config->item('antrag_prestudentstatus_whitelist'))) {
			$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['prestudent_id' => $prestudent_id, 'typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG, 'campus.get_status_studierendenantrag(studierendenantrag_id)' => Studierendenantragstatus_model::STATUS_APPROVED]);
			if (isError($result))
				return $result;
			if (hasData($result))
				return success(-1);

			return success(0);
		}
		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere(['prestudent_id' => $prestudent_id]);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return success(1);
		$result= getData($result);
		foreach ($result as $antrag)
		{
			if ($antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG || $antrag->typ == Studierendenantrag_model::TYP_ABMELDUNG_STGL)
			{
				if($antrag->status == Studierendenantragstatus_model::STATUS_CREATED)
					return success(-2);
				elseif($antrag->status == Studierendenantragstatus_model::STATUS_APPROVED && $antrag->datum > $datumStatus)
					return success(-2);
			}
			if ($antrag->typ == Studierendenantrag_model::TYP_WIEDERHOLUNG)
			{
				return success(-1);
			}
		}

		return success(1);
	}

	/**
	 * Gets details for a new Antrag
	 *
	 * @param integer		$prestudent_id
	 *
	 * @return \stdClass
	 */
	public function getDetailsForNewAntrag($prestudent_id)
	{
		$result = $this->_ci->PrestudentstatusModel->loadLastWithStgDetails($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error('No Studentstatus found for: ' . $prestudent_id);
		$result = current(getData($result));
		return success($result);
	}

	public function getDetailsForLastAntrag($prestudent_id, $typ = null)
	{
		$result = $this->_ci->PrestudentstatusModel->loadLastWithStgDetails($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error('No Studentstatus found for: ' . $prestudent_id);
		$resultDetails = current(getData($result));

		$where = [
			'prestudent_id' => $prestudent_id
		];
		if ($typ)
			$where['typ'] = $typ;
		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere($where);
		if (isError($result))
			return $result;

		$antraege = getData($result) ?: [];
		$resultAntrag = null;
		foreach ($antraege as $antrag) {
			if ($antrag->status != Studierendenantragstatus_model::STATUS_CANCELLED) {
				$resultAntrag = $antrag;
				break;
			}
		}
		if (!$resultAntrag)
			return error('No Antrag ' . trim(($typ ?: '') . ' ') . 'found for: ' . $prestudent_id);

		$resultDetails->status = $resultAntrag->status;
		$resultDetails->statustyp = $resultAntrag->statustyp;
		$resultDetails->grund = $resultAntrag->grund;
		$resultDetails->studierendenantrag_id = $resultAntrag->studierendenantrag_id;
		$resultDetails->typ = $resultAntrag->typ;

		return success($resultDetails);
	}

	public function getDetailsForAntrag($studierendenantrag_id)
	{
		$where = [
			'studierendenantrag_id' => $studierendenantrag_id
		];

		$result = $this->_ci->StudierendenantragModel->loadWithStatusWhere($where);
		if (isError($result))
		return $result;

		if (!hasData($result))
			return error("No Antrag found with id: " . $studierendenantrag_id);
		$resultAntrag = current(getData($result));

		$result = $this->_ci->PrestudentstatusModel->loadLastWithStgDetails($resultAntrag->prestudent_id, $resultAntrag->studiensemester_kurzbz);
		if (isError($result))
			return $result;
		if (!hasData($result)) {
			$result = $this->_ci->PrestudentstatusModel->loadLastWithStgDetails($resultAntrag->prestudent_id);
			if (isError($result))
				return $result;
			if (!hasData($result))
				return error('No Studentstatus found for: ' . $resultAntrag->prestudent_id);
		}
		$resultDetails = current(getData($result));

		$resultDetails->status = $resultAntrag->status;
		$resultDetails->statustyp = $resultAntrag->statustyp;
		$resultDetails->grund = $resultAntrag->grund;
		$resultDetails->studierendenantrag_id = $resultAntrag->studierendenantrag_id;
		$resultDetails->typ = $resultAntrag->typ;
		$resultDetails->dms_id = $resultAntrag->dms_id;
		$resultDetails->datum_wiedereinstieg = $resultAntrag->datum_wiedereinstieg;

		return success($resultDetails);
	}

	public function getSemesterForUnterbrechung($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester)
	{
		$this->_ci->load->model('organisation/Studienplan_model', 'StudienplanModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$result = $this->_ci->StudienplanModel->getStudienplaeneBySemester($studiengang_kz, $studiensemester_kurzbz, $ausbildungssemester);
		if (!hasData($result))
			return [];

		$studienplaene = getData($result);
		$studienplan_ids = array_map(function ($studienplan) {
			return $studienplan->studienplan_id;
		}, $studienplaene);

		$result = $this->_ci->StudiensemesterModel->getFollowingSemester($studienplan_ids, $studiensemester_kurzbz, $ausbildungssemester);
		if (!hasData($result))
			return [];

		$stsems = getData($result);

		$result = $this->_ci->StudiensemesterModel->loadWhere();
		if (!hasData($result))
			return [];
		$result = getData($result);
		usort($result, function($a, $b) {
			return $a->start > $b->start ? 1 : -1;
		});
		foreach ($stsems as $stsem) {
			$stsem->wiedereinstieg = array_filter($result, function ($sem) use ($stsem) {
				return $sem->start > $stsem->ende;
			});
		}

		return $stsems;
	}

	public function getAktivePrestudentenInStgs($studiengaenge, $query)
	{
		$blacklist = $this->_ci->config->item('stgkz_blacklist_abmeldung');
		$studiengaenge = array_diff($studiengaenge, $blacklist);
		return $this->_ci->StudiengangModel->getAktivePrestudenten(
			$studiengaenge,
			[ Studierendenantrag_model::TYP_ABMELDUNG ],
			$query
		);
	}

	public function getFailedExamForPrestudent($prestudent_id)
	{
		return $this->_ci->PruefungModel->loadWhereCommitteeExamFailedForPrestudent($prestudent_id);
	}

	public function saveLvs($lvArray)
	{
		$result = $this->_ci->StudierendenantraglehrveranstaltungModel->deleteWhere(['studierendenantrag_id' => $lvArray[0]['studierendenantrag_id']]);
		if (isError($result))
			return $result;

		$result = $this->_ci->StudierendenantraglehrveranstaltungModel->insertBatch($lvArray);
		if (isError($result))
			return $result;

		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $lvArray[0]['studierendenantrag_id'],
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_LVSASSIGNED,
			'insertvon' => $lvArray[0]['insertvon']
		]);
		if (isError($result))
			return $result;

		$antrag_status_id = getData($result);
		$result = $this->_ci->StudierendenantragstatusModel->loadWithTyp($antrag_status_id);

		return $result;
	}

	public function approveWiederholung($antrag_id, $insertvon)
	{
		$result = $this->_ci->StudierendenantragstatusModel->insert([
			'studierendenantrag_id' => $antrag_id,
			'studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
			'insertvon' => $insertvon
		]);

		if (isError($result)) {
			return $result;
		}

		$result = $this->_ci->StudierendenantragModel->getStgEmail($antrag_id);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error('No studiengang email found for: ' . $antrag_id);

		$email = current($result)->email;

		$result = $this->_ci->StudierendenantragModel->getStgAndSem($antrag_id);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error('No studiengang found for: ' . $antrag_id);

		$result = current($result);
		$studiengang_kz = $result->studiengang_kz;
		$semester = $result->ausbildungssemester;

		$result = $this->_ci->StudiengangModel->load($studiengang_kz);
		if (isError($result))
			return $result;
		$result = getData($result);
		if (!$result)
			return error('No studiengang found for: ' . $antrag_id);

		$stg = current($result);

		$result = $this->_ci->StudierendenantragModel->load($antrag_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error('No Antrag found for: ' . $antrag_id);
		$result = current(getData($result));
		$prestudent_id = $result->prestudent_id;

		$result = $this->_ci->PersonModel->loadPrestudent($prestudent_id);
		if (isError($result))
			return $result;
		if (!hasData($result))
			return error('No Person found for prestudent: ' . $prestudent_id);
		$result = current(getData($result));
		$student = trim($result->vorname . ' ' . $result->nachname);

		$result = $this->_ci->PersonModel->getFullName($insertvon);
		if (isError($result))
			return $result;
		$mitarbeiter = $insertvon;
		if (hasData($result)) {
			$mitarbeiter = getData($result);
		}


		// NOTE(chris): Sancho mail
		if (!sendSanchoMail(
			'Sancho_Mail_Antrag_W_Approve',
			[
				'antrag_id' => $antrag_id,
				'stg' => $stg->bezeichnung,
				'sem' => $semester,
				'student' => $student,
				'mitarbeiter' => $mitarbeiter
			],
			$email,
			'Wiederholung von Stgleitung freigegeben'
		))
			return error('Email konnte nicht versendet werden an '. $email);

		return success();
	}

	public function getAntragHistory($antrag_id)
	{
		$result = $this->_ci->StudierendenantragstatusModel->loadWithTypWhere([
			'studierendenantrag_id' => $antrag_id
		]);
		return $result;
	}


	/**
	 * @param integer		$studierendenantrag_id
	 *
	 * @return boolean
	 */
	protected function isOwnAntrag($studierendenantrag_id)
	{
		if ($studierendenantrag_id == null)
			return false;
		$result = $this->_ci->StudierendenantragModel->loadForPerson(getAuthPersonId());
		if (!hasData($result))
			return false;
		$antraege = array_map(function ($antrag) {
			return $antrag->studierendenantrag_id;
		}, getData($result));

		return in_array($studierendenantrag_id, $antraege);
	}

	/**
	 * @param integer		$studierendenantrag_id
	 * @param string		$permission either 'student/antragfreigabe' or 'student/studierendenantrag'
	 *
	 * @return boolean
	 */
	protected function hasAccessToAntrag($studierendenantrag_id, $permission)
	{
		$studiengaenge = $this->_ci->permissionlib->getSTG_isEntitledFor($permission);
		if (!$studiengaenge)
			return false;
		$result = $this->_ci->StudierendenantragModel->isInStudiengang($studierendenantrag_id, $studiengaenge);
		return (boolean)getData($result);
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToShowAntrag($antrag_id)
	{
		return
		(
			$this->isOwnAntrag($antrag_id) ||
			$this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe') ||
			$this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag')
		);
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToSeeHistoryForAntrag($antrag_id)
	{
		return
		(
			$this->isOwnAntrag($antrag_id) ||
			$this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe') ||
			$this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag')
		);
	}

	/**
	 * @param integer		$prestudent_id
	 * @param boolean		$checkAssistencePermission
	 *
	 * @return boolean
	 */
	public function isEntitledToCreateAntragFor($prestudent_id, $checkAssistencePermission = false)
	{
		$result = $this->_ci->PrestudentModel->load($prestudent_id);
		if (!hasData($result))
			return false;

		$result = getData($result)[0];
		$person_id = $result->person_id;

		if (getAuthPersonId() == $person_id)
			return true;

		if ($checkAssistencePermission)
		{
			$studiengaenge = $this->_ci->permissionlib->getSTG_isEntitledFor('student/studierendenantrag');
			if (in_array($result->studiengang_kz, $studiengaenge ?: []))
				return true;
		}

		return false;
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToCancelAntrag($antrag_id)
	{
		return $this->isOwnAntrag($antrag_id);
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToReopenAntrag($antrag_id)
	{
		return $this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag');
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToObjectAntrag($antrag_id)
	{
		return ($this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe') || $this->hasAccessToAntrag($antrag_id, 'student/studierendenantrag'));
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToApproveAntrag($antrag_id)
	{
		return $this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe');
	}

	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isEntitledToRejectAntrag($antrag_id)
	{
		return $this->hasAccessToAntrag($antrag_id, 'student/antragfreigabe');
	}

	/**
	 * @param integer		$antrag_id
	 * @param string|array	$status
	 *
	 * @return boolean
	 */
	public function hasStatus($antrag_id, $status)
	{
		$result = $this->_ci->StudierendenantragModel->getWithLastStatusWhere(['s.studierendenantrag_id' => $antrag_id]);
		if (!hasData($result))
			return false;
		$lastStatus = getData($result)[0];

		if (!is_array($status))
			$status = [$status];

		return in_array($lastStatus->studierendenantrag_statustyp_kurzbz, $status);
	}

	/**
	 * @param integer		$antrag_id
	 * @param string|array	$type
	 *
	 * @return boolean
	 */
	public function hasType($antrag_id, $type)
	{
		$result = $this->_ci->StudierendenantragModel->load($antrag_id);
		if (!hasData($result))
			return false;
		$antrag = getData($result)[0];

		if (!is_array($type))
			$type = [$type];

		return in_array($antrag->typ, $type);
	}


	/**
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function getLvsForPrestudent($prestudent_id, $studiensemester_kurzbz)
	{
		$result = $this->_ci->StudierendenantraglehrveranstaltungModel->getLvsForPrestudent($prestudent_id, $studiensemester_kurzbz);
		return $result;
	}
}
