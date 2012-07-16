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

	public function __construct()
	{
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

	public function beforeProcessPayment(Order $CheckoutSale)
	{
		$SaleModule = $CheckoutSale->getSaleModule();
		$SaleId = $SaleModule->saveSale($CheckoutSale);

		$this->params['oid'] = $SaleId;
		$this->params[Session::getSessionName()] = Session::getSessionId();
		$this->params['sale_module'] = $SaleModule->getCode();

		$this->params['subtotal'] = $CheckoutSale->TotalManager->getTotalValue('subtotal');
		$this->params['chargetotal'] = $CheckoutSale->TotalManager->getTotalValue('total');

		$TypeConvert = array(
			'Mastercard' => 'M',
			'Visa'       => 'V',
			'Amex'       => 'A',
			'Diners'     => 'C',
			'JCB'        => 'J',
			'Discover'   => 'D'
		);

		$this->params['paymentMethod'] = $TypeConvert[$CheckoutSale->PaymentManager->getInfo('cardType')];
		$this->params['cardnumber'] = $CheckoutSale->PaymentManager->getInfo('cardNumber');
		$this->params['expmonth'] = $CheckoutSale->PaymentManager->getInfo('cardExpMonth');
		$this->params['expyear'] = $CheckoutSale->PaymentManager->getInfo('cardExpYear');
		$this->params['cvm'] = $CheckoutSale->PaymentManager->getInfo('cardCvvNumber');
		$this->params['cvmnotpres'] = ($CheckoutSale->PaymentManager->getInfo('cardCvvNumber') == '');

		if ($this->params['mode'] == 'payplus'){
			$BillingAddress = $CheckoutSale->AddressManager->getAddress('billing');

			$this->params['bcompany'] = $BillingAddress->getCompany();
			$this->params['bname'] = $BillingAddress->getName();
			$this->params['baddr1'] = $BillingAddress->getStreetAddress();
			//$this->params['baddr2'] = '';
			$this->params['bcity'] = $BillingAddress->getCity();
			$this->params['bstate'] = $BillingAddress->getZoneCode();
			//$this->params['bstate2'] = $BillingAddress->getSuburb();
			$this->params['bcountry'] = $BillingAddress->getCountryCode();
			$this->params['bzip'] = $BillingAddress->getPostcode();
			$this->params['phone'] = $CheckoutSale->InfoManager->getInfo('customers_telephone_number');
			$this->params['fax'] = $CheckoutSale->InfoManager->getInfo('customers_fax_number');
			$this->params['email'] = $CheckoutSale->InfoManager->getInfo('customers_email_address');
		}
		$this->params['payment_module'] = $this->getCode();
	}

	public function getHiddenFields()
	{
		$DateTime = new DateTime('now');
		$DateTime = $DateTime->modify('+3 Seconds');
		$this->params['txndatetime'] = $DateTime->format('Y:m:d-H:i:s');

		$this->params['timezone'] = 'EST';
		//$this->params['timezone'] = 'EDT';

		$this->params['hash'] = $this->params['storename'] . $this->params['txndatetime'] . $this->params['chargetotal'] . $this->secret;
		$hex_str = '';
		for($i = 0; $i < strlen($this->params['hash']); $i++){
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

	public function ownsProcessPage()
	{
		if (isset($_GET['payment_module']) && $_GET['payment_module'] == $this->getCode()){
			return true;
		}
		elseif (isset($_POST['payment_module']) && $_POST['payment_module'] == $this->getCode()) {
			return true;
		}
		return false;
	}

	public function afterProcessPayment($success)
	{
		global $messageStack;

		$CardDetails = array(
			'cardOwner'    => $_POST['bname'],
			'cardNumber'   => $_POST['cardnumber'],
			'cardExpMonth' => $_POST['expmonth'],
			'cardExpYear'  => $_POST['expyear'],
			'approvalCode' => $_POST['approval_code']
		);

		$ResponseStatus = strtolower(trim($_POST['status']));
		switch($ResponseStatus){
			case 'approved':
				$this->onSuccess(array(
					'status'      => $ResponseStatus,
					'saleModule'  => $_POST['sale_module'],
					'saleId'      => $_POST['oid'],
					'message'     => '',
					'amount'      => $_POST['chargetotal'],
					'cardDetails' => $CardDetails
				));
				break;
			case 'declined':
			case 'duplicate':
			case 'fraud':
				$this->onFail(array(
					'status'      => $ResponseStatus,
					'saleId'      => $_POST['oid'],
					'saleModule'  => $_POST['sale_module'],
					'message'     => $_POST['fail_reason'],
					'amount'      => $_POST['chargetotal'],
					'cardDetails' => $CardDetails
				));
				break;
			default:
				$messageStack->addSession('pageStack', 'An unknown response was recieved from the payment gateway<br>The administrator has been notified, please try again later.', 'error');
				tep_mail('stephen@itwebexperts.com', 'Unknown First Data Response', print_r($_POST, true));
				tep_redirect(itw_app_link('paymentError=1', 'checkout', 'default'));
				break;
		}
	}

	private function onSuccess($info)
	{
		$Sale = AccountsReceivable::getSale($info['saleModule'], $info['saleId']);
		$Sale->sendNewSaleSuccessEmail();

		$this->logPayment(array(
			'module'      => $this->getCode(),
			'saleId'      => $info['saleId'],
			'amount'      => $_POST['chargetotal'],
			'message'     => $info['message'],
			'success'     => 1,
			'can_reuse'   => (isset($_POST['canReuse']) ? 1 : 0),
			'cardDetails' => $info['cardDetails']
		));

		tep_redirect(itw_app_link(null, 'checkout', 'success'));
	}

	private function onFail($info)
	{
		global $messageStack;

		$Sale = AccountsReceivable::getSale($info['saleModule'], $info['saleId']);
		$Sale->sendNewSaleFailEmail();

		if ($this->removeOrderOnFail === true){
			$Sale = Doctrine_Core::getTable('AccountsReceivableSales')
				->findBySaleId($info['saleId']);
			if ($Sale){
				$Sale->delete();
			}

			$messageStack->addSession('pageStack', $info['message'], 'error');
		}
		else {
			$this->logPayment(array(
				'module'      => $this->getCode(),
				'saleId'      => $info['saleId'],
				'amount'      => $info['amount'],
				'message'     => $info['message'],
				'success'     => 0,
				'cardDetails' => $info['cardDetails']
			));
		}

		$messageStack->addSession('pageStack', $_POST['fail_reason'], 'error');
		tep_redirect(itw_app_link('paymentError=1', 'checkout', 'default'));
	}
}
