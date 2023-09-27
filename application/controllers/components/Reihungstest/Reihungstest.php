<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class Reihungstest extends FHC_Controller
{
	
	private $_ci;
	public function __construct()
	{
		parent::__construct();
		
		$this->_ci = & get_instance();
		
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->_ci->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->_ci->load->library('AuthLib');
		
	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	public function getStg()
	{
		$this->_ci->StudiengangModel->addOrder('typ, kurzbz');
		$stg = $this->_ci->StudiengangModel->load();
		$this->outputJsonSuccess(getData($stg));
	}
	
	public function getYear()
	{
		$semester = $this->_ci->StudiensemesterModel->getAktAndFutureSemester();
		$this->outputJsonSuccess(getData($semester));
	}
	
	public function loadReport()
	{
		$stg = $this->_ci->input->get('stg');
		$semester = $this->_ci->input->get('studiensemester');
		
		$qry = 'SELECT ps.prestudent_id AS "PrestudentID",
					vorname AS "Vorname",
					nachname AS "Nachname",
					gebdatum AS "Geburtsdatum",
					geschlecht AS "Geschlecht",
					UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) AS "Studiengang",
					tbl_studienplan.orgform_kurzbz AS "OrgForm",
					tbl_prestudentstatus.ausbildungssemester AS "Sem",
					COALESCE(ROUND(tbl_rt_person.punkte, 2), 0) AS "RT-Punkte",
					tbl_reihungstest.datum AS "RT-Datum",
					get_rolle_prestudent(ps.prestudent_id, tbl_prestudentstatus.studiensemester_kurzbz) AS "Status",
					(
					SELECT datum
						FROM PUBLIC.tbl_prestudentstatus
						WHERE prestudent_id = ps.prestudent_id
							AND status_kurzbz = get_rolle_prestudent(ps.prestudent_id, tbl_prestudentstatus.studiensemester_kurzbz)
							AND studiensemester_kurzbz = ?
						ORDER BY datum DESC
						LIMIT 1
					) AS "Status Datum",
					(
						COALESCE (
						(
							SELECT ARRAY_TO_STRING(
								(
									ARRAY_AGG(get_rolle_prestudent(ps_andere.prestudent_id,  ?) || \' \' || UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) || \' \' || tbl_studienplan.orgform_kurzbz)), \'<br/>\')
									FROM PUBLIC.tbl_prestudent ps_andere
										JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
										JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
										JOIN lehre.tbl_studienplan USING (studienplan_id)
									WHERE person_id = ps.person_id
										AND ps_andere.prestudent_id != ps.prestudent_id
										AND status_kurzbz = \'Interessent\'
										AND studiensemester_kurzbz= ?
						), \'-\')
					) AS "Status andere",
					COALESCE (
					(
						SELECT row
						FROM (
							SELECT priorisierung,
									ROW_NUMBER() OVER (
										ORDER BY priorisierung ASC
									) AS row
							FROM PUBLIC.tbl_prestudent
								JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
								JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
							WHERE person_id = ps.person_id
								AND get_rolle_prestudent(tbl_prestudent.prestudent_id,  ?) IN
									(
										\'Interessent\',
										\'Bewerber\',
										\'Wartender\',
										\'Aufgenommener\',
										\'Student\'
									 )
								AND status_kurzbz = \'Bewerber\'
								AND studiensemester_kurzbz= ?
								AND typ IN (\'b\',\'e\',\'m\')
							 ) prio
						WHERE priorisierung = ps.priorisierung LIMIT 1
					), 0) AS "Prio",
					(
					SELECT count(*)
					FROM PUBLIC.tbl_prestudent
						JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
						JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
					WHERE person_id = ps.person_id
						AND studiensemester_kurzbz= ?
						AND status_kurzbz = \'Interessent\'
						AND typ IN ( \'b\',\'e\')
					) AS "Summe_Bakk",
					(
						SELECT count(*)
						FROM PUBLIC.tbl_prestudent
							JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
							JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
						WHERE person_id = ps.person_id
							AND studiensemester_kurzbz= ?
							AND status_kurzbz = \'Interessent\'
							AND get_rolle_prestudent (tbl_prestudent.prestudent_id,  ?) != \'Abgewiesener\'
							AND typ IN ( \'b\',\'e\')
					) AS "Summe_Bakk_aktiv",
					(
						CASE WHEN (
							SELECT row
							FROM (
								SELECT priorisierung,
								ROW_NUMBER() OVER (
								ORDER BY priorisierung ASC
								) AS row
								FROM PUBLIC.tbl_prestudent
									JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
									JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
								WHERE person_id = ps.person_id
									AND get_rolle_prestudent(tbl_prestudent.prestudent_id,  ?) IN (
										\'Interessent\',
										\'Bewerber\',
										\'Wartender\',
										\'Aufgenommener\',
										\'Student\'
									)
									AND status_kurzbz = \'Bewerber\'
									AND studiensemester_kurzbz= ?
									AND typ IN ( \'b\',\'e\')
							) prio
							WHERE priorisierung = ps.priorisierung LIMIT 1
						)
						=
						(
							SELECT count(*)
							FROM PUBLIC.tbl_prestudent
								JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
								JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
							WHERE person_id = ps.person_id
								AND studiensemester_kurzbz= ?
								AND status_kurzbz = \'Bewerber\'
								AND get_rolle_prestudent (tbl_prestudent.prestudent_id,  ?) != \'Abgewiesener\'
								AND typ IN ( \'b\',\'e\')
						)
						THEN \'Ja\'
						ELSE \'Nein\'
						END
					) AS "Letzter in Kette",
					(
						SELECT count(*)
						FROM PUBLIC.tbl_prestudent
								 JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
								 JOIN PUBLIC.tbl_studiengang USING (studiengang_kz)
						WHERE person_id = ps.person_id
						  AND studiensemester_kurzbz= ?
						  AND get_rolle_prestudent (tbl_prestudent.prestudent_id,  ?) = \'Aufgenommener\'
						  AND typ IN ( \'b\',\'e\')
					) AS "Summe_Aufgenommene",
					(
						SELECT date_part(\'year\', age(now(), zgvdatum))
					) AS "Alter der ZGV",
					(
						SELECT CASE
								WHEN (
										SELECT SUM(betrag)
										FROM PUBLIC.tbl_konto konto
										WHERE person_id = ps.person_id
											AND buchungstyp_kurzbz = \'StudiengebuehrAnzahlung\'
											AND studiengang_kz = ps.studiengang_kz
											AND studiensemester_kurzbz = tbl_prestudentstatus.studiensemester_kurzbz
										) = 0
									THEN \'OK\'
								WHEN (
										SELECT count(*)
										FROM PUBLIC.tbl_konto
										WHERE person_id = ps.person_id
											AND buchungstyp_kurzbz = \'StudiengebuehrAnzahlung\'
											AND studiengang_kz = ps.studiengang_kz
											AND studiensemester_kurzbz = tbl_prestudentstatus.studiensemester_kurzbz
										) = 0
									THEN \'Belastung fehlt\'
								ELSE \'Offen\'
								END
					) AS "Anzahlung"
				FROM PUBLIC.tbl_prestudent ps
				JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
				JOIN PUBLIC.tbl_person USING (person_id)
				JOIN PUBLIC.tbl_studiengang ON (ps.studiengang_kz = tbl_studiengang.studiengang_kz)
				LEFT JOIN PUBLIC.tbl_rt_person ON (
						ps.person_id = tbl_rt_person.person_id
						AND tbl_prestudentstatus.studienplan_id = tbl_rt_person.studienplan_id
						)
				LEFT JOIN PUBLIC.tbl_reihungstest ON (tbl_rt_person.rt_id = tbl_reihungstest.reihungstest_id)
				JOIN lehre.tbl_studienplan ON (tbl_prestudentstatus.studienplan_id = tbl_studienplan.studienplan_id)
				WHERE status_kurzbz = \'Bewerber\'
					AND tbl_prestudentstatus.studiensemester_kurzbz= ?
					AND get_rolle_prestudent(ps.prestudent_id, tbl_prestudentstatus.studiensemester_kurzbz) IN (
						\'Interessent\',
						\'Bewerber\',
						\'Wartender\',
						\'Aufgenommener\',
						\'Abgewiesener\',
						\'Student\'
					)
					AND tbl_studiengang.typ IN ( \'b\',\'e\')
					AND (
						tbl_reihungstest.studiensemester_kurzbz= ?
						OR tbl_reihungstest.studiensemester_kurzbz IS NULL
					)
					AND (
						tbl_reihungstest.stufe = 1
						OR tbl_reihungstest.stufe IS NULL
					)
					AND ps.studiengang_kz = ?
				ORDER BY nachname,
					vorname';

		$params = array_pad(array(), 16, $semester);
		$params[] = $stg;
		$db = new DB_Model();
		$this->outputJsonSuccess($db->execReadOnlyQuery($qry, $params));
	}
}

