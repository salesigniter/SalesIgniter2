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
 * Product for the order product manager
 *
 * @package   Order
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderProduct
{

	/**
	 * @var array|null
	 */
	protected $pInfo = array(
		'products_id'        => 0,
		'orders_products_id' => 0,
		'products_tax'       => 0,
		'products_quantity'  => 0,
		'products_model'     => '',
		'products_name'      => ''
	);

	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @var float
	 */
	protected $products_weight = 0;

	/**
	 * @var
	 */
	protected $ProductTypeClass;

	/**
	 * @param array|null $pInfo
	 */
	public function __construct(array $pInfo = null)
	{
		$this->regenerateId();
		if (is_null($pInfo) === false){
			$this->pInfo = $pInfo;
			//$this->id = $pInfo['orders_products_id'];

			$this->productClass = new Product((int)$this->pInfo['products_id']);
			$this->products_weight = $this->productClass->getWeight();
		}
	}

	/**
	 *
	 */
	public function init()
	{
	}

	/**
	 * @return Product
	 */
	public function &getProductClass()
	{
		return $this->productClass;
	}

	/**
	 *
	 */
	public function loadProductTypeClass()
	{
		$this->ProductTypeClass = $this->getProductClass()->getProductTypeClass();
	}

	/**
	 * @return ProductTypeStandard|ProductTypePackage|ProductTypeGiftVoucher|ProductTypeMembership
	 */
	public function &getProductTypeClass()
	{
		return $this->getProductClass()->getProductTypeClass();
	}

	/**
	 *
	 */
	public function regenerateId()
	{
		$this->id = tep_rand(5555, 99999);
	}

	/**
	 * @param float $val
	 */
	public function setWeight($val)
	{
		$this->products_weight = (float)$val;
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getOrderedProductId()
	{
		return (int)$this->pInfo['orders_products_id'];
	}

	/**
	 * @return int
	 */
	public function getProductsId()
	{
		return (int)$this->pInfo['products_id'];
	}

	/**
	 * @return int
	 */
	public function getIdString()
	{
		return (int)$this->pInfo['orders_products_id'];
	}

	/**
	 * @return float
	 */
	public function getTaxRate()
	{
		return (float)$this->pInfo['products_tax'];
	}

	/**
	 * @return int
	 */
	public function getQuantity()
	{
		return (int)$this->pInfo['products_quantity'];
	}

	/**
	 * @return string
	 */
	public function getModel()
	{
		return (string)$this->pInfo['products_model'];
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return (string)$this->pInfo['products_name'];
	}

	/**
	 * @return bool
	 */
	public function hasBarcode()
	{
		return ($this->getBarcode() !== false);
	}

	/**
	 * @return string
	 */
	public function displayBarcodes()
	{
		$return = '';

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'displayOrderedProductBarcodes')){
			$return = $ProductType->displayOrderedProductBarcodes($this);
		}
		return $return;
	}

	/**
	 * @return string
	 */
	public function getBarcodes()
	{
		$barcode = '';

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'getOrderedProductBarcodes')){
			$barcode = $ProductType->getOrderedProductBarcodes($this->pInfo);
		}
		return (string)$barcode;
	}

	/**
	 * @param bool $wQty
	 * @param bool $wTax
	 * @return float
	 */
	public function getFinalPrice($wQty = false, $wTax = false)
	{
		$price = $this->pInfo['final_price'];
		if ($wQty === true){
			$price *= $this->getQuantity();
		}

		if ($wTax === true){
			$price = tep_add_tax($price, $this->getTaxRate());
		}
		return (float)$price;
	}

	/**
	 * @param bool $wTax
	 * @return float
	 */
	public function getPrice($wTax = false)
	{
		$price = $this->pInfo['products_price'];

		if ($wTax === true){
			$price = tep_add_tax($price, $this->getTaxRate());
		}
		return (float)$price;
	}

	/**
	 * @return float
	 */
	public function getWeight()
	{
		return (float)($this->products_weight * $this->getQuantity());
	}

	/**
	 * @return array
	 */
	private function getTaxAddressInfo()
	{
		global $order, $userAccount;
		$zoneId = null;
		$countryId = null;
		if (is_object($order)){
			$taxAddress = $userAccount->plugins['addressBook']->getAddress($order->taxAddress);
			$zoneId = $taxAddress['entry_zone_id'];
			$countryId = $taxAddress['entry_country_id'];
		}
		return array(
			'zoneId'    => $zoneId,
			'countryId' => $countryId
		);
	}

	/**
	 * @param bool $showExtraInfo
	 * @return string
	 */
	public function getNameHtml($showExtraInfo = true)
	{
		$nameHref = htmlBase::newElement('a')
			->setHref(itw_catalog_app_link('products_id=' . $this->getProductsId(), 'product', 'info'))
			->css(array(
			'font-weight' => 'bold'
		))
			->attr('target', '_blank')
			->html($this->getName());

		$ProductType = $this->getProductClass()->getProductTypeClass();

		$name = $nameHref->draw() .
			'<br />' .
			$ProductType->showOrderedProductInfo($this, $showExtraInfo);

		$Result = EventManager::notifyWithReturn('OrderProductAfterProductName', $this, $showExtraInfo);
		foreach($Result as $html){
			$name .= $html;
		}

		return (string)$name;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function hasInfo($key)
	{
		return (isset($this->pInfo[$key]));
	}

	/**
	 * @param        $k
	 * @param string $v
	 */
	public function setInfo($k, $v = '')
	{
		if (is_array($k)){
			$this->pInfo = $k;
		}
		else {
			$this->pInfo[$k] = $v;
		}
	}

	/**
	 * @param null $key
	 * @return array|bool|null
	 */
	public function getInfo($key = null)
	{
		if (is_null($key)){
			return $this->pInfo;
		}
		else {
			if (isset($this->pInfo[$key])){
				return $this->pInfo[$key];
			}
			else {
				return null;
			}
		}
	}

	/**
	 * @param null $Qty
	 * @return bool
	 */
	public function hasEnoughInventory($Qty = null)
	{
		$return = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'hasEnoughInventory')){
			$return = $ProductType->hasEnoughInventory($this, $Qty);
		}
		return $return;
	}

	/**
	 * @param $SaleProduct
	 */
	public function onSaveProgress(&$SaleProduct)
	{
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onSaveProgress')){
			$ProductType->onSaveProgress($this, $SaleProduct);
		}

		$SaleProduct->product_json = json_encode($this->prepareJsonSave());
	}

	/**
	 * @param      $SaleProduct
	 * @param bool $AssignInventory
	 */
	public function onSaveSale(&$SaleProduct, $AssignInventory = false)
	{
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onSaveSale')){
			$ProductType->onSaveSale($this, $SaleProduct, $AssignInventory);
		}

		$SaleProduct->product_json = json_encode($this->prepareJsonSave());
	}

	/**
	 * @return array
	 */
	public function prepareJsonSave()
	{
		$toEncode = array(
			'id'    => $this->id,
			'pInfo' => $this->pInfo
		);

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'prepareJsonSave')){
			$toEncode['ProductTypeJson'] = $ProductType->prepareJsonSave($this);
		}
		return $toEncode;
	}

	/**
	 * Used when loading the sale from the database
	 *
	 * @param AccountsReceivableSalesProducts $Product
	 */
	public function jsonDecodeProduct(AccountsReceivableSalesProducts $Product)
	{
		$ProductInfo = json_decode($Product->product_json, true);
		if ($ProductInfo){
			//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($ProductInfo);
			$this->id = $ProductInfo['id'];
			$this->pInfo = $ProductInfo['pInfo'];

			$this->productClass = new Product($this->pInfo['products_id']);
			$this->products_weight = $this->productClass->getWeight();

			$this->loadProductTypeClass($this->productClass->getProductType());

			$ProductType = $this->getProductTypeClass();
			if (method_exists($ProductType, 'jsonDecodeProduct')){
				$ProductType->jsonDecode($this, $ProductInfo['ProductTypeJson']);
			}else{
				if (method_exists($ProductType, 'setProductId')){
					$ProductType->setProductId($this->pInfo['products_id']);
				}
			}
		}
	}

	/**
	 * @param array $ProductInfo
	 */
	public function jsonDecode(array $ProductInfo)
	{
		$this->id = $ProductInfo['id'];
		$this->pInfo = $ProductInfo['pInfo'];

		$this->productClass = new Product($this->pInfo['products_id']);
		$this->products_weight = $this->productClass->getWeight();

		$this->loadProductTypeClass($this->productClass->getProductType());

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'jsonDecode')){
			$ProductType->jsonDecode($this, $ProductInfo['ProductTypeJson']);
		}else{
			if (method_exists($ProductType, 'setProductId')){
				$ProductType->setProductId($this->pInfo['products_id']);
			}
		}
	}
}
