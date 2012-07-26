<?php
if (class_exists('ProductTypeStandard') === false){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/productTypeModules/standard/module.php');
}

/**
 * Standard product type for the order class
 *
 * @package   Order\ProductManager
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @since     2.0
 * @copyright 2012 I.T. Web Experts
 * @license   http://itwebexperts.com/license/ses-license.php
 */

class OrderProductTypeStandard extends ProductTypeStandard
{

	/**
	 * @var PurchaseTypeBase
	 */
	protected $PurchaseTypeClass;

	/**
	 * @var array
	 */
	protected $pInfo = array();

	/**
	 * @param $k
	 * @return mixed
	 */
	public function getInfo($k = null)
	{
		return ($k === null ? $this->pInfo : $this->pInfo[$k]);
	}

	/**
	 * @param      $k
	 * @param null $v
	 */
	public function setInfo($k, $v = null)
	{
		if ($v === null){
			$this->pInfo = $k;
		}
		else {
			$this->pInfo[$k] = $v;
		}
	}

	/**
	 * @param $k
	 * @return bool
	 */
	public function hasInfo($k)
	{
		return isset($this->pInfo[$k]);
	}

	/**
	 * @param bool $PurchaseType
	 * @param bool $ignoreStatus
	 * @return null
	 */
	public function loadPurchaseType($PurchaseType = false, $ignoreStatus = false)
	{
		global $appExtension;
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			if ($ignoreStatus === false){
				return null;
			}
		}

		if (is_object($this->PurchaseTypeClass) === false){
			PurchaseTypeModules::$classPrefix = 'OrderPurchaseType';
			$ModuleDir = sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/PurchaseTypeModules/' . $PurchaseType . '/';

			foreach($appExtension->getExtensions() as $ext){
				if (is_dir($ext->getExtensionDir() . 'catalog/includes/classes/Order/ProductManager/PurchaseTypeModules/' . $PurchaseType . '/')){
					$ModuleDir = $ext->getExtensionDir() . 'catalog/includes/classes/Order/ProductManager/PurchaseTypeModules/' . $PurchaseType . '/';
				}
			}
			$isLoaded = PurchaseTypeModules::loadModule($PurchaseType, $ModuleDir);

			if ($isLoaded === true){
				$this->PurchaseTypeClass = PurchaseTypeModules::getModule($PurchaseType);
				if ($this->PurchaseTypeClass === false){
					echo '<pre>';
					debug_print_backtrace();
					echo '</pre>';
					die('Error loading purchase type: ' . $PurchaseType);
				}
				$this->PurchaseTypeClass->loadData($this->getProductId());
				$this->PurchaseTypeClass->loadInventoryData($this->getProductId());
			}
		}
	}

	/**
	 * @param bool $showExtraInfo
	 * @return string
	 */
	public function showProductInfo($showExtraInfo = true)
	{
		//echo __FILE__ . '::' . __LINE__ . '<pre>';print_r($this);
		$PurchaseTypeCls = $this->getPurchaseTypeClass();
		if ($showExtraInfo === true){
			$purchaseTypeHtml = htmlBase::newElement('span')
				->css(
				array(
					'font-size'  => '.8em',
					'font-style' => 'italic'
				))
				->html(' - Purchase Type: ' . $PurchaseTypeCls->getTitle());

			$html = $purchaseTypeHtml->draw();
		}
		else {
			$html = '';
		}

		if (method_exists($PurchaseTypeCls, 'showProductInfo')){
			$html .= $PurchaseTypeCls->showProductInfo($showExtraInfo);
		}

		return $html;
	}

	/**
	 * @param null $Qty
	 * @return bool
	 */
	public function hasEnoughInventory($Qty = null)
	{
		//echo __FILE__ . '::' . __LINE__ . '::CHECKING QTY::' . $Qty . "\n";
		$return = true;
		$PurchaseType = $this->getPurchaseTypeClass();
		if (method_exists($PurchaseType, 'hasEnoughInventory')){
			$return = $PurchaseType->hasEnoughInventory($Qty);
		}
		return $return;
	}

	/**
	 * @return array
	 */
	public function prepareSave()
	{
		$toEncode = $this->getInfo();

		$PurchaseType = $this->getPurchaseTypeClass();
		if (method_exists($PurchaseType, 'prepareSave')){
			$toEncode['PurchaseTypeJson'] = $PurchaseType->prepareSave();
		}
		return $toEncode;
	}

	/**
	 * Cannot typehint $SaleProduct due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param OrderProduct                                                            $OrderProduct
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $SaleProduct
	 * @param bool                                                                    $AssignInventory
	 */
	public function onSaveSale(OrderProduct $OrderProduct, &$SaleProduct, $AssignInventory = false)
	{
		$PurchaseType = $this->getPurchaseTypeClass();
		if (method_exists($PurchaseType, 'onSaveSale')){
			$PurchaseType->onSaveSale($OrderProduct, $SaleProduct, $AssignInventory);
		}
	}

	/**
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $Product
	 * @param array                                                                   $ProductTypeJson
	 */
	public function loadDatabaseData($Product, array $ProductTypeJson = null)
	{
		$this->setInfo($ProductTypeJson);
		if (isset($ProductTypeJson['PurchaseType'])){
			$this->loadPurchaseType();

			if (isset($ProductTypeJson['PurchaseTypeJson'])){
				$PurchaseType = $this->getPurchaseTypeClass();
				if (method_exists($PurchaseType, 'loadDatabaseData')){
					$PurchaseType->loadDatabaseData($Product, $ProductTypeJson['PurchaseTypeJson']);
				}
			}
		}
	}

	/**
	 * @param $orderedProductsString
	 */
	public function onGetEmailList(&$orderedProductsString)
	{
		$PurchaseType = $this->getPurchaseTypeClass();

		$orderedProductsString .= ' - Purchase Type: ' . $PurchaseType->getTitle() . "\n";

		if (method_exists($PurchaseType, 'onGetEmailList')){
			$PurchaseType->onGetEmailList(&$orderedProductsString);
		}
	}
}