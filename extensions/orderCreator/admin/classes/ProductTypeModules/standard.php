<?php
/**
 * Standard product type class for the order creator product manager class
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderCreatorProductTypeStandard extends ProductTypeStandard
{

	/**
	 * @param bool $PurchaseType
	 * @param bool $ignoreStatus
	 * @return null
	 */
	public function loadPurchaseType($PurchaseType = false, $ignoreStatus = false) {
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			if ($ignoreStatus === false){
				return null;
			}
		}

		if (!isset($this->purchaseTypes[$PurchaseType])){
			$className = 'OrderCreatorPurchaseType' . ucfirst($PurchaseType);
			if (class_exists($className) === false){
				$fileName = sysConfig::getDirFsCatalog() . 'extensions/orderCreator/admin/classes/PurchaseTypeModules/' . $PurchaseType . '.php';
				if (file_exists($fileName)){
					require($fileName);
				}
			}

			$this->purchaseTypes[$PurchaseType] = new $className;
			if ($this->purchaseTypes[$PurchaseType] === false){
				echo '<pre>';
				debug_print_backtrace();
				echo '</pre>';
				die('Error loading purchase type: ' . $PurchaseType);
			}
			$this->purchaseTypes[$PurchaseType]->loadData($this->getProductId());
			$this->purchaseTypes[$PurchaseType]->loadInventoryData($this->getProductId());
		}
	}

	/**
	 * @param bool $PurchaseType
	 * @param bool $ignoreStatus
	 * @return null
	 */
	public function &getPurchaseType($PurchaseType = false, $ignoreStatus = false) {
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			if ($ignoreStatus === false){
				return null;
			}
		}

		if (empty($this->purchaseTypes) || array_key_exists($PurchaseType, $this->purchaseTypes) === false){
			$this->loadPurchaseType($PurchaseType, $ignoreStatus);
		}
		return $this->purchaseTypes[$PurchaseType];
	}

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorProductOnInit(array $pInfo) {
		$this->setPurchaseType($pInfo['purchase_type']);
	}

	public function onUpdateOrderProduct(OrderCreatorProduct &$OrderedProduct){
		$PurchaseType = $this->getPurchaseType();
		if (method_exists($PurchaseType, 'onUpdateOrderProduct')){
			$PurchaseType->onUpdateOrderProduct($OrderedProduct);
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 * @return array
	 */
	public function getExcludedPurchaseTypes(OrderCreatorProduct $OrderedProduct) {
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
	 * @param array $SelectedBarcodes
	 * @return string
	 */
	public function OrderCreatorBarcodeEdit(OrderCreatorProduct $OrderedProduct, &$SelectedBarcodes = array()) {
		$return = '';
		$PurchaseType = $OrderedProduct->getProductTypeClass()
			->getPurchaseType($OrderedProduct->getInfo('purchase_type'));
		if ($PurchaseType && $PurchaseType->getTrackMethod() == 'barcode'){
			$barcodeInput = htmlBase::newElement('selectbox')
				->css('width', '75%')
				->addClass('ui-widget-content barcode')
				->setName('product[' . $OrderedProduct->getId() . '][barcode_id][]');
			$barcodeInput->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));

			EventManager::attachEvent('ProductInventoryBarcodeGetInventoryItemsQueryBeforeExecute', function ($invData, &$Qcheck) {
				global $appExtension, $Editor;
				$MultiStore = $appExtension->getExtension('multiStore');
				if ($MultiStore && $MultiStore->isEnabled() === true){
					$Qcheck->andWhere('ib2s.inventory_store_id = ?', $Editor->getData('store_id'));
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
	 * @param bool $allowEdit
	 * @return string
	 */
	public function OrderCreatorAfterProductName(OrderCreatorProduct $OrderedProduct, $allowEdit = true) {
		$return = '';
		$selectedPurchaseType = '';
		if ($OrderedProduct->hasInfo('purchase_type')){
			$PurchaseTypeClass = $OrderedProduct
				->getProductTypeClass()
				->getPurchaseType($OrderedProduct->getInfo('purchase_type'));
			if ($OrderedProduct->getInfo('purchase_type') == 'membership'){
				$allowEdit = false;
				$PurchaseTypeHtml = $PurchaseTypeClass->getTitle();
			}else{
				$PurchaseTypeHtml = $PurchaseTypeClass->getTitle();
				$selectedPurchaseType = $PurchaseTypeClass->getCode();
			}
		}

		if ($allowEdit === true){
			$PurchaseTypes = $this->getPurchaseTypes(true);
			//It should show reservation so the product can have it's purchase type selected as reservation
			//On editing an order
			//$excludedPurchaseTypes = $this->getExcludedPurchaseTypes($OrderedProduct);
			$excludedPurchaseTypes = array();

			$purchaseTypeInput = htmlBase::newElement('selectbox')
				->addClass('ui-widget-content purchaseType')
				->setName('product[' . $OrderedProduct->getId() . '][purchase_type]');
			$purchaseTypeInput->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));
			foreach($PurchaseTypes as $k => $pType){
				if (!in_array($k, $excludedPurchaseTypes)){
					$purchaseTypeInput->addOption($pType->getCode(), $pType->getTitle());
				}
			}
			$purchaseTypeInput->selectOptionByValue($selectedPurchaseType);
			$PurchaseTypeHtml = $purchaseTypeInput->draw();
		}

		$return = '<br><nobr><small>&nbsp;<i> - Purchase Type: ' . $PurchaseTypeHtml . '</i></small></nobr>';

		if (isset($PurchaseTypeClass) && is_object($PurchaseTypeClass)){
			if (method_exists($PurchaseTypeClass, 'OrderCreatorAfterProductName')){
				$return .= $PurchaseTypeClass->OrderCreatorAfterProductName($OrderedProduct, $allowEdit);
			}
		}
		return $return;
	}

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorUpdateProductInfo(array &$pInfo) {
		if (isset($_GET['purchase_type']) && !empty($_GET['purchase_type'])){
			$pInfo['purchase_type'] = $_GET['purchase_type'];

			$this->setPurchaseType($pInfo['purchase_type']);
			$PurchaseType = $this->getPurchaseType();

			$pInfo['products_price'] = $PurchaseType->getPrice();
			$pInfo['final_price'] = $PurchaseType->getPrice();

			$PurchaseType->processAddToOrder(&$pInfo);
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function OrderCreatorAllowAddToContents(OrderCreatorProduct $OrderProduct) {
		$return = true;
		if ($OrderProduct->hasInfo('purchase_type')){
			$PurchaseType = $this->getPurchaseType($OrderProduct->getInfo('purchase_type'));
			if (method_exists($PurchaseType, 'OrderCreatorAllowAddToContents')){
				$return = $PurchaseType->OrderCreatorAllowAddToContents($OrderProduct);
			}
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 */
	public function OrderCreatorOnAddToContents(OrderCreatorProduct &$OrderProduct) {
		if ($OrderProduct->hasInfo('purchase_type')){
			$PurchaseType = $this->getPurchaseType($OrderProduct->getInfo('purchase_type'));

			$OrderProduct->updateInfo(array(
				'purchase_type'  => $PurchaseType->getCode(),
				'products_price' => $PurchaseType->getPrice(),
				'final_price'	=> $PurchaseType->getPrice(),
				'products_tax'   => $PurchaseType->getPrice() * $PurchaseType->getTaxRate()
			));

			if (method_exists($PurchaseType, 'OrderCreatorOnAddToContents')){
				$PurchaseType->OrderCreatorOnAddToContents($OrderProduct);
			}
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function OrderCreatorAllowProductUpdate(OrderCreatorProduct $OrderProduct) {
		$return = true;
		if (isset($_GET['purchase_type']) && !empty($_GET['purchase_type'])){
			$PurchaseType = $this->getPurchaseType($_GET['purchase_type']);
			if (method_exists($PurchaseType, 'OrderCreatorAllowProductUpdate')){
				$return = $PurchaseType->OrderCreatorAllowProductUpdate($OrderProduct);
			}
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @param OrdersProducts $OrderedProduct
	 */
	public function addToOrdersProductCollection(OrderCreatorProduct $OrderProduct, OrdersProducts &$OrderedProduct) {
		$OrderedProduct->purchase_type = $OrderProduct->getInfo('purchase_type');
		if ($OrderProduct->hasInfo('Barcodes')){
			foreach($OrderProduct->getInfo('Barcodes') as $bInfo){
				$NewBarcode = new OrdersProductsBarcodes();
				$NewBarcode->barcode_id = $bInfo['barcode_id'];
				$NewBarcode->ProductsInventoryBarcodes->status = 'R';

				$OrderedProduct->Barcodes->add($NewBarcode);
			}
		}
		elseif ($OrderProduct->hasInfo('quantity_id')) {
			$OrderedProduct->quantity_id = $OrderProduct->getInfo('quantity_id');
		}

		$PurchaseType =& $this->getPurchaseType($OrderedProduct->purchase_type);
		if (method_exists($PurchaseType, 'addToOrdersProductCollection')){
			$PurchaseType->addToOrdersProductCollection($OrderProduct, $OrderedProduct);
		}
	}

	/**
	 * @param OrderCreatorProduct $Product
	 */
	public function OrderCreatorProductManagerUpdateFromPost(OrderCreatorProduct &$Product){
		$PurchaseType = $this->getPurchaseType($Product->getInfo('purchase_type'));
		if (method_exists($PurchaseType, 'OrderCreatorProductManagerUpdateFromPost')){
			$PurchaseType->OrderCreatorProductManagerUpdateFromPost($Product);
		}
	}
}