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
	 * Used from init method in OrderCreator class
	 *
	 * @param array $Totals
	 */
	public function init(array $Totals)
	{
		$this->totals = array();
		foreach($Totals as $tInfo){
			$OrderTotal = $this->getTotalClass();
			$OrderTotal->init($tInfo);

			$this->add($OrderTotal);
		}
	}

	/**
	 * @return array
	 */
	public function prepareSave()
	{
		$TotalsJsonArray = array();
		foreach($this->getAll() as $Total){
			$TotalsJsonArray[] = $Total->prepareSave();
		}
		//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($ProductsJsonArray);
		return $TotalsJsonArray;
	}

	/**
	 * @param AccountsReceivableSalesTotals $SaleTotals
	 */
	public function onSaveProgress(AccountsReceivableSalesTotals &$SaleTotals)
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

	/**
	 *
	 */
	public function updateFromPost()
	{
		global $currencies, $Editor;
		foreach($_POST['order_total'] as $id => $tInfo){
			$OrderTotal = $this->get($tInfo['type']);

			$addTotal = false;
			if (is_null($OrderTotal) === true){
				$OrderTotal = new OrderCreatorTotal();
				$OrderTotal->setModule($tInfo['type']);
				$addTotal = true;
			}

			$value = $tInfo['value'];
			if (substr($value, -3, 1) == ',' || substr($value, -5, 1) == ','){
				$value = str_replace(',', '.', $value);
				$value[strpos($value, '.')] = '';
			}
			else {
				$value = str_replace(',', '', $value);
			}

			$OrderTotal->setSortOrder($tInfo['sort_order']);
			$OrderTotal->setTitle($tInfo['title']);
			$OrderTotal->setValue($value);
			$OrderTotal->setText($currencies->format($value, true, $Editor->getCurrency(), $Editor->getCurrencyValue()));
			$OrderTotal->setModule($tInfo['type']);
			$OrderTotal->setMethod(null);

			if ($addTotal === true){
				$this->totals[$OrderTotal->getModule()] = $OrderTotal;
			}

			if ($tInfo['type'] == 'shipping'){
				$shipModule = explode('_', $tInfo['title']);
				$OrderTotal->setModule($shipModule[0]);
				$OrderTotal->setMethod($shipModule[1]);

				$Module = OrderShippingModules::getModule($shipModule[0]);
				$Quote = $Module->quote($shipModule[1]);
				$OrderTotal->setTitle($Quote['module'] . ' ( ' . $Quote['methods'][0]['title'] . ' ) ');
				$Editor->setShippingModule($tInfo['title']);
			}
		}
	}

	/**
	 * @param string $key
	 * @param float  $amount
	 */
	public function addToTotal($key, $amount)
	{
		foreach($this->totals as $OrderTotal){
			if ($OrderTotal->getModule() == $key){
				$OrderTotal->setValue($OrderTotal->getValue() + $amount);
			}
		}
	}
}

require(__DIR__ . '/Total.php');
