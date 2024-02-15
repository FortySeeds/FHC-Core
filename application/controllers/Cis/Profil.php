<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 */
class Profil extends Auth_Controller
{

	/**
	 * Constructor
	 */

	public function __construct()
	{
		parent::__construct([
			'index' => ['student/anrechnung_beantragen:r', 'user:r'], 
			'foto_sperre_function' => ['student/anrechnung_beantragen:r', 'user:r'],
			'getView' => ['student/anrechnung_beantragen:r', 'user:r'],
			'View' => ['student/anrechnung_beantragen:r', 'user:r'],
			'isMitarbeiter' => ['student/anrechnung_beantragen:r', 'user:r'],
			'isStudent' => ['student/anrechnung_beantragen:r', 'user:r'],
			'getZustellAdresse' => ['student/anrechnung_beantragen:r', 'user:r'],
			'getZustellKontakt' => ['student/anrechnung_beantragen:r', 'user:r'],
			
		]);

		

		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');
		$this->load->model('ressource/Betriebsmittelperson_model', 'BetriebsmittelpersonModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		$this->load->model('person/Profil_update_model', 'ProfilUpdateModel');
		$this->load->model('content/DmsVersion_model', 'DmsVersionModel');


		//? put the uid and pid inside the controller for reusability
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();
		


	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */

	public function index()
	{
		$this->load->view('Cis/Profil');
	}
	public function View($uid)
	{

		if ($uid === $this->uid) {
			$this->index();
		} else {
			$this->load->view('Cis/Profil');
		}

	}

	//? foreward declaration of the function isStudent in Student_model.php
	public function isStudent($uid){
		$result = $this->StudentModel->isStudent($uid);
		if(!isSuccess($result)){
			show_error("error when calling Student_model function isStudent with uid ".$uid);
		}
		$result = getData($result);
		echo json_encode($result);
	}

	//? foreward declaration of the function isMitarbeiter in Mitarbeiter_model.php
	public function isMitarbeiter($uid){
		$result = $this->MitarbeiterModel->isMitarbeiter($uid);
		if(!isSuccess($result)){
			show_error("error when calling Mitarbeiter_model function isMitarbeiter with uid ".$uid);
		}
		$result = getData($result);
		echo json_encode($result);
	}

	public function getZustellAdresse(){
		$this->AdresseModel->addSelect(["adresse_id"]);
		$adressen_res = $this->AdresseModel->loadWhere(['person_id'=>$this->pid, 'zustelladresse'=>true]);
		$adressen_res = hasData($adressen_res) ? getData($adressen_res): null;
		$adressen_res = array_map(function($item){
			return $item->adresse_id;
		},$adressen_res);
		echo json_encode($adressen_res);
	}

	public function getZustellKontakt(){
		$this->KontaktModel->addSelect(["kontakt_id"]);
		$kontakt_res = $this->KontaktModel->loadWhere(['person_id'=>$this->pid, 'zustellung'=>true]);
		$kontakt_res = hasData($kontakt_res) ? getData($kontakt_res): null;
		$kontakt_res = array_map(function($item){
			return $item->kontakt_id;
		},$kontakt_res);
		echo json_encode($kontakt_res);
	}


	private function viewMitarbeiterProfil($uid)
	{
		$mailverteiler_res = $this->getMailverteiler($uid);
		$benutzer_funktion_res = $this->getBenutzerFunktion($uid);
		$benutzer_res = $this->getBenutzerAlias($uid);
		$person_res = $this->getPersonInfo($uid);
		$mitarbeiter_res = $this->getMitarbeiterInfo($uid);
		$telefon_res = $this->getTelefonInfo($uid);

		$res = new stdClass();
		$res->username = $uid;

		//? Person Info
		foreach($person_res as $key => $val){
			$res->$key = $val;
		}
	
		//? Mitarbeiter Info
		foreach ($mitarbeiter_res as $key => $val) {
			$res->$key = $val;

		}

		$intern_email = array();
		$intern_email["type"] = "intern";
		$intern_email["email"] = $uid . "@" . DOMAIN;
		$extern_email = array();
		$extern_email["type"] = "alias";
		$extern_email["email"] = $benutzer_res->alias . "@" . DOMAIN;
		$res->emails = array($intern_email, $extern_email);

		$res->funktionen = $benutzer_funktion_res;
		$res->mailverteiler = $mailverteiler_res;
		$res->standort_telefon = isset($telefon_res)? $telefon_res->kontakt : null;

		return $res;
	}



	private function viewStudentProfil($uid)
	{
		$mailverteiler_res = $this->getMailverteiler($uid);
		$person_res = $this->getPersonInfo($uid);
		$student_res = $this->getStudentInfo($uid);
		$matr_res = $this->getMatrikelNummer($uid);

		$res = new stdClass();
		$res->username = $uid;

		//? Person Information
		foreach($person_res as $key => $value){
			$res->$key = $value;
		}

		//? Student Information
		foreach ($student_res as $key => $value) {
			$res->$key = $value;
		}

		$intern_email = array();
		$intern_email["type"] = "intern";
		$intern_email["email"] = $uid . "@" . DOMAIN;

		$res->emails = [$intern_email];
		$res->matrikelnummer = $matr_res->matr_nr;
		$res->mailverteiler = $mailverteiler_res;

		return $res;
	}

	private function mitarbeiterProfil()
	{

		$zutrittskarte_ausgegebenam = $this->getZutrittskarteDatum($this->uid);
		$adresse_res = $this->getAdressenInfo($this->pid);
		$kontakte_res = $this->getKontaktInfo($this->pid);
		$mailverteiler_res = $this->getMailverteiler($this->uid);
		$person_res = $this->getPersonInfo($this->uid,true);
		$benutzer_funktion_res = $this->getBenutzerFunktion($this->uid);
		$betriebsmittelperson_res = $this->getBetriebsmittelInfo($this->pid);
		$profilUpdates = $this->getProfilUpdates($this->uid);
		$telefon_res = $this->getTelefonInfo($this->uid);
		$mitarbeiter_res = $this->getMitarbeiterInfo($this->uid);

		$res = new stdClass();
		$res->username = $this->uid;

		//? Person Information
		foreach($person_res as $key => $value){
			$res->$key = $value;
		}

		//? Mitarbeiter Information
		foreach ($mitarbeiter_res as $key => $value) {
			$res->$key = $value;
		}

		$res->adressen = $adresse_res;
		$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
		$res->kontakte = $kontakte_res;
		$res->mittel = $betriebsmittelperson_res;
		$res->mailverteiler = $mailverteiler_res;
		
		$intern_email = array();
		$intern_email["type"] = "intern";
		$intern_email["email"] = $this->uid . "@" . DOMAIN;
		$extern_email = array();
		$extern_email["type"] = "alias";
		$extern_email["email"] = $mitarbeiter_res->alias . "@" . DOMAIN;
		$res->emails = [$intern_email, $extern_email];

		$res->funktionen = $benutzer_funktion_res;
		$res->standort_telefon = $telefon_res;
		$res->profilUpdates = $profilUpdates;

		return $res;
	}


	private function studentProfil()
	{
		$betriebsmittelperson_res = $this->getBetriebsmittelInfo($this->pid);
		$kontakte_res = $this->getKontaktInfo($this->pid);
		$zutrittskarte_ausgegebenam = $this->getZutrittskarteDatum($this->uid);
		$adresse_res = $this->getAdressenInfo($this->pid);
		$mailverteiler_res = $this->getMailverteiler($this->uid);
		$person_res = $this->getPersonInfo($this->uid, true);
		$zutrittsgruppe_res = $this->getZutrittsgruppen($this->uid);
		$student_res = $this->getStudentInfo($this->uid);
		$matr_res = $this->getMatrikelNummer($this->uid);
		$profilUpdates = $this->getProfilUpdates($this->uid);

		$res = new stdClass();
		$res->username = $this->uid;

		//? Person Information
		foreach($person_res as $key => $value){
			$res->$key = $value;
		}

		//? Student Information
		foreach ($student_res as $key => $value) {
			$res->$key = trim($value);
		}

		$intern_email = array();
		$intern_email["type"] = "intern";
		$intern_email["email"] = $this->uid . "@" . DOMAIN;

		$res->emails = [$intern_email];
		$res->adressen = $adresse_res;
		$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
		$res->kontakte = $kontakte_res;
		$res->mittel = $betriebsmittelperson_res;
		$res->matrikelnummer = $matr_res->matr_nr;
		$res->zuttritsgruppen = $zutrittsgruppe_res;
		$res->mailverteiler = $mailverteiler_res;
		$res->profilUpdates = $profilUpdates;
	
		return $res;
	}

	public function getView($uid)
	{
		//TODO: refactor
		$uid = $uid != "Profil" ? $uid : null;

		$isMitarbeiter = null;
		if ($uid) {
			if (isSuccess($this->PersonModel->addSelect(["person_id"]))) {
				$pid = $this->PersonModel->getByUid($uid);
				$pid = hasData($pid) ? getData($pid)[0] : null;
			}
			if (!$pid) {
				//! if no Person_ID was found, null is returned and the vue component will show a 404 View
				return null;
			}
			$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter($uid);
		} else
			$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter($this->uid);

		if (isError($isMitarbeiter)) {
			//catch error
		}
		$isMitarbeiter = hasData($isMitarbeiter) ? getData($isMitarbeiter) : null;

		$res = new stdClass();

		if ($uid == $this->uid || !$uid) {
			// if the $uid is empty, then no payload was supplied and the own profile is being requested
			if ($isMitarbeiter) {
				$res->view = "MitarbeiterProfil";
				$res->data = $this->mitarbeiterProfil();
				$res->data->pid = $this->pid;
			} else {
				$res->view = "StudentProfil";
				$res->data = $this->studentProfil();
				$res->data->pid = $this->pid;
			}
		} elseif ($uid) {
			// if an $uid was passed as payload to the function then the user is trying to view another profile
			if ($isMitarbeiter) {
				$res->view = "ViewMitarbeiterProfil";
				$res->data = $this->viewMitarbeiterProfil($uid);

			} else {
				$res->view = "ViewStudentProfil";
				$res->data = $this->viewStudentProfil($uid);

			}
		}
		echo json_encode($res);

	}

	public function foto_sperre_function($value) //TODO: refactor function
	{
		//? Nur der Index User hat die Erlaubniss das Profilbild zu sperren 
		$res = $this->PersonModel->update($this->pid, ["foto_sperre" => $value]);

		if (isError($res)) {
			echo json_encode("error encountered when updating foto_sperre");
			return;
			// error handling
		} else {
			//? select the value of the column foto_sperre to return 
			if (isSuccess($this->PersonModel->addSelect("foto_sperre"))) {
				$res = $this->PersonModel->load($this->pid);
				if (isError($res)) {
					// error handling
				}
				$res = hasData($res) ? getData($res)[0] : null;
			}

		}
		echo json_encode($res);
	}




	//? queries the Mailverteiler of a benutzer
	private function getMailverteiler($uid){
		$mailverteiler_res = null;
		if (
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
			isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))
		) {

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid' => $uid));
			if (isError($mailverteiler_res)) {
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res) ? getData($mailverteiler_res) : null;

			$mailverteiler_res = array_map(function ($element) {
				$element->mailto = "mailto:" . $element->gruppe_kurzbz . "@" . DOMAIN;
				return $element; }, $mailverteiler_res);
		}

		
		return $mailverteiler_res;
	}

	private function getBenutzerFunktion($uid){
		$benutzer_funktion_res = null;
		if (
			//! Summe der Wochenstunden wird jetzt in der hr/tbl_dienstverhaeltnis gespeichert
			isSuccess($this->BenutzerfunktionModel->addSelect(["tbl_benutzerfunktion.bezeichnung as Bezeichnung", "tbl_organisationseinheit.bezeichnung as Organisationseinheit", "datum_von as Gültig_von", "datum_bis as Gültig_bis", "wochenstunden as Wochenstunden"])) &&
			isSuccess($this->BenutzerfunktionModel->addJoin("tbl_organisationseinheit", "oe_kurzbz"))
		) {
			$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere(array('uid' => $uid));
			if (isError($benutzer_funktion_res)) {
				// error handling
			} else {
				$benutzer_funktion_res = hasData($benutzer_funktion_res) ? getData($benutzer_funktion_res) : null;
			}
		}
		
		return $benutzer_funktion_res;
	}

	private function getBetriebsmittelInfo($pid){
		$betriebsmittelperson_res = null;
		if (

			isSuccess($this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel", "nummer as Nummer", "ausgegebenam as Ausgegeben_am"]))

		) {
			//? betriebsmittel are not needed in a view
			$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel($pid);
			if (isError($betriebsmittelperson_res)) {
				// error handling
			} else {
				$betriebsmittelperson_res = hasData($betriebsmittelperson_res) ? getData($betriebsmittelperson_res) : null;
			}
		}
		return $betriebsmittelperson_res;
	}

	private function getBenutzerAlias($uid){
		$benutzer_res = null;
		if (isSuccess($this->BenutzerModel->addSelect(["alias"]))) {
			$benutzer_res = $this->BenutzerModel->load([$uid]);
			if (isError($benutzer_res)) {
				// error handling
			} else {
				$benutzer_res = hasData($benutzer_res) ? getData($benutzer_res)[0] : null;
			}
		}
		return $benutzer_res;
	}

	private function getPersonInfo($uid, $geburtsInfo = null){
		
		$person_res = null;
		$selectClause = ["foto", "anrede", "titelpost as postnomen", "titelpre as titel", "vorname", "nachname"];
		//? $geburtsInfo flag checks whether gebort and gebdatum should also be added to the query
		if($geburtsInfo){
			array_push($selectClause,"gebort");
			array_push($selectClause,"gebdatum");
			array_push($selectClause,"foto_sperre");
		}
		if (
			isSuccess($this->BenutzerModel->addSelect($selectClause))
			&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))
		) {

			$person_res = $this->BenutzerModel->load([$uid]);
			if (isError($person_res)) {
				// error handling
			} else {
				$person_res = hasData($person_res) ? getData($person_res)[0] : null;
			}
		}
		return $person_res;
	}

	private function getMitarbeiterInfo($uid){
		$mitarbeiter_res = null;
		if (
			isSuccess($this->MitarbeiterModel->addSelect(["kurzbz", "telefonklappe", "alias", "ort_kurzbz"]))
			&& isSuccess($this->MitarbeiterModel->addJoin("tbl_benutzer", "tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid"))
		) {
			$mitarbeiter_res = $this->MitarbeiterModel->load($uid);
			if (isError($mitarbeiter_res)) {
				// error handling
			} else {
				$mitarbeiter_res = hasData($mitarbeiter_res) ? getData($mitarbeiter_res)[0] : null;
			}
		}
		return $mitarbeiter_res;
	}

	private function getTelefonInfo($uid){
		$telefon_res = null;
		if (
			isSuccess($this->MitarbeiterModel->addSelect(["kontakt"])) &&
			isSuccess($this->MitarbeiterModel->addJoin("tbl_kontakt", "tbl_mitarbeiter.standort_id = tbl_kontakt.standort_id"))


		) {
			$this->MitarbeiterModel->addLimit(1);
			$telefon_res = $this->MitarbeiterModel->loadWhere(["mitarbeiter_uid" => $uid, "kontakttyp" => "telefon"]);
			if (isError($telefon_res)) {
				// error handling
			} else {
				$telefon_res = hasData($telefon_res) ? getData($telefon_res)[0] : null;
			}
		}
		return $telefon_res;

		
	}


	private function getStudentInfo($uid){

		$student_res = null;

		//? personenkennzeichen ist die Spalte Matrikelnr in der Tabelle Student
		if (
			isSuccess($this->StudentModel->addSelect(['tbl_studiengang.bezeichnung as studiengang', 'tbl_student.semester', 'tbl_student.verband', 'tbl_student.gruppe', 'tbl_student.matrikelnr as personenkennzeichen']))
			&& isSuccess($this->StudentModel->addJoin('tbl_studiengang', "tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz"))
		) {
			$student_res = $this->StudentModel->load([$uid]);
			if (isError($student_res)) {
				// catch error
			}
			$student_res = hasData($student_res) ? getData($student_res)[0] : null;

		}

		return $student_res;
	}


	private function getProfilUpdates($uid){
		$profilUpdates = null;
		$profilUpdates = $this->ProfilUpdateModel->getProfilUpdatesWhere(['uid'=>$uid]);
		if(isError($profilUpdates)){
			//error handling
		}else{
			//? array containing all the requested profil information changes from the current user
			$profilUpdates = hasData($profilUpdates) ? getData($profilUpdates) : null;
			
		} 
		return $profilUpdates;
	}

	private function getMatrikelNummer($uid){
		$matr_res = null;
		if (
			isSuccess($this->BenutzerModel->addSelect(["matr_nr"]))
			&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))
		) {
			$matr_res = $this->BenutzerModel->load([$uid]);
			if (isError($matr_res)) {
				// catch error
			} else {
				$matr_res = hasData($matr_res) ? getData($matr_res)[0] : [];

			}
		}
		return $matr_res;
	}

	private function getZutrittsgruppen($uid){
		$zutrittsgruppe_res = null;
		if (

			isSuccess($this->BenutzergruppeModel->addSelect(['bezeichnung']))
			&& isSuccess($this->BenutzergruppeModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))
		) {
			$zutrittsgruppe_res = $this->BenutzergruppeModel->loadWhere(array("uid" => $uid, "zutrittssystem" => true));
			if (isError($zutrittsgruppe_res)) {
				// catch error
			}
			$zutrittsgruppe_res = hasData($zutrittsgruppe_res) ? getData($zutrittsgruppe_res) : null;

		}
		return $zutrittsgruppe_res;
	}

	private function getAdressenInfo($pid){
		$adresse_res = null;
		if (

			isSuccess($adresse_res = $this->AdresseModel->addSelect(["adresse_id","strasse", "tbl_adressentyp.bezeichnung as typ", "plz", "ort","zustelladresse"])) &&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("zustelladresse", "DESC")) &&
			isSuccess($adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp", "typ=adressentyp_kurzbz"))
		) {
			$adresse_res = $this->AdresseModel->loadWhere(array("person_id" => $pid));
			if (isError($adresse_res)) {
				// error handling
			} else {
				$adresse_res = hasData($adresse_res) ? getData($adresse_res) : null;
			}
		}
		
		return $adresse_res;
	}

	private function getKontaktInfo($pid){
		$kontakte_res = null;
		if (

			//? kontaktdaten soll auch nur der user selbst sehen
			//DISTINCT ON (kontakttyp)
			isSuccess($this->KontaktModel->addSelect(['kontakttyp','kontakt_id','kontakt', 'tbl_kontakt.anmerkung', 'tbl_kontakt.zustellung'])) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum'))
		) {
			$kontakte_res = $this->KontaktModel->loadWhere(array('person_id' => $pid));
			if (isError($kontakte_res)) {
				// handle error	
			} else {
				$kontakte_res = hasData($kontakte_res) ? getData($kontakte_res) : null;
				
				
			}

		}
		
		return $kontakte_res;
	}

	private function getZutrittskarteDatum($uid){
		$zutrittskarte_ausgegebenam = null;
		$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid($uid, "Zutrittskarte");
		if (isError($zutrittskarte_ausgegebenam)) {
			// error handling
		} else {
			$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam) ? getData($zutrittskarte_ausgegebenam)[0]->ausgegebenam : null;
			//? formats the date from 01-01-2000 to 01.01.2000
			$zutrittskarte_ausgegebenam = str_replace("-", ".", $zutrittskarte_ausgegebenam);
		}

		
		return $zutrittskarte_ausgegebenam;
	}

	

}