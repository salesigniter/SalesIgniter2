<?php
class DataManagementModules extends SystemModulesLoader
{

	public static $dir = 'dataManagementModules';

	public static $classPrefix = 'DataManagementModule';
}

class DataManagementModuleBase extends ModuleBase
{

	protected $format;

	protected $action;

	protected $importFile = null;

	public function setFormat($val) {
		$this->format = $val;
	}

	public function setAction($val) {
		$this->action = $val;
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

	public function perform() {
		switch($this->action){
			case 'import':
				$this->runImport();
				break;
			case 'export':
				$this->runExport();
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

require(sysConfig::getDirFsAdmin() . 'includes/classes/upload.php');

$appContent = $App->getAppContentFile();

$separator = "\t";
$default_image_manufacturer = '';
$default_image_product = '';
$default_image_category = '';
$active = 'Active';
$inactive = 'Inactive';
$deleteStatus = 'Delete';
$zero_qty_inactive = false;
$replace_quotes = false;

$showLogInfo = false;

/* SHOULD NOT BE HERE, IT IS IN THE GENERAL REMOVED AND SHOULD BE MOVED BACK THERE IF YOU NEED IT */
function tep_get_tax_class_rate($tax_class_id) {
	$tax_multiplier = 0;
	$QtaxRate = Doctrine_Manager::getInstance()
		->getCurrentConnection()
		->fetchAssoc("select SUM(tax_rate) as tax_rate from tax_rates WHERE  tax_class_id = '" . $tax_class_id . "' GROUP BY tax_priority");
	if (sizeof($QtaxRate)){
		foreach($QtaxRate as $tax){
			$tax_multiplier += $tax['tax_rate'];
		}
	}
	return $tax_multiplier;
}

function tep_get_tax_title_class_id($tax_class_title) {
	$QtaxClass = Doctrine_Manager::getInstance()
		->getCurrentConnection()
		->fetchAssoc("select tax_class_id from tax_class WHERE tax_class_title = '" . $tax_class_title . "'");
	$tax_class_id = $QtaxClass[0]['tax_class_id'];
	return $tax_class_id;
}

//if (isset($_POST['buttoninsert'])) $action = 'importProducts';
//if (isset($_POST['buttonsplit'])) $action = 'splitFile';
//if (isset($_POST['buttoninserttemp'])) $action = 'importProducts';
?>