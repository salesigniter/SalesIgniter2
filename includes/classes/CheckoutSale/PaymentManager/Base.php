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

	protected $_info = array();

	/**
	 * @var PaymentModuleBase
	 */
	protected $Module = null;

	public function setPaymentModule($module){
		$this->setInfo('payment_module', $module);
		$this->Module = OrderPaymentModules::getModule($module);
	}

	public function setInfo($k, $v){
		$this->_info[$k] = $v;
	}

	public function getInfo($k = null){
		if ($k !== null){
			return $this->_info[$k];
		}
		return $this->_info;
	}

	public function validate(){
		$validateSuccess = true;
		$validateSuccess = $this->Module->validate($this);
		return $validateSuccess;
	}

	public function getHistory()
	{
		return $this->History;
	}

	/**
	 * @param array       $paymentInfo
	 * @param null|Orders $CollectionObj
	 * @return array|bool
	 */
	public function processPayment(array $paymentInfo, Orders &$CollectionObj = null)
	{
		global $Editor;
		$Module = OrderPaymentModules::getModule($paymentInfo['payment_method']);
		if (is_null($CollectionObj) === false){
			$Module->logToCollection($CollectionObj);
		}

		$Address = $Editor->AddressManager->getAddress('billing');
		if (is_object($Address) === false){
			$Address = $Editor->AddressManager->getAddress('customer');
		}

		$RequestData = array(
			'amount'                => $paymentInfo['payment_amount'],
			'currencyCode'          => $Editor->getCurrency(),
			'orderID'               => $Editor->getOrderId(),
			'description'           => (isset($paymentInfo['comments']) && !empty($paymentInfo['comments']) ? $paymentInfo['comments'] : 'Administration Order Payment'),
			'customerId'            => $Editor->getCustomerId(),
			'customerEmail'         => $Editor->getEmailAddress(),
			'customerTelephone'     => $Editor->getTelephone(),
			'customerFirstName'     => $Address->getFirstName(),
			'customerLastName'      => $Address->getLastName(),
			'customerStreetAddress' => $Address->getStreetAddress(),
			'customerPostcode'      => $Address->getPostcode(),
			'customerCity'          => $Address->getCity(),
			'customerState'         => $Address->getState(),
			'customerCountry'       => $Address->getCountry()
		);

		if (sysConfig::get('ACCOUNT_COMPANY') == 'true'){
			$RequestData['customerCompany'] = $Address->getCompany();
		}

		if (isset($paymentInfo['cardNumber']) && $paymentInfo['cardNumber'] != '' && $paymentInfo['cardExpMonth'] != '' && $paymentInfo['cardExpYear'] != '' && $paymentInfo['cardCvvNumber'] != ''){
			$RequestData['cardNum'] = $paymentInfo['cardNumber'];
			$RequestData['cardExpDate'] = $paymentInfo['cardExpMonth'] . $paymentInfo['cardExpYear'];
			if (count($expDate) == 2){
				$RequestData['cardExpDateCIM'] = $paymentInfo['cardExpMonth'] . '-' . $paymentInfo['cardExpYear'];
			}

			$RequestData['cardCvv'] = $paymentInfo['cardCvvNumber'];
		}

		$success = $Module->sendPaymentRequest($RequestData);
		if ($success === true){
			return true;
		}
		else {
			return array(
				'error_message' => $Module->getErrorMessage()
			);
		}
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		$data = array(
			'orderId'       => $this->orderId,
			'History'       => $this->History,
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