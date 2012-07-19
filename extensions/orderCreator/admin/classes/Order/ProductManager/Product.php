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
	 * @var ProductTypeBase
	 */
	protected $ProductTypeClass;

	/**
	 *
	 */
	public function loadProductType()
	{
		$ProductType = $this->pInfo['products_type'];
		ProductTypeModules::$classPrefix = 'OrderCreatorProductType';
		$isLoaded = ProductTypeModules::loadModule(
			$ProductType,
			sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/ProductTypeModules/' . $ProductType . '/'
		);
		if ($isLoaded === true){
			$this->ProductTypeClass = ProductTypeModules::getModule($ProductType);
			if (is_object($this->ProductTypeClass) === false){
				echo '<pre>';
				debug_print_backtrace();
				echo '</pre>';
				die('Error loading product type: ' . $ProductType);
			}
			$this->ProductTypeClass->setProductId($this->pInfo['products_id']);
		}
		else {
			echo '<pre>';
			debug_print_backtrace();
			echo '</pre>';
			die('Error loading product type: ' . $ProductType);
		}
	}

	/**
	 * @param array $ProductInfo
	 */
	public function loadSessionData(array $ProductInfo)
	{
		//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($ProductInfo);
		$this->id = $ProductInfo['id'];
		$this->pInfo = $ProductInfo['pInfo'];

		$this->loadProductBaseInfo($this->pInfo['products_id']);
		$this->loadProductType();
		if (method_exists($this->ProductTypeClass, 'loadSessionData')){
			$this->ProductTypeClass->loadSessionData($ProductInfo['ProductTypeJson']);
		}
	}

	/**
	 * @return ProductTypeBase|OrderCreatorProductTypeStandard|OrderCreatorProductTypePackage
	 */
	public function &getProductTypeClass()
	{
		return $this->ProductTypeClass;
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

		$taxAddress = null;
		if (is_object($Editor->AddressManager)){
			$taxAddress = $Editor->AddressManager->getAddress('billing');
		}
		$this->setTaxRate(tep_get_tax_rate(
			$this->getTaxClassId(),
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
	public function OrderCreatorUpdateProductInfo()
	{
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
		$this->pInfo['products_tax'] = (float)$val;
	}

	/**
	 * @param int $val
	 */
	public function setQuantity($val)
	{
		$this->pInfo['products_quantity'] = (int)$val;

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
		$this->pInfo['products_price'] = (float)$val;
		$this->pInfo['final_price'] = (float)$val;
	}

	/**
	 * @param array $val
	 */
	public function setBarcodes(array $val)
	{
		$this->pInfo['Barcodes'] = $val;
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
			$this->pInfo = $k;
		}
		else {
			$this->pInfo[$k] = $v;
		}
	}

	/**
	 * @param array $newInfo
	 */
	public function updateInfo(array $newInfo)
	{
		$newProductInfo = $this->pInfo;
		foreach($newInfo as $k => $v){
			$newProductInfo[$k] = $v;
		}
		$this->pInfo = $newProductInfo;
		//$this->purchaseTypeClass->processUpdateCart(&$this->pInfo);
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
		if (method_exists($ProductType, 'onSaveProgress')){
			$ProductType->onSaveProgress($SaleProduct);
		}

		$SaleProduct->product_json = $this->prepareSave();
	}
}

?>