<?php
if (class_exists('OrderProductTypeStandard') === false){
	require(sysConfig::getDirFsCatalog() . 'includes/classes/Order/ProductManager/ProductTypeModules/standard/module.php');
}

/**
 * Standard product type class for the order creator product manager class
 *
 * @package   OrderCreator
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderCreatorProductTypeStandard extends OrderProductTypeStandard
{

	/**
	 * @param bool $PurchaseType
	 * @param bool $ignoreStatus
	 * @return null
	 */
	public function loadPurchaseType($PurchaseType = false, $ignoreStatus = false)
	{
		$PurchaseType = $this->getInfo('PurchaseType');

		if (is_object($this->PurchaseTypeClass) === false){
			PurchaseTypeModules::$classPrefix = 'OrderCreatorPurchaseType';
			$ModuleDir = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/Order/ProductManager/PurchaseTypeModules/' . $PurchaseType . '/';

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
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function allowAddToContents(OrderCreatorProduct &$OrderProduct)
	{
		$return = true;
		if (isset($_GET['purchase_type']) && !empty($_GET['purchase_type'])){
			$this->setInfo('PurchaseType', $_GET['purchase_type']);
			$this->loadPurchaseType();

			$PurchaseType = $this->getPurchaseTypeClass();
			if (method_exists($PurchaseType, 'allowAddToContents')){
				$return = $PurchaseType->allowAddToContents($OrderProduct);
			}
		}else{
			$return = false;
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 */
	public function onAddToContents(OrderCreatorProduct &$OrderProduct)
	{
		if ($this->hasInfo('PurchaseType')){
			$PurchaseType = $this->getPurchaseTypeClass();
			$OrderProduct->setPrice($PurchaseType->getPrice());
			$OrderProduct->setTaxClassId($PurchaseType->getTaxClassId());

			if (method_exists($PurchaseType, 'onAddToContents')){
				$PurchaseType->onAddToContents($OrderProduct);
			}
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
		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'onSaveProgress')){
			$PurchaseType->onSaveProgress($SaleProduct);
		}
	}

	/**
	 * @param array $ProductTypeJson
	 */
	public function loadSessionData(array $ProductTypeJson)
	{
		$this->setInfo($ProductTypeJson);
		if (isset($ProductTypeJson['PurchaseType'])){
			$this->loadPurchaseType();

			if (isset($ProductTypeJson['PurchaseTypeJson'])){
				$PurchaseType = $this->getPurchaseTypeClass();
				if (method_exists($PurchaseType, 'loadSessionData')){
					$PurchaseType->loadSessionData($ProductTypeJson['PurchaseTypeJson']);
				}
			}
		}
	}
}