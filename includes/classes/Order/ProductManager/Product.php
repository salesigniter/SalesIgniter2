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
		'products_tax'       => 0,
		'products_quantity'  => 0,
		'products_model'     => '',
		'products_name'      => '',
		'products_weight'    => 0
	);

	/**
	 * @var array
	 */
	protected $inventory = array();
	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @var Product
	 */
	protected $ProductClass;

	/**
	 * @var ProductTypeBase
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

			$this->loadProduct($this->pInfo['products_id']);
		}
	}

	public function loadProduct($productId){
		$this->ProductClass = new Product((int)$productId);
		$this->ProductTypeClass = $this->ProductClass->getProductTypeClass();
	}

	/**
	 * @return Product
	 */
	public function &getProductClass()
	{
		return $this->ProductClass;
	}

	/**
	 * @return ProductTypeBase
	 */
	public function &getProductTypeClass()
	{
		return $this->ProductTypeClass;
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
		$this->pInfo['products_weight'] = (float)$val;
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
	public function getProductsId()
	{
		return (int)$this->pInfo['products_id'];
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
	 * @return bool
	 */
	public function hasBarcodes()
	{
		return (isset($this->pInfo['Barcodes']));
	}

	/**
	 * @return string
	 */
	public function displayBarcodes()
	{
		$return = array();
		foreach($this->inventory as $BarcodeInfo){
			$return[] = $BarcodeInfo['barcode'];
		}
		$return = implode('<br>', $return);

		if (method_exists($this->ProductTypeClass, 'displayOrderedProductBarcodes')){
			$return .= $this->ProductTypeClass->displayOrderedProductBarcodes($this);
		}
		return $return;
	}

	/**
	 * @return string
	 */
	public function getBarcodes()
	{
		$barcode = '';

		if (method_exists($this->ProductTypeClass, 'getOrderedProductBarcodes')){
			$barcode = $this->ProductTypeClass->getOrderedProductBarcodes($this->pInfo);
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
		return (float)($this->productClass->getWeight() * $this->getQuantity());
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

		$name = $nameHref->draw() .
			'<br />' .
			$this->ProductTypeClass->showOrderedProductInfo($this, $showExtraInfo);

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
	 * @param array $newInfo
	 */
	public function updateInfo(array $newInfo)
	{
		foreach($this->pInfo as $k => $v){
			$this->setInfo($k, $v);
		}
	}

	/**
	 * @param null $Qty
	 * @return bool
	 */
	public function hasEnoughInventory($Qty = null)
	{
		$return = true;
		if (method_exists($this->ProductTypeClass, 'hasEnoughInventory')){
			$return = $this->ProductTypeClass->hasEnoughInventory($this, $Qty);
		}
		return $return;
	}

	/**
	 * @param AccountsReceivableSalesProducts $SaleProduct
	 */
	public function onSaveProgress(AccountsReceivableSalesProducts &$SaleProduct)
	{
		if (method_exists($this->ProductTypeClass, 'onSaveProgress')){
			$this->ProductTypeClass->onSaveProgress($this, $SaleProduct);
		}

		$SaleProduct->product_json = json_encode($this->prepareJsonSave());
	}

	/**+
	 * @param AccountsReceivableSalesProducts $SaleProduct
	 * @param bool                            $AssignInventory
	 */
	public function onSaveSale(AccountsReceivableSalesProducts &$SaleProduct, $AssignInventory = false)
	{
		if (method_exists($this->ProductTypeClass, 'onSaveSale')){
			//echo __FILE__ . '::' . __LINE__ . '<br>';
			$this->ProductTypeClass->onSaveSale($this, $SaleProduct, $AssignInventory);
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

		if (method_exists($this->ProductTypeClass, 'prepareJsonSave')){
			$toEncode['ProductTypeJson'] = $this->ProductTypeClass->prepareJsonSave($this);
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
			$this->id = $ProductInfo['id'];
			$this->pInfo = $ProductInfo['pInfo'];

			$this->inventory = array();
			foreach($Product->SaleInventory as $Inventory){
				if ($Inventory->barcode_id > 0){
					$BarcodeInfo = $Inventory->Barcode;
					$this->inventory[] = array(
						'barcode_id' => $BarcodeInfo->barcode_id,
						'barcode' => $BarcodeInfo->barcode,
						'status' => $BarcodeInfo->status
					);
				}
			}

			//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($this->inventory);

			$this->loadProduct($this->pInfo['products_id']);
			if (method_exists($this->ProductTypeClass, 'jsonDecode')){
				$this->ProductTypeClass->jsonDecode($this, $ProductInfo['ProductTypeJson']);
			}
		}
	}

	/**
	 * @param array $ProductInfo
	 */
	public function jsonDecode(array $ProductInfo)
	{
		//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($ProductInfo);
		$this->id = $ProductInfo['id'];
		$this->pInfo = $ProductInfo['pInfo'];

		$this->loadProduct($this->pInfo['products_id']);
		if (method_exists($this->ProductTypeClass, 'jsonDecode')){
			$this->ProductTypeClass->jsonDecode($this, $ProductInfo['ProductTypeJson']);
		}
	}
}
