<?php
/**
 * Standard product type class for the order creator product manager class
 *
 * @package   OrderCreator
 * @author    Stephen Walker <stephen@itwebexperts.com>
 * @copyright Copyright (c) 2011, I.T. Web Experts
 */

class OrderCreatorProductTypePackage extends ProductTypePackage
{

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorProductOnInit(array $pInfo)
	{
		if (isset($pInfo['PackagedProducts'])){
			foreach($pInfo['PackagedProducts'] as $PackageProduct){
				$PackageProduct->init();
			}
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 */
	public function onUpdateOrderProduct(OrderCreatorProduct &$OrderedProduct)
	{
		foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();
			if (method_exists($ProductType, 'onUpdateOrderProduct')){
				$ProductType->onUpdateOrderProduct($PackageOrderProduct);
			}
		}
	}

	/**
	 * @param OrderCreatorProduct $OrderedProduct
	 * @param array               $SelectedBarcodes
	 * @return string
	 */
	public function OrderCreatorBarcodeEdit(OrderCreatorProduct $OrderedProduct, &$SelectedBarcodes = array())
	{
		$return = '';
		foreach($OrderedProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
			$ProductType = $PackageOrderProduct->getProductTypeClass();
			$return .= '<span style="font-size:.8em;"><b>' . $PackageOrderProduct->getName() . '</b><br><span style="font-style:italic;">';
			if (method_exists($ProductType, 'OrderCreatorBarcodeEdit')){
				$SelectedBarcodes = $PackageOrderProduct->getBarcodes();
				for($i = 0; $i < $OrderedProduct->getQuantity(); $i++){
					$return .= $ProductType->OrderCreatorBarcodeEdit($PackageOrderProduct, &$SelectedBarcodes);
				}
			}
			elseif (method_exists($ProductType, 'displayOrderedBarcodes')) {
				$return .= $ProductType->displayOrderedBarcodes();
			}
			$return .= '</span></span><br>';
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
					'font-size'  => '.8em',
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
				$return = '&nbsp;' . htmlBase::newElement('button')
					->attr('data-product_ids', implode(',', $reservationIds))->addClass('reservationDates')
					->setText('Select Reservation Dates')->draw() . $return;
			}
		}
		return $return;
	}

	/**
	 * @param array $pInfo
	 */
	public function OrderCreatorUpdateProductInfo(array &$pInfo)
	{
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function OrderCreatorAllowAddToContents(OrderCreatorProduct $OrderProduct)
	{
		$return = true;
		return $return;
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 */
	public function OrderCreatorOnAddToContents(OrderCreatorProduct &$OrderProduct)
	{
		global $Editor, $appExtension;

		$PackageProducts = array();
		$MainPrice = 0;
		$ConfirmProducts = array();
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
					'PackageData'   => $PackageData
				));
			}

			$ProductType = $PackageOrderProduct->getProductTypeClass();
			if (method_exists($ProductType, 'OrderCreatorOnAddToContents')){
				$ProductType->OrderCreatorOnAddToContents($PackageOrderProduct);
			}

			if (isset($PackageData->price)){
				if ($PackageData->purchase_type == 'reservation'){
					$PurchaseType = $ProductType->getPurchaseType('reservation');
					$pprId = $PurchaseType->getPayPerRentalId();

					$ReservationInfo = $PackageOrderProduct->getInfo('reservationInfo');

					$PricingInfo = PurchaseType_reservation_utilities::getPricingPeriodInfo(
						$pprId,
						$ReservationInfo['start_date'],
						$ReservationInfo['end_date']
					);

					$Prices = array();
					foreach($PricingInfo as $PriceInfo){
						$TypeId = $PriceInfo['Type']['pay_per_rental_types_id'];

						$Prices[] = $PriceInfo;

						$NumberOf = $PriceInfo['number_of'];
						if (
							isset($PackageData->price) &&
							isset($PackageData->price->$pprId) &&
							isset($PackageData->price->$pprId->$TypeId) &&
							isset($PackageData->price->$pprId->$TypeId->$NumberOf)
						){
							$Prices[] = array_merge($PriceInfo, array(
								'price' => $PackageData->price->$pprId->$TypeId->$NumberOf
							));
						}

						if ($PurchaseType->hasDiscounts()){
							$Discounted = array();
							foreach($Prices as $Price){
								$Discounted[] = PurchaseType_reservation_utilities::discountPrice(
									$Price['price'],
									$PricingInfo,
									$PurchaseType->getDiscounts(),
									$ReservationInfo
								);
							}

							foreach($Discounted as $Price){
								$Prices[] = array_merge($PriceInfo, array(
									'price' => $Price
								));
							}
						}
					}

					$Lowest = PurchaseType_reservation_utilities::getLowestPrice(
						$Prices,
						$ReservationInfo['start_date'],
						$ReservationInfo['end_date']
					);
					$Price = $Lowest['price'];

					$NumberOfMinutes = $ReservationInfo['end_date']->diff($ReservationInfo['start_date'])->i;
					$PriceBasedOnMsg = ($NumberOfMinutes / $Lowest['Type']['minutes']) .
						'X' .
						$Lowest['number_of'] . ' ' . $Lowest['Type']['pay_per_rental_types_name'] .
						'@' .
						$Lowest['price'] .
						'/' .
						$Lowest['Type']['pay_per_rental_types_name'];
				}
				else {
					$Price = $PackageData->price;
				}
				$PackageOrderProduct->setPrice($Price);
			}

			$MainPrice += $PackageOrderProduct->getPrice();
			$PackageProducts[] = $PackageOrderProduct;

			if ($PackageOrderProduct->needsConfirmation() === true){
				$OrderProduct->needsConfirmation(true);
				$ConfirmProducts[] = $PackageOrderProduct->getName();
			}
		}

		if ($OrderProduct->needsConfirmation() === true){
			$OrderProduct->setConfirmationMessage('The Following Products In This Package Do Not Have Enough Inventory:' . '<br><br>' . implode('<br>', $ConfirmProducts));
		}

		$OrderProduct->setPrice($MainPrice);
		$OrderProduct->updateInfo(array(
			'PackagedProducts' => $PackageProducts
		));

		//$this->loadReservationPricing($OrderProduct->getInfo('PackagedProducts'));
	}

	/**
	 * @param OrderCreatorProduct $OrderProduct
	 * @return bool
	 */
	public function OrderCreatorAllowProductUpdate(OrderCreatorProduct $OrderProduct)
	{
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
	 * @param OrdersProducts      $OrderedProduct
	 */
	public function addToOrdersProductCollection(OrderCreatorProduct $OrderProduct, OrdersProducts &$OrderedProduct)
	{
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
	public function OrderCreatorProductManagerUpdateFromPost(OrderCreatorProduct &$Product)
	{
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

	public function jsonDecodeProduct(OrderProduct &$OrderProduct, $Product)
	{
		if ($Product->Packaged && $Product->Packaged->count() > 0){
			$Info = $OrderProduct->getInfo();
			foreach($Product->Packaged as $PackagedProduct){
				$PackageProduct = new OrderCreatorProduct();
				$PackageProduct->jsonDecodeProduct($PackagedProduct);
				$Info['PackagedProducts'][$k] = $PackageProduct;
			}
			$OrderProduct->setInfo($Info);
		}
	}

	public function jsonDecode(OrderProduct &$OrderProduct, $ProductTypeJson)
	{
		$Info = $OrderProduct->getInfo();
		/*
		 * @TODO: Figure out a way to hand this off to the reservation purchase type?!?!
		 */
		if (isset($ProductTypeJson['reservationInfo'])){
			$StartDate = SesDateTime::createFromFormat(DATE_TIMESTAMP, $ProductTypeJson['reservationInfo']['start_date']['date']);
			$StartDate->setTimezone(new DateTimeZone($ProductTypeJson['reservationInfo']['start_date']['timezone']));

			$EndDate = SesDateTime::createFromFormat(DATE_TIMESTAMP, $ProductTypeJson['reservationInfo']['end_date']['date']);
			$EndDate->setTimezone(new DateTimeZone($ProductTypeJson['reservationInfo']['end_date']['timezone']));

			$Info['reservationInfo']['start_date'] = $StartDate;
			$Info['reservationInfo']['end_date'] = $EndDate;
		}

		if (isset($ProductTypeJson['PackagedProducts'])){
			foreach($ProductTypeJson['PackagedProducts'] as $k => $PackagedJson){
				$PackageProduct = new OrderCreatorProduct();
				$PackageProduct->jsonDecode($PackagedJson);
				$Info['PackagedProducts'][$k] = $PackageProduct;
			}
		}
		$OrderProduct->setInfo($Info);
	}
}
