<?php
/**
 * Sales Igniter E-Commerce System
 * Version: {ses_version}
 *
 * I.T. Web Experts
 * http://www.itwebexperts.com
 *
 * Copyright (c) {ses_copyright} I.T. Web Experts
 *
 * This script and its source are not distributable without the written consent of I.T. Web Experts
 */

/**
 * Main order class
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class Order
{

	/**
	 * @var int
	 */
	public $saleId = 0;

	/**
	 * @var string
	 */
	public $mode = 'details';

	/**
	 * @var OrderInfoManager
	 */
	public $InfoManager;

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
	 * @var AccountsReceivableModule
	 */
	protected $SaleModule = null;

	/**
	 *
	 */
	public function __construct($saleType, $saleId = 0, $revision = null)
	{
		$this->InfoManager = new OrderInfoManager();
		$this->AddressManager = new OrderAddressManager();
		$this->ProductManager = new OrderProductManager($saleId);
		$this->TotalManager = new OrderTotalManager();
		$this->PaymentManager = new OrderPaymentManager();

		if (AccountsReceivableModules::loadModule($saleType)){
			$Module = AccountsReceivableModules::getModule($saleType);
			$this->SaleModule = $Module;
			$this->SaleModule->load($this, true, $saleId, $revision);
		}
	}

	/**
	 * @param AccountsReceivableModule $Module
	 */
	public function setSaleModule(AccountsReceivableModule $Module)
	{
		$this->SaleModule = $Module;
	}

	/**
	 * @return AccountsReceivableModule|null
	 */
	public function getSaleModule()
	{
		return $this->SaleModule;
	}

	/**
	 * @param int $ProductId
	 * @param int $Quantity
	 */
	public function addProduct($ProductId, $Quantity = 1){
		$OrderProduct = new OrderProduct();
		$OrderProduct->setProductId($ProductId);
		$OrderProduct->setQuantity($Quantity);

		$Success = $this->ProductManager->add($OrderProduct);
		if ($Success === false){
			$this->addErrorMessage('Unable to add product to order!');
		}else{
			$this->TotalManager->onProductAdded($this->ProductManager);
		}
	}

	/**
	 * @return int
	 */
	public function getSaleId()
	{
		return $this->saleId;
	}

	/**
	 * @param int $val
	 */
	public function setSaleId($val)
	{
		$this->saleId = $val;
	}

	/**
	 * @return string
	 */
	public function getCustomersName()
	{
		return $this->InfoManager->getInfo('customers_firstname') . ' ' . $this->InfoManager->getInfo('customers_lastname');
	}

	/**
	 * @return OrderInfo[]
	 */
	public function getDateAdded()
	{
		return $this->InfoManager->getInfo('date_added');
	}

	/**
	 * @return mixed
	 */
	public function getStatusName()
	{
		$Status = Doctrine_Query::create()
			->from('OrdersStatusDescription')
			->where('orders_status_id = ?', $this->InfoManager->getInfo('status'))
			->andWhere('language_id = ?', Session::get('languages_id'))
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		return (isset($Status[0]) ? $Status[0]['orders_status_name'] : 'Unknown');
	}

	/**
	 * @return OrderInfo[]
	 */
	public function getDateModified()
	{
		return $this->InfoManager->getInfo('last_modified');
	}

	/**
	 * @return OrderInfo[]
	 */
	public function getPaymentMethod()
	{
		return $this->InfoManager->getInfo('payment_method');
	}

	/**
	 * @return OrderInfo[]
	 */
	public function getRevision()
	{
		return $this->InfoManager->getInfo('revision');
	}

	/**
	 * @return int
	 */
	public function getCustomerId()
	{
		return $this->InfoManager->getInfo('customers_id');
	}

	/**
	 * @return array
	 */
	public function getOrderInfo()
	{
		return $this->InfoManager->getInfo();
	}

	/**
	 * @return string
	 */
	public function getCurrency()
	{
		return (string)$this->InfoManager->getInfo('currency');
	}

	/**
	 * @return float
	 */
	public function getCurrencyValue()
	{
		return (float)$this->InfoManager->getInfo('currency_value');
	}

	/**
	 * @return bool
	 */
	public function hasTaxes()
	{
		return ($this->TotalManager->getTotalValue('tax') > 0);
	}

	/**
	 * @return bool
	 */
	public function hasShippingMethod()
	{
		return ($this->InfoManager->getInfo('shipping_method') != '');
	}

	/**
	 * @return string
	 */
	public function getShippingMethod()
	{
		return (string)$this->InfoManager->getInfo('shipping_method');
	}

	/**
	 * @param bool $format
	 * @return float
	 */
	public function getTotal($format = false)
	{
		global $currencies;
		$value = $this->TotalManager->getTotalValue('total');
		if ($format === true){
			$value = $currencies->format(
				$value,
				true,
				$this->getCurrency(),
				$this->getCurrencyValue()
			);
		}
		return $value;
	}

	/**
	 * @param bool $isID
	 * @return int|string
	 */
	public function getCurrentStatus($isID = false)
	{
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
	 * @return bool
	 */
	public function hasStatusHistory()
	{
		$history = $this->getStatusHistory();
		return (!empty($history));
	}

	/**
	 * @return array
	 */
	public function getStatusHistory()
	{
		return $this->Order['OrdersStatusHistory'];
	}

	/**
	 * @param bool $cardData
	 * @return htmlElement_table
	 */
	public function listPaymentHistory($cardData = true)
	{
		return $this->PaymentManager->show($cardData);
	}

	/**
	 * @return htmlElement_table
	 */
	public function listTotals()
	{
		return $this->TotalManager->show();
	}

	/**
	 * @param $type
	 * @return string
	 */
	public function getFormattedAddress($type)
	{
		return (string)$this->AddressManager->getFormattedAddress($type, true);
	}

	/**
	 * @return string
	 */
	public function listAddresses()
	{
		return (string)$this->AddressManager->listAll();
	}

	/**
	 * @return OrderProduct[]
	 */
	public function getProducts()
	{
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
	public function listProducts($showTableHeading = true, $showQty = true, $showBarcode = true, $showModel = true, $showName = true, $showExtraInfo = true, $showPrice = true, $showPriceWithTax = true, $showTotal = true, $showTotalWithTax = true, $showTax = true)
	{
		return $this->ProductManager->listProducts($showTableHeading, $showQty, $showBarcode, $showModel, $showName, $showExtraInfo, $showPrice, $showPriceWithTax, $showTotal, $showTotalWithTax, $showTax, $this);
	}

	/**
	 * @param bool $mask
	 * @return string
	 */
	public function getCreditCard($mask = true)
	{
		$Payment = $this->PaymentManager->getPaymentHistory();
		$cardInfo = unserialize(cc_decrypt($Payment[0]['card_details']));
		return str_repeat('*', 12) . substr($cardInfo['cardNumber'], 12);
	}

	/**
	 * @return string
	 */
	public function getTelephone()
	{
		return (string)$this->InfoManager->getInfo('customers_telephone');
	}

	/**
	 * @return string
	 */
	public function getCellTelephone()
	{
		return (string)$this->InfoManager->getInfo('customers_cellphone');
	}

	/**
	 * @return string
	 */
	public function getIPAddress()
	{
		return (string)$this->InfoManager->getInfo('ip_address');
	}

	/**
	 * @return string
	 */
	public function getEmailAddress()
	{
		return (string)$this->InfoManager->getInfo('customers_email_address');
	}

	/**
	 * @return string
	 */
	public function getDriversLicense()
	{
		return (string)$this->InfoManager->getInfo('customers_drivers_license');
	}

	/**
	 * @return string
	 */
	public function getPassPort()
	{
		return (string)$this->InfoManager->getInfo('customers_passport');
	}

	/**
	 * @return string
	 */
	public function getRoomNumber()
	{
		return (string)$this->InfoManager->getInfo('customers_room_number');
	}
}

require(dirname(__FILE__) . '/InfoManager/Base.php');
require(dirname(__FILE__) . '/AddressManager/Base.php');
require(dirname(__FILE__) . '/ProductManager/Base.php');
require(dirname(__FILE__) . '/TotalManager/Base.php');
require(dirname(__FILE__) . '/PaymentManager/Base.php');
