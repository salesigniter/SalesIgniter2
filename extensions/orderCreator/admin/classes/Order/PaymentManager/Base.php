<?php
/**
 * Payment manager class for the order creator
 *
 * @package   OrderCreator
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorPaymentManager extends OrderPaymentManager
{

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
			'saleId'                => $Editor->getSaleId(),
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
	 * @param string      $moduleName
	 * @param int         $history_id
	 * @param float       $amount
	 * @param Orders|null $CollectionObj
	 * @return array|bool
	 */
	public function refundPayment($moduleName, $history_id, $amount, Orders &$CollectionObj = null)
	{
		global $Editor;
		$Module = OrderPaymentModules::getModule($moduleName);
		if (is_null($CollectionObj) === false){
			$Module->logToCollection($CollectionObj);
		}

		$Qhistory = Doctrine_Query::create()
			->from('OrdersPaymentsHistory')
			->where('payment_history_id = ?', $history_id)
			->limit(1)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		$paymentHistory = $Qhistory[0];

		$requestData = array(
			'amount'        => (isset($amount) ? $amount : $paymentHistory['payment_amount']),
			'saleId'        => $paymentHistory['orders_id'],
			'transactionID' => $paymentHistory['gateway_message'],
			'cardDetails'   => unserialize(cc_decrypt($paymentHistory['card_details'])),
			'is_refund'     => 1
		);

		$success = $Module->refundPayment($requestData);
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