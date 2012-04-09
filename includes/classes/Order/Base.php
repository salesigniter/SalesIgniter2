<?php
/**
 * Main order class
 *
 * @package Order
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(dirname(__FILE__) . '/AddressManager/Base.php');
require(dirname(__FILE__) . '/ProductManager/Base.php');
require(dirname(__FILE__) . '/TotalManager/Base.php');
require(dirname(__FILE__) . '/PaymentManager/Base.php');

class Order
{

	/**
	 * @var string
	 */
	public $mode = 'details';

	/**
	 * @var array
	 */
	public $Order = array();

	/**
	 * @var int
	 */
	public $orderId = 0;

	/**
	 * @var int
	 */
	public $customerId = 0;

	/**
	 * @var OrderAddressManager
	 */
	public $AddressManager;

	/**
	 * @var OrderProductManager
	 */
	public $ProductManager;

	/**
	 * @var OrderTotalManager
	 */
	public $TotalManager;

	/**
	 * @var OrderPaymentManager
	 */
	public $PaymentManager;

	/**
	 * @param int $orderId
	 */
	public function __construct($orderId = 0) {
		if ($orderId > 0){
			$this->setOrderId($orderId);

			$Qorder = Doctrine_Query::create()
				->from('Orders o')
				->leftJoin('o.OrdersAddresses oa')
				->leftJoin('oa.Zones z')
				->leftJoin('oa.Countries c')
				->leftJoin('c.AddressFormat af')
				->leftJoin('o.OrdersTotal ot')
				->leftJoin('o.OrdersPaymentsHistory oph')
				->leftJoin('o.OrdersStatusHistory osh')
				->leftJoin('osh.OrdersStatus s')
				->leftJoin('s.OrdersStatusDescription sd')
				->leftJoin('o.OrdersProducts op')
				->where('o.orders_id = ?', $orderId)
				->andWhere('sd.language_id = ?', Session::get('languages_id'))
				->orderBy('ot.sort_order ASC, osh.date_added DESC');

			EventManager::notify('OrderQueryBeforeExecute', &$Qorder);

			//echo $Qorder->getSqlQuery();
			$Order = $Qorder->execute()->toArray();
			//echo '<pre>';print_r($Order);
			$this->Order = $Order[0];
			$this->customerId = $this->Order['customers_id'];

			$this->AddressManager = new OrderAddressManager($this->Order['OrdersAddresses']);
			$this->AddressManager->setOrderId($this->Order['orders_id']);

			$this->ProductManager = new OrderProductManager($this->Order['OrdersProducts'], $this->Order);
			$this->ProductManager->setOrderId($this->Order['orders_id']);

			$this->TotalManager = new OrderTotalManager($this->Order['OrdersTotal']);
			$this->TotalManager->setOrderId($this->Order['orders_id']);

			$this->PaymentManager = new OrderPaymentManager($this->Order['OrdersPaymentsHistory']);
			$this->PaymentManager->setOrderId($this->Order['orders_id']);
		}
	}

	/**
	 * @param int $val
	 */
	public function setOrderId($val) {
		$this->orderId = (int) $val;
	}

	/**
	 * @return int
	 */
	public function getOrderId() {
		return $this->orderId;
	}

	/**
	 * @return int
	 */
	public function getCustomerId() {
		return (int)$this->customerId;
	}

	/**
	 * @return array
	 */
	public function getOrderInfo() {
		return $this->Order;
	}

	/**
	 * @return string
	 */
	public function getCurrency() {
		return (string)$this->Order['currency'];
	}

	/**
	 * @return float
	 */
	public function getCurrencyValue() {
		return (float)$this->Order['currency_value'];
	}

	/**
	 * @return bool
	 */
	public function hasTaxes() {
		return ($this->TotalManager->getTotalValue('tax') > 0);
	}

	/**
	 * @return bool
	 */
	public function hasShippingMethod() {
		return (empty($this->Order['shipping_method']) === false);
	}

	/**
	 * @return string
	 */
	public function getShippingMethod() {
		return (string)$this->Order['shipping_method'];
	}

	/**
	 * @return float
	 */
	public function getTotal() {
		return (float)$this->TotalManager->getTotalValue('total');
	}

	/**
	 * @param bool $isID
	 * @return int|string
	 */
	public function getCurrentStatus($isID = false) {
		/*
		 * DO NOT CHANGE FROM 0, IT IS ORDERED DESC SO 0 WILL ALWAYS ME THE MOST RECENT
		 */
		if (isset($this->Order['OrdersStatusHistory'][0])){
			if ($isID === false){
				return (string)$this->Order['OrdersStatusHistory'][0]['OrdersStatus']['OrdersStatusDescription'][Session::get('languages_id')]['orders_status_name'];
			}
			else {
				return (int)$this->Order['OrdersStatusHistory'][0]['OrdersStatus']['OrdersStatusDescription'][Session::get('languages_id')]['orders_status_id'];
			}
		}
		return ($isID === false ? '' : 0);
	}

	/**
	 * @return SesDateTime
	 */
	public function getDatePurchased() {
		return $this->Order['date_purchased'];
	}

	/**
	 * @return bool
	 */
	public function hasStatusHistory() {
		$history = $this->getStatusHistory();
		return (!empty($history));
	}

	/**
	 * @return array
	 */
	public function getStatusHistory() {
		return $this->Order['OrdersStatusHistory'];
	}

	/**
	 * @param bool $cardData
	 * @return htmlElement_table
	 */
	public function listPaymentHistory($cardData = true) {
		return $this->PaymentManager->show($cardData);
	}

	/**
	 * @return htmlElement_table
	 */
	public function listTotals() {
		return $this->TotalManager->show();
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getFormattedAddress($type) {
		return (string)$this->AddressManager->getFormattedAddress($type, true);
	}

	/**
	 * @return string
	 */
	public function listAddresses() {
		return (string)$this->AddressManager->listAll();
	}

	/**
	 * @return OrderProduct[]
	 */
	public function getProducts() {
		return $this->ProductManager->getContents();
	}

	/**
	 * @param bool $showTableHeading
	 * @param bool $showQty
	 * @param bool $showBarcode
	 * @param bool $showModel
	 * @param bool $showName
	 * @param bool $showExtraInfo
	 * @param bool $showPrice
	 * @param bool $showPriceWithTax
	 * @param bool $showTotal
	 * @param bool $showTotalWithTax
	 * @param bool $showTax
	 * @return htmlElement_table
	 */
	public function listProducts($showTableHeading = true, $showQty = true, $showBarcode = true, $showModel = true, $showName = true, $showExtraInfo = true, $showPrice = true, $showPriceWithTax = true, $showTotal = true, $showTotalWithTax = true, $showTax = true) {
		return $this->ProductManager->listProducts($showTableHeading, $showQty, $showBarcode, $showModel, $showName, $showExtraInfo, $showPrice, $showPriceWithTax, $showTotal, $showTotalWithTax, $showTax, $this);
	}

	/**
	 * @param bool $mask
	 * @return string
	 */
	public function getCreditCard($mask = true) {
		$Payment = $this->PaymentManager->getPaymentHistory();
		$cardInfo = unserialize(cc_decrypt($Payment[0]['card_details']));
		return str_repeat('*', 12) . substr($cardInfo['cardNumber'], 12);
	}

	/**
	 * @return string
	 */
	public function getTelephone() {
		$telephone = '';
		if (isset($this->Order['customers_telephone'])){
			$telephone = $this->Order['customers_telephone'];
		}
		return (string)$telephone;
	}

	/**
	 * @return string
	 */
	public function getCellTelephone() {
		$cellphone = '';
		if (isset($this->Order['customers_cellphone'])){
			$cellphone = $this->Order['customers_cellphone'];
		}
		return (string)$cellphone;
	}

	/**
	 * @return string
	 */
	public function getIPAddress() {
		$ip = '';
		if (isset($this->Order['ip_address'])){
			$ip = $this->Order['ip_address'];
		}
		return (string)$ip;
	}

	/**
	 * @return string
	 */
	public function getEmailAddress() {
		$email = '';
		if (isset($this->Order['customers_email_address'])){
			$email = $this->Order['customers_email_address'];
		}
		return (string)$email;
	}

	/**
	 * @return string
	 */
	public function getDriversLicense() {
		$num = '';
		if (isset($this->Order['customers_drivers_license'])){
			$num = $this->Order['customers_drivers_license'];
		}
		return (string)$num;
	}

	/**
	 * @return string
	 */
	public function getPassPort() {
		$passport = '';
		if (isset($this->Order['customers_passport'])){
			$passport = $this->Order['customers_passport'];
		}
		return (string)$passport;
	}

	/**
	 * @return string
	 */
	public function getRoomNumber() {
		$room_number = '';
		if (isset($this->Order['customers_room_number'])){
			$room_number = $this->Order['customers_room_number'];
		}
		return (string)$room_number;
	}
}

?>