<?php
defined('BASEPATH') || exit('No direct script access allowed');
/**
 * Description of DashboardLib
 *
 * @author bambi
 */
class DashboardLib
{
	const WIDGET_ID_RANDOM_BYTES = 16;
	const DEFAULT_DASHBOARD_KURZBZ = 'fhcomplete';
	
	private $_ci; // CI instance
	
	public function __construct($params=null)
	{
		// Loads CI instance
		$this->_ci =& get_instance();
		
		$this->_ci->load->model('dashboard/Dashboard_model', 'DashboardModel');
		$this->_ci->load->model('dashboard/Dashboard_Preset_model', 'DashboardPresetModel');
		$this->_ci->load->model('dashboard/Dashboard_Override_model', 'DashboardOverrideModel');
	}

	public function generateWidgetId($dashboard_kurzbz='') 
	{
		$dashboard_kurzbz = (!empty($dashboard_kurzbz)) ? $dashboard_kurzbz 
			: self::DEFAULT_DASHBOARD_KURZBZ;
		$widgetid_input = time() . '_' . $dashboard_kurzbz . '_' 
			. bin2hex(random_bytes(self::WIDGET_ID_RANDOM_BYTES));
		$widgetid = md5($widgetid_input);
		return array(
			'widgetid' => $widgetid,
			'widgetid_input' => $widgetid_input
		);
	}
	
	public function getDashboardByKurzbz($dashboard_kurzbz) 
	{
		$dashboard = null;
		$result = $this->_ci->DashboardModel->getDashboardByKurzbz($dashboard_kurzbz);
		if( isSuccess($result) && ($dashboards = getData($result)) ) 
		{
			$dashboard = $dashboards[0];
		}
		return $dashboard;
	}
	
	public function getMergedConfig($dashboard_id, $uid) 
	{
		$defaultconfig = $this->getDefaultConfig($dashboard_id, $uid);
		$userconfig = $this->getUserConfig($dashboard_id, $uid);
		
		$mergedconfig = array_replace_recursive($defaultconfig, $userconfig);
		
		return $mergedconfig;
	}
	
	public function getDefaultConfig($dashboard_id, $uid)
	{
		$res_presets = $this->_ci->DashboardPresetModel->getPresets($dashboard_id, $uid);
		$defaultconfig = array();
		
		if( isSuccess($res_presets) && hasData($res_presets) ) 
		{
			$presets = getData($res_presets);
			foreach ($presets as $presetobj)
			{
				if( null !== ($preset = json_decode($presetobj->preset, true)) )
				{
					$defaultconfig = array_replace_recursive($defaultconfig, 
						$preset);
				}
			}
		}
		
		return $defaultconfig;
	}
	
	public function getUserConfig($dashboard_id, $uid)
	{
		$res_userconfig = $this->_ci->DashboardOverrideModel->getOverride($dashboard_id, $uid);
		$userconfig = array();
		
		if( isSuccess($res_userconfig) && hasData($res_userconfig) ) 
		{
			$data = getData($res_userconfig);
			if( null !== ($decodedconfig = json_decode($data[0]->override, true)) )
			{
				$userconfig = $decodedconfig;
			}
		}
		
		return $userconfig;
	}
	
	public function getOverrideOrCreateEmptyOverride($dashboard_kurzbz, $uid) 
	{
		$override = $this->getOverride($dashboard_kurzbz, $uid);
		if( null !== $override ) {
			return $override;			
		}
		
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		
		$emptyoverride = new stdClass();
		$emptyoverride->dashboard_id = $dashboard->dashboard_id;
		$emptyoverride->uid = $uid;
		$emptyoverride->override = '{"widgets": {}}';
		
		return $emptyoverride;
	}
	
	public function getPresetOrCreateEmptyPreset($dashboard_kurzbz, $funktion_kurzbz) 
	{
		$preset = $this->getPreset($dashboard_kurzbz, $funktion_kurzbz);
		if( null !== $preset ) {
			return $preset;			
		}
		
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		
		$emptypreset = new stdClass();
		$emptypreset->dashboard_id = $dashboard->dashboard_id;
		$emptypreset->funktion_kurzbz = $funktion_kurzbz;
		$emptypreset->preset = '{"widgets": {}}';
		
		return $emptypreset;
	}
	
	public function getPreset($dashboard_kurzbz, $funktion_kurzbz) 
	{
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		$preset = null;
		
		$result = $this->_ci->DashboardPresetModel
			->getPresetByDashboardAndFunktion($dashboard->dashboard_id, $funktion_kurzbz);
		
		if( isSuccess($result) && hasData($result) && ($presets = getData($result)) ) 
		{
			$preset = $presets[0];
		}
		
		return $preset;
	}

	public function getOverride($dashboard_kurzbz, $uid) 
	{
		$dashboard = $this->getDashboardByKurzbz($dashboard_kurzbz);
		$override = null;
		
		$result = $this->_ci->DashboardOverrideModel
			->getOverride($dashboard->dashboard_id, $uid);
		
		if( isSuccess($result) && hasData($result) && ($overrides = getData($result)) ) 
		{
			$override = $overrides[0];
		}
		
		return $override;
	}
	
	public function insertOrUpdatePreset($preset) 
	{
		if( isset($preset->preset_id) && $preset->preset_id > 0 ) 
		{
			$result = $this->_ci->DashboardPresetModel->update($preset->preset_id, $preset);
		} else 
		{
			$result = $this->_ci->DashboardPresetModel->insert($preset);
		}
		
		return $result;
	}
	
	public function insertOrUpdateOverride($override) 
	{
		if( isset($override->override_id) && $override->override_id > 0 ) 
		{
			$result = $this->_ci->DashboardOverrideModel->update($override->override_id, $override);
		} else 
		{
			$result = $this->_ci->DashboardOverrideModel->insert($override);
		}
		
		return $result;
	}	
}
