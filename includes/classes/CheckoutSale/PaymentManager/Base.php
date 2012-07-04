<?php
/**
 * Payment manager class for the checkout sale class
 *
 * @package    CheckoutSale\PaymentManager
 * @author     Stephen Walker <stephen@itwebexperts.com>
 * @since      2.0
 * @copyright  2012 I.T. Web Experts
 * @license    http://itwebexperts.com/license/ses-license.php
 */

class CheckoutSalePaymentManager extends OrderPaymentManager
{

	public function validate()
	{
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