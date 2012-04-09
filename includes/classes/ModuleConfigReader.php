<?php
class ModuleConfigReader extends ConfigurationReader
{

	/**
	 * @var string
	 */
	private $module;

	/**
	 * @var string
	 */
	private $moduleType;

	/**
	 * @param string $module
	 * @param string $moduleType
	 * @param bool $moduleDir
	 */
	public function __construct($module, $moduleType, $moduleDir = false) {
		$this->module = $module;
		$this->moduleType = $moduleType;

		if ($moduleDir === false){
			$moduleDir = sysConfig::getDirFsCatalog() . 'includes/modules/' . $this->moduleType . 'Modules/' . $this->module . '/';
		}
		$this->loadConfiguration($moduleDir . 'data/config.xml');

		$Extensions = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions');
		foreach($Extensions as $Ext){
			if ($Ext->isDot() || $Ext->isFile()) {
				continue;
			}

			if (is_dir($Ext->getPathname() . '/data/base')){
				if (file_exists($Ext->getPathname() . '/data/base/' . $this->moduleType . 'Modules.xml')){
					$this->loadConfiguration($Ext->getPathname() . '/data/base/' . $this->moduleType . 'Modules.xml');
				}
			}
		}

		EventManager::notify('ModuleConfigReaderModuleConfigLoad', &$this->configData, $this->module, $this->moduleType);
	}

	/**
	 * @param SimpleXMLElement $xmlObj
	 * @return bool
	 */
	public function check(SimpleXMLElement $xmlObj){
		if (isset($xmlObj['modules'])){
			$AllowedModules = explode(',', $xmlObj['modules']);
			if (!in_array($this->module, $AllowedModules)){
				return false;
			}
		}
		return true;
	}

	/**
	 * @param SimpleXMLElement $xmlObj
	 * @return array
	 */
	public function loadCompareData(SimpleXMLElement $xmlObj){
		$ModuleConfig = array();
		$QModuleConfig = Doctrine_Manager::getInstance()
			->getCurrentConnection()
			->fetchAssoc('select mc.* from modules m left join modules_configuration mc using(modules_id) where m.modules_type = "' . $this->moduleType . '" and m.modules_code = "' . $this->module . '"');
		foreach($QModuleConfig as $cfgInfo){
			$ModuleConfig[$cfgInfo['configuration_key']] = $cfgInfo;
		}
		return $ModuleConfig;
	}
}
