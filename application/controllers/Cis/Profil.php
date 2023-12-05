<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

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
			'index' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'View' => ['student/anrechnung_beantragen:r','user:r'],

			'isMitarbeiterOrStudent' => ['student/anrechnung_beantragen:r','user:r'],
			'getMitarbeiterAnsicht' => ['student/anrechnung_beantragen:r','user:r'],
			'foto_sperre_function' => ['student/anrechnung_beantragen:r','user:r'],
			'indexProfilInformaion' => ['student/anrechnung_beantragen:r','user:r'],
			'mitarbeiterProfil' => ['student/anrechnung_beantragen:r','user:r'],
			'studentProfil' => ['student/anrechnung_beantragen:r','user:r'],
			'getView' => ['student/anrechnung_beantragen:r','user:r'],
			
			
			
			
			
			
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
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		
		
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */

	
	public function index($test=null)
	{
		
		//? we can pass data to the view by using the second parameter of the load->view() function 
		//* the first parameter is the route that Code Igniter will switch to
		//* the second parameter can be used to pass data to the view  
		$this->load->view('Cis/Profil');
	}

	//? attempt at rerouting methods
	/* public function _remap($method, $params = array())

	{
		echo json_encode($params);
      
			
			
				echo json_encode($method);
				return;
			
		 }else{
			if (method_exists($this, $method))
			{
					return call_user_func_array(array($this, $method), $params);
			}
		} 
	} */
	
	public function View($uid){
		
		if($uid === getAuthUID()){
			$this->index();
		}else{
			$this->load->view('Cis/Profil');
		}
		
	}



	private function viewMitarbeiterProfil($uid){

		
		if(
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung'))&&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) && 
		isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
		isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))){

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid'=>$uid));
			if( isError($mailverteiler_res)){
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res)? getData($mailverteiler_res) : null;
			
			$mailverteiler_res = array_map(function($element) { $element->mailto="mailto:".$element->gruppe_kurzbz."@".DOMAIN; return $element;},$mailverteiler_res);
		}

	
		

		if(
			//! Summe der Wochenstunden wird jetzt in der hr/tbl_dienstverhaeltnis gespeichert
		 isSuccess($this->BenutzerfunktionModel->addSelect(["tbl_benutzerfunktion.bezeichnung as Bezeichnung","tbl_organisationseinheit.bezeichnung as Organisationseinheit","datum_von as Gültig_von","datum_bis as Gültig_bis","wochenstunden as Wochenstunden"]))&&
	  	  isSuccess($this->BenutzerfunktionModel->addJoin("tbl_organisationseinheit","oe_kurzbz"))
		){
			$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere(array('uid'=>$uid));
			if(isError($benutzer_funktion_res)){
				// error handling
			}else{
				$benutzer_funktion_res = hasData($benutzer_funktion_res)? getData($benutzer_funktion_res) : null;
			}
		}

		if(isSuccess($this->BenutzerModel->addSelect(["alias"]))){
		$benutzer_res = $this->BenutzerModel->load([$uid]);
		if(isError($benutzer_res)){
			// error handling
		}else{
			$benutzer_res = hasData($benutzer_res)? getData($benutzer_res)[0] : null;
		}
		}

		if(isSuccess($this->BenutzerModel->addSelect(["foto","foto_sperre","anrede","titelpost","titelpre","vorname","nachname"]))
		&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))){

			$person_res = $this->BenutzerModel->load([$uid]);
			if(isError($person_res)){
				// error handling
			}else{
				$person_res = hasData($person_res)? getData($person_res)[0] : null;
			}
		}


		if(isSuccess($this->MitarbeiterModel->addSelect(["kurzbz","telefonklappe", "alias","ort_kurzbz"]))
			&& isSuccess($this->MitarbeiterModel->addJoin("tbl_benutzer", "tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid")))
		{
			$mitarbeiter_res = $this->MitarbeiterModel->load($uid);
			if(isError($mitarbeiter_res)){
				// error handling
			}else{
				$mitarbeiter_res = hasData($mitarbeiter_res)? getData($mitarbeiter_res)[0] : null;
			}
		}

		
		$res = new stdClass();
		$res->username = $uid;


		//? Person Info
		$res->foto = $person_res->foto;
		$res->foto_sperre = $person_res->foto_sperre;
		
		$res->anrede = $person_res->anrede;
		$res->titelpre = $person_res->titelpre;
		$res->titelpost = $person_res->titelpost;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;
		
		
		//? Mitarbeiter Info
		foreach($mitarbeiter_res as $key => $val){
			$res->$key = $val;

		}
		//? Email Info 
		$intern_email = array();
		$intern_email+=array("type" => "intern");
		$intern_email+=array("email"=> $uid . "@" . DOMAIN);
		$extern_email=array();
		$extern_email+=array("type" => "alias");
		$extern_email+=array("email" => $benutzer_res->alias . "@" . DOMAIN);
		$res->emails = array($intern_email,$extern_email);
	
		//? Benutzerfunktion Info
		$res->funktionen = $benutzer_funktion_res;
	
		//? Mailverteiler Info
		$res->mailverteiler = $mailverteiler_res;
		 
		return $res;

	}


	
	private function viewStudentProfil($uid){
		


		if(
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung'))&&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) && 
		isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
		isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))){

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid'=>$uid));
			if( isError($mailverteiler_res)){
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res)? getData($mailverteiler_res) : null;
			
			$mailverteiler_res = array_map(function($element) { $element->mailto="mailto:".$element->gruppe_kurzbz."@".DOMAIN; return $element;},$mailverteiler_res);
		}

		
		
		if(isSuccess($this->BenutzerModel->addSelect(["foto","foto_sperre","anrede","titelpost","titelpre","vorname","nachname"]))
		&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))){

			$person_res = $this->BenutzerModel->load([$uid]);
			if(isError($person_res)){
				// error handling
			}else{
				$person_res = hasData($person_res)? getData($person_res)[0] : null;
			}
		}
		

		//? personenkennzeichen ist die Spalte Matrikelnr in der Tabelle Student
		if(isSuccess($this->StudentModel->addSelect(['tbl_studiengang.bezeichnung as studiengang','tbl_student.semester', 'tbl_student.verband', 'tbl_student.gruppe' ,'tbl_student.matrikelnr as personenkennzeichen']))
		 && isSuccess($this->StudentModel->addJoin('tbl_studiengang', "tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz")))
		{
			$student_res = $this->StudentModel->load([$uid]);
			if(isError($student_res)){
				// catch error
			}
			$student_res = hasData($student_res)?getData($student_res)[0]:null;
		
		}
		

		
		//? Matrikelnummer ist die Spalte matr_nr in Person
		if(isSuccess($this->BenutzerModel->addSelect(["matr_nr"])) 
		&& isSuccess($this->BenutzerModel->addJoin("tbl_person","person_id"))){
			$matr_res = $this->BenutzerModel->load([$uid]);
			if(isError($matr_res)){
				// catch error
			}else{
				$matr_res = hasData($matr_res)? getData($matr_res)[0] : [];
				
			}
		}

		$res = new stdClass();
		
		
		$res->foto = $person_res->foto;
		$res->username = $uid;
		
		$res->anrede = $person_res->anrede;
		$res->titel = $person_res->titelpre;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;
	
	
		$res->postnomen = $person_res->titelpost; 
		
		$intern_email = array();
			$intern_email+=array("type" => "intern");
			$intern_email+=array("email"=> getAuthUID() . "@" . DOMAIN);
		
			$res->emails = array($intern_email);
	
	
		
		
		$res->matrikelnummer = $matr_res->matr_nr;
		foreach($student_res as $key => $value){
			$res->$key = $value;
		}

	
		
		$res->mailverteiler = $mailverteiler_res;
		
		return $res;
		
		 
	}

	private function mitarbeiterProfil(){
		

			//? betriebsmittel soll nur der user selber sehen
			if(
			
				isSuccess($this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel","nummer as Nummer","ausgegebenam as Ausgegeben_am"]))
				
			){
				//? betriebsmittel are not needed in a view
				$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel(getAuthPersonId());
				if(isError($betriebsmittelperson_res)){
					   // error handling
				   }else{
					   $betriebsmittelperson_res = hasData($betriebsmittelperson_res)? getData($betriebsmittelperson_res) : null;
				   }
			   }

			   if(
			
				//? kontaktdaten soll auch nur der user selbst sehen
				isSuccess($this->KontaktModel->addSelect('DISTINCT ON (kontakttyp) kontakttyp, kontakt, tbl_kontakt.anmerkung, tbl_kontakt.zustellung')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT'))&&
			isSuccess($this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum'))
			){
				$kontakte_res = $this->KontaktModel->loadWhere(array('person_id' => getAuthPersonID()));
				if(isError($kontakte_res)){
					// handle error	
				}else{
					$kontakte_res = hasData($kontakte_res)? getData($kontakte_res) : null;
				}
				
			}

			//? FH Ausweis Austellungsdatum soll auch nur der user selbst sehen
			$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid(getAuthUID(),"Zutrittskarte");
			if(isError($zutrittskarte_ausgegebenam)){
				// error handling
			}else{
				$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam)? getData($zutrittskarte_ausgegebenam)[0]->ausgegebenam : null;
				//? formats the date from 01-01-2000 to 01.01.2000
				$zutrittskarte_ausgegebenam = str_replace("-",".",$zutrittskarte_ausgegebenam);
			}


			//? Die Adressen soll auch nur der user selber sehen

			if(
				
				isSuccess($adresse_res = $this->AdresseModel->addSelect(array("strasse","tbl_adressentyp.bezeichnung as adr_typ","plz","ort")))&&
				isSuccess($adresse_res = $this->AdresseModel->addOrder("zustelladresse","DESC"))&&
				isSuccess($adresse_res = $this->AdresseModel->addOrder("sort"))&&
				isSuccess($adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp","typ=adressentyp_kurzbz"))
			){
				$adresse_res = $this->AdresseModel->loadWhere(array("person_id"=>getAuthPersonID()));
				if(isError($adresse_res)){
					// error handling
				}else{									
					$adresse_res = hasData($adresse_res)? getData($adresse_res) : null;
				}
			}

			//? die folgenden Informationen darf nur der eigene user sehen
		

		
		

		if(
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung'))&&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) && 
		isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
		isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))){

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid'=>getAuthUID()));
			if( isError($mailverteiler_res)){
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res)? getData($mailverteiler_res) : null;
			
			$mailverteiler_res = array_map(function($element) { $element->mailto="mailto:".$element->gruppe_kurzbz."@".DOMAIN; return $element;},$mailverteiler_res);
		}

		
		
		if(isSuccess($this->BenutzerModel->addSelect(["foto","foto_sperre","anrede","titelpost","titelpre","vorname","nachname", "gebort", "gebdatum"]))
		&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))){

			$person_res = $this->BenutzerModel->load([getAuthUID()]);
			if(isError($person_res)){
				// error handling
			}else{
				$person_res = hasData($person_res)? getData($person_res)[0] : null;
			}
		}


		if(
			//! Summe der Wochenstunden wird jetzt in der hr/tbl_dienstverhaeltnis gespeichert
		isSuccess($this->BenutzerfunktionModel->addSelect(["tbl_benutzerfunktion.bezeichnung as Bezeichnung","tbl_organisationseinheit.bezeichnung as Organisationseinheit","datum_von as Gültig_von","datum_bis as Gültig_bis","wochenstunden as Wochenstunden"]))&&
			isSuccess($this->BenutzerfunktionModel->addJoin("tbl_organisationseinheit","oe_kurzbz"))
		){
			$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere(array('uid'=>getAuthUID()));
			if(isError($benutzer_funktion_res)){
				// error handling
			}else{
				$benutzer_funktion_res = hasData($benutzer_funktion_res)? getData($benutzer_funktion_res) : null;
			}
		}
	
	
		
	
		if(isSuccess($this->MitarbeiterModel->addSelect(["kurzbz","telefonklappe", "alias","ort_kurzbz"]))
			&& isSuccess($this->MitarbeiterModel->addJoin("tbl_benutzer", "tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid"))
		){
			$mitarbeiter_res = $this->MitarbeiterModel->load(getAuthUID());
				if(isError($mitarbeiter_res)){
					// error handling
				}else{
					$mitarbeiter_res = hasData($mitarbeiter_res)? getData($mitarbeiter_res)[0] : null;
				}
			}
		
			$res = new stdClass();
			$res->foto = $person_res->foto;
			$res->foto_sperre = $person_res->foto_sperre;
			$res->username = getAuthUID();
			
			$res->anrede = $person_res->anrede;
			$res->titel = $person_res->titelpre;
			$res->vorname = $person_res->vorname;
			$res->nachname = $person_res->nachname;
			
			$res->gebort = $person_res->gebort;
			$res->gebdatum = $person_res->gebdatum;
		
			$res->postnomen = $person_res->titelpost; 
			
			
			$res->adressen = $adresse_res;
			$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
			$res->kontakte = $kontakte_res;
			$res->mittel = $betriebsmittelperson_res;
			
			$res->mailverteiler = $mailverteiler_res;
			
			foreach($mitarbeiter_res as $key => $value){
				$res->$key = $value;
			}
			$intern_email = array();
			$intern_email+=array("type" => "intern");
			$intern_email+=array("email"=> getAuthUID() . "@" . DOMAIN);
			$extern_email=array();
			$extern_email+=array("type" => "alias");
			$extern_email+=array("email" => $mitarbeiter_res->alias . "@" . DOMAIN);
			$res->emails = array($intern_email,$extern_email);
			
			$res->funktionen = $benutzer_funktion_res;
			return $res;
	}
	
	
	private function studentProfil(){


		
			//? betriebsmittel soll nur der user selber sehen
			if(
			
				isSuccess($this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel","nummer as Nummer","ausgegebenam as Ausgegeben_am"]))
				
			){
				//? betriebsmittel are not needed in a view
				$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel(getAuthPersonId());
				if(isError($betriebsmittelperson_res)){
					   // error handling
				   }else{
					   $betriebsmittelperson_res = hasData($betriebsmittelperson_res)? getData($betriebsmittelperson_res) : null;
				   }
			   }

			   if(
			
				//? kontaktdaten soll auch nur der user selbst sehen
				isSuccess($this->KontaktModel->addSelect('DISTINCT ON (kontakttyp) kontakttyp, kontakt, tbl_kontakt.anmerkung, tbl_kontakt.zustellung')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT'))&&
			isSuccess($this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum'))
			){
				$kontakte_res = $this->KontaktModel->loadWhere(array('person_id' => getAuthPersonID()));
				if(isError($kontakte_res)){
					// handle error	
				}else{
					$kontakte_res = hasData($kontakte_res)? getData($kontakte_res) : null;
				}
				
			}

			//? FH Ausweis Austellungsdatum soll auch nur der user selbst sehen
			$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid(getAuthUID(),"Zutrittskarte");
			if(isError($zutrittskarte_ausgegebenam)){
				// error handling
			}else{
				$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam)? getData($zutrittskarte_ausgegebenam)[0]->ausgegebenam : null;
				//? formats the date from 01-01-2000 to 01.01.2000
				$zutrittskarte_ausgegebenam = str_replace("-",".",$zutrittskarte_ausgegebenam);
			}


			//? Die Adressen soll auch nur der user selber sehen

			if(
				
				isSuccess($adresse_res = $this->AdresseModel->addSelect(array("strasse","tbl_adressentyp.bezeichnung as adr_typ","plz","ort")))&&
				isSuccess($adresse_res = $this->AdresseModel->addOrder("zustelladresse","DESC"))&&
				isSuccess($adresse_res = $this->AdresseModel->addOrder("sort"))&&
				isSuccess($adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp","typ=adressentyp_kurzbz"))
			){
				$adresse_res = $this->AdresseModel->loadWhere(array("person_id"=>getAuthPersonID()));
				if(isError($adresse_res)){
					// error handling
				}else{									
					$adresse_res = hasData($adresse_res)? getData($adresse_res) : null;
				}
			}

			//? die folgenden Informationen darf nur der eigene user sehen
		

		
		

		if(
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung'))&&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) && 
		isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
		isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))){

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid'=>getAuthUID()));
			if( isError($mailverteiler_res)){
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res)? getData($mailverteiler_res) : null;
			
			$mailverteiler_res = array_map(function($element) { $element->mailto="mailto:".$element->gruppe_kurzbz."@".DOMAIN; return $element;},$mailverteiler_res);
		}

		
		
		if(isSuccess($this->BenutzerModel->addSelect(["foto","foto_sperre","anrede","titelpost","titelpre","vorname","nachname", "gebort", "gebdatum"]))
		&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))){

			$person_res = $this->BenutzerModel->load([getAuthUID()]);
			if(isError($person_res)){
				// error handling
			}else{
				$person_res = hasData($person_res)? getData($person_res)[0] : null;
			}
		}
		
		if(
		
		isSuccess($this->BenutzergruppeModel->addSelect(['bezeichnung']))
		&& isSuccess($this->BenutzergruppeModel->addJoin('tbl_gruppe', 'gruppe_kurzbz' ))
		){
			$zutrittsgruppe_res = $this->BenutzergruppeModel->loadWhere(array("uid"=>getAuthUID(), "zutrittssystem"=>true));
			if(isError($zutrittsgruppe_res)){
				// catch error
			}
			$zutrittsgruppe_res = hasData($zutrittsgruppe_res) ? getData($zutrittsgruppe_res) : null;
			
		}
		


		//? personenkennzeichen ist die Spalte Matrikelnr in der Tabelle Student
		if(isSuccess($this->StudentModel->addSelect(['tbl_studiengang.bezeichnung as studiengang','tbl_student.semester', 'tbl_student.verband', 'tbl_student.gruppe' ,'tbl_student.matrikelnr as personenkennzeichen']))
		 && isSuccess($this->StudentModel->addJoin('tbl_studiengang', "tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz")))
		{
			$student_res = $this->StudentModel->load([getAuthUID()]);
			if(isError($student_res)){
				// catch error
			}
			$student_res = hasData($student_res)?getData($student_res)[0]:null;
		
		}
		

		
		//? Matrikelnummer ist die Spalte matr_nr in Person
		if(isSuccess($this->BenutzerModel->addSelect(["matr_nr"])) 
		&& isSuccess($this->BenutzerModel->addJoin("tbl_person","person_id"))){
			$matr_res = $this->BenutzerModel->load([getAuthUID()]);
			if(isError($matr_res)){
				// catch error
			}else{
				$matr_res = hasData($matr_res)? getData($matr_res)[0] : [];
				
			}
		}

		$res = new stdClass();
		
		
		$res->foto = $person_res->foto;
		$res->foto_sperre = $person_res->foto_sperre;
		$res->username = getAuthUID();
		
		$res->anrede = $person_res->anrede;
		$res->titel = $person_res->titelpre;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;
		
		$res->gebort = $person_res->gebort;
		$res->gebdatum = $person_res->gebdatum;
	
		$res->postnomen = $person_res->titelpost; 
		

		$intern_email = array();
		$intern_email+=array("type" => "intern");
		$intern_email+=array("email"=> getAuthUID() . "@" . DOMAIN);
		
		$res->emails = array($intern_email);
		$res->adressen = $adresse_res;
		$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
		$res->kontakte = $kontakte_res;
		$res->mittel = $betriebsmittelperson_res;
		$res->matrikelnummer = $matr_res->matr_nr;
		foreach($student_res as $key => $value){
			$res->$key = $value;
		}
		$res->zuttritsgruppen = $zutrittsgruppe_res;

	
		
		$res->mailverteiler = $mailverteiler_res;
		
		return $res;
		
		 

	}

	public function getView(){
		// property_exists(object|string $object_or_class, string $property): bool
		
		$payload = json_decode($this->input->raw_input_stream);
		/* echo json_encode($payload);
		return; */
		$uid = property_exists($payload,"uid")?  $payload->uid : null;
		
		
		$isMitarbeiter = null;
		if($uid)
			$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter($uid);
		else
			$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter(getAuthUID());
		

		if(isError($isMitarbeiter)){
			//catch error
		}
		$isMitarbeiter = hasData($isMitarbeiter) ? getData($isMitarbeiter) : null;
		
		$res = new stdClass();
		
		if($uid == getAuthUID() || !$uid ){
			// if the $uid is empty, then no payload was supplied and the own profile is being requested
			if($isMitarbeiter ) {
				$res->view= "MitarbeiterProfil";
				$res->data = $this->mitarbeiterProfil();
			}
			else {
				$res->view= "StudentProfil";
				$res->data = $this->studentProfil();
			}
		}
		elseif($uid){
			// if an $uid was passed as payload to the function then the user is trying to view another profile
			if($isMitarbeiter ){
				 $res->view= "ViewMitarbeiterProfil";
				 $res->data= $this->viewMitarbeiterProfil($uid);
			}
			else {
				$res->view= "ViewStudentProfil";
				$res->data= $this->viewStudentProfil($uid);
			}
		}


		echo json_encode($res);
		
		

		
		
	}

	
	//? the view parameter is a flag that describes if a Profile from a different person is being viewed
	public function indexProfilInformaion($uid, $view=false){
		//? funktion returns all data needed for the student and the mitarbeiter profil view


		$res = new stdClass();

		//! falls der view flag auf flase gesetzt ist werden extra informationen des Users abgefragt
		if(!$view){
			//? betriebsmittel soll nur der user selber sehen
			if(
			
				isSuccess($this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel","nummer as Nummer","ausgegebenam as Ausgegeben_am"]))
				
			){
				//? betriebsmittel are not needed in a view
				$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel(getAuthPersonId());
				if(isError($betriebsmittelperson_res)){
					   // error handling
				   }else{
					   $betriebsmittelperson_res = hasData($betriebsmittelperson_res)? getData($betriebsmittelperson_res) : null;
				   }
			   }

			   if(
			
				//? kontaktdaten soll auch nur der user selbst sehen
				isSuccess($this->KontaktModel->addSelect('DISTINCT ON (kontakttyp) kontakttyp, kontakt, tbl_kontakt.anmerkung, tbl_kontakt.zustellung')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT'))&&
			isSuccess($this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum'))
			){
				$kontakte_res = $this->KontaktModel->loadWhere(array('person_id' => getAuthPersonID()));
				if(isError($kontakte_res)){
					// handle error	
				}else{
					$kontakte_res = hasData($kontakte_res)? getData($kontakte_res) : null;
				}
				
			}

			//? FH Ausweis Austellungsdatum soll auch nur der user selbst sehen
			$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid($uid,"Zutrittskarte");
			if(isError($zutrittskarte_ausgegebenam)){
				// error handling
			}else{
				$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam)? getData($zutrittskarte_ausgegebenam)[0]->ausgegebenam : null;
				//? formats the date from 01-01-2000 to 01.01.2000
				$zutrittskarte_ausgegebenam = str_replace("-",".",$zutrittskarte_ausgegebenam);
			}


			//? Die Adressen soll auch nur der user selber sehen

			if(
				!$view &&
				isSuccess($adresse_res = $this->AdresseModel->addSelect(array("strasse","tbl_adressentyp.bezeichnung as adr_typ","plz","ort")))&&
				isSuccess($adresse_res = $this->AdresseModel->addOrder("zustelladresse","DESC"))&&
				isSuccess($adresse_res = $this->AdresseModel->addOrder("sort"))&&
				isSuccess($adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp","typ=adressentyp_kurzbz"))
			){
				$adresse_res = $this->AdresseModel->loadWhere(array("person_id"=>getAuthPersonID()));
				if(isError($adresse_res)){
					// error handling
				}else{									
					$adresse_res = hasData($adresse_res)? getData($adresse_res) : null;
				}
			}

			//? die folgenden Informationen darf nur der eigene user sehen
		$res->adressen = $adresse_res;
		$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
		$res->kontakte = $kontakte_res;
		$res->mittel = $betriebsmittelperson_res;

		}
		

		if(
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung'))&&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) && 
		isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
		isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))){

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid'=>$uid));
			if( isError($mailverteiler_res)){
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res)? getData($mailverteiler_res) : null;
			
			$mailverteiler_res = array_map(function($element) { $element->mailto="mailto:".$element->gruppe_kurzbz."@".DOMAIN; return $element;},$mailverteiler_res);
		}

		
		$benutzer_info_sql_columns = ["foto","foto_sperre","anrede","titelpost","titelpre","vorname","nachname"];
		//? der Geburtsort und das Geburtsdatum darf auch nur der eigene User sehen
		if (!$view){
			array_push($benutzer_info_sql_columns, "gebort", "gebdatum");
		}
		if(isSuccess($this->BenutzerModel->addSelect($benutzer_info_sql_columns))
		&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))){

			$person_res = $this->BenutzerModel->load([$uid]);
			if(isError($person_res)){
				// error handling
			}else{
				$person_res = hasData($person_res)? getData($person_res)[0] : null;
			}
		}
		

				
		
		$res->foto = $person_res->foto;
		$res->foto_sperre = $person_res->foto_sperre;
		$res->username = $uid;
		
		$res->anrede = $person_res->anrede;
		$res->titel = $person_res->titelpre;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;
		if(!$view){
		$res->gebort = $person_res->gebort;
		$res->gebdatum = $person_res->gebdatum;
	}
		$res->postnomen = $person_res->titelpost; 
		$intern_email = array();
		$intern_email+=array("type" => "intern");
		$intern_email+=array("email"=> $uid . "@" . DOMAIN);
		$res->emails = array($intern_email);

	
		
		$res->mailverteiler = $mailverteiler_res;
		 


		echo json_encode($res);

	}



	//! old function that was used to fetch all the data
	// todo delete the function at the end
	public function getMitarbeiterAnsicht(){

		

		if(
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung'))&&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) && 
		isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
		isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))){

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid'=>getAuthUID()));
			if( isError($mailverteiler_res)){
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res)? getData($mailverteiler_res) : null;
			
			$mailverteiler_res = array_map(function($element) { $element->mailto="mailto:".$element->gruppe_kurzbz."@".DOMAIN; return $element;},$mailverteiler_res);
		}

	

		if(isSuccess($this->KontaktModel->addSelect('DISTINCT ON (kontakttyp) kontakttyp, kontakt, tbl_kontakt.anmerkung, tbl_kontakt.zustellung')) &&
		isSuccess($this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT')) &&
		isSuccess($this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT'))&&
		isSuccess($this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum'))
		){
			$kontakte_res = $this->KontaktModel->loadWhere(array('person_id' => getAuthPersonID()));
			if(isError($kontakte_res)){
				// handle error	
			}else{
				$kontakte_res = hasData($kontakte_res)? getData($kontakte_res) : null;
			}
			
		}

		$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid(getAuthUID(),"Zutrittskarte");
		if(isError($zutrittskarte_ausgegebenam)){
			// error handling
		}else{
			$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam)? getData($zutrittskarte_ausgegebenam)[0]->ausgegebenam : null;
			//? formats the date from 01-01-2000 to 01.01.2000
			$zutrittskarte_ausgegebenam = str_replace("-",".",$zutrittskarte_ausgegebenam);
		}
		
		

		if(
			isSuccess($this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel","nummer as Nummer","ausgegebenam as Ausgegeben_am"]))
			
		){
			$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel(getAuthPersonId());
			if(isError($betriebsmittelperson_res)){
				   // error handling
			   }else{
				   $betriebsmittelperson_res = hasData($betriebsmittelperson_res)? getData($betriebsmittelperson_res) : null;
			   }
		   }

		if(
			//! Summe der Wochenstunden wird jetzt in der hr/tbl_dienstverhaeltnis gespeichert
		 isSuccess($this->BenutzerfunktionModel->addSelect(["tbl_benutzerfunktion.bezeichnung as Bezeichnung","tbl_organisationseinheit.bezeichnung as Organisationseinheit","datum_von as Gültig_von","datum_bis as Gültig_bis","wochenstunden as Wochenstunden"]))&&
	  	  isSuccess($this->BenutzerfunktionModel->addJoin("tbl_organisationseinheit","oe_kurzbz"))
		){
			$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere(array('uid'=>getAuthUID()));
			if(isError($benutzer_funktion_res)){
				// error handling
			}else{
				$benutzer_funktion_res = hasData($benutzer_funktion_res)? getData($benutzer_funktion_res) : null;
			}
		}


		if(
			isSuccess($adresse_res = $this->AdresseModel->addSelect(array("strasse","tbl_adressentyp.bezeichnung as adr_typ","plz","ort")))&&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("zustelladresse","DESC"))&&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("sort"))&&
			isSuccess($adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp","typ=adressentyp_kurzbz"))
		){
			$adresse_res = $this->AdresseModel->loadWhere(array("person_id"=>getAuthPersonID()));
			if(isError($adresse_res)){
				// error handling
			}else{									
				$adresse_res = hasData($adresse_res)? getData($adresse_res) : null;
			}
		}

		$benutzer_res = $this->BenutzerModel->load([getAuthUID()]);
		if(isError($benutzer_res)){
			// error handling
		}else{
			$benutzer_res = hasData($benutzer_res)? getData($benutzer_res)[0] : null;
		}

		$person_res = $this->PersonModel->load(getAuthPersonId());
		if(isError($person_res)){
			// error handling
		}else{
			$person_res = hasData($person_res)? getData($person_res)[0] : null;
		}
		
		$mitarbeiter_res = $this->MitarbeiterModel->load(getAuthUID());
		if(isError($mitarbeiter_res)){
			// error handling
		}else{
			$mitarbeiter_res = hasData($mitarbeiter_res)? getData($mitarbeiter_res)[0] : null;
		}

		
		$res = new stdClass();
		$res->username = getAuthUID();


		//? Person Info
		$res->foto = $person_res->foto;
		$res->foto_sperre = $person_res->foto_sperre;
		
		$res->anrede = $person_res->anrede;
		$res->titelpre = $person_res->titelpre;
		$res->titelpost = $person_res->titelpost;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;
		//$res->postnomen = $person_res->postnomen; //!POSTNOMEN?
		
		$res->gebdatum = $person_res->gebdatum;
		$res->gebort = $person_res->gebort;
		//? Mitarbeiter Info
		$res->kurzbz = $mitarbeiter_res->kurzbz;
		$res->telefonklappe = $mitarbeiter_res->telefonklappe;
		//? Email Info 
		$intern_email = array();
		$intern_email+=array("type" => "intern");
		$intern_email+=array("email"=> getAuthUID() . "@" . DOMAIN);
		$extern_email=array();
		$extern_email+=array("type" => "alias");
		$extern_email+=array("email" => $benutzer_res->alias . "@" . DOMAIN);
		$res->emails = array($intern_email,$extern_email);
		//? Adresse Info 
		$res->adressen = $adresse_res;
		//? Benutzerfunktion Info
		$res->funktionen = $benutzer_funktion_res;
		//? Betriebsmittel Info
		$res->mittel = $betriebsmittelperson_res;
		//? Austellungsdatum von der Zutrittskarte
		$res->zutrittskarte_ausgegebenam = $zutrittskarte_ausgegebenam;
		//? Kontakt Info
		$res->kontakte = $kontakte_res;
		//? Mailverteiler Info
		$res->mailverteiler = $mailverteiler_res;
		 
		echo json_encode($res);
		
	}

	//? check wheter the parameter uid is a Mitarbeiter or a Student
	public function isMitarbeiterOrStudent($uid){
		if($this->MitarbeiterModel->isMitarbeiter($uid)->retval){
			echo json_encode("Mitarbeiter");
		}//! not sure if the user is automatically a student if he is not a mitarbeiter
		else if($this->StudentModel->isStudent($uid)->retval){
			echo json_encode("Student");
		}else{
			echo json_encode("Not a Mitarbeiter or Student");
		}

		
	}

	public function foto_sperre_function($value){
		$res = $this->PersonModel->update(getAuthPersonID(),array("foto_sperre"=>$value));
		if(isError($res)){
			// error handling
		}else{
			//? select the value of the column foto_sperre to return 
			if(isSuccess($this->PersonModel->addSelect("foto_sperre"))){
				$res = $this->PersonModel->load(getAuthPersonID());
				if(isError($res)){
					// error handling
				}
				$res = hasData($res) ? getData($res)[0] : null;
			}
			
		}
		echo json_encode($res);
	}



	

	

	



	//? this idea was to use _remap, to call different views based on the type of user
	/*
	public function _remap($param)
	{
		/$uid wird als global variable weiter gegeben
			 /get the data from the database with the uid
			 /give the data to the view 
			 /put the queried data in global array and access properties needed in the specific view $profile_information = array()
			/$this -> load->view('Cis/StudentProfile', ["uid" => $uid])
			
			
			if ($param === 'some_method')
			{
					echo "if";
			}
			else
			{
					echo "else";
			}
	}*/

	

}
