<?php
/**
 * Main order creator order class
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/InfoManager/Base.php');
require(dirname(__FILE__) . '/AddressManager/Base.php');
require(dirname(__FILE__) . '/ProductManager/Base.php');
require(dirname(__FILE__) . '/TotalManager/Base.php');
require(dirname(__FILE__) . '/PaymentManager/Base.php');

class OrderCreator extends Order implements Serializable
{

	/**
	 * @var array
	 */
	private $data = array();

	/**
	 * @var OrderCreatorInfoManager
	 */
	public $InfoManager;

	/**
	 * @var OrderCreatorAddressManager
	 */
	public $AddressManager;

	/**
	 * @var OrderCreatorProductManager
	 */
	public $ProductManager;

	/**
	 * @var OrderCreatorTotalManager
	 */
	public $TotalManager;

	/**
	 * @var OrderCreatorPaymentManager
	 */
	public $PaymentManager;

	/**
	 * @var array
	 */
	private $errorMessages = array();

	/**
	 * @var AccountsReceivableModule
	 */
	protected $SaleModule = null;

	public function __construct() {
		$this->InfoManager = new OrderCreatorInfoManager();
		$this->AddressManager = new OrderCreatorAddressManager();
		$this->ProductManager = new OrderCreatorProductManager();

		$SubTotalModule = OrderTotalModules::getModule('subtotal');
		$TaxModule = OrderTotalModules::getModule('tax');
		$TotalModule = OrderTotalModules::getModule('total');

		$SubTotal = new OrderCreatorTotal();
		$SubTotal->setModule($SubTotalModule->getCode());
		$SubTotal->setModuleType($SubTotalModule->getModuleType());
		$SubTotal->setTitle($SubTotalModule->getTitle());
		$SubTotal->setSortOrder(1);
		$SubTotal->setValue(0.0000);

		$Tax = new OrderCreatorTotal();
		$Tax->setModule($TaxModule->getCode());
		$Tax->setModuleType($TaxModule->getModuleType());
		$Tax->setTitle($TaxModule->getTitle());
		$Tax->setSortOrder(2);
		$Tax->setValue(0.0000);

		$Total = new OrderCreatorTotal();
		$Total->setModule($TotalModule->getCode());
		$Total->setModuleType($TotalModule->getModuleType());
		$Total->setTitle($TotalModule->getTitle());
		$Total->setSortOrder(3);
		$Total->setValue(0.0000);

		$this->TotalManager = new OrderCreatorTotalManager();
		$this->TotalManager->add($SubTotal);
		$this->TotalManager->add($Tax);
		$this->TotalManager->add($Total);

		$this->PaymentManager = new OrderCreatorPaymentManager();

		$this->InfoManager->setInfo('status', 1);
		$this->InfoManager->setInfo('currency', Session::get('currency'));
		$this->InfoManager->setInfo('currency_value', Session::get('currency_value'));

		$this->errorMessages = array();

		AccountsReceivableModules::loadModules();
		foreach(AccountsReceivableModules::getModules() as $Module){
			if ($Module->ownsSale() === true){
				$Module->load(
					$this,
					true,
					(isset($_GET['sale_id']) ? (int) $_GET['sale_id'] : 0),
					(isset($_GET['sale_revision']) ? (int) $_GET['sale_revision'] : 0)
				);
			}
		}

		EventManager::notify('OrderCreatorLoadOrder', $this);
	}

	/**
	 *
	 */
	public function init() {
		if (is_object($this->ProductManager) === false){
			print_r(debug_print_backtrace());
		}
		$this->ProductManager->init();

		if (isset($this->SaleModule)){
			$this->SaleModule = AccountsReceivableModules::getModule($this->SaleModule);
			$this->SaleModule->load($this, false);
		}
		/*$this->AddressManager->init();
		$this->TotalManager->init();
		$this->PaymentManager->init();*/
	}

	public function hasSaleModule(){
		return !($this->SaleModule === null);
	}

	public function getSaleModule(){
		return $this->SaleModule;
	}

	/**
	 * @return string
	 */
	public function serialize() {
		$data = array(
			'orderId'        => $this->getOrderId(),
			'customerId'     => $this->getCustomerId(),
			'mode'           => $this->mode,
			'Order'          => $this->Order,
			'InfoManager'    => $this->InfoManager,
			'ProductManager' => $this->ProductManager,
			'AddressManager' => $this->AddressManager,
			'TotalManager'   => $this->TotalManager,
			'PaymentManager' => $this->PaymentManager,
			'errorMessages'  => $this->errorMessages,
			'data'           => $this->data
		);

		if (is_object($this->SaleModule)){
			$data['SaleModule'] = $this->SaleModule->getCode();
		}
		return serialize($data);
	}

	/**
	 * @param string $data
	 * @return mixed|string
	 */
	public function unserialize($data) {
		$data = unserialize($data);
		foreach($data as $key => $dInfo){
			$this->$key = $dInfo;
		}
		return $data;
	}

	/**
	 * @param string|int $k
	 * @param mixed $v
	 */
	public function setData($k, $v) {
		$this->data[$k] = $v;
	}

	/**
	 * @param string|int $k
	 * @return mixed
	 */
	public function getData($k) {
		return $this->data[$k];
	}

	/**
	 * @param string|int $k
	 * @return bool
	 */
	public function hasData($k) {
		return (isset($this->data[$k]));
	}

	/**
	 * @param string $val
	 */
	public function addErrorMessage($val) {
		$this->errorMessages[] = $val;
	}

	/**
	 * @return bool
	 */
	public function hasErrors() {
		return (sizeof($this->errorMessages) > 0);
	}

	/**
	 * @return array
	 */
	public function getErrors() {
		$return = $this->errorMessages;
		$this->errorMessages = array();
		return $return;
	}

	/**
	 * @return bool
	 */
	public function hasDebt() {
		return ($this->TotalManager->getTotalValue('total') > $this->PaymentManager->getPaymentsTotal());
	}

	/**
	 * @return bool
	 */
	public function hasCredit() {
		return ($this->TotalManager->getTotalValue('total') < $this->PaymentManager->getPaymentsTotal());
	}

	/**
	 * @return bool
	 */
	public function hasPendingPayments() {
		return ($this->PaymentManager->getPendingPaymentsTotal() > 0);
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getBalance($type) {
		global $currencies;
		switch($type){
			case 'debt':
				$Balance = ($this->TotalManager->getTotalValue('total') - $this->PaymentManager->getPaymentsTotal());
				break;
			case 'credit':
				$Balance = ($this->PaymentManager->getPaymentsTotal() - $this->TotalManager->getTotalValue('total'));
				break;
			case 'pending':
				$Balance = $this->PaymentManager->getPendingPaymentsTotal();
				break;
		}
		return $currencies->format($Balance);
	}

	/**
	 * @return string
	 */
	public function editAddresses() {
		return $this->AddressManager->editAll();
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function editAddress($type) {
		return $this->AddressManager->editAddress($type);
	}

	/**
	 * @param string $val
	 */
	public function setShippingModule($val) {
		$this->Order['shipping_module'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setPaymentModule($val) {
		$this->Order['payment_module'] = (string) $val;
	}

	/**
	 * @return string
	 */
	public function getShippingModule() {
		return (string) $this->Order['shipping_module'];
	}

	/**
	 * @return string
	 */
	public function getPaymentModule() {
		return (string) $this->Order['payment_module'];
	}

	/**
	 * @return string
	 */
	public function getPassword() {
		if (isset($_POST['account_password']) && !empty($_POST['account_password'])){
			return $_POST['account_password'];
		}
		else {
			return tep_create_random_value(sysConfig::get('ENTRY_PASSWORD_MIN_LENGTH'));
		}
	}

	/**
	 * @param int $val
	 */
	public function setCustomerId($val) {
		$this->customerId = (int) $val;
	}

	/**
	 * @param string $val
	 */
	public function setTelephone($val) {
		$this->Order['customers_telephone'] = (string) $val;
	}

	/**
	 * @param string $val
	 */
	public function setEmailAddress($val) {
		$this->Order['customers_email_address'] = (string) $val;
	}

	/**
	 * @return string
	 */
	public function editTelephone() {
		$input = htmlBase::newElement('input')
			->setName('telephone')
			->val($this->getTelephone());

		return (string) $input->draw();
	}

	/**
	 * @return string
	 */
	public function editEmailAddress() {
		$input = htmlBase::newElement('input')
			->setName('email')
			->val($this->getEmailAddress());

		return (string) $input->draw();
	}

	/**
	 * @return string
	 */
	public function editDriversLicense() {
		$input = htmlBase::newElement('input')
			->setName('drivers_license')
			->val($this->getDriversLicense());

		return (string) $input->draw();
	}

	/**
	 * @return string
	 */
	public function editPassPort() {
		$input = htmlBase::newElement('input')
			->setName('passport')
			->val($this->getPassPort());

		return (string) $input->draw();
	}

	/**
	 * @return string
	 */
	public function editRoomNumber() {
		$input = htmlBase::newElement('input')
			->setName('room_number')
			->val($this->getRoomNumber());

		return (string) $input->draw();
	}

	/**
	 * @param string $val
	 */
	public function setMemberNumber($val) {
		$this->Order['customers_number'] = (string) $val;
	}

	/**
	 * @return string
	 */
	public function getMemberNumber() {
		$Qcustomer = Doctrine_Query::create()
			->select('customers_number')
			->from('Customers')
			->where('customers_id = ?', $this->getCustomerId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		return (sizeof($Qcustomer) > 0 ? $Qcustomer[0]['customers_number'] : '');
	}

	/**
	 * @return string
	 */
	public function editMemberNumber() {
		$input = htmlBase::newElement('input')
			->attr('max_length', 12)
			->setName('member_number')
			->val($this->getMemberNumber());

		return (string) $input->draw();
	}

	/**
	 * @param Customers $CollectionObj
	 */
	public function createCustomerAccount(Customers $CollectionObj) {
		$CustomerAddress = $this->AddressManager->getAddress('customer');

		$CollectionObj->language_id = Session::get('languages_id');
		$CollectionObj->customers_number = $this->Order['customers_number'];
		$CollectionObj->customers_firstname = $CustomerAddress->getFirstName();
		$CollectionObj->customers_lastname = $CustomerAddress->getLastName();
		$CollectionObj->customers_email_address = $this->getEmailAddress();
		$CollectionObj->customers_telephone = $this->getTelephone();

		$password = '';
		for($i = 0; $i < 10; $i++){
			$password .= tep_rand();
		}
		$salt = substr(md5($password), 0, 2);
		$password = md5($salt . $this->getPassword()) . ':' . $salt;

		$CollectionObj->customers_password = $password;
		//$CollectionObj->customers_gender = $this->getGender();
		//$CollectionObj->customers_dob = $this->getDateOfBirth();
		//$CollectionObj->customers_default_address_id = $this->getAddressId();
		//$CollectionObj->customers_fax = $this->getFax();
		//$CollectionObj->customers_newsletter = 1;

		EventManager::notify('OrderCreatorCreateCustomerAccount', $CollectionObj);

		$AddressBook = new AddressBook();
		$AddressBook->entry_gender = $CustomerAddress->getGender();
		if (sysConfig::get('ACCOUNT_COMPANY') == 'true'){
			$AddressBook->entry_company = $CustomerAddress->getCompany();
		}
		$AddressBook->entry_firstname = $CustomerAddress->getFirstName();
		$AddressBook->entry_lastname = $CustomerAddress->getLastName();
		$AddressBook->entry_street_address = $CustomerAddress->getStreetAddress();
		$AddressBook->entry_suburb = $CustomerAddress->getSuburb();
		$AddressBook->entry_postcode = $CustomerAddress->getPostcode();
		$AddressBook->entry_city = $CustomerAddress->getCity();
		$AddressBook->entry_state = $CustomerAddress->getState();
		$AddressBook->entry_country_id = $CustomerAddress->getCountryId();
		$AddressBook->entry_zone_id = $CustomerAddress->getZoneId();

		EventManager::notify('OrderCreatorCreateCustomerAddress', $AddressBook);

		$CollectionObj->AddressBook->add($AddressBook);

		$CollectionObj->CustomersInfo->customers_info_number_of_logons = 0;

		$firstName = $CustomerAddress->getFirstName();
		$lastName = $CustomerAddress->getLastName();
		$emailAddress = $this->getEmailAddress();
		$fullName = $firstName . ' ' . $lastName;

		$emailEvent = new emailEvent('create_account');

		$emailEvent->setVars(array(
			'email_address' => $emailAddress,
			'password'	  => $this->getPassword(),
			'firstname'	 => $firstName,
			'lastname'	  => $lastName,
			'full_name'	 => $fullName
		));

		if (isset($this->newCustomerEmailVars)){
			foreach($this->newCustomerEmailVars as $var => $val){
				$emailEvent->setVar($var, $val);
			}
		}
		if (sysConfig::get('EXTENSION_ORDER_CREATOR_SEND_WELCOME_EMAIL') == 'True'){
			$emailEvent->sendEmail(array(
				'email' => $emailAddress,
				'name'  => $fullName
			));
		}
	}

	/**
	 * @param Orders $CollectionObj
	 */
	public function sendNewOrderEmail(Orders $CollectionObj) {
		global $appExtension, $currencies;
		$DeliveryAddress = $this->AddressManager->getAddress('delivery');
		$BillingAddress = $this->AddressManager->getAddress('billing');

		$sendToFormatted = $this->AddressManager->showAddress($DeliveryAddress, false);
		$billToFormatted = $this->AddressManager->showAddress($BillingAddress, false);

		$products_ordered = '';
		foreach($CollectionObj->OrdersProducts as $opInfo){
			$products_ordered .= sprintf("%s x %s (%s) = %s\n",
				$opInfo->products_quantity,
				$opInfo->products_name,
				$opInfo->products_model,
				$currencies->display_price(
					$opInfo->products_price,
					$opInfo->products_tax,
					$opInfo->products_quantity
				)
			);

			EventManager::notify('OrderCreatorAddProductToEmail', $opInfo, &$products_ordered);
		}

		$emailEvent = new emailEvent('order_success', Session::get('languages_id'));
		$emailEvent->setVar('order_id', $CollectionObj->orders_id);
		$emailEvent->setVar('invoice_link', itw_catalog_app_link('order_id=' . $CollectionObj->orders_id, 'account', 'history_info', 'SSL', false));
		$emailEvent->setVar('date_ordered', strftime(sysLanguage::getDateFormat('long')));
		$emailEvent->setVar('ordered_products', $products_ordered);
		$emailEvent->setVar('billing_address', $billToFormatted);
		$emailEvent->setVar('shipping_address', $sendToFormatted);
		if (sysConfig::get('ONEPAGE_CHECKOUT_PICKUP_ADDRESS') == 'true'){
			$PickupAddress = $this->AddressManager->getAddress('pickup');
			$pickUpFormatted = $this->AddressManager->showAddress($PickupAddress, false);
			$emailEvent->setVar('pickup_address', $pickUpFormatted);
		}
		if ($appExtension->isInstalled('goRentalsDepot') && $appExtension->isEnabled('goRentalsDepot')){
			//$emailEvent->setVar('rental_city', $this->info['delivery_depot_postcode']);
			//$emailEvent->setVar('delivery_depot', $this->info['delivery_depot']);
		}
		$emailEvent->setVar('order_comments', $CollectionObj->OrdersStatusHistory[0]->comments);

		$orderTotals = '';
		foreach($CollectionObj->OrdersTotal as $tInfo){
			$orderTotals .= strip_tags($tInfo['title']) . ' ' . strip_tags($tInfo['text']) . "\n";
		}
		$emailEvent->setVar('orderTotals', $orderTotals);

		/*
		 * @TODO: Why is ['payment_module'] == payment method title, it should be ['payment_method'] == payment method title
		 */
		if (!empty($CollectionObj->payment_module)){
			$Module = OrderPaymentModules::getModule($CollectionObj->payment_module);
			$emailEvent->setVar('paymentTitle', $Module->getTitle());
			if ($CollectionObj->payment_module == 'po'){
				$emailEvent->setVar('po_number', 'P.O. Number: ' . $CollectionObj->po_number);
			}
		}
		$sendVariables = array();
		EventManager::notify('OrderCreatorBeforeSendNewEmail', $CollectionObj, $emailEvent, &$products_ordered, &$sendVariables);
		$sendVariables['email'] = $CollectionObj->customers_email_address;
		$sendVariables['name'] = $BillingAddress->getName();

		$emailEvent->sendEmail($sendVariables);

		// send emails to other people
		if (sysConfig::get('SEND_EXTRA_ORDER_EMAILS_TO') != ''){
			$emailEvent->sendEmail(array(
				'email' => sysConfig::get('SEND_EXTRA_ORDER_EMAILS_TO'),
				'name'  => ''
			));
		}
	}

	/**
	 * @param Orders $CollectionObj
	 * @param string $emailAddress
	 */
	public function sendNewEstimateEmail(Orders $CollectionObj, $emailAddress = '') {
		global $appExtension, $currencies;
		$DeliveryAddress = $this->AddressManager->getAddress('delivery');
		$BillingAddress = $this->AddressManager->getAddress('billing');

		$sendToFormatted = $this->AddressManager->showAddress($DeliveryAddress, false);
		$billToFormatted = $this->AddressManager->showAddress($BillingAddress, false);

		$products_ordered = '';
		foreach($CollectionObj->OrdersProducts as $opInfo){
			$products_ordered .= sprintf("%s x %s (%s) = %s\n",
				$opInfo->products_quantity,
				$opInfo->products_name,
				$opInfo->products_model,
				$currencies->display_price(
					$opInfo->products_price,
					$opInfo->products_tax,
					$opInfo->products_quantity
				)
			);

			EventManager::notify('OrderCreatorAddProductToEmail', $opInfo, &$products_ordered);
		}

		$emailEvent = new emailEvent('estimate_success', Session::get('languages_id'));
		$emailEvent->setVar('order_id', $CollectionObj->orders_id);
		$emailEvent->setVar('invoice_link', itw_catalog_app_link('order_id=' . $CollectionObj->orders_id, 'account', 'history_info', 'SSL', false));
		$emailEvent->setVar('date_ordered', strftime(sysLanguage::getDateFormat('long')));
		$emailEvent->setVar('ordered_products', $products_ordered);
		$emailEvent->setVar('billing_address', $billToFormatted);
		$emailEvent->setVar('shipping_address', $sendToFormatted);
		if (sysConfig::get('ONEPAGE_CHECKOUT_PICKUP_ADDRESS') == 'true'){
			$PickupAddress = $this->AddressManager->getAddress('pickup');
			$pickUpFormatted = $this->AddressManager->showAddress($PickupAddress, false);
			$emailEvent->setVar('pickup_address', $pickUpFormatted);
		}
		if ($appExtension->isInstalled('goRentalsDepot') && $appExtension->isEnabled('goRentalsDepot')){
			//$emailEvent->setVar('rental_city', $this->info['delivery_depot_postcode']);
			//$emailEvent->setVar('delivery_depot', $this->info['delivery_depot']);
		}
		$emailEvent->setVar('order_comments', $CollectionObj->OrdersStatusHistory[0]->comments);

		$orderTotals = '';
		foreach($CollectionObj->OrdersTotal as $tInfo){
			$orderTotals .= strip_tags($tInfo['title']) . ' ' . strip_tags($tInfo['text']) . "\n";
		}
		$emailEvent->setVar('orderTotals', $orderTotals);
		$sendVariables = array();
		EventManager::notify('OrderCreatorBeforeSendNewEmail', $CollectionObj, $emailEvent, &$products_ordered, &$sendVariables);
		if ($emailAddress == ''){
			$email = $CollectionObj->customers_email_address;
		}
		else {
			$email = $emailAddress;
		}
		$sendVariables['email'] = $email;
		$sendVariables['name'] = $BillingAddress->getName();

		$emailEvent->sendEmail($sendVariables);

		// send emails to other people
		if (sysConfig::get('SEND_EXTRA_ORDER_EMAILS_TO') != ''){
			$emailEvent->sendEmail(array(
				'email' => sysConfig::get('SEND_EXTRA_ORDER_EMAILS_TO'),
				'name'  => ''
			));
		}
	}
}

?>