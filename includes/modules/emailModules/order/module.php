<?php
class EmailModuleOrder extends EmailModuleBase
{

	protected $_events = array(
		array(
			'key' => 'ORDER_PLACED_EMAIL',
			'text' => 'An Order Is Placed',
			'description' => 'This email is sent to the specified people when a customer has placed an order'
		),
		array(
			'key' => 'ORDER_STATUS_EMAIL',
			'text' => 'An Orders Status Is Updated',
			'description' => 'This email is sent to the specified people when an order has been updated to the specified status'
		),
		array(
			'key' => 'ORDER_TIME_SPECIFIC_EMAIL',
			'text' => 'Specifc Time Afer Order Is Placed',
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

	public function getEvents(){
		return $this->_events;
	}

	public function getEventVariables(){
		return array(
			array('varName' => 'trackingLinks', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'adminComments', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'historyLink', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'full_name', 'conditional' => false),
			array('varName' => 'orderID', 'conditional' => false),
			array('varName' => 'status', 'conditional' => false),
			array('varName' => 'datePurchased', 'conditional' => false),
			array('varName' => 'orderedProducts', 'conditional' => false),
			array('varName' => 'orderTotals', 'conditional' => false),
			array('varName' => 'trackingLinks', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'adminComments', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'historyLink', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'full_name', 'conditional' => false),
			array('varName' => 'orderID', 'conditional' => false),
			array('varName' => 'status', 'conditional' => false),
			array('varName' => 'datePurchased', 'conditional' => false),
			array('varName' => 'orderedProducts', 'conditional' => false),
			array('varName' => 'orderTotals', 'conditional' => false),
			array('varName' => 'order_comments', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'shipping_address', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'pickup_address', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'po_number', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'payment_footer', 'conditional' => true, 'conditionCheck' => null),
			array('varName' => 'order_id', 'conditional' => false),
			array('varName' => 'invoice_link', 'conditional' => false),
			array('varName' => 'date_ordered', 'conditional' => false),
			array('varName' => 'ordered_products', 'conditional' => false),
			array('varName' => 'orderTotals', 'conditional' => false),
			array('varName' => 'billing_address', 'conditional' => false),
			array('varName' => 'paymentTitle', 'conditional' => false)
		);
	}

	public function getEventSettings($eventKey, $currentSettings = array()){
		$settings = array(
			'fields' => array()
		);

		switch($eventKey){
			case 'ORDER_PLACED_EMAIL':
				$settings = false;
				break;
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
		}
		return $settings;
	}

	public function prepareEventSettingsJson(){
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
}
