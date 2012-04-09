<?php
/**
 * Standard product type class for the order creator product manager class
 *
 * @package OrderCreator
 * @author Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderCreatorProductTypePackage extends ProductTypePackage
{

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorProductOnInit(array $pInfo) {
		if (isset($pInfo['PackagedProducts'])){
			foreach($pInfo['PackagedProducts'] as $PackageProduct){
				$PackageProduct->init();
			}
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 */
	public function onUpdateOrderProduct(OrderCreatorProduct &$OrderedProduct){
		foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();
			if (method_exists($ProductType, 'onUpdateOrderProduct')){
				$ProductType->onUpdateOrderProduct($PackageOrderProduct);
			}
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 * @param array $SelectedBarcodes
	 * @return string
	 */
	public function OrderCreatorBarcodeEdit(OrderCreatorProduct $OrderedProduct, &$SelectedBarcodes = array()) {
		$return = '';
		foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();
			$return .= '<span style="font-size:.8em;"><b>' . $PackageOrderProduct->getName() . '</b><br><span style="font-style:italic;">';
			if (method_exists($ProductType, 'OrderCreatorBarcodeEdit')){
				$SelectedBarcodes = $PackageOrderProduct->getBarcodes();
				for($i=0; $i<$OrderedProduct->getQuantity(); $i++){
					$return .= $ProductType->OrderCreatorBarcodeEdit($PackageOrderProduct, &$SelectedBarcodes);
				}
			}elseif (method_exists($ProductType, 'displayOrderedBarcodes')){
				$return .= $ProductType->displayOrderedBarcodes();
			}
			$return .= '</span></span><br>';
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
		if ($OrderedProduct->hasInfo('PackagedProducts')){
			$hasReservation = false;
			$reservationIds = array();
			foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
				if ($PackageOrderProduct->hasInfo('purchase_type') && $PackageOrderProduct->getInfo('purchase_type') == 'reservation'){
					$hasReservation = true;
					$reservationIds[] = $PackageOrderProduct->getProductsId();
				}

				$pInfoHtml = htmlBase::newElement('span')
					->css(array(
					'font-size' => '.8em',
					'font-style' => 'italic'
				))
					->html(' - ' . $PackageOrderProduct->getQuantity() . 'x ' . $PackageOrderProduct->getName());

				$return .= '<br>' . $pInfoHtml->draw();

				$ProductTypeClass = $PackageOrderProduct->getProductTypeClass();
				if (method_exists($ProductTypeClass, 'OrderCreatorAfterProductName')){
					$return .= $ProductTypeClass->OrderCreatorAfterProductName($PackageOrderProduct, false) . '<br>';
				}
			}

			if ($hasReservation === true){
				$return = '&nbsp;' . htmlBase::newElement('button')->attr('data-product_ids', implode(',', $reservationIds))->addClass('reservationDates')->setText('Select Reservation Dates')->draw() . $return;
			}
		}
		return $return;
	}

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorUpdateProductInfo(array &$pInfo) {
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function OrderCreatorAllowAddToContents(OrderCreatorProduct $OrderProduct) {
		$return = true;
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 */
	public function OrderCreatorOnAddToContents(OrderCreatorProduct &$OrderProduct) {
		$PackageProducts = array();
		$MainPrice = 0;
		foreach($this->getProductsRaw() as $PackageInfo){
			$Product = $PackageInfo['productClass'];
			$PackageData = $PackageInfo['packageData'];

			$PackageOrderProduct = new OrderCreatorProduct();
			$PackageOrderProduct->regenerateId();
			$PackageOrderProduct->setProductId($Product->getId());
			$PackageOrderProduct->setQuantity($PackageData->quantity);
			if (isset($PackageData->purchase_type)){
				$PackageOrderProduct->updateInfo(array(
					'purchase_type' => $PackageData->purchase_type,
					'PackageData' => $PackageData
				));
			}

			$ProductType = $PackageOrderProduct->getProductTypeClass();
			if (method_exists($ProductType, 'OrderCreatorOnAddToContents')){
				$ProductType->OrderCreatorOnAddToContents($PackageOrderProduct);
			}

			if (isset($PackageData->price)){
				$PackageOrderProduct->setPrice($PackageData->price);
			}

			$MainPrice += $PackageOrderProduct->getPrice();
			$PackageProducts[] = $PackageOrderProduct;
		}

		$OrderProduct->setPrice($MainPrice);
		$OrderProduct->updateInfo(array(
			'PackagedProducts' => $PackageProducts
		));
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function OrderCreatorAllowProductUpdate(OrderCreatorProduct $OrderProduct) {
		$return = true;
		foreach($OrderProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();
			if (method_exists($ProductType, 'OrderCreatorAllowProductUpdate')){
				$return = $ProductType->OrderCreatorAllowProductUpdate($PackageOrderProduct);
			}
		}
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @param OrdersProducts $OrderedProduct
	 */
	public function addToOrdersProductCollection(OrderCreatorProduct $OrderProduct, OrdersProducts &$OrderedProduct) {
		foreach($OrderProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();

			$PackageOrderedProduct = new OrdersProducts();
			//$PackageOrderedProduct->orders_id = $orderID;
			$PackageOrderedProduct->products_id = (int)$PackageOrderProduct->getProductsId();
			$PackageOrderedProduct->products_model = $PackageOrderProduct->getModel();
			$PackageOrderedProduct->products_name = $PackageOrderProduct->getName();
			$PackageOrderedProduct->products_price = $PackageOrderProduct->getPrice();
			$PackageOrderedProduct->final_price = $PackageOrderProduct->getFinalPrice();
			$PackageOrderedProduct->products_tax = $PackageOrderProduct->getTaxRate();
			$PackageOrderedProduct->products_quantity = $PackageOrderProduct->getQuantity();

			if (method_exists($ProductType, 'addToOrdersProductCollection')){
				$ProductType->addToOrdersProductCollection($PackageOrderProduct, $PackageOrderedProduct);
			}

			EventManager::notify('InsertOrderedProductBeforeSave', $PackageOrderedProduct, $PackageOrderProduct);

			$OrderedProduct->Packaged->add($PackageOrderedProduct);

			EventManager::notify('InsertOrderedProductAfterSave', $PackageOrderedProduct, $PackageOrderProduct);
		}
	}

	/**
	 * @param OrderCreatorProduct $Product
	 */
	public function OrderCreatorProductManagerUpdateFromPost(OrderCreatorProduct &$Product){
		foreach($Product->getInfo('PackagedProducts') as $PackageProduct){
			if (isset($_POST['product'][$PackageProduct->getId()]['barcode_id'])){
				if ($PackageProduct->hasInfo('Barcodes') && $PackageProduct->getInfo('Barcodes') != ''){
					foreach($PackageProduct->getInfo('Barcodes') as $Barcode){
						Doctrine_Query::create()
							->update('ProductsInventoryBarcodes')
							->set('status', '?', 'A')
							->where('barcode_id = ?', $Barcode['barcode_id'])
							->execute();
					}
				}

				$Barcodes = array();
				foreach($_POST['product'][$PackageProduct->getId()]['barcode_id'] as $bID){
					$Barcodes[] = array(
						'barcode_id' => $bID
					);
				}
				$PackageProduct->setBarcodes($Barcodes);
			}
		}
	}
}
