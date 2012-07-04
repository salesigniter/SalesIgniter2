<?php
/**
 * Order total manager class for the checkout sale class
 *
 * @package   Order\CheckoutSale\TotalManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class CheckoutSaleTotalManager extends OrderTotalManager
{

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

	/**
	 * Used when loading the sale from the database
	 *
	 * @param AccountsReceivableSalesTotals $Total
	 */
	public function jsonDecodeTotal(AccountsReceivableSalesTotals $Total)
	{
		$TotalDecoded = json_decode($Total->total_json, true);
		$OrderTotal = new CheckoutSaleTotal($TotalDecoded['data']['module_code']);
		$OrderTotal->jsonDecode($TotalDecoded);

		$this->add($OrderTotal);
	}

	/**
	 * Used from init method in CheckoutSale class
	 *
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$this->totals = array();
		//echo __FILE__ . '::' . __LINE__ . '::' . $data . '<br>';
		$Totals = json_decode($data, true);
		foreach($Totals as $tInfo){
			$OrderTotal = new CheckoutSaleTotal();
			$OrderTotal->jsonDecode($tInfo);

			$this->add($OrderTotal);
		}
	}
}

require(dirname(__FILE__) . '/Total.php');
