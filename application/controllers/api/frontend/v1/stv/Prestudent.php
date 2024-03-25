<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class Prestudent extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'get' => ['admin:r', 'assistenz:r'],
			'updatePrestudent' =>  ['admin:w', 'assistenz:w'],
			'getHistoryPrestudents' => ['admin:r', 'assistenz:r'],
			'getBezeichnungZGV' => 'admin:r', // TODO(manu): self::PERM_LOGGED
			'getBezeichnungDZgv' => 'admin:r', // TODO(manu): self::PERM_LOGGED
			'getBezeichnungMZgv' => 'admin:r', // TODO(manu): self::PERM_LOGGED
			'getAusbildung' => 'admin:r', // TODO(manu): self::PERM_LOGGED
			'getAufmerksamdurch' => 'admin:r', // TODO(manu): self::PERM_LOGGED
			'getBerufstaetigkeit' => 'admin:r', // TODO(manu): self::PERM_LOGGED
			'getTypenStg' => 'admin:r', // TODO(manu): self::PERM_LOGGED
			'getStudiensemester' => 'admin:r', // TODO(manu): self::PERM_LOGGED
			'getStudienplaene' => 'admin:r', // TODO(manu): self::PERM_LOGGED
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);


		// Load language phrases
		$this->loadPhrases([
			'ui', 'studierendenantrag'
		]);
	}

	public function get($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$this->PrestudentModel->addSelect('*');
		$result = $this->PrestudentModel->loadWhere(['prestudent_id' => $prestudent_id]);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		} elseif (!hasData($result)) {
			return show_404();
		} else {
			$this->terminateWithSuccess(current(getData($result)));
		}
	}

	public function updatePrestudent($prestudent_id)
	{
		$this->load->library('form_validation');
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		//get Studiengang von prestudent_id
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');
		$result = $this->PrestudentModel->load([
			'prestudent_id'=> $prestudent_id,
		]);
		if(isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$result = current(getData($result));

		$stg = $result->studiengang_kz;

		//Form validation
		$this->form_validation->set_rules('priorisierung', 'Priorisierung', 'numeric', [
			'numeric' => $this->p->t('ui','error_fieldNotNumeric',['field' => 'Priorisierung'])
		]);

		if ($this->form_validation->run() == false)
		{
			$this->terminateWithValidationErrors($this->form_validation->error_array());
		}

		$deltaData = $_POST;

		$uid = getAuthUID();

		$array_allowed_props_prestudent = [
			'aufmerksamdurch_kurzbz',
			'studiengang_kz',
			'gsstudientyp_kurzbz',
			'person_id',
			'berufstaetigkeit_code',
			'ausbildungcode',
			'zgv_code',
			'zgvort',
			'zgvdatum',
			'zgvnation',
			'zgvmas_code',
			'zgvmaort',
			'zgvmadatum',
			'zgvmanation',
			'facheinschlberuf',
			'bismelden',
			'anmerkung',
			'dual',
			'zgvdoktor_code',
			'zgvdoktorort',
			'zgvdoktordatum',
			'zgvdoktornation',
			'aufnahmegruppe_kurzbz',
			'priorisierung',
			'foerderrelevant',
			'zgv_erfuellt',
			'zgvmas_erfuellt',
			'zgvdoktor_erfuellt',
			'mentor',
			'aufnahmeschluessel',
			'standort_code'
		];

		$update_prestudent = array();
		foreach ($array_allowed_props_prestudent as $prop)
		{
			$val = isset($deltaData[$prop]) ? $deltaData[$prop] : null;
			if ($val !== null || $prop == 'foerderrelevant') {
				$update_prestudent[$prop] = $val;
			}
		}

		$update_prestudent['updateamum'] = date('c');
		$update_prestudent['updatevon'] = $uid;

		//utf8-decode for special chars (eg tag der offenen Tür, FH-Führer)
		function utf8_decode_if_string($value)
		{
			if (is_string($value)) {
				return utf8_decode($value);
			} else {
				return $value;
			}
		}
		$update_prestudent_encoded = array_map('utf8_decode_if_string', $update_prestudent);

		if (count($update_prestudent))
		{
			$result = $this->PrestudentModel->update(
				$prestudent_id,
				$update_prestudent_encoded
			);
			if (isError($result)) {
				$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
			}
			return $this->terminateWithSuccess(true);
		}
	}

	public function getHistoryPrestudents($person_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$result = $this->PrestudentModel->getHistoryPrestudents($person_id);
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		$this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getBezeichnungZGV()
	{
		$this->load->model('codex/Zgv_model', 'ZgvModel');

		$this->ZgvModel->addOrder('zgv_code');

		$result = $this->ZgvModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getBezeichnungDZgv()
	{
		$this->load->model('codex/Zgvdoktor_model', 'ZgvdoktorModel');

		$this->ZgvdoktorModel->addOrder('zgvdoktor_code');

		$result = $this->ZgvdoktorModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getBezeichnungMZgv()
	{
		$this->load->model('codex/Zgvmaster_model', 'ZgvmasterModel');

		$this->ZgvmasterModel->addOrder('zgvmas_code');

		$result = $this->ZgvmasterModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getAusbildung()
	{
		$this->load->model('codex/Ausbildung_model', 'AusbildungModel');

		$this->AusbildungModel->addOrder('ausbildungcode');

		$result = $this->AusbildungModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getAufmerksamdurch()
	{
		$this->load->model('codex/Aufmerksamdurch_model', 'AufmerksamdurchModel');

		$this->AufmerksamdurchModel->addOrder('aufmerksamdurch_kurzbz');

		$result = $this->AufmerksamdurchModel->load();
		if (isError($result))
		{
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getBerufstaetigkeit()
	{
		$this->load->model('codex/Berufstaetigkeit_model', 'BerufstaetigkeitModel');

		$this->BerufstaetigkeitModel->addOrder('berufstaetigkeit_code');

		$result = $this->BerufstaetigkeitModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getTypenStg()
	{
		$this->load->model('education/Gsstudientyp_model', 'GsstudientypModel');

		$this->GsstudientypModel->addOrder('gsstudientyp_kurzbz');

		$result = $this->GsstudientypModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getStudiensemester()
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		$this->StudiensemesterModel->addOrder('start', 'DESC');
		$this->StudiensemesterModel->addLimit(20);

		$result = $this->StudiensemesterModel->load();
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}

	public function getStudienplaene($prestudent_id)
	{
		$this->load->model('crm/Prestudent_model', 'PrestudentModel');

		$result = $this->PrestudentModel->loadWhere(
			array('prestudent_id' => $prestudent_id)
		);
		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}

		$result = current(getData($result));
		$studiengang_kz = $result->studiengang_kz;

		$this->load->model('organisation/Studienplan_model', 'StudienplanModel');

		$this->StudienplanModel->addOrder('studienplan_id', 'DESC');

		$result = $this->StudienplanModel->getStudienplaene($studiengang_kz);

		if (isError($result)) {
			$this->terminateWithError(getError($result), self::ERROR_TYPE_GENERAL);
		}
		return $this->terminateWithSuccess(getData($result) ?: []);
	}
}
