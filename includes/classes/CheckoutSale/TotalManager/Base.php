<?php
/**
 * Order total manager class for the checkout sale class
 *
 * @package   CheckoutSale\TotalManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class CheckoutSaleTotalManager extends OrderTotalManager
{

	/**
	 * This function is overridden in all other total managers that need to use
	 * their own custom total class
	 *
	 * @return CheckoutSaleTotal|OrderTotal
	 */
	public function getTotalClass()
	{
		return new CheckoutSaleTotal();
	}

	/**
	 * Used from init method in CheckoutSale class
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
