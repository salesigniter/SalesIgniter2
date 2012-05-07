<?php
class DataManagementModules extends SystemModulesLoader
{

	public static $dir = 'dataManagementModules';

	public static $classPrefix = 'DataManagementModule';

	/**
	 * @static
	 * @param $moduleName
	 * @param bool $ignoreStatus
	 * @return DataManagementModuleBase
	 */
	public static function getModule($moduleName, $ignoreStatus = false){
		return parent::getModule($moduleName, $ignoreStatus);
	}
}

class DataManagementModuleBase extends ModuleBase
{

	protected $format;

	protected $action;

	protected $importFile = null;

	protected $exportIds = array();

	protected $exportColumns = array();

	public function setFormat($val) {
		$this->format = $val;
	}

	public function setAction($val) {
		$this->action = $val;
	}

	public function getSupportedColumns(){
		return array();
	}

	public function getSupportedActions() {
		return array(
			'import' => 'Import File',
			'export' => 'Export File'
		);
	}

	public function getSupportedFormats() {
		return array(
			'csv' => 'Comma Delimited File ( .csv )'/*,
			'tsv' => 'Tab Delimited File ( .tsv )',
			'xml' => 'XML File ( .xml )'*/
		);
	}

	public function setExportIds(array $val){
		$this->exportIds = $val;
	}

	public function setExportColumns(array $val){
		$this->exportColumns = $val;
	}

	public function perform() {
		switch($this->action){
			case 'import':
				$this->runImport();
				break;
			case 'export':
				$this->runExport($this->exportIds, $this->exportColumns);
				break;
		}
	}

	public function beforeActionProcess(){
	}

	public function afterActionProcess(){
	}

	public function getExportFileWriter(){
		$className = 'FileWriter' . ucfirst($this->format);
		if (class_exists($className) === false){
			require(sysConfig::getDirFsCatalog() . 'includes/classes/FileWriter/' . $this->format . '.php');
		}
		return new $className('temp');
	}

	public function getImportFileReader(){
		$className = 'FileParser' . ucfirst($this->format);
		if (class_exists($className) === false){
			require(sysConfig::getDirFsCatalog() . 'includes/classes/FileParser/' . $this->format . '.php');
		}
		return new $className($this->importFile);
	}

	public function setImportFile($filePath){
		$this->importFile = $filePath;
	}

	public function getMemoryLimit(){
		$MemoryLimit = ini_get('memory_limit');
		if (!is_int($MemoryLimit)){
			if (substr($MemoryLimit, -1) == 'M'){
				$MemoryLimit = (int) substr($MemoryLimit, 0, -1) * (1024 * 1000);
			}elseif (substr($MemoryLimit, -1) == 'K'){
				$MemoryLimit = (int) substr($MemoryLimit, 0, -1) * 1024;
			}elseif (substr($MemoryLimit, -1) == 'B'){
				$MemoryLimit = (int) substr($MemoryLimit, 0, -1);
			}
		}
		return $MemoryLimit;
	}

	public function checkMemoryThreshold($numOfItems, $totalItems){
		$MemoryLimit = $this->getMemoryLimit();
		$MemoryThreshold = (1024 * 10000);
		if (memory_get_usage(true) + $MemoryThreshold > $MemoryLimit){
			$MemoryLimit += $MemoryThreshold;
			if (ini_set('memory_limit', $MemoryLimit) === false){
				echo 'Unable to increase memory limit.<br>';
				echo 'Used ' . memory_get_usage(true) . ' / ' . $MemoryLimit . ' Memory, processed ' . $x . ' out of ' . $totalItems . ' items<br><br>';
				if ($ExceptionManager->size() > 0){
					echo $ExceptionManager->output();
				}
				if ($messageStack->size('footerStack') > 0){
					echo $messageStack->output('footerStack');
				}
				die();
			}
		}
	}
}
