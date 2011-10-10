<?php
/**
 * Product for the order product manager
 *
 * @package OrderManager
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2010, I.T. Web Experts
 */

class OrderProduct
{

	protected $pInfo = array();

	protected $id = null;

	private $products_weight = 0;

	public function __construct($pInfo = null) {
		$this->regenerateId();
		if (is_null($pInfo) === false){
			$this->pInfo = $pInfo;
			//$this->id = $pInfo['orders_products_id'];

			$this->productClass = new Product((int)$this->pInfo['products_id']);
			$this->products_weight = $this->productClass->getWeight();
		}
	}

	/**
	 * @return Product
	 */
	public function &getProductClass() {
		return $this->productClass;
	}

	/**
	 * @return mixed
	 */
	public function &getProductTypeClass() {
		return $this->getProductClass()->getProductTypeClass();
	}

	public function regenerateId() {
		$this->id = tep_rand(5555, 99999);
	}

	public function setWeight($val){
		$this->products_weight = $val;
	}

	public function getId() {
		return $this->id;
	}

	public function getOrderedProductId() {
		return $this->pInfo['orders_products_id'];
	}

	public function getProductsId() {
		return $this->pInfo['products_id'];
	}

	public function getIdString() {
		return $this->pInfo['orders_products_id'];
	}

	public function getTaxRate() {
		return $this->pInfo['products_tax'];
	}

	public function getQuantity() {
		return $this->pInfo['products_quantity'];
	}

	public function getModel() {
		return $this->pInfo['products_model'];
	}

	public function getName() {
		return $this->pInfo['products_name'];
	}

	public function hasBarcode() {
		return ($this->getBarcode() !== false);
	}

	public function displayBarcode() {
		$barcode = '';

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'displayOrderedProductBarcode')){
			$barcode = $ProductType->displayOrderedProductBarcode($this->pInfo);
		}
		return $barcode;
	}

	public function getBarcode() {
		$barcode = false;

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'getOrderedProductBarcode')){
			$barcode = $ProductType->getOrderedProductBarcode($this->pInfo);
			if (empty($barcode)){
				$barcode = false;
			}
		}
		return $barcode;
	}

	public function getFinalPrice($wQty = false, $wTax = false) {
		$price = $this->pInfo['final_price'];
		if ($wQty === true){
			$price *= $this->getQuantity();
		}

		if ($wTax === true){
			$price = tep_add_tax($price, $this->getTaxRate());
		}
		return $price;
	}

	public function getPrice($wTax = false) {
		$price = $this->pInfo['products_price'];

		if ($wTax === true){
			$price = tep_add_tax($price, $this->getTaxRate());
		}
		return $price;
	}

	public function getWeight() {
		return $this->products_weight * $this->getQuantity();
	}

	private function getTaxAddressInfo() {
		global $order, $userAccount;
		$zoneId = null;
		$countryId = null;
		if (is_object($order)){
			$taxAddress = $userAccount->plugins['addressBook']->getAddress($order->taxAddress);
			$zoneId = $taxAddress['entry_zone_id'];
			$countryId = $taxAddress['entry_country_id'];
		}
		return array(
			'zoneId' => $zoneId,
			'countryId' => $countryId
		);
	}

	public function getNameHtml($showExtraInfo = true) {
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

		$Result = EventManager::notifyWithReturn('OrderProductAfterProductName', &$this, $showExtraInfo);
		foreach($Result as $html){
			$name .= $html;
		}

		return $name;
	}

	public function hasInfo($key) {
		return (isset($this->pInfo[$key]));
	}

	public function setInfo($pInfo){
		$this->pInfo = $pInfo;
	}

	public function getInfo($key = null) {
		if (is_null($key)){
			return $this->pInfo;
		}
		else {
			if (isset($this->pInfo[$key])){
				return $this->pInfo[$key];
			}
			else {
				return false;
			}
		}
	}
}

?>