<?php
/*
	Rental Store Version 2

	I.T. Web Experts
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

class OrderPaymentFdggc2 extends CreditCardModule
{

	private $params = array();

	private $secret;

	public function __construct() {
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('First Data Global Gateway');
		$this->setDescription('First Data Global Gateway Connect 2.0');

		$this->init('fdggc2');

		if ($this->isEnabled() === true){
			$this->removeOrderOnFail = false;
			$this->requireCvv = true;

			$allowedTypes = $this->getConfigData('MODULE_PAYMENT_FDGGC2_ACCEPTED_CC');
			foreach($allowedTypes as $k => $v){
				$this->allowedTypes[$v] = $v;
			}

			//$this->setFormUrl('https://connect.merchanttest.firstdataglobalgateway.com/IPGConnect/gateway/processing');
			$this->setFormUrl('https://connect.firstdataglobalgateway.com/IPGConnect/gateway/processing');
			$this->secret = trim($this->getConfigData('MODULE_PAYMENT_FDGGC2_SECRET'));

			$this->params['txntype'] = 'sale';
			$this->params['timezone'] = 'EST';
			$this->params['storename'] = trim($this->getConfigData('MODULE_PAYMENT_FDGGC2_STORE_ID'));
			$this->params['mode'] = 'payplus';
			$this->params['trxOrigin'] = 'ECI';
		}
	}

	public function beforeProcessPayment(Order $Order) {
		$this->params['oid'] = $Order->getSaleId();
		$this->params[Session::getSessionName()] = Session::getSessionId();

		$this->params['subtotal'] = $Order->TotalManager->getTotalValue('subtotal');
		$this->params['chargetotal'] = $Order->TotalManager->getTotalValue('total');

		$TypeConvert = array(
			'Mastercard' => 'M',
			'Visa' => 'V',
			'Amex' => 'A',
			'Diners' => 'C',
			'JCB' => 'J',
			'Discover' => 'D'
		);

		$this->params['paymentMethod'] = $TypeConvert[$Order->PaymentManager->getInfo('cardType')];
		$this->params['cardnumber'] = $Order->PaymentManager->getInfo('cardNumber');
		$this->params['expmonth'] = $Order->PaymentManager->getInfo('cardExpMonth');
		$this->params['expyear'] = $Order->PaymentManager->getInfo('cardExpYear');
		$this->params['cvm'] = $Order->PaymentManager->getInfo('cardCvvNumber');
		$this->params['cvmnotpres'] = ($Order->PaymentManager->getInfo('cardCvvNumber') == '');

		if ($this->params['mode'] == 'payplus'){
			$BillingAddress = $Order->AddressManager->getAddress('billing');
			$this->params['bcompany'] = $BillingAddress->getCompany();
			$this->params['bname'] = $BillingAddress->getName();
			$this->params['baddr1'] = $BillingAddress->getStreetAddress();
			//$this->params['baddr2'] = '';
			$this->params['bcity'] = $BillingAddress->getCity();
			$this->params['bstate'] = $BillingAddress->getZoneCode();
			//$this->params['bstate2'] = $BillingAddress->getSuburb();
			$this->params['bcountry'] = $BillingAddress->getCountryCode();
			$this->params['bzip'] = $BillingAddress->getPostcode();
			$this->params['phone'] = $Order->InfoManager->getInfo('customers_telephone_number');
			$this->params['fax'] = $Order->InfoManager->getInfo('customers_fax_number');
			$this->params['email'] = $Order->InfoManager->getInfo('customers_email_address');
		}
		$this->params['payment_module'] = $this->getCode();
	}

	public function getHiddenFields() {
		$DateTime = new DateTime('now');
		$DateTime = $DateTime->modify('+3 Seconds');
		$this->params['txndatetime'] = $DateTime->format('Y:m:d-H:i:s');

		$this->params['timezone'] = 'EST';
		//$this->params['timezone'] = 'EDT';

		$this->params['hash'] = $this->params['storename'] . $this->params['txndatetime'] . $this->params['chargetotal'] . $this->secret;
		$hex_str = '';
		for ($i = 0; $i < strlen($this->params['hash']); $i++){
			$hex_str .= dechex(ord($this->params['hash'][$i]));
		}
		$this->params['hash'] = hash('sha256', $hex_str);

		$hiddenFields = '';
		foreach($this->params as $k => $v){
			$hiddenFields .= htmlBase::newInput()
				->setType('hidden')
				->setName($k)
				->setValue($v)
				->draw();
		}
		return $hiddenFields;
	}

	public function ownsProcessPage(){
		if (isset($_GET['payment_module']) && $_GET['payment_module'] == $this->getCode()){
			return true;
		}elseif (isset($_POST['payment_module']) && $_POST['payment_module'] == $this->getCode()){
			return true;
		}
		return false;
	}

	public function afterProcessPayment($success){
		global $messageStack;
		$ResponseStatus = strtolower(trim($_POST['status']));
		switch($ResponseStatus){
			case 'approved':
				break;
			case 'declined':
			case 'duplicate':
			case 'fraud':
				$messageStack->addSession('pageStack', $_POST['fail_reason'], 'error');
				tep_redirect(itw_app_link('paymentError=1', 'checkout', 'default'));
				break;
			default:
				$messageStack->addSession('pageStack', 'An unknown response was recieved from the payment gateway<br>The administrator has been notified, please try again later.', 'error');
				tep_mail('stephen@itwebexperts.com', 'Unknown First Data Response', print_r($_POST, true));
				tep_redirect(itw_app_link('paymentError=1', 'checkout', 'default'));
				break;
		}
	}

	public function sendPaymentRequest($requestData) {
		$CurlRequest = new CurlRequest($this->gatewayUrl);
		$CurlRequest->setData($this->params);
		$CurlResponse = $CurlRequest->execute();

		return $this->onResponse($CurlResponse);
	}

	private function onResponse($CurlResponse, $isCron = false) {
		global $order;
		$response = $CurlResponse->getResponse();
		echo 'RESPONSE::' . $response;
		$response = explode(',', $response);

		$code = /*$response[0]*/1;
		$subCode = /*$response[1]*/2;
		$reasonCode = /*$response[2]*/2;
		$reasonText = /*$response[3]*/2;

		$this->transactionId = /*$response[6]*/4324123432;
		$success = true;
		$errMsg = $reasonText;
		if ($code != 1){
			$success = false;
			switch($code){
				case '':
					$errMsg = 'The server cannot connect to ' . $this->getTitle() . '.  Please check your cURL and server settings.';
					break;
				case '2':
					$errMsg = 'Your credit card was declined ( ' . $code . '-' . $reasonCode . ' ):' . $reasonText;
					break;
				case '3':
					$errMsg = 'There was an error processing your credit card ( ' . $code . '-' . $reasonCode . ' ):' . $reasonText;
					break;
				default:
					$errMsg = 'There was an unspecified error processing your credit card ( ' . $code . '-' . $reasonCode . ' ):' . $reasonText;
					break;
			}
		}

		if ($isCron === true){
			$this->cronMsg = $errMsg;
		}

		if ($success === true || (isset($order) && sysConfig::get('EXTENSION_PAY_PER_RENTALS_PROCESS_SEND') == 'True' && $order->info['total'] == 0)){
			if (isset($order) && sysConfig::get('EXTENSION_PAY_PER_RENTALS_PROCESS_SEND') == 'True' && $order->info['total'] == 0){
				$errMsg = 'Payment on hold';
			}
			$this->onSuccess(array(
				'curlResponse' => $CurlResponse,
				'message'      => $errMsg
			));
		}
		else {
			$this->onFail(array(
				'curlResponse' => $CurlResponse,
				'message'      => $errMsg
			));
		}
		return $success;
	}

	private function onSuccess($info) {
		$RequestData = $info['curlResponse']->getDataRaw();
		$saleId = $RequestData['x_invoice_num'];

		$cardDetails = array(
			'cardOwner'    => $RequestData['x_first_name'] . ' ' . $RequestData['x_last_name'],
			'cardNumber'   => $RequestData['x_card_num'],
			'cardExpMonth' => substr($RequestData['x_exp_date'], 0, 2),
			'cardExpYear'  => substr($RequestData['x_exp_date'], 2),
			'transId'      => (isset($this->transactionId) ? $this->transactionId : '')
		);

		$this->logPayment(array(
			'saleId'     => $saleId,
			'amount'      => $RequestData['x_amount'],
			'message'     => $info['message'],
			'success'     => 1,
			'can_reuse'   => (isset($_POST['canReuse']) ? 1 : 0),
			'cardDetails' => $cardDetails
		));
	}

	private function onFail($info) {
		global $messageStack;
		$RequestData = $info['curlResponse']->getDataRaw();
		$saleId = $RequestData['x_invoice_num'];
		$this->setErrorMessage($this->getTitle() . ' : ' . $info['message']);
		if ($this->removeOrderOnFail === true){
			$Order = Doctrine_Core::getTable('Orders')->find($saleId);
			if ($Order){
				$Order->delete();
			}

			$messageStack->addSession('pageStack', $info['message'], 'error');
		}
		else {
			$this->logPayment(array(
				'saleId'     => $saleId,
				'amount'      => $RequestData['x_amount'],
				'message'     => $info['message'],
				'success'     => 0,
				'cardDetails' => array(
					'cardOwner'    => $RequestData['x_first_name'] . ' ' . $RequestData['x_last_name'],
					'cardNumber'   => $RequestData['x_card_num'],
					'cardExpMonth' => $RequestData['x_exp_date'],
					'cardExpYear'  => $RequestData['x_exp_date']
				)
			));
		}
	}
}
