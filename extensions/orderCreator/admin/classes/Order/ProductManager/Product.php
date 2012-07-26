<?php
/**
 * Product class for the order creator product manager class
 *
 * @package   OrderCreator\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderCreatorProduct extends OrderProduct
{

	/**
	 * @var bool
	 */
	private $requireConfirm = false;

	/**
	 * @var string
	 */
	private $confirmationMessage = '';

	/**
	 * @return ProductTypeBase|OrderCreatorProductTypeStandard|OrderCreatorProductTypePackage
	 */
	public function &getProductTypeClass()
	{
		return $this->ProductTypeClass;
	}

	/**
	 *
	 */
	public function loadProductType()
	{
		ProductTypeModules::$classPrefix = 'OrderCreatorProductType';
		$isLoaded = ProductTypeModules::loadModule(
			$this->products_type,
			sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/Order/ProductManager/ProductTypeModules/' . $this->products_type . '/'
		);
		if ($isLoaded === true){
			$this->ProductTypeClass = ProductTypeModules::getModule($this->products_type);
			if (is_object($this->ProductTypeClass) === false){
				echo '<pre>';
				debug_print_backtrace();
				echo '</pre>';
				die('Error loading product type: ' . $this->products_type);
			}
			$this->ProductTypeClass->setProductId($this->products_id);
		}
		else {
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
			die('Error loading product type: ' . $this->products_type);
		}
	}

	/**
	 * @param null|bool $val
	 * @return bool
	 */
	public function needsConfirmation($val = null)
	{
		if ($val !== null){
			$this->requireConfirm = $val;
		}
		return $this->requireConfirm;
	}

	/**
	 * @return string
	 */
	public function getConfirmationMessage()
	{
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
	public function setConfirmationMessage($val = '')
	{
		$this->confirmationMessage = $val;
	}

	/**
	 * @param int $pID
	 */
	public function setProductId($pID)
	{
		global $Editor;
		$this->loadProductBaseInfo($pID);
		$this->loadProductType();

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'setProductId')){
			$ProductType->setProductId($this->products_id);
		}
	}

	/**
	 *
	 */
	public function OrderCreatorUpdateProductInfo()
	{
		$updateAllowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorAllowProductUpdate')){
			$updateAllowed = $ProductType->OrderCreatorAllowProductUpdate($this);
		}

		if ($updateAllowed === true && method_exists($ProductType, 'OrderCreatorUpdateProductInfo')){
			$extraInfo = $this->extraInfo;
			$ProductType->OrderCreatorUpdateProductInfo(&$extraInfo);
			$this->extraInfo = $extraInfo;
		}
	}

	/**
	 * @return bool
	 */
	public function allowAddToContents()
	{
		$addAllowed = true;
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'allowAddToContents')){
			$addAllowed = $ProductType->allowAddToContents($this);
		}
		return $addAllowed;
	}

	/**
	 *
	 */
	public function onAddToContents()
	{
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onAddToContents')){
			$ProductType->onAddToContents($this);
		}
	}

	/**
	 * @param float $val
	 */
	public function setTaxRate($val)
	{
		$this->products_tax = (float)$val;
	}

	/**
	 * @param float $val
	 */
	public function setTaxClassId($val)
	{
		$this->products_tax_class_id = (float)$val;
	}

	/**
	 * @param int $val
	 */
	public function setQuantity($val)
	{
		$this->products_quantity = (int)$val;

		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onSetQuantity')){
			$ProductType->onSetQuantity($this);
		}
	}

	/**
	 * @param float $val
	 */
	public function setPrice($val)
	{
		$this->products_price = (float)$val;
	}

	/**
	 * @param array $val
	 */
	public function setBarcodes(array $val)
	{
		$this->extraInfo['Barcodes'] = $val;
	}

	/**
	 * @return string
	 */
	public function getTaxRateEdit()
	{
		return '<input type="text" size="5" class="ui-widget-content taxRate" name="product[' . $this->id . '][tax_rate]" value="' . $this->getTaxRate() . '">%';
	}

	/**
	 * @param bool $incQty
	 * @param bool $incTax
	 * @return string
	 */
	public function getPriceEdit($incQty = false, $incTax = false)
	{
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
	public function getQuantityEdit()
	{
		return '<input type="text" size="3" class="ui-widget-content productQty" name="product[' . $this->id . '][qty]" value="' . $this->getQuantity() . '">&nbsp;x';
	}

	/**
	 * @param array $excludedPurchaseTypes
	 * @return string
	 */
	public function getNameEdit($excludedPurchaseTypes = array())
	{
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
	public function getBarcodeEdit()
	{
		$barcodeDrop = '';
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'OrderCreatorBarcodeEdit')){
			$barcodeDrop = $ProductType->OrderCreatorBarcodeEdit($this);
		}
		return $barcodeDrop;
	}

	/**
	 * @param        $k
	 * @param string $v
	 */
	public function setInfo($k, $v = '')
	{
		if (is_array($k)){
			$this->extraInfo = $k;
		}
		else {
			$this->extraInfo[$k] = $v;
		}
	}

	/**
	 * @param array $newInfo
	 */
	public function updateInfo(array $newInfo)
	{
		$newProductInfo = $this->extraInfo;
		foreach($newInfo as $k => $v){
			$newProductInfo[$k] = $v;
		}
		$this->extraInfo = $newProductInfo;
		//$this->purchaseTypeClass->processUpdateCart(&$this->extraInfo);
	}

	public function onUpdateOrderProduct()
	{
		$ProductType = $this->getProductTypeClass();
		if (method_exists($ProductType, 'onUpdateOrderProduct')){
			$ProductType->onUpdateOrderProduct($this);
		}
	}

	/**
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $SaleProduct
	 */
	public function onSaveProgress(&$SaleProduct)
	{
		$ProductType = $this->getProductTypeClass();

		/**
		 * This is only for core fields, all possible module/extension fields should
		 * either be in their own table or in the provided product_json array
		 */
		$SaleProduct->product_id = $this->getProductsId();
		$SaleProduct->products_model = $this->getModel();
		$SaleProduct->products_name = $this->getName();
		$SaleProduct->products_price = $this->getPrice();
		$SaleProduct->products_tax = $this->getTaxRate();
		$SaleProduct->products_tax_class_id = $this->getTaxClassId();
		$SaleProduct->products_quantity = $this->getQuantity();
		$SaleProduct->products_weight = $this->getWeight();
		$SaleProduct->products_type = $ProductType->getCode();

		if (method_exists($ProductType, 'onSaveProgress')){
			$ProductType->onSaveProgress($SaleProduct);
		}

		$SaleProduct->product_json = $this->prepareSave();
	}

	/**
	 * @param array $ProductInfo
	 */
	public function loadSessionData(array $ProductInfo)
	{
		$this->id = $ProductInfo['id'];
		$this->product_id = $ProductInfo['product_id'];
		$this->products_model = $ProductInfo['products_model'];
		$this->products_name = $ProductInfo['products_name'];
		$this->products_price = $ProductInfo['products_price'];
		$this->products_tax = $ProductInfo['products_tax'];
		$this->products_tax_class_id = $ProductInfo['products_tax_class_id'];
		$this->products_quantity = $ProductInfo['products_quantity'];
		$this->products_weight = $ProductInfo['products_weight'];
		$this->products_type = $ProductInfo['products_type'];
		$this->extraInfo = $ProductInfo['extra_info'];

		$this->loadProductType();
		if (isset($ProductInfo['ProductTypeJson']) && is_array($ProductInfo['ProductTypeJson'])){
			if (method_exists($this->ProductTypeClass, 'loadSessionData')){
				$this->ProductTypeClass->loadSessionData($ProductInfo['ProductTypeJson']);
			}
		}
	}
}

?>