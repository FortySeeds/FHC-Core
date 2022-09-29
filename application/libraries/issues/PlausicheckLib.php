<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PlausicheckLib
{
	private $_ci; // Code igniter instance
	private $_db; // database object

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance(); // get ci instance

		// load models
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->_ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		// get database for queries
		$this->_db = new DB_Model();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Studiengang checks

	/**
	 * Studiengang should be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getPrestudentenStgUngleichStgStudent($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz prestudent_stg_oe_kurzbz, student_stg.oe_kurzbz student_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING(prestudent_id)
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN public.tbl_studiengang student_stg ON stud.studiengang_kz = student_stg.studiengang_kz
			WHERE
				stud.studiengang_kz != pre.studiengang_kz
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Orgform of a Studiengang in Studienplan should be the same as orgform of student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getOrgformStgUngleichOrgformPrestudent($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, status.studiensemester_kurzbz,
				studiengang.orgform_kurzbz as stg_orgform, status.orgform_kurzbz as student_orgform,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_studiengang studiengang
				JOIN public.tbl_student student USING(studiengang_kz)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_benutzer benutzer on(benutzer.uid = student.student_uid)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv = true
				AND status.status_kurzbz='Student'
				AND studiengang.studiengang_kz < 10000
				AND status.studiensemester_kurzbz = ?
				AND stg.melderelevant
				AND NOT EXISTS(
					SELECT 1 FROM lehre.tbl_studienplan JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					WHERE
						tbl_studienordnung.studiengang_kz = prestudent.studiengang_kz
						AND tbl_studienplan.orgform_kurzbz = status.orgform_kurzbz)";

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		$qry .= "
			ORDER BY student_uid";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students in "mixed" Studiengang should have Orgform.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getPrestudentMischformOhneOrgform($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				status.status_kurzbz IN ('Bewerber', 'Student')
				AND stg.mischform
				AND (status.orgform_kurzbz='' OR status.orgform_kurzbz IS NULL)
				AND status.studiensemester_kurzbz=?
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang should be the same for prestudent and studienplan.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getPrestudentStgUngleichStgStudienplan($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (ps.prestudent_id) ps.person_id, ps.prestudent_id,
				stplan.bezeichnung AS studienplan, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent ps
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
				JOIN lehre.tbl_studienplan stplan USING(studienplan_id)
				JOIN lehre.tbl_studienordnung stordnung USING(studienordnung_id)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_studiengang stg ON ps.studiengang_kz = stg.studiengang_kz
			WHERE
				ps.studiengang_kz<>stordnung.studiengang_kz
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND ps.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Studentstatus checks

	/**
	 * Abbrecher cannot be active.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAbbrecherAktiv($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudentstatus pre_status
				JOIN public.tbl_prestudent pre USING(prestudent_id)
				JOIN public.tbl_student student USING(prestudent_id)
				JOIN public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				pre_status.status_kurzbz ='Abbrecher'
				AND benutzer.aktiv=true
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * There shouldn't be any status after Abbrecher status.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getStudentstatusNachAbbrecher($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();
		$result = array();

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student student
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				prestatus.status_kurzbz = 'Abbrecher'
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		// TODO - maybe also put in sql?
		$qryRes = $this->_db->execReadOnlyQuery($qry, $params);

		if (isError($qryRes)) return $qryRes;

		if (hasData($qryRes))
		{
			$students = getData($qryRes);

			foreach ($students as $student)
			{
				$lastStatusRes = $this->_ci->PrestudentstatusModel->getLastStatus($student->prestudent_id);

				if (isError($lastStatusRes)) return $lastStatusRes;

				if (hasData($lastStatusRes))
				{
					$lastStatus = getData($lastStatusRes)[0]->status_kurzbz;

					if ($lastStatus != 'Abbrecher') $result[] = $student;
				}
			}
		}

		return success($result);
	}

	/**
	 * Ausbildungssemester of prestudent (lehrverband) must be the same as Ausbildungssemester of prestudentstatus.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAusbildungssemPrestudentUngleichAusbildungssemStatus($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz, $studiensemester_kurzbz, $studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT(student.student_uid), prestudent.person_id, prestudent.prestudent_id,
				status.ausbildungssemester, lv.semester, status.studiensemester_kurzbz,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student student
				JOIN public.tbl_studentlehrverband lv USING(student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				status.studiensemester_kurzbz = ?
				AND lv.studiensemester_kurzbz = ?
				AND status.status_kurzbz NOT IN ('Interessent','Bewerber','Aufgenommener','Wartender','Abgewiesener','Unterbrecher')
				AND get_rolle_prestudent (prestudent_id, ?)='Student'
				AND status.ausbildungssemester != lv.semester
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students with active status should have an active Benutzer.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getInaktiverStudentAktiverStatus($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT(student.student_uid), prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_benutzer benutzer
				JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv=false
				AND get_rolle_prestudent(prestudent_id, ?) IN ('Student', 'Diplomand', 'Unterbrecher', 'Praktikant')
				AND stg.melderelevant
				AND prestudent.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students of a semester shouldn't start studies before the date of Bismeldung.
	 * e.g. If student studies in WS2022 datum of status shouldn't be before 15.4.2020
	 * e.g. If student studies in SS2022 datum of status shouldn't be before 15.11.2022
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getInskriptionVorLetzerBismeldung($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);
		$results = array();

		// get active students
		$qry = "
			SELECT
				DISTINCT(student.student_uid),
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_benutzer benutzer
				JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv=true
				AND status.studiensemester_kurzbz = ?
				AND stg.melderelevant
				AND prestudent.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qryRes = $this->_db->execReadOnlyQuery($qry, $params);

		if (isError($qryRes)) return $qryRes;

		// TODO: maybe do this in query already instead?
		if (hasData($qryRes))
		{
			$students = getData($qryRes);

			// get Bismeldedatum
			$datumBis = $this->_getBisdateFromSemester($studiensemester_kurzbz);

			foreach ($students as $student)
			{
				// get first Status of student
				$firstStatusRes = $this->_ci->PrestudentstatusModel->getFirstStatus($student->prestudent_id, 'Student');

				if (isError($firstStatusRes)) return $firstStatusRes;

				if (hasData($firstStatusRes))
				{
					$firstStatus = getData($firstStatusRes)[0];

					if ($firstStatus->studiensemester_kurzbz != $studiensemester_kurzbz)
						continue;

					$datumInscription = date_format(date_create($firstStatus->datum), 'Y-m-d');

					// if student inscription was before Bismeldedatum
					if ($datumInscription < $datumBis)
					{
						// add the student to result with dates for info output
						$student->datum_inskription = $datumInscription;
						$student->datum_bismeldung = $datumBis;

						$results[] = $student;
					}
				}
			}
		}

		return success($results);
	}

	/**
	 * Status Dates and status studysemester dates should be in correct order.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getDatumStudiensemesterFalscheReihenfolge($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);
		$results = array();

		// all active students with Status student in current semester
		$qry = "
			SELECT
				DISTINCT(student_uid), prestudent.person_id, prestudent.prestudent_id,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_student student
				JOIN public.tbl_benutzer benutzer on(student.student_uid = benutzer.uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv=true
				AND status.status_kurzbz='Student'
				AND status.studiensemester_kurzbz=?
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qryRes = $this->_db->execReadOnlyQuery($qry, $params);

		if (isError($qryRes)) return $qryRes;

		if (hasData($qryRes))
		{
			$students = getData($qryRes);

			foreach ($students as $student)
			{
				// get all status of student, sorted by semester start
				$qryOrderSemester = "
					SELECT
						status.*
					FROM
						public.tbl_prestudentstatus status
						JOIN public.tbl_studiensemester semester USING(studiensemester_kurzbz)
					WHERE
						prestudent_id = ?
					ORDER BY semester.start DESC, status.datum DESC;";

				$qryOrderSemesterRes = $this->_db->execReadOnlyQuery($qryOrderSemester, array($student->prestudent_id));

				if (isError($qryOrderSemesterRes)) return $qryOrderSemesterRes;

				$prestudentsSemesterSorted = hasData($qryOrderSemesterRes) ? getData($qryOrderSemesterRes) : array();

				// get all status of student, sorted by status date
				$this->_ci->PrestudentstatusModel->addSelect('studiensemester_kurzbz');
				$this->_ci->PrestudentstatusModel->addOrder('datum', 'DESC');
				$this->_ci->PrestudentstatusModel->addOrder('insertamum', 'DESC');
				$qryOrderDateRes = $this->_ci->PrestudentstatusModel->loadWhere(array('prestudent_id' => $student->prestudent_id));

				if (isError($qryOrderDateRes)) return $qryOrderDateRes;

				$prestudentsDateSorted = hasData($qryOrderDateRes) ? getData($qryOrderDateRes) : array();

				// check if differently sorted status have same Studiensemester order
				$countStatus = count($prestudentsSemesterSorted);

				for ($i = 0; $i < $countStatus; $i++)
				{
					if ($prestudentsSemesterSorted[$i]->studiensemester_kurzbz != $prestudentsDateSorted[$i]->studiensemester_kurzbz)
					{
						$results[] = $student;
						break;
					}
				}
			}
		}

		return success($results);
	}

	/**
	 * Students with active Benutzer should have a status in the current semester.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAktiverStudentOhneStatus($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();
		$results = array();

		$qry = "
			SELECT
				DISTINCT (student_uid), prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student student
				JOIN public.tbl_benutzer benutzer on (benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv=TRUE
				AND stg.melderelevant
				AND prestudent.bismelden
				AND NOT EXISTS (
					SELECT 1
					FROM public.tbl_prestudentstatus
					JOIN public.tbl_studiensemester sem USING (studiensemester_kurzbz)
					WHERE prestudent_id = prestudent.prestudent_id
					AND sem.ende::date > NOW() - interval '4 months'
				)";

				// TODO - why use getLastStatus function - maybe use not exists for two semester instead - faster??
				// generell - kein Status in Zukunft - sollte nicht mehr aktiv sein, aber: auch 4 Monate Puffer, wenn im Sommer noch nicht vorgerückt z.B.

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studienplan should be valid in current Ausbildungssemester of prestudent.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getStudienplanUngueltig($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT pre.person_id, pre.prestudent_id,
				tbl_studienplan.bezeichnung AS studienplan,
				status.status_kurzbz,
				status.studiensemester_kurzbz,
				status.ausbildungssemester,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_person USING(person_id)
				JOIN lehre.tbl_studienplan USING(studienplan_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				status_kurzbz in('Student', 'Interessent','Bewerber','Aufgenommener')
				AND NOT EXISTS (
					SELECT
						1
					FROM
						lehre.tbl_studienplan_semester
					WHERE
						studienplan_id=status.studienplan_id
						AND tbl_studienplan_semester.semester = status.ausbildungssemester
						AND tbl_studienplan_semester.studiensemester_kurzbz = status.studiensemester_kurzbz
				)
				AND status.studiensemester_kurzbz=?
				AND pre.bismelden
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students with finished studies should have exactly one final exam.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlAbschlusspruefungen($studiensemester_kurzbz = null, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT * FROM (
				SELECT
					DISTINCT ON(pre.prestudent_id) pre.person_id, pre.prestudent_id, student_uid, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz,
					(
						SELECT COUNT(*)
						FROM lehre.tbl_abschlusspruefung
						WHERE student_uid = stud.student_uid
						AND abschlussbeurteilung_kurzbz != 'nicht'
						AND abschlussbeurteilung_kurzbz IS NOT NULL
					) AS anzahl_abschlusspruefungen
				FROM
					public.tbl_prestudent pre
					JOIN public.tbl_student stud USING(prestudent_id)
					JOIN public.tbl_prestudentstatus status USING(prestudent_id)
					JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				WHERE
					status_kurzbz = 'Absolvent'
					AND stg.melderelevant
					AND pre.bismelden
					AND NOT EXISTS ( /* exclude gs */
						SELECT 1
						FROM bis.tbl_mobilitaet
						WHERE prestudent_id = pre.prestudent_id
						AND studiensemester_kurzbz = status.studiensemester_kurzbz
					)";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qry .= ") studenten
			WHERE anzahl_abschlusspruefungen != 1";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Date of final exam shouldn't be missing for Absolvent.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getDatumAbschlusspruefungFehlt($studiensemester_kurzbz = null, $studiengang_kz = null, $abschlusspruefung_id = null)
	{
		$results = array();

		$pruefungenRes = $this->_getInvalidAbschlusspruefungen($studiensemester_kurzbz, $studiengang_kz, $abschlusspruefung_id);

		if (isError($pruefungenRes)) return $pruefungenRes;

		if (hasData($pruefungenRes))
		{
			$pruefungen = getData($pruefungenRes);

			foreach ($pruefungen as $pruefung)
			{
				if (isEmptyString($pruefung->datum)) $results[] = $pruefung;
			}
		}

		return success($results);
	}

	/**
	 * Date of sponsion shouldn't be missing for Absolvent.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getDatumSponsionFehlt($studiensemester_kurzbz, $studiengang_kz = null, $abschlusspruefung_id = null)
	{
		$results = array();

		$pruefungenRes = $this->_getInvalidAbschlusspruefungen($studiensemester_kurzbz, $studiengang_kz, $abschlusspruefung_id);

		if (isError($pruefungenRes)) return $pruefungenRes;

		if (hasData($pruefungenRes))
		{
			$pruefungen = getData($pruefungenRes);

			foreach ($pruefungen as $pruefung)
			{
				if (isEmptyString($pruefung->sponsion)) $results[] = $pruefung;
			}
		}

		return success($results);
	}

	/**
	 * Bewerber should have participated in Reihungstest.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getBewerberNichtZumRtAngetreten($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$previousStudiensemesterRes = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester_kurzbz);

		if (isError($previousStudiensemesterRes)) return $previousStudiensemesterRes;

		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent prestudent
				JOIN public.tbl_prestudentstatus status ON(prestudent.prestudent_id=status.prestudent_id)
				JOIN public.tbl_person USING(person_id)
				LEFT JOIN bis.tbl_orgform USING(orgform_kurzbz)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				status_kurzbz='Bewerber'
				AND reihungstestangetreten=false
				AND stg.melderelevant
				AND prestudent.bismelden";

		if (hasData($previousStudiensemesterRes))
		{
			$previousStudiensemester = getData($previousStudiensemesterRes)[0]->studiensemester_kurzbz;
			$qry .= " AND (studiensemester_kurzbz=? OR studiensemester_kurzbz=?)";
			$params[] = $previousStudiensemester;
		}
		else
		{
			$qry .= " AND studiensemester_kurzbz=?";
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Current Ausbildungssemester shouldn't be 0.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAktSemesterNull($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, prestat.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus prestat USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				prestat.status_kurzbz != 'Incoming'
				AND prestat.studiensemester_kurzbz = ?
				AND ausbildungssemester = 0
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Prestudent should have a final status.
	 * 
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAbschlussstatusFehlt($studiensemester_kurzbz = null, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (pre.prestudent_id)
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				NOT EXISTS( /*student does not study anymore*/
					SELECT
						1
					FROM
						public.tbl_prestudentstatus ps
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE
						prestudent_id=pre.prestudent_id
						/* 4 months: There might be Diplomanden, in summer months end status is often not entered yet */
						AND tbl_studiensemester.ende>now() - interval '4 months'
				)
				/* check only valid begininng with 2018 */
				AND '2018-01-01'<(SELECT max(datum) FROM public.tbl_prestudentstatus WHERE prestudent_id=pre.prestudent_id)
				AND NOT EXISTS( /* no end status */
					SELECT 1
					FROM public.tbl_prestudentstatus ps
					WHERE
						prestudent_id=pre.prestudent_id
						AND status_kurzbz IN('Abbrecher','Abgewiesener','Absolvent','Incoming')
				)
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiensemester_kurzbz))
		{
			$prevStudiensemesterRes = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester_kurzbz);

			if (isError($prevStudiensemesterRes)) return $prevStudiensemesterRes;

			if (hasData($prevStudiensemesterRes))
			{
				// if Studiensemester given, check only if has status in current or previous semester
				$prevStudiensemester = getData($prevStudiensemesterRes)[0]->studiensemester_kurzbz;
				$qry .= " AND EXISTS (
							SELECT 1
							FROM public.tbl_prestudentstatus ps
							WHERE studiensemester_kurzbz IN (?, ?)
							AND ps.prestudent_id = pre.prestudent_id
						)";
				$params[] = $prevStudiensemester;
				$params[] = $studiensemester_kurzbz;
			}
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Person checks

	/**
	 * Birthdate is too long ago.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getGbDatumWeitZurueck($studiensemester_kurzbz = null, $studiengang_kz = null, $person_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pers.person_id
			FROM
				public.tbl_person pers
			WHERE
				pers.gebdatum < '1920-01-01'
				AND EXISTS (
					SELECT 1
					FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus status USING(prestudent_id)
					JOIN public.tbl_studiengang stg ON tbl_prestudent.studiengang_kz = stg.studiengang_kz
					WHERE person_id = pers.person_id
					AND stg.melderelevant";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		$qry .= ")";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND pers.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Nation is not Austria, but address has austrian Gemeinde.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getNationNichtOesterreichAberGemeinde($studiengang_kz = null, $person_id = null)
	{
		$params = array();

		$qry = "SELECT DISTINCT tbl_person.person_id, adr.gemeinde, adr.adresse_id
				FROM
					public.tbl_adresse adr
					JOIN public.tbl_prestudent USING(person_id)
					JOIN public.tbl_person USING(person_id)
					JOIN public.tbl_student USING(prestudent_id)
					JOIN public.tbl_benutzer ON(uid=student_uid)
					JOIN public.tbl_studiengang stg ON tbl_prestudent.studiengang_kz = stg.studiengang_kz
				WHERE
					adr.nation!='A'
					AND tbl_benutzer.aktiv
					AND gemeinde NOT IN ('Münster')
					AND EXISTS(SELECT 1 FROM bis.tbl_gemeinde WHERE name = adr.gemeinde)
					AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND tbl_person.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students should have exactly one home address.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlHeimatadressen($studiensemester_kurzbz = null, $studiengang_kz = null, $person_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT person_id
			FROM
				(
					SELECT person_id, COUNT(adresse_id) AS anzahl_adressen
					FROM public.tbl_adresse addr
					WHERE heimatadresse IS TRUE
					GROUP BY person_id
				) adressen
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent pre USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_student USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				anzahl_adressen != 1
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students should have exactly one delivery address.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlZustelladressen($studiensemester_kurzbz = null, $studiengang_kz = null, $person_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT person_id
			FROM
				(
					SELECT person_id, COUNT(adresse_id) AS anzahl_adressen
					FROM public.tbl_adresse addr
					WHERE zustelladresse IS TRUE
					GROUP BY person_id
				) adressen
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent pre USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_student USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				anzahl_adressen != 1
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// I/O checks

	/**
	 * Incoming shouldn't have austrian home address.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getIncomingHeimatNationOesterreich($studiensemester_kurzbz, $studiengang_kz = null, $person_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT pers.person_id
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_adresse addr USING(person_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				status.status_kurzbz = 'Incoming'
				AND addr.nation = 'A'
				AND addr.heimatadresse
				AND status.studiensemester_kurzbz = ?
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND pers.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Incoming should have IN/OUT data.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getIncomingOhneIoDatensatz($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON(student_uid, nachname, vorname)
				tbl_person.person_id,
				tbl_prestudent.prestudent_id,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student
				JOIN public.tbl_benutzer ON(student_uid=uid)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
				JOIN public.tbl_studiengang stg ON(stg.studiengang_kz=tbl_student.studiengang_kz)
			WHERE
				bismelden=TRUE
				AND status_kurzbz='Incoming' AND NOT EXISTS (SELECT 1 FROM bis.tbl_bisio WHERE student_uid=tbl_student.student_uid)
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Get final exams in a semester which are invalid (e.g. missing data)
	 */
	private function _getInvalidAbschlusspruefungen($studiensemester_kurzbz = null, $studiengang_kz = null, $abschlusspruefung_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id,
				pruefung.sponsion, pruefung.datum, pruefung.abschlusspruefung_id,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN lehre.tbl_abschlusspruefung pruefung ON stud.student_uid = pruefung.student_uid
			WHERE
				status_kurzbz = 'Absolvent'
				AND NOT EXISTS ( /* exclude gs */
					SELECT 1
					FROM bis.tbl_mobilitaet
					WHERE prestudent_id = pre.prestudent_id
					AND studiensemester_kurzbz = prestatus.studiensemester_kurzbz
				)
				AND abschlussbeurteilung_kurzbz!='nicht'
				AND abschlussbeurteilung_kurzbz IS NOT NULL
				AND (pruefung.datum IS NULL OR pruefung.sponsion IS NULL)
				AND pre.bismelden
				AND stg.melderelevant";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND prestatus.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($abschlusspruefung_id))
		{
			$qry .= " AND pruefung.abschlusspruefung_id = ?";
			$params[] = $abschlusspruefung_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Gets Bismeldedate from Studiensemester.
	 */
	private function _getBisdateFromSemester($studiensemester_kurzbz)
	{
		$semesterYear = substr($studiensemester_kurzbz, 2, 6);
		$semesterType = substr($studiensemester_kurzbz, 0, 2);

		if ($semesterType == 'SS')
		{
			return date_format(date_create(($semesterYear - 1)."-11-15"), 'Y-m-d');
		}

		if ($semesterType == 'WS')
		{
			return date_format(date_create($semesterYear."-04-15"), 'Y-m-d');
		}
	}
}
