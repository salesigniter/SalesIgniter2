<?php
class EmailModuleOrder extends EmailModuleBase
{

	protected $_events = array(
		array(
			'key'         => 'ORDER_PLACED_EMAIL',
			'text'        => 'An Order Is Placed',
			'description' => 'This email is sent to the specified people when a customer has placed an order'
		),
		array(
			'key'         => 'ORDER_STATUS_EMAIL',
			'text'        => 'An Orders Status Is Updated',
			'description' => 'This email is sent to the specified people when an order has been updated to the specified status'
		),
		array(
			'key'         => 'ORDER_DELIVERED_EMAIL',
			'text'        => 'An Order Has Been Delivered',
			'description' => 'This email is sent to the specified people when an order has been delivered'
		),
		array(
			'key'         => 'ORDER_PICKEDUP_EMAIL',
			'text'        => 'An Order Has Been Picked Up',
			'description' => 'This email is sent to the specified people when an order has been picked up'
		),
		array(
			'key'         => 'ORDER_TIME_SPECIFIC_EMAIL',
			'text'        => 'Specifc Time Afer Order Is Placed',
			'description' => 'This email will be sent to the specified people the specified period after an order has been placed ( Must have cron job "Order Time Emails" enabled )'
		)
	);

	public function __construct()
	{
		/*
		 * Default title and description for modules that are not yet installed
		 */
		$this->setTitle('Order Email Management');
		$this->setDescription('Order Email Management');

		$this->init('order');
	}

	public function getEvents()
	{
		return $this->_events;
	}

	public function getEventVariables()
	{
		return array(
			array('varName' => 'fullName', 'description' => 'Customers Full Name'),
			array('varName' => 'firstName', 'description' => 'Customers First Name'),
			array('varName' => 'lastName', 'description' => 'Customers Last Name'),
			array('varName' => 'emailAddress', 'description' => 'Customers Email Address'),
			array('varName' => 'trackingLinks', 'description' => 'Order Tracking Numbers'),
			array('varName' => 'adminComments', 'description' => 'Administrator Comments'),
			array('varName' => 'historyLink', 'description' => 'Account History Sale Link'),
			array('varName' => 'orderID', 'description' => 'Sale Id (Order, Estimate, etc..)'),
			array('varName' => 'status', 'description' => 'Sales Status Name (ex. Pending)'),
			array('varName' => 'datePurchased', 'description' => 'Date Sale Was Done'),
			array('varName' => 'orderedProducts', 'description' => 'List Of Purchased Products'),
			array('varName' => 'orderTotals', 'description' => 'List Of Sale Totals'),
			array('varName' => 'billing_address', 'description' => 'Customers Billing Address'),
			array('varName' => 'shipping_address', 'description' => 'Customers Shipping Address'),
			array('varName' => 'pickup_address', 'description' => 'Customers Pickup Address'),
			array('varName' => 'po_number', 'description' => 'Purchase Order Number'),
			array('varName' => 'payment_footer', 'description' => 'Footer Text Added By Payment Module'),
			array('varName' => 'paymentTitle', 'description' => 'Payment Method Name')
		);
	}

	public function getEventSettings($eventKey, $currentSettings = array())
	{
		$settings = array(
			'fields' => array()
		);

		switch($eventKey){
			case 'ORDER_STATUS_EMAIL':
				$SelectField = htmlBase::newSelectbox()
					->setName('send_on_status');
				if (isset($currentSettings['send_on_status'])){
					$SelectField->selectOptionByValue($currentSettings['send_on_status']);
				}
				$SelectField->addOption('all', 'All Status Updates');

				$OrderStatus = Doctrine_Core::getTable('OrdersStatus')
					->findAll();
				foreach($OrderStatus as $Status){
					$SelectField->addOption(
						$Status->orders_status_id,
						$Status->OrdersStatusDescription[Session::get('languages_id')]->orders_status_name
					);
				}

				$settings['fields'][] = array(
					'label' => 'Send On Status',
					'input' => $SelectField
				);
				break;
			case 'ORDER_TIME_SPECIFIC_EMAIL':
				$settings['fields'] = array();

				$NumberInput = htmlBase::newInput()
					->setName('send_on_time');
				if (isset($currentSettings['send_on_time'])){
					$NumberInput->val($currentSettings['send_on_time']);
				}

				$PeriodInput = htmlBase::newSelectbox()
					->setName('send_on_period')
					->addOption('3600', 'Hour(s)')
					->addOption('86400', 'Day(s)')
					->addOption('604800', 'Week(s)');
				if (isset($currentSettings['send_on_period'])){
					$PeriodInput->selectOptionByValue($currentSettings['send_on_period']);
				}

				$settings['fields'][] = array(
					'label' => 'Send On Time',
					'input' => $NumberInput
				);
				$settings['fields'][] = array(
					'label' => 'Send On Period',
					'input' => $PeriodInput
				);
				break;
			default:
				$settings = false;
				break;
		}
		return $settings;
	}

	public function prepareEventSettingsJson()
	{
		$return = array();
		switch($_POST['module_event_key']){
			case 'ORDER_STATUS_EMAIL':
				$return['send_on_status'] = $_POST['send_on_status'];
				break;
			case 'ORDER_TIME_SPECIFIC_EMAIL':
				$return['send_on_time'] = $_POST['send_on_time'];
				$return['send_on_period'] = $_POST['send_on_period'];
				break;
		}
		return $return;
	}

	public function process($eventKey, $o = array())
	{
		$Qtemplate = Doctrine_Query::create()
			->select('t.template_settings, td.email_templates_subject, td.email_templates_content')
			->from('EmailTemplates t')
			->leftJoin('t.Description td')
			->where('t.email_module_event_key = ?', $eventKey)
			->andWhere('t.template_status = ?', '1')
			->andWhere('td.language_id = ?', Session::get('languages_id'))
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		if ($Qtemplate && sizeof($Qtemplate) > 0){
			foreach($Qtemplate as $Template){
				$TemplateSettings = json_decode($Template['template_settings'], true);
				$GlobalTemplateSettings = $TemplateSettings['global'];
				$ModuleTemplateSettings = $TemplateSettings['module'];

				$this->setEmailSubject($Template['Description'][0]['email_templates_subject']);
				$this->setEmailBody($Template['Description'][0]['email_templates_content']);

				if (isset($o['SaleObj'])){
					if ($o['SaleObj'] instanceof Order){
						/**
						 * @var Order $Sale
						 */
						$Sale = $o['SaleObj'];
					}
					elseif ($o['SaleObj'] instanceof AccountsReceivableSales){
						/**
						 * @var Order $Sale
						 */
						$Sale = AccountsReceivable::getSale($o['SaleObj']->sale_module, $o['SaleObj']->sale_id);
					}
				}
				elseif (isset($o['saleModule']) && isset($o['saleId'])){
					/**
					 * @var Order $Sale
					 */
					$Sale = AccountsReceivable::getSale($o['saleModule'], $o['saleId']);
				}

				if (isset($o['testMode']) && $o['testMode'] === true){
					$Sale = true;
				}

				if ($Sale){
					if (isset($o['testMode']) && $o['testMode'] === true){
						$GlobalTemplateSettings['send_to'] = $o['sendTo'];

						$this->setVar('trackingLinks', '890234578439758902378');
						$this->setVar('adminComments', 'This is a test email send with prepopulated information');
						$this->setVar('historyLink', 'http://viewMySale.com');
						$this->setVar('fullName', 'John Doe');
						$this->setVar('firstName', 'John');
						$this->setVar('lastName', 'Doe');
						$this->setVar('emailAddress', 'johndoe@domain.com');
						$this->setVar('orderID', 'XXXX');
						$this->setVar('status', 'Pending/Processing/etc..');
						$this->setVar('datePurchased', date(sysLanguage::getDateFormat('long')));
						$this->setVar('orderedProducts', 'List Of Products<br>Product 1<br>Product 2<br>Product 3');
						$this->setVar('orderTotals', 'List Of Totals<br>Subtotal: $10.00<br>Tax: $0.00<br>Total: $10.00');
						$this->setVar('billing_address', 'CustomersBillingAddressFormatted');
						$this->setVar('shipping_address', 'CustomersShippingAddressFormatted');
						$this->setVar('pickup_address', 'CustomersPickupAddressFormatted');
						$this->setVar('po_number', '89230489230');
						$this->setVar('payment_footer', 'Payment Module Footer Information');
						$this->setVar('paymentTitle', 'Some Payment Method');
					}else{
						if ($Sale->InfoManager->getInfo('trackingLinks') != ''){
							$this->setVar('trackingLinks', $Sale->InfoManager->getInfo('trackingLinks'));
						}
						if ($Sale->InfoManager->getInfo($adminComments) != ''){
							$this->setVar('adminComments', $Sale->InfoManager->getInfo('comments'));
						}
						//$this->setVar('historyLink', itw_catalog_app_link('sale_id=' . $Sale->getSaleId(), 'account', 'history'));
						$this->setVar('fullName', $Sale->AddressManager->getAddress('customer')->getName());
						$this->setVar('firstName', $Sale->AddressManager->getAddress('customer')->getFirstName());
						$this->setVar('lastName', $Sale->AddressManager->getAddress('customer')->getLastName());
						$this->setVar('emailAddress', $Sale->getEmailAddress());
						$this->setVar('orderID', $Sale->getSaleId());
						$this->setVar('status', $Sale->getStatusName());
						$this->setVar('datePurchased', $Sale->getDateAdded()->format(sysLanguage::getDateFormat('long')));
						$this->setVar('orderedProducts', $Sale->ProductManager->getEmailList());
						$this->setVar('orderTotals', $Sale->TotalManager->getEmailList());
						$this->setVar('billing_address', $Sale->AddressManager->getFormattedAddress('billing'));
						$this->setVar('shipping_address', $Sale->AddressManager->getFormattedAddress('delivery'));
						$this->setVar('pickup_address', $Sale->AddressManager->getFormattedAddress('pickup'));
						$this->setVar('po_number', $Sale->InfoManager->getInfo('po_number'));
						$this->setVar('payment_footer', $Sale->InfoManager->getInfo('payment_module_footer'));
						$this->setVar('paymentTitle', $Sale->PaymentManager->getPaymentModule()->getTitle());
					}

					if ($GlobalTemplateSettings['send_to'] == 'customer'){
						$this->sendEmail(array(
							'name'  => $this->getVar('fullName'),
							'email' => $this->getVar('emailAddress'),
							'attach' => (isset($o['attach']) ? $o['attach'] : false)
						));
					}
					elseif ($GlobalTemplateSettings['send_to'] == 'admin') {
						$this->sendEmail(array(
							'name'  => sysConfig::get('STORE_OWNER'),
							'email' => sysConfig::get('STORE_OWNER_EMAIL_ADDRESS'),
							'attach' => (isset($o['attach']) ? $o['attach'] : false)
						));
					}
					else {
						$this->sendEmail(array(
							'name'  => $GlobalTemplateSettings['send_to'],
							'email' => $GlobalTemplateSettings['send_to'],
							'attach' => (isset($o['attach']) ? $o['attach'] : false)
						));
					}
				}
				unset($Customer);
			}
		}
	}
}
