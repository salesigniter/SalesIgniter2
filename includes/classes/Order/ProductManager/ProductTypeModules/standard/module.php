<?php
if (class_exists('ProductTypeStandard') === false){
	require(sysConfig::getDirFsCatalog() . 'includes/modules/productTypeModules/standard/module.php');
}

/**
 * Standard product type for the order class
 *
 * @package   Order\ProductManager\Product\ProductTypeModules
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
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			if ($ignoreStatus === false){
				return null;
			}
		}

		if (is_object($this->PurchaseTypeClass) === false){
			PurchaseTypeModules::$classPrefix = 'OrderPurchaseType';
			$isLoaded = PurchaseTypeModules::loadModule(
				$PurchaseType,
				sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/PurchaseTypeModules/' . $PurchaseType . '/'
			);

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
	 * @return PurchaseTypeBase
	 */
	public function &getPurchaseTypeClass()
	{
		return $this->PurchaseTypeClass;
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
	 * @return array
	 */
	public function prepareJsonSave()
	{
		$toEncode = $this->getInfo();

		$PurchaseType = $this->getPurchaseTypeClass();
		if (method_exists($PurchaseType, 'prepareJsonSave')){
			$toEncode['PurchaseTypeJson'] = $PurchaseType->prepareJsonSave();
		}
		return $toEncode;
	}

	/**
	 * @param array $ProductTypeJson
	 */
	public function jsonDecode(array $ProductTypeJson)
	{
		$this->setInfo($ProductTypeJson);
//echo __FILE__ . '::' . __LINE__ . '<Br><pre>';print_r($ProductTypeJson);
		$this->loadPurchaseType();

		if (isset($ProductTypeJson['PurchaseTypeJson'])){
			$PurchaseType = $this->getPurchaseTypeClass();
			if (method_exists($PurchaseType, 'jsonDecode')){
				$PurchaseType->jsonDecode($ProductTypeJson['PurchaseTypeJson']);
			}
		}
	}

	/**
	 * Cannot typehint due to the possibility of packages extension being installed
	 * and its' products are from another table with the same columns
	 *
	 * @param AccountsReceivableSalesProducts|AccountsReceivableSalesProductsPackaged $Product
	 * @param array                                                                   $ProductTypeJson
	 */
	public function jsonDecodeProduct($Product, array $ProductTypeJson)
	{
		$this->setInfo($ProductTypeJson);
//echo __FILE__ . '::' . __LINE__ . '<Br><pre>';print_r($ProductTypeJson);
		$this->loadPurchaseType();

		if (isset($ProductTypeJson['PurchaseTypeJson'])){
			$PurchaseType = $this->getPurchaseTypeClass();
			if (method_exists($PurchaseType, 'jsonDecodeProduct')){
				$PurchaseType->jsonDecodeProduct($Product, $ProductTypeJson['PurchaseTypeJson']);
			}
		}
	}

	public function onGetEmailList(&$orderedProductsString)
	{
		$PurchaseType = $this->getPurchaseTypeClass();

		$orderedProductsString .= ' - Purchase Type: ' . $PurchaseType->getTitle() . "\n";

		if (method_exists($PurchaseType, 'onGetEmailList')){
			$PurchaseType->onGetEmailList(&$orderedProductsString);
		}
	}
}