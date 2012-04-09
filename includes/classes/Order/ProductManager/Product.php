<?php
/**
 * Product for the order product manager
 *
 * @package OrderManager
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderProduct
{

	/**
	 * @var array|null
	 */
	protected $pInfo = array();

	/**
	 * @var int
	 */
	protected $id = 0;

	/**
	 * @var float
	 */
	private $products_weight = 0;

	/**
	 * @param array|null $pInfo
	 */
	public function __construct(array $pInfo = null) {
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
	public function init(){

	}

	/**
	 * @return Product
	 */
	public function &getProductClass() {
		return $this->productClass;
	}

	/**
	 * @return ProductTypeStandard|ProductTypePackage|ProductTypeGiftVoucher|ProductTypeMembership
	 */
	public function &getProductTypeClass() {
		return $this->getProductClass()->getProductTypeClass();
	}

	/**
	 *
	 */
	public function regenerateId() {
		$this->id = tep_rand(5555, 99999);
	}

	/**
	 * @param float $val
	 */
	public function setWeight($val){
		$this->products_weight = (float) $val;
	}

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @return int
	 */
	public function getOrderedProductId() {
		return (int) $this->pInfo['orders_products_id'];
	}

	/**
	 * @return int
	 */
	public function getProductsId() {
		return (int) $this->pInfo['products_id'];
	}

	/**
	 * @return int
	 */
	public function getIdString() {
		return (int) $this->pInfo['orders_products_id'];
	}

	/**
	 * @return float
	 */
	public function getTaxRate() {
		return (float) $this->pInfo['products_tax'];
	}

	/**
	 * @return int
	 */
	public function getQuantity() {
		return (int) $this->pInfo['products_quantity'];
	}

	/**
	 * @return string
	 */
	public function getModel() {
		return (string) $this->pInfo['products_model'];
	}

	/**
	 * @return string
	 */
	public function getName() {
		return (string) $this->pInfo['products_name'];
	}

	/**
	 * @return bool
	 */
	public function hasBarcode() {
		return ($this->getBarcode() !== false);
	}

	/**
	 * @return string
	 */
	public function displayBarcodes() {
		$return = '';

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'displayOrderedProductBarcodes')){
			$return = $ProductType->displayOrderedProductBarcodes($this->pInfo);
		}
		return $return;
	}

	/**
	 * @return string
	 */
	public function getBarcodes() {
		$barcode = '';

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'getOrderedProductBarcodes')){
			$barcode = $ProductType->getOrderedProductBarcodes($this->pInfo);
		}
		return (string) $barcode;
	}

	/**
	 * @param bool $wQty
	 * @param bool $wTax
	 * @return float
	 */
	public function getFinalPrice($wQty = false, $wTax = false) {
		$price = $this->pInfo['final_price'];
		if ($wQty === true){
			$price *= $this->getQuantity();
		}

		if ($wTax === true){
			$price = tep_add_tax($price, $this->getTaxRate());
		}
		return (float) $price;
	}

	/**
	 * @param bool $wTax
	 * @return float
	 */
	public function getPrice($wTax = false) {
		$price = $this->pInfo['products_price'];

		if ($wTax === true){
			$price = tep_add_tax($price, $this->getTaxRate());
		}
		return (float) $price;
	}

	/**
	 * @return float
	 */
	public function getWeight() {
		return (float) ($this->products_weight * $this->getQuantity());
	}

	/**
	 * @return array
	 */
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

	/**
	 * @param bool $showExtraInfo
	 * @return string
	 */
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

		return (string) $name;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function hasInfo($key) {
		return (isset($this->pInfo[$key]));
	}

	/**
	 * @param array $pInfo
	 */
	public function setInfo(array $pInfo){
		$this->pInfo = $pInfo;
	}

	/**
	 * @param null $key
	 * @return array|bool|null
	 */
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