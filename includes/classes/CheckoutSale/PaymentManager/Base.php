<?php
/**
 * Payment manager class for the checkout sale class
 *
 * @package   CheckoutSale
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class CheckoutSalePaymentManager extends OrderPaymentManager
{

	/**
	 * @var PaymentModuleBase
	 */
	protected $Module = null;

	public function setPaymentModule($module){
		$this->setInfo('payment_module', $module);
		$this->Module = OrderPaymentModules::getModule($module);
	}

	public function getPaymentModule(){
		return $this->Module;
	}

	public function validate(){
		$validateSuccess = true;
		$validateSuccess = $this->Module->validate($this);
		return $validateSuccess;
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		$data = array(
			'orderId'       => $this->orderId,
			'PaymentsTotal' => $this->PaymentsTotal
		);
		return $data;
	}

	/**
	 * @param string $data
	 */
	public function jsonDecode($data)
	{
		$data = json_decode($data, true);
		foreach($data as $key => $dInfo){
			$this->$key = $dInfo;
		}
	}
}

?>