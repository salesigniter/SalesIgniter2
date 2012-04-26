<?php
/*
	Sales Igniter E-Commerce Store Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2011 I.T. Web Experts

	This script and it's source is not redistributable
*/

class ModuleBase extends MI_Base
{

	/**
	 * @var SimpleXMLElement
	 */
	private $moduleInfo;

	/**
	 * @var ModuleConfigReader
	 */
	private $Config;

	/**
	 * @var bool
	 */
	private $enabled = false;

	/**
	 * @var string
	 */
	private $code = '';

	/**
	 * @var string
	 */
	private $moduleType = '';

	/**
	 * @var string
	 */
	private $title = 'No Title Set';

	/**
	 * @var string
	 */
	private $description = 'No Description Set';

	/**
	 * @var string
	 */
	private $path = '';

	/**
	 * @var string
	 */
	private $relativePath = '';

	/**
	 * @param string $code
	 * @param bool $forceEnable
	 * @param bool $moduleDir
	 */
	public function init($code, $forceEnable = false, $moduleDir = false) {
		$this->setCode($code);

		if ($moduleDir === false){
			$this->setPath(sysConfig::getDirFsCatalog() . 'includes/modules/' . $this->getModuleType() . 'Modules/' . $this->getCode() . '/');
			$this->setRelativePath('includes/modules/' . $this->getModuleType() . 'Modules/' . $this->getCode() . '/');
		}
		else {
			$this->setPath($moduleDir);
			$this->setRelativePath(str_replace(sysConfig::getDirFsCatalog(), '', $moduleDir));
		}

		$this->moduleInfo = simplexml_load_file(
			$this->getPath() . 'data/info.xml',
			'SimpleXMLElement',
			LIBXML_NOCDATA
		);

		$this->Config = new ModuleConfigReader(
			$this->getCode(),
			$this->getModuleType(),
			$this->getPath()
		);

		sysLanguage::loadDefinitions($this->getPath() . 'language_defines/global.xml');
		if (file_exists(sysConfig::getDirFsCatalog() . 'includes/languages/' . Session::get('language') . '/includes/modules/' . $this->getModuleType() . 'Modules/' . $this->getCode() . '/global.xml')){
			sysLanguage::loadDefinitions(sysConfig::getDirFsCatalog() . 'includes/languages/' . Session::get('language') . '/includes/modules/' . $this->getModuleType() . 'Modules/' . $this->getCode() . '/global.xml');
		}

		if (is_dir(sysConfig::getDirFsCatalog() . 'includes/modules/' . $this->getModuleType() . 'Modules/' . $this->getCode() . '/Doctrine/')){
			Doctrine_Core::loadModels(sysConfig::getDirFsCatalog() . 'includes/modules/' . $this->getModuleType() . 'Modules/' . $this->getCode() . '/Doctrine/', Doctrine_Core::MODEL_LOADING_AGGRESSIVE);
		}

		$this->setTitle(sysLanguage::get((string)$this->moduleInfo->title_key));
		$this->setDescription(sysLanguage::get((string)$this->moduleInfo->description_key));

		if ($this->configExists((string)$this->moduleInfo->status_key)){
			$this->setEnabled(($this->getConfigData((string)$this->moduleInfo->status_key) == 'True' ? true : false));
		}

		if ($forceEnable === true){
			$this->setEnabled(true);
		}

		if ($this->imported('Installable')){
			$this->setInstalled(($this->getConfigData($this->getModuleInfo('installed_key')) == 'True') ? true : false);
		}

		if ($this->imported('SortedDisplay')){
			$this->setDisplayOrder((int)$this->getConfigData($this->getModuleInfo('display_order_key')));
		}
	}

	/**
	 * @param $k
	 * @return null|string
	 */
	public function getModuleInfo($k) {
		if (isset($this->moduleInfo->$k)){
			return (string)$this->moduleInfo->$k;
		}
		return null;
	}

	/**
	 * @param bool $val
	 */
	public function setEnabled($val) {
		$this->enabled = $val;
	}

	/**
	 * @return bool
	 */
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	 * @return bool
	 */
	public function isFromExtension() {
		return false;
	}

	/**
	 * @return bool
	 */
	public function getExtensionName() {
		return false;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function configExists($key) {
		return ($this->Config->getConfig($key) != null);
	}

	/**
	 * @return ModuleConfigReader
	 */
	public function getConfig() {
		return $this->Config;
	}

	/**
	 * @param $key
	 * @return mixed
	 */
	public function getConfigData($key) {
		if ($this->configExists($key)){
			return $this->Config->getConfig($key)->getValue();
		}
		$backtrace = debug_backtrace();
		$debugInfo = array(
			'calledMethod' => $method,
			'calledFromFile' => $backtrace[0]['file'],
			'calledFromLine' => $backtrace[0]['line'],
			'callArgs' => $backtrace[0]['args'][1]
		);
		echo '<pre>';print_r($debugInfo);
		die('You should be verifying a configuration key exists: Configuration Group (' . $this->code . ') -> Configuration Key (' . $key . ')');
	}

	/**
	 * @param string $val
	 */
	public function setModuleType($val) {
		$this->moduleType = $val;
	}

	/**
	 * @return string
	 */
	public function getModuleType() {
		return $this->moduleType;
	}

	/**
	 * @param string $val
	 */
	public function setPath($val) {
		if (substr($val, -1) != DIRECTORY_SEPARATOR){
			$val .= DIRECTORY_SEPARATOR;
		}
		$this->path = $val;
	}

	/**
	 * @return string
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * @param string $val
	 */
	public function setRelativePath($val) {
		$this->relativePath = $val;
	}

	/**
	 * @return string
	 */
	public function getRelativePath() {
		return $this->relativePath;
	}

	/**
	 * @param string $val
	 */
	public function setCode($val) {
		$this->code = $val;
	}

	/**
	 * @return string
	 */
	public function getCode() {
		return $this->code;
	}

	/**
	 * @param string $val
	 */
	public function setTitle($val) {
		$this->title = $val;
	}

	/**
	 * @return string
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * @param string $val
	 */
	public function setDescription($val) {
		$this->description = $val;
	}

	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}
}
