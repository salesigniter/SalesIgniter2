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
	 * @var PurchaseTypeBase
	 */
	protected $PurchaseTypeClass;

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
			$isLoaded = PurchaseTypeModules::loadModule(
				$PurchaseType,
				sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/PurchaseTypeModules/' . $PurchaseType . '/'
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

	public function onUpdateOrderProduct(OrderCreatorProduct &$OrderedProduct)
	{
		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'onUpdateOrderProduct')){
			$PurchaseType->onUpdateOrderProduct($OrderedProduct);
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 * @return array
	 */
	public function getExcludedPurchaseTypes(OrderCreatorProduct $OrderedProduct)
	{
		global $Editor;
		$excludedTypes = array();
		foreach($Editor->ProductManager->getContents() as $Product){
			if ($OrderedProduct->getProductsId() == $Product->getProductsId() && $OrderedProduct->getId() != $Product->getId()){
				$ProductType = $Product->getProductTypeClass();
				if (isset($ProductType->cartPurchaseType) && $ProductType->cartPurchaseType == 'reservation'){
					$excludedTypes[] = 'reservation';
				}
			}
		}
		return $excludedTypes;
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 * @param array               $SelectedBarcodes
	 * @return string
	 */
	public function OrderCreatorBarcodeEdit(OrderCreatorProduct $OrderedProduct, &$SelectedBarcodes = array())
	{
		$return = '';
		$PurchaseType = $OrderedProduct
			->getProductTypeClass()
			->getPurchaseType($OrderedProduct->getInfo('purchase_type'));
		if ($PurchaseType && $PurchaseType->getTrackMethod() == 'barcode'){
			$barcodeInput = htmlBase::newElement('selectbox')
				->css('width', '75%')
				->addClass('ui-widget-content barcode')
				->setName('product[' . $OrderedProduct->getId() . '][barcode_id][]');
			$barcodeInput->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));

			EventManager::attachEvent('ProductInventoryBarcodeGetInventoryItemsQueryBeforeExecute', function ($invData, &$Qcheck)
			{
				global $appExtension, $Editor;
				$MultiStore = $appExtension->getExtension('multiStore');
				if ($MultiStore && $MultiStore->isEnabled() === true){
					$Qcheck->andWhere('ib2s.inventory_store_id = ?', $Editor->InfoManager->getInfo('store_id'));
				}
			});

			foreach($PurchaseType->getInventoryItems(true) as $k => $bInfo){
				$barcodeInput->addOption($bInfo['id'], $bInfo['barcode'] . ' - ' . $bInfo['status']);
			}

			for($i = 0; $i < $OrderedProduct->getQuantity(); $i++){
				if (isset($SelectedBarcodes[$i])){
					$barcodeInput->selectOptionByValue($SelectedBarcodes[$i]['barcode_id']);
					unset($SelectedBarcodes[$i]);
				}
				else {
					$barcodeInput->selectOptionByValue('');
				}

				$return .= $barcodeInput->draw() . '<br>';
			}
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 * @param bool                $allowEdit
	 * @return string
	 */
	public function OrderCreatorAfterProductName(OrderCreatorProduct $OrderedProduct, $allowEdit = true)
	{
		$return = '';

		//It should show reservation so the product can have it's purchase type selected as reservation
		//On editing an order
		//$excludedPurchaseTypes = $this->getExcludedPurchaseTypes($OrderedProduct);
		$excludedPurchaseTypes = array();

		$purchaseTypeInput = htmlBase::newElement('selectbox')
			->addClass('ui-widget-content purchaseType')
			->setName('product[' . $OrderedProduct->getId() . '][purchase_type]')
			->selectOptionByValue($this->PurchaseTypeClass->getCode())
			->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));

		foreach(PurchaseTypeModules::getModules() as $k => $pType){
			if (!in_array($k, $excludedPurchaseTypes)){
				$purchaseTypeInput->addOption($pType->getCode(), $pType->getTitle());
			}
		}

		$return = '<br><nobr><small>&nbsp;<i> - Purchase Type: ' . $purchaseTypeInput->draw() . '</i></small></nobr>';

		if (method_exists($this->PurchaseTypeClass, 'OrderCreatorAfterProductName')){
			$return .= $this->PurchaseTypeClass->OrderCreatorAfterProductName($OrderedProduct, $allowEdit);
		}
		return $return;
	}

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorUpdateProductInfo(array &$pInfo)
	{
		if (isset($_GET['purchase_type']) && !empty($_GET['purchase_type'])){
			$this->setInfo('PurchaseType', $_GET['purchase_type']);
			$this->loadPurchaseType();

			$PurchaseType = $this->getPurchaseType();

			$this->setInfo('products_price', $PurchaseType->getPrice());
			$this->setInfo('final_price', $PurchaseType->getPrice());

			$PurchaseType->processAddToOrder(&$pInfo);
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

			$OrderProduct->updateInfo(array(
				'products_price' => $PurchaseType->getPrice(),
				'final_price'    => $PurchaseType->getPrice(),
				'products_tax'   => $PurchaseType->getPrice() * $PurchaseType->getTaxRate()
			));

			if (method_exists($PurchaseType, 'onAddToContents')){
				$PurchaseType->onAddToContents($OrderProduct);
			}
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function OrderCreatorAllowProductUpdate(OrderCreatorProduct $OrderProduct)
	{
		$return = true;
		if ($this->hasInfo('PurchaseType')){
			$PurchaseType = $this->getPurchaseType();
			if (method_exists($PurchaseType, 'OrderCreatorAllowProductUpdate')){
				$return = $PurchaseType->OrderCreatorAllowProductUpdate($OrderProduct);
			}
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $Product
	 */
	public function OrderCreatorProductManagerUpdateFromPost(OrderCreatorProduct &$Product)
	{
		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'OrderCreatorProductManagerUpdateFromPost')){
			$PurchaseType->OrderCreatorProductManagerUpdateFromPost($Product);
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
}