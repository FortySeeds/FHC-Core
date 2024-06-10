<?php
class Stundenplan_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_stundenplan';
		$this->pk = 'stundenplan_id';
	}

	/**
	 * @param string $ort_kurzbz
	 * @param string $date
	 * 
	 * @return stdClass
	 */
	public function getRoomDataOnDay($ort_kurzbz='EDV_A2.06',$start_date,$end_date){


		//TODO alternative query version that unions the reservierungen into the stundenplan with different 'eintrags_type' column
		/*"
		-- merging all reservierungs information with the stundenplan information but with different types
		SELECT 'stundenplan_eintrag' as eintrags_type, ort_kurzbz, studiengang_kz, uid, stunde, datum, titel, semester, verband, gruppe, gruppe_kurzbz, stg_kurzbz, CONCAT(UPPER(sp.stg_typ),UPPER(sp.stg_kurzbz),'-',COALESCE(CAST(sp.semester AS varchar),'/'),COALESCE(CAST(sp.verband AS varchar),'/')) AS stg, CONCAT(lehrfach,'-',lehrform) AS lv_info, * FROM lehre.vw_stundenplan sp
		WHERE ort_kurzbz = ? AND datum >= ? AND datum <= ? 
		UNION ALL
		SELECT 'reservierungs_eintrag' as eintrags_type, ort_kurzbz, studiengang_kz, uid, stunde, datum, titel, semester, verband, gruppe, gruppe_kurzbz, stg_kurzbz, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL FROM lehre.vw_reservierung res
		WHERE ort_kurzbz = ? AND datum >= ? AND datum <= ?
		"*/

		$raum_stundenplan= $this->execReadOnlyQuery("
		SELECT CONCAT(UPPER(sp.stg_typ),UPPER(sp.stg_kurzbz),'-',COALESCE(CAST(sp.semester AS varchar),'/'),COALESCE(CAST(sp.verband AS varchar),'/')) AS stg, CONCAT(lehrfach,'-',lehrform) AS lv_info, * FROM lehre.vw_stundenplan sp
		WHERE ort_kurzbz = ? AND datum >= ? AND datum <= ? 
		", [$ort_kurzbz, $start_date, $end_date]);

		return $raum_stundenplan;
	}

	/**
	 * @param string $uid
	 * 
	 * @return stdClass
	 */
	public function loadForUid($uid)
	{
		$this->addSelect('sp.*');
		$this->db->join('public.tbl_benutzergruppe bg', 'sp.gruppe_kurzbz=bg.gruppe_kurzbz AND bg.uid=?', 'LEFT', false);
		$this->addJoin('public.tbl_studiensemester ss1', 'bg.studiensemester_kurzbz=ss1.studiensemester_kurzbz AND ss1.start<=sp.datum AND ss1.ende>=sp.datum', 'LEFT');
		$this->db->join('public.tbl_studentlehrverband slv', "sp.studiengang_kz=slv.studiengang_kz AND slv.student_uid=? AND (slv.semester=sp.semester OR sp.semester IS NULL) AND (slv.verband=sp.verband OR sp.verband IS NULL OR sp.verband='' OR sp.verband='0') AND (slv.gruppe=sp.gruppe OR sp.gruppe IS NULL OR sp.gruppe='' OR sp.gruppe='0') AND sp.gruppe_kurzbz IS NULL", 'LEFT', false);
		$this->addJoin('public.tbl_studiensemester ss2', 'slv.studiensemester_kurzbz=ss2.studiensemester_kurzbz AND ss2.start<=sp.datum AND ss2.ende>=sp.datum', 'LEFT');
		$this->db->or_where('ss1.studiensemester_kurzbz IS NOT NULL', null, false);
		$this->db->or_where('ss2.studiensemester_kurzbz IS NOT NULL', null, false);
		
		$query = $this->db->get_compiled_select('lehre.vw_stundenplan sp');
		
		return $this->execQuery($query, [$uid, $uid]);
	}

}
