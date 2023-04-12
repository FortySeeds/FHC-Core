<?php
require_once __DIR__ . '/Vertragsbestandteil.php';
require_once __DIR__ . '/VertragsbestandteilStunden.php';
require_once __DIR__ . '/VertragsbestandteilFunktion.php';
require_once __DIR__ . '/VertragsbestandteilZeitaufzeichnung.php';
require_once __DIR__ . '/VertragsbestandteilKuendigungsfrist.php';
require_once __DIR__ . '/VertragsbestandteilFreitext.php';
require_once __DIR__ . '/VertragsbestandteilFactory.php';

use vertragsbestandteil\Vertragsbestandteil;
use vertragsbestandteil\VertragsbestandteilFactory;

/**
 * Description of VertragsbestandteilLib
 *
 * @author bambi
 */
class VertragsbestandteilLib
{		
	protected $CI;
	/** @var Vertragsbestandteil_model */
	protected $VertragsbestandteilModel;
	/** 
	 * @var GehaltsbestandteilLib
	 */
	protected $GehaltsbestandteilLib;
	
	public function __construct()
	{
		$this->CI = get_instance();
		$this->CI->load->model('vertragsbestandteil/Vertragsbestandteil_model', 
			'VertragsbestandteilModel');
		$this->VertragsbestandteilModel = $this->CI->VertragsbestandteilModel;
		$this->CI->load->library('vertragsbestandteil/GehaltsbestandteilLib', 
			null, 'GehaltsbestandteilLib');
		$this->GehaltsbestandteilLib = $this->CI->GehaltsbestandteilLib;
	}

	public function handleGUIData($guidata, $employeeUID, $userUID)
	{
		$guiHandler  = new GUIHandler($employeeUID, $userUID);
		$ret = false;
		try {
			$ret = $guiHandler->handle($guidata,  $employeeUID, $userUID);
		} catch (Exception $ex)
		{
			log_message('debug', "Error handling json data from GUI. " . $ex->getMessage());
		}	

		return $ret;
	}

	public function fetchVertragsbestandteile($dienstverhaeltnis_id, $stichtag=null)
	{
		return $this->VertragsbestandteilModel->getVertragsbestandteile($dienstverhaeltnis_id, $stichtag);
	}

	public function fetchVertragsbestandteil($vertragsbestandteil_id)
	{
		return $this->VertragsbestandteilModel->getVertragsbestandteil($vertragsbestandteil_id);
	}
	
	public function storeVertragsbestandteil(Vertragsbestandteil $vertragsbestandteil) 
	{
		$this->CI->db->trans_begin();
		try
		{
			if( intval($vertragsbestandteil->getVertragsbestandteil_id()) > 0 )
			{
				$this->updateVertragsbestandteil($vertragsbestandteil);
			}
			else
			{
				$this->insertVertragsbestandteil($vertragsbestandteil);
			}
			if( $this->CI->db->trans_status() === false )
			{
				log_message('debug', "Transaction failed");
				throw new Exception("Transaction failed");
			}	
			$this->CI->db->trans_commit();
		}
		catch (Exception $ex)
		{
			log_message('debug', "Transaction rolled back. " . $ex->getMessage());
			$this->CI->db->trans_rollback();
			throw new Exception('Storing Vertragsbestandteil failed.');
		}	
	}
	
	protected function insertVertragsbestandteil(Vertragsbestandteil $vertragsbestandteil)
	{
		$vertragsbestandteil->beforePersist();
		$ret = $this->VertragsbestandteilModel->insert($vertragsbestandteil->baseToStdClass());
		if( hasData($ret) ) 
		{
			$vertragsbestandteil->setVertragsbestandteil_id(getData($ret));
		}
		else
		{
			throw new Exception('error inserting vertragsbestandteil');
		}

		$specialisedModel = VertragsbestandteilFactory::getVertragsbestandteilDBModel(
			$vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
		$retspecial = $specialisedModel->insert($vertragsbestandteil->toStdClass());
		
		if(isError($retspecial) )
		{
			throw new Exception('error updating vertragsbestandteil ' 
				. $vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
		}
		
		try 
		{
			$gehaltsbestandteile = $vertragsbestandteil->getGehaltsbestandteile();
			$this->GehaltsbestandteilLib->storeGehaltsbestandteile($gehaltsbestandteile);
		} 
		catch(Exception $ex) 
		{
			throw new Exception('VertragsbestandteilLib insertVertragsbestandteil '
				. 'failed to store Gehaltsbestandteile. ' . $ex->getMessage());
		}
	}
	
	protected function updateVertragsbestandteil(Vertragsbestandteil $vertragsbestandteil)
	{
		$vertragsbestandteil->setUpdateamum(strftime('%Y-%m-%d %H:%M:%S'));
		$vertragsbestandteil->beforePersist();
		$ret = $this->VertragsbestandteilModel->update($vertragsbestandteil->getVertragsbestandteil_id(), 
			$vertragsbestandteil->baseToStdClass());
		
		if(isError($ret) )
		{
			throw new Exception('error updating vertragsbestandteil');
		}
		
		$specialisedModel = VertragsbestandteilFactory::getVertragsbestandteilDBModel(
			$vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
		$retspecial = $specialisedModel->update($vertragsbestandteil->getVertragsbestandteil_id(), 
			$vertragsbestandteil->toStdClass());
		
		if(isError($retspecial) )
		{
			throw new Exception('error updating vertragsbestandteil ' 
				. $vertragsbestandteil->getVertragsbestandteiltyp_kurzbz());
		}
		
		try 
		{
			$gehaltsbestandteile = $vertragsbestandteil->getGehaltsbestandteile();
			$this->GehaltsbestandteilLib->storeGehaltsbestandteile($gehaltsbestandteile);
		} 
		catch(Exception $ex) 
		{
			throw new Exception('VertragsbestandteilLib updateVertragsbestandteil '
				. 'failed to store Gehaltsbestandteile. ' . $ex->getMessage());
		}
	}
}
