<?php

use CI3_Events as Events;

class Konto_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_konto';
		$this->pk = 'buchungsnr';
	}


	/**
	 * Adds additional fields to the Query
	 *
	 * @return Konto_model
	 */
	public function withAdditionalInfo()
	{
		$this->addSelect($this->dbTable . '.*');
		$this->addSelect('UPPER(typ::varchar(1) || kurzbz) AS kuerzel');
		$this->addSelect('person.anrede');
		$this->addSelect('person.titelpost');
		$this->addSelect('person.titelpre');
		$this->addSelect('person.vorname');
		$this->addSelect('person.vornamen');
		$this->addSelect('person.nachname');

		$this->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
		$this->addJoin('public.tbl_person person', 'person_id', 'LEFT');

		Events::trigger('konto_query');

		return $this;
	}

	/**
	 * Get all accounting entries for a person optionally filtered by Studiengang
	 *
	 * @param integer|array		$person_id
	 * @param string			(optional) $studiengang_kz
	 *
	 * @return stdClass
	 */
	public function getAlleBuchungen($person_id, $studiengang_kz = '')
	{
		$this->withAdditionalInfo();

		$this->addOrder('buchungsdatum');

		if (is_array($person_id))
			$this->db->where_in('person_id', $person_id);
		else
			$this->db->where('person_id', $person_id);

		if ($studiengang_kz)
			return $this->loadWhere([
				'studiengang_kz' => $studiengang_kz
			]);
		return $this->load();
	}

	/**
	 * Get all open accounting entries for a person optionally filtered by Studiengang
	 *
	 * @param integer|array		$person_id
	 * @param string			(optional) $studiengang_kz
	 *
	 * @return stdClass
	 */
	public function getOffeneBuchungen($person_id, $studiengang_kz = '')
	{
		$this->addSelect('buchungsnr');
		$this->db->where('(betrag + (
			SELECT CASE WHEN sum(betrag) is null THEN 0 ELSE sum(betrag) END
			FROM ' . $this->dbTable . '
			WHERE buchungsnr_verweis=konto_a.buchungsnr
		)) !=', 0, false);
		if (is_array($person_id))
			$this->db->where_in('person_id', $person_id);
		else
			$this->db->where('person_id', $person_id);
		$sql = $this->db->get_compiled_select($this->dbTable . ' konto_a');

		$this->db->group_start();
		$this->db->where_in('buchungsnr', $sql, false);
		$this->db->or_where_in('buchungsnr_verweis', $sql, false);
		$this->db->group_end();

		return $this->getAlleBuchungen($person_id, $studiengang_kz);
	}

	/**
	 * Sets a Payment as paid
	 */
	public function setPaid($buchungsnr)
	{
		// get payment
		$buchungResult = $this->loadWhere(array('buchungsnr' => $buchungsnr));

		if (isSuccess($buchungResult) && hasData($buchungResult))
		{
			$buchung = getData($buchungResult)[0];

			// get already paid amount
			$this->addSelect('sum(betrag) as bezahlt');
			$this->addGroupBy('buchungsnr_verweis');
			$buchungVerweisResult = $this->loadWhere(array('buchungsnr_verweis' => $buchungsnr));

			if (isSuccess($buchungVerweisResult))
			{
				$betragBezahlt = 0;

				if (hasData($buchungVerweisResult))
				{
					$betragBezahlt = getData($buchungVerweisResult)[0]->bezahlt;
				}

				// calculate open amount
				$betragOffen = $betragBezahlt - $buchung->betrag * (-1);

				if ($betragOffen != 0)
				{
					$data = array(
						'person_id' => $buchung->person_id,
						'studiengang_kz' => $buchung->studiengang_kz,
						'studiensemester_kurzbz' => $buchung->studiensemester_kurzbz,
						'buchungsnr_verweis' => $buchungsnr,
						'betrag' => str_replace(',', '.', $betragOffen * (-1)),
						'buchungsdatum' => date('Y-m-d'),
						'buchungstext' => $buchung->buchungstext,
						'insertamum' => date('Y-m-d H:i:s'),
						'insertvon' => '',
						'buchungstyp_kurzbz' => $buchung->buchungstyp_kurzbz,
					);

					return $this->insert($data);
				}
				else
				{
					return success();
				}
			}
			else
			{
				return error('Failed to load Payment');
			}
		}
		else
		{
			return error('Failed to load Payment');
		}
	}

	public function getStudienbeitraege($uid, $buchungstypen)
	{
		$query = 'SELECT konto.studiensemester_kurzbz
					FROM public.tbl_konto konto
							JOIN public.tbl_studiensemester studiensemester ON konto.studiensemester_kurzbz = studiensemester.studiensemester_kurzbz,
						public.tbl_benutzer,
						public.tbl_student
					WHERE tbl_benutzer.uid = \'' . $uid . '\'
					AND tbl_benutzer.uid = tbl_student.student_uid
					AND tbl_benutzer.person_id = konto.person_id
					AND konto.studiengang_kz = tbl_student.studiengang_kz
					AND konto.buchungstyp_kurzbz IN (\'' . $buchungstypen . '\')
					AND 0 = (
						SELECT sum(betrag)
						FROM public.tbl_konto skonto
						WHERE skonto.buchungsnr = konto.buchungsnr_verweis
						OR skonto.buchungsnr_verweis = konto.buchungsnr_verweis
						)
					ORDER BY studiensemester.start DESC;
					';

		return $this->execQuery($query);
	}

	public function checkStudienbeitrag($uid, $stsem, $buchungstypen)
	{
		$query = 'SELECT tbl_konto.buchungsnr,
       				tbl_konto.buchungsdatum
					FROM public.tbl_konto,
					public.tbl_benutzer,
					public.tbl_student
					WHERE
						tbl_konto.studiensemester_kurzbz = \'' . $stsem . '\'
						AND tbl_benutzer.uid = \'' . $uid . '\'
						AND tbl_benutzer.uid = tbl_student.student_uid
						AND tbl_benutzer.person_id = tbl_konto.person_id
						AND tbl_konto.studiengang_kz=tbl_student.studiengang_kz
						AND tbl_konto.buchungstyp_kurzbz IN (\'' . $buchungstypen . '\')
						AND 0 >= (
							SELECT sum(betrag)
							FROM public.tbl_konto skonto
							WHERE skonto.buchungsnr = tbl_konto.buchungsnr_verweis
							OR skonto.buchungsnr_verweis = tbl_konto.buchungsnr_verweis
						)
					ORDER BY buchungsnr DESC LIMIT 1
					';

		return $this->execQuery($query);
	}

	/**
	 * check if student has paid studienbeitrag for certain semester
	 *
	 * @param $person_id person_id
	 * @param $stsem stsem
	 *
	 * @return boolean
	 */
	public function checkStudienbeitragFromPerson($person_id, $stsem)
	{
		$this->addOrder('buchungsnr');
		$this->addLimit(1);
		$result = $this->loadWhere([
			'person_id'=>$person_id,
			'studiensemester_kurzbz' => $stsem,
			'buchungstyp_kurzbz' => 'Studiengebuehr'
		]);

		if (!getData($result))
			return false;

		$data = getData($result)[0];

		$this->resetQuery();

		$this->addSelect('sum(betrag) as differenz');
		$this->db->or_where('buchungsnr', $data->buchungsnr);
		$this->db->or_where('buchungsnr_verweis', $data->buchungsnr);

		$result = $this->load();
		if (!getData($result))
			return false;

		$data = getData($result)[0];
		return $data->differenz >= 0;
	}
}
