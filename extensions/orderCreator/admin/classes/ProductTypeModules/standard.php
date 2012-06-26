<?php
/**
 * Standard product type class for the order creator product manager class
 *
 * @package   OrderCreator
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderCreatorProductTypeStandard extends ProductTypeStandard
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
		$PurchaseType = $this->getPurchaseTypeCode($PurchaseType);
		if ($PurchaseType === false || $this->purchaseTypeEnabled($PurchaseType) === false){
			if ($ignoreStatus === false){
				return null;
			}
		}

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

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorProductOnInit(array $pInfo)
	{
		$this->setPurchaseType($pInfo['purchase_type']);
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
	public function OrderCreatorAllowAddToContents(OrderCreatorProduct &$OrderProduct)
	{
		if (isset($_GET['purchase_type']) && !empty($_GET['purchase_type'])){
			$OrderProduct->updateInfo(array(
				'purchase_type' => $_GET['purchase_type']
			));

			$this->setPurchaseType($OrderProduct->getInfo('purchase_type'));
		}

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
	public function OrderCreatorOnAddToContents(OrderCreatorProduct &$OrderProduct)
	{
		if ($OrderProduct->hasInfo('purchase_type')){
			$PurchaseType = $this->getPurchaseType($OrderProduct->getInfo('purchase_type'));

			$OrderProduct->updateInfo(array(
				'purchase_type'  => $PurchaseType->getCode(),
				'products_price' => $PurchaseType->getPrice(),
				'final_price'    => $PurchaseType->getPrice(),
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
	public function OrderCreatorAllowProductUpdate(OrderCreatorProduct $OrderProduct)
	{
		$return = true;
		$PurchaseTypeName = $OrderProduct->getInfo('purchase_type');
		if (!empty($PurchaseTypeName)){
			$PurchaseType = $this->getPurchaseType($PurchaseTypeName);
			if (method_exists($PurchaseType, 'OrderCreatorAllowProductUpdate')){
				$return = $PurchaseType->OrderCreatorAllowProductUpdate($OrderProduct);
			}
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @param OrdersProducts      $OrderedProduct
	 */
	public function addToOrdersProductCollection(OrderCreatorProduct $OrderProduct, OrdersProducts &$OrderedProduct)
	{
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
	public function OrderCreatorProductManagerUpdateFromPost(OrderCreatorProduct &$Product)
	{
		$PurchaseType = $this->getPurchaseType($Product->getInfo('purchase_type'));
		if (method_exists($PurchaseType, 'OrderCreatorProductManagerUpdateFromPost')){
			$PurchaseType->OrderCreatorProductManagerUpdateFromPost($Product);
		}
	}
}