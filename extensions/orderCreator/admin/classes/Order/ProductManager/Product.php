<?php
/**
 * Product class for the order creator product manager class
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

require(sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/product/Base.php');

class OrderCreatorProduct extends OrderProduct
{

	/**
	 * @var
	 */
	private $ProductTypeClass;

	/**
	 * @var bool
	 */
	private $requireConfirm = false;

	/**
	 * @var string
	 */
	private $confirmationMessage = '';

	/**
	 * @param array|null $pInfo
	 */
	public function __construct(array $pInfo = null) {
		if (is_null($pInfo) === false){
			$this->setProductId($pInfo['products_id']);

			$ProductType =& $this->getProductTypeClass();
			if (method_exists($ProductType, 'processAddToOrder')){
				$ProductType->processAddToOrder(&$pInfo);
			}

			$this->updateInfo($pInfo);
		}
	}

	/**
	 * @param null|bool $val
	 * @return bool
	 */
	public function needsConfirmation($val = null){
		if ($val !== null){
			$this->requireConfirm = $val;
		}
		return $this->requireConfirm;
	}

	/**
	 * @return string
	 */
	public function getConfirmationMessage(){
		$message = 'This product does not have enough inventory available.';
		if ($this->confirmationMessage != ''){
			$message = $this->confirmationMessage;
		}
		$message .= "<br><br>" . 'Would you like to add it anyway?';
		return $message;
	}

	/**
	 * @param string $val
	 */
	public function setConfirmationMessage($val = ''){
		$this->confirmationMessage = $val;
	}

	/**
	 * @param string $type
	 */
	public function loadProductTypeClass($type) {
		$className = 'OrderCreatorProductType' . ucfirst($type);
		if (class_exists($className) === false){
			$fileName = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/ProductTypeModules/' . $type . '.php';
			if (file_exists($fileName)){
				require($fileName);
			}
		}

		if (class_exists($className)){
			$this->ProductTypeClass = new $className;
			$this->ProductTypeClass->setProductId($this->getProductsId());
		}
		else {
			$this->ProductTypeClass = $this->getProductClass()->getProductTypeClass();
		}
	}

	/**
	 * @return OrderCreatorProductTypeStandard|OrderCreatorProductTypePackage
	 */
	public function &getProductTypeClass() {
		return $this->ProductTypeClass;
	}

	/**
	 *
	 */
	public function init() {
		$this->setProductId((int)$this->pInfo['products_id']);

		$ProductType =& $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorProductOnInit')){
			$ProductType->OrderCreatorProductOnInit(&$this->pInfo);
		}
	}

	/**
	 * @param int $pID
	 */
	public function setProductId($pID) {
		global $Editor;
		$this->productClass = new Product($pID);
		$this->loadProductTypeClass($this->productClass->getProductType());

		$this->pInfo['products_id'] = $pID;
		$this->pInfo['products_name'] = $this->getProductClass()->getName();
		$this->pInfo['products_weight'] = $this->getProductClass()->getWeight();
		$this->pInfo['products_model'] = $this->getProductClass()->getModel();

		$taxAddress = null;
		if (is_object($Editor->AddressManager)){
			$taxAddress = $Editor->AddressManager->getAddress('billing');
		}
		$this->setTaxRate(tep_get_tax_rate(
			$this->getProductTypeClass()->getTaxClassId(),
			(is_object($taxAddress) ? $taxAddress->getCountryId() : -1),
			(is_object($taxAddress) ? $taxAddress->getZoneId() : -1)
		));

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'setProductId')){
			$ProductType->setProductId($this->pInfo['products_id']);
		}
	}

	/**
	 *
	 */
	public function OrderCreatorUpdateProductInfo() {
		$updateAllowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorAllowProductUpdate')){
			$updateAllowed = $ProductType->OrderCreatorAllowProductUpdate($this);
		}

		if ($updateAllowed === true && method_exists($ProductType, 'OrderCreatorUpdateProductInfo')){
			$pInfo = $this->pInfo;
			$ProductType->OrderCreatorUpdateProductInfo(&$pInfo);
			$this->pInfo = $pInfo;
		}
	}

	/**
	 * @return bool
	 */
	public function OrderCreatorAllowAddToContents() {
		$addAllowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorAllowAddToContents')){
			$addAllowed = $ProductType->OrderCreatorAllowAddToContents($this);
		}
		return $addAllowed;
	}

	/**
	 *
	 */
	public function OrderCreatorOnAddToContents() {
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorOnAddToContents')){
			$ProductType->OrderCreatorOnAddToContents($this);
		}
	}

	/**
	 * @param float $val
	 */
	public function setTaxRate($val) {
		$this->pInfo['products_tax'] = (float) $val;
	}

	/**
	 * @param int $val
	 */
	public function setQuantity($val) {
		$this->pInfo['products_quantity'] = (int) $val;

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onSetQuantity')){
			$ProductType->onSetQuantity($this);
		}
	}

	/**
	 * @param float $val
	 */
	public function setPrice($val) {
		$this->pInfo['products_price'] = (float) $val;
		$this->pInfo['final_price'] = (float) $val;
	}

	/**
	 * @param array $val
	 */
	public function setBarcodes(array $val) {
		$this->pInfo['Barcodes'] = $val;
	}

	/**
	 * @return array
	 */
	public function getBarcodes() {
		return $this->pInfo['Barcodes'];
	}

	/**
	 * @return bool
	 */
	public function hasBarcodes() {
		return (isset($this->pInfo['Barcodes']));
	}

	/**
	 * @return string
	 */
	public function getTaxRateEdit() {
		return '<input type="text" size="5" class="ui-widget-content taxRate" name="product[' . $this->id . '][tax_rate]" value="' . $this->getTaxRate() . '">%';
	}

	/**
	 * @param bool $incQty
	 * @param bool $incTax
	 * @return string
	 */
	public function getPriceEdit($incQty = false, $incTax = false) {
		global $Editor, $currencies;
		$html = '';
		if ($incQty === false && $incTax === false){
			$html = '<input type="text" size="5" class="ui-widget-content priceEx" name="product[' . $this->id . '][price]" value="' . $this->getFinalPrice($incQty, $incTax) . '">';
		}
		elseif ($incQty === true && $incTax === false) {
			$html = '<b class="priceExTotal">' . $currencies->format($this->getFinalPrice($incQty, $incTax), true, $Editor->getCurrency(), $Editor->getCurrencyValue()) . '</b>';
		}
		elseif ($incQty === false && $incTax === true) {
			$html = '<b class="priceIn">' . $currencies->format($this->getFinalPrice($incQty, $incTax), true, $Editor->getCurrency(), $Editor->getCurrencyValue()) . '</b>';
		}
		elseif ($incQty === true && $incTax === true) {
			$html = '<b class="priceInTotal">' . $currencies->format($this->getFinalPrice($incQty, $incTax), true, $Editor->getCurrency(), $Editor->getCurrencyValue()) . '</b>';
		}
		return $html;
	}

	/**
	 * @return string
	 */
	public function getQuantityEdit() {
		return '<input type="text" size="3" class="ui-widget-content productQty" name="product[' . $this->id . '][qty]" value="' . $this->getQuantity() . '">&nbsp;x';
	}

	/**
	 * @param array $excludedPurchaseTypes
	 * @return string
	 */
	public function getNameEdit($excludedPurchaseTypes = array()) {
		$ProductType = $this->getProductTypeClass();
		$productsName = $this->getName();
		if (method_exists($ProductType, 'OrderCreatorAfterProductName')){
			$productsName .= $ProductType->OrderCreatorAfterProductName($this);
		}

		$contents = EventManager::notifyWithReturn('OrderProductAfterProductNameEdit', $this);
		foreach($contents as $content){
			$productsName .= $content;
		}
		return $productsName;
	}

	/**
	 * @return string
	 */
	public function getBarcodeEdit() {
		$barcodeDrop = '';
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorBarcodeEdit')){
			$barcodeDrop = $ProductType->OrderCreatorBarcodeEdit($this);
		}
		return $barcodeDrop;
	}

	/**
	 * @param string $k
	 * @param mixed $v
	 */
	public function setInfo($k, $v = ''){
		if (is_array($k)){
			$this->pInfo = $k;
		}else{
			$this->pInfo[$k] = $v;
		}
	}

	/**
	 * @param array $newInfo
	 */
	public function updateInfo(array $newInfo) {
		$newProductInfo = $this->pInfo;
		foreach($newInfo as $k => $v){
			$newProductInfo[$k] = $v;
		}
		$this->pInfo = $newProductInfo;

		//$this->purchaseTypeClass->processUpdateCart(&$this->pInfo);
	}

	public function onUpdateOrderProduct(){
		$this->updateInfo(array(
			'purchase_type' => $_GET['purchase_type']
		));

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onUpdateOrderProduct')){
			$ProductType->onUpdateOrderProduct($this);
		}
	}

	/**
	 * @param OrdersProducts $OrderedProduct
	 */
	public function onAddToCollection(OrdersProducts &$OrderedProduct) {
		$ProductType =& $this->getProductTypeClass();
		if (method_exists($ProductType, 'addToOrdersProductCollection')){
			$ProductType->addToOrdersProductCollection($this, $OrderedProduct);
		}

		EventManager::notify('OrderCreatorProductAddToCollection', $this, $OrderedProduct);
	}
}

?>