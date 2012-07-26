<?php
/**
 * Totals manager for the order class
 *
 * @package   Order\TotalManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     1.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderTotalManager
{

	/**
	 * @var OrderTotal[]
	 */
	public $totals = array();

	/**
	 *
	 */
	public function __construct()
	{
		$this->loadModules();
	}

	/**
	 * This function is overridden in all other total managers that need to use
	 * their own custom total class
	 *
	 * @return OrderTotal
	 */
	public function getTotalClass()
	{
		return new OrderTotal();
	}

	/**
	 *
	 */
	public function loadModules()
	{
		$Paths = array();

		if (is_dir(sysConfig::getDirFsCatalog() . 'clientData/includes/classes/Order/TotalManager/modules')){
			$Paths[] = sysConfig::getDirFsCatalog() . 'clientData/includes/classes/Order/TotalManager/modules/';
		}

		$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions');
		foreach($Dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isFile()){
				continue;
			}

			if (is_dir($dInfo->getPathname() . '/classes/Order/TotalManager/modules')){
				$Paths[] = $dInfo->getPathname() . '/classes/Order/TotalManager/modules/';
			}
		}

		$Paths[] = realpath(__DIR__) . '/modules/';

		foreach($Paths as $Path){
			$Dir = new DirectoryIterator($Path);
			foreach($Dir as $dInfo){
				if ($dInfo->isDot() || $dInfo->isFile() || in_array($dInfo->getBasename(), $this->loadedModules)){
					continue;
				}

				if (file_exists($dInfo->getPathname() . '/module.php')){
					require($dInfo->getPathname() . '/module.php');
					$ClassName = 'OrderTotalModule' . ucfirst($dInfo->getBasename());

					$TotalModule = new $ClassName();

					$SaleTotal = $this->getTotalClass();
					$SaleTotal->setModule($TotalModule);
					if ($SaleTotal->isEnabled() === true){
						$this->add($SaleTotal);
					}
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function prepareSave(){
		$toEncode = array();
		foreach($this->getAll() as $Total){
			$toEncode[] = $Total->prepareSave();
		}
		return $toEncode;
	}

	/**
	 * load()
	 *
	 * Takes a JSON encoded string from the database and sends it to the
	 * order total class to be loaded.
	 *
	 * @internal The total_json value is already decoded to an associative array in the model
	 * @param AccountsReceivableSalesTotals $Totals
	 */
	public function loadDatabaseData($Totals)
	{
		foreach($Totals as $Total){
			$OrderTotal = $this->getTotalClass();
			$OrderTotal->loadDatabaseData($Total->total_json);

			$this->add($OrderTotal);
		}
	}

	/**
	 * onSaveSale()
	 *
	 * Goes through all totals and adds them to the database
	 *
	 * @param AccountsReceivableSalesTotals $SaleTotals
	 */
	public function onSaveSale(&$SaleTotals)
	{
		foreach($this->getAll() as $Total){
			$SaleTotal = $SaleTotals
				->getTable()
				->getRecord();

			$Total->onSaveSale($SaleTotal);

			$SaleTotals->add($SaleTotal);
		}
	}

	/**
	 * @param OrderTotal $orderTotal
	 */
	public function add(OrderTotal $orderTotal)
	{
		$this->totals[$orderTotal->getCode()] = $orderTotal;
	}

	/**
	 * @param string $ModuleCode
	 */
	public function remove($ModuleCode)
	{
		if (isset($this->totals[$ModuleCode]) === true){
			unset($this->totals[$ModuleCode]);
		}
	}

	/**
	 * @param string $type
	 * @return float|null
	 */
	public function getTotalValue($type)
	{
		$OrderTotal = $this->get($type);
		if ($OrderTotal !== false){
			return (float)$OrderTotal->getValue();
		}
		return null;
	}

	/**
	 * @param $code
	 * @return bool
	 */
	public function has($code)
	{
		return (isset($this->totals[$code]) === true);
	}

	/**
	 * @param string $moduleType
	 * @return bool|OrderTotal
	 */
	public function &get($moduleType)
	{
		if (!isset($this->totals[$moduleType])){
			die('Module Is Not Loaded: ' . $moduleType);
		}
		return $this->totals[$moduleType];
	}

	/**
	 * @return OrderTotal[]
	 */
	public function getAll()
	{
		$TotalsSorted = $this->totals;
		//echo '<pre>';print_r($TotalsSorted);
		usort($TotalsSorted, function ($a, $b)
		{
			return ($a->getDisplayOrder() < $b->getDisplayOrder() ? -1 : 1);
		});
		return $TotalsSorted;
	}

	/**
	 * @param Order $Sale
	 */
	public function updateSale(Order &$Sale){
		foreach($this->getAll() as $Module){
			$Module->updateSale($Sale);
		}
	}

	/**
	 * getEmailList()
	 *
	 * Returns a string broken up into multiple lines of the totals in the sale
	 *
	 * @return string
	 */
	public function getEmailList()
	{
		$orderTotals = '';
		foreach($this->getAll() as $OrderTotal){
			$orderTotals .= strip_tags($OrderTotal->getTitle()) . ' ' . strip_tags($OrderTotal->getText()) . "\n";
		}
		return $orderTotals;
	}

	/**
	 * @param $addColumns
	 * @param $CurrentRow
	 * @param $HeaderRow
	 */
	public function onExport($addColumns, &$CurrentRow, &$HeaderRow)
	{
		foreach($this->getAll() as $OrderTotal){
			$Code = $OrderTotal->getCode();
			if ($HeaderRow->hasColumn('v_total_' . $Code) === false){
				$HeaderRow->addColumn('v_total_' . $Code);
			}
			$OrderTotal->onExport($addColumns, &$CurrentRow, &$HeaderRow);
		}
	}
}

require(__DIR__ . '/Total.php');
