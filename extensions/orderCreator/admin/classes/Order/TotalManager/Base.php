<?php
/**
 * Order total manager class for the order creator
 *
 * @package   OrderCreator\TotalManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorTotalManager extends OrderTotalManager
{

	/**
	 * This function is overridden in all other total managers that need to use
	 * their own custom total class
	 *
	 * @return OrderCreatorTotal|OrderTotal
	 */
	public function getTotalClass()
	{
		return new OrderCreatorTotal();
	}

	/**
	 *
	 */
	public function loadModules()
	{
		$Paths = array();

		if (is_dir(sysConfig::getDirFsCatalog() . 'clientData/extensions/orderCreator/admin/classes/Order/TotalManager/modules')){
			$Paths[] = sysConfig::getDirFsCatalog() . 'clientData/extensions/orderCreator/admin/classes/Order/TotalManager/modules/';
		}

		$Dir = new DirectoryIterator(sysConfig::getDirFsCatalog() . 'extensions');
		foreach($Dir as $dInfo){
			if ($dInfo->isDot() || $dInfo->isFile()){
				continue;
			}

			if (is_dir($dInfo->getPathname() . '/extensions/orderCreator/admin/classes/Order/TotalManager/modules')){
				$Paths[] = $dInfo->getPathname() . '/extensions/orderCreator/admin/classes/Order/TotalManager/modules/';
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
					$ClassName = 'OrderCreatorTotalModule' . ucfirst($dInfo->getBasename());

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
	 * Used from init method in OrderCreator class
	 *
	 * @param array $Totals
	 */
	public function loadSessionData(array $Totals)
	{
		foreach($Totals as $TotalInfo){
			$OrderTotal =& $this->get($TotalInfo['data']['module_code']);
			$OrderTotal->loadSessionData($TotalInfo);
		}
	}

	/**
	 * @param AccountsReceivableSalesTotals $SaleTotals
	 */
	public function onSaveProgress(&$SaleTotals)
	{
		$SaleTotals->clear();
		foreach($this->getAll() as $Total){
			$SaleTotal = $SaleTotals
				->getTable()
				->getRecord();

			$Total->onSaveProgress($SaleTotal);

			$SaleTotals->add($SaleTotal);
		}
	}
}

require(__DIR__ . '/Total.php');
