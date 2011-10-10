<?php
/*
	Product Purchase Type: Rental

	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

/**
 * Rental Membership Stream Purchase Type
 * @package ProductPurchaseTypes
 */
class PurchaseType_Rental extends PurchaseTypeBase
{

	public function __construct($forceEnable = false) {
		$this->setTitle('Rental');
		$this->setDescription('Rentals Which Mimic A Retail Rental Store');

		$this->init(
			'rental',
			$forceEnable,
			sysConfig::getDirFsCatalog() . 'extensions/rentalProducts/purchaseTypeModules/rental/'
		);
	}

	public function getRentalSettings(){
		$Data = Doctrine_Query::create()
			->from('ProductsRentalSettings')
			->where('products_id = ?', $this->getProductId())
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		return $Data[0];
	}

	/**
	 * @param array $CartProductData
	 * @return bool
	 */
	public function allowAddToCart($CartProductData){
		global $messageStack, $userAccount;

		if ($userAccount->isLoggedIn() === false && Session::exists('on_set_location') === false){
			Session::set('on_set_location', $_POST);
			tep_redirect(itw_app_link('appExt=rentalProducts', 'chooseLocation', 'default'));
		}else{
			PurchaseTypeModules::loadModule('rental');
			$PurchaseType = PurchaseTypeModules::getModule('rental');

			if ($PurchaseType->rentalLimitReached() === true){
				$messageStack->addSession('pageStack', sysLanguage::get('TEXT_RENTAL_LIMIT_REACHED'), 'error');

				tep_redirect(itw_app_link(tep_get_all_get_params(array('action', 'rType'))));
			}
		}
		return true;
	}

	public function processAddToCart(&$pInfo) {
		$pInfo['price'] = $this->productInfo['price'];
		$pInfo['final_price'] = $this->productInfo['price'];

		EventManager::notify('PurchaseTypeAddToCart', $this->getCode(), &$pInfo, $this->productInfo);
	}

	public function updateStock($orderId, $orderProductId, &$cartProduct) {
		return false;
	}

	public function onInsertOrderedProduct($cartProduct, $orderId, &$orderedProduct, &$products_ordered) {
		$pID = (int)$cartProduct->getIdString();
		$InventoryCls =& $this->getInventoryClass();

		$startDate = date(DATE_RSS, time());
		$endDate = date(DATE_RSS, strtotime('+' . $this->getConfigData('RENTAL_PERIOD') . ' Day'));

		$trackMethod = $InventoryCls->getTrackMethod();

		$Rental =& $orderedProduct->OrdersProductsRentals;
		$Rental->start_date = $startDate;
		$Rental->end_date = $endDate;
		$Rental->rental_state = $this->getConfigData('RENTAL_STATUS_RESERVED');

		$nextInvItem = (int) $InventoryCls->getNextInventoryItemId();
		if ($nextInvItem > 0){
			if ($trackMethod == 'barcode'){
				$Rental->barcode_id = $nextInvItem;
				$Rental->ProductsInventoryBarcodes->status = 'R';
			}elseif ($trackMethod == 'quantity'){
				$Rental->quantity_id = $nextInvItem;
				$Rental->ProductsInventoryQuantity->available -= 1;
				$Rental->ProductsInventoryQuantity->reserved += 1;
			}
			EventManager::notify('RentalProductsOnInsertOrderedProduct', $Rental, &$cartProduct);
		}

		$products_ordered .= 'Reservation Info' .
			"\n\t" . 'Start Date: ' . date('d/m/Y', strtotime($startDate)) .
			"\n\t" . 'End Date: ' . date('d/m/Y', strtotime($endDate));

		EventManager::notify('RentalProductsAppendOrderedProductsString', &$products_ordered, &$cartProduct);

		$orderedProduct->purchase_type = $this->getCode();
		$orderedProduct->save();
	}

	public function addToOrdersProductCollection(OrderCreatorProduct $OrderProduct, &$OrderedProduct){
		$InventoryCls =& $this->getInventoryClass();

		$startDate = date(DATE_RSS, time());
		$endDate = date(DATE_RSS, strtotime('+' . $this->getConfigData('RENTAL_PERIOD') . ' Day'));

		$trackMethod = $InventoryCls->getTrackMethod();

		$Rental =& $OrderedProduct->OrdersProductsRentals;
		$Rental->start_date = $startDate;
		$Rental->end_date = $endDate;
		$Rental->rental_state = $this->getConfigData('RENTAL_STATUS_RESERVED');

		if ($OrderProduct->hasBarcodeId()){
			$Rental->barcode_id = $OrderProduct->getBarcodeId();
			$Rental->ProductsInventoryBarcodes->status = 'R';
		}else{
			$nextInvItem = (int) $InventoryCls->getNextInventoryItemId();
			if ($nextInvItem > 0){
				if ($trackMethod == 'barcode'){
					$Rental->barcode_id = $nextInvItem;
					$Rental->ProductsInventoryBarcodes->status = 'R';
				}elseif ($trackMethod == 'quantity'){
					$Rental->quantity_id = $nextInvItem;
					$Rental->ProductsInventoryQuantity->available -= 1;
					$Rental->ProductsInventoryQuantity->reserved += 1;
				}
				EventManager::notify('RentalProductsOnInsertOrderedProduct', $Rental, &$OrderedProduct);
			}
		}
	}
	
	public function rentalLimitReached($customerId = false){
		global $userAccount;
		$reached = false;
		if ($userAccount->isLoggedIn() === true || $customerId !== false){
			if ($customerId === false){
				$customerId = $userAccount->getCustomerId();
			}
			$Qcheck = Doctrine_Query::create()
				->select('count(opr.orders_products_rentals_id) as total')
				->from('Orders o')
				->leftJoin('o.OrdersProducts op')
				->leftJoin('op.OrdersProductsRentals opr')
				->where('o.customers_id = ?', $customerId)
				->andWhereIn('opr.rental_state', array($this->getConfigData('RENTAL_STATUS_RESERVED'), $this->getConfigData('RENTAL_STATUS_OUT')))
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($Qcheck[0]['total'] >= $this->getConfigData('MAXIMUM_ALLOWED_OUT')){
				$reached = true;
			}
		}
		return $reached;
	}
	
	public function expireReservations(){
		$expireTime = strtotime('-' . $this->getConfigData('RENTAL_PRODUCT_RESERVATION_EXPIRATION') . ' minutes');
		
		$Qorders = Doctrine_Query::create()
			->from('Orders o')
			->leftJoin('o.OrdersProducts op')
			->leftJoin('op.OrdersProductsRentals opr')
			->where('opr.rental_state = ?', $this->getConfigData('RENTAL_STATUS_RESERVED'))
			->execute();
		if ($Qorders && $Qorders->count() > 0){
			foreach($Qorders as $Order){
				$DateParsed = date_parse($Order->date_purchased);
				$TimePurchased = mktime($Date['hour'], $Date['minute'], $Date['second'], $Date['month'], $Date['day'], $Date['year']);
				if ($TimePurchased <= $expireTime){
					foreach($Order->OrdersProducts as $Product){
						if ($Product->OrdersProductsRentals){
							$Product->OrdersProductsRentals->rental_state = $this->getConfigData('RENTAL_STATUS_EXPIRED');
						}
					}
					$Order->save();
				}
			}
		}
	}

	public function expireShoppingCart(){
		$expireTime = time();

		$Qdata = Doctrine_Query::create()
			->from('CustomersBasket')
			->execute();
		if ($Qdata && $Qdata->count() > 0){
			foreach($Qdata as $cInfo){
				$CartContents = unserialize($cInfo->cart_data);
				foreach($CartContents as $CartProduct){
					if ($CartProduct->hasData('expires') && $CartProduct->getData('expires') < $expireTime){
						$CartContents->remove($CartProduct);
					}
				}
				$cInfo->cart_data = $CartContents->serialize();
			}
			$Qdata->save();
		}
	}

	public function getPurchaseHtml($key) {
		global $userAccount;
		$return = null;
		switch($key){
			case 'product_info':
				$button = htmlBase::newElement('button')
					->setType('submit')
					->setName('buy_rental_product')
					->setText(sysLanguage::get('TEXT_BUTTON_RENT'));

				$allowQty = ($this->getConfigData('ALLOW_MULTIPLE_IN_CART') == 'True' && $this->getConfigData('ALLOWED_PRODUCT_INFO_QUANTITY_FIELD') == 'True');
				if ($this->hasInventory() === false){
					$allowQty = false;
					switch($this->getConfigData('OUT_OF_STOCK_PRODUCT_INFO_DISPLAY')){
						case 'Disable Button':
							$button->disable();
							break;
						case 'Out Of Stock Text':
							$button = htmlBase::newElement('span')
								->addClass('outOfStockText')
								->html(sysLanguage::get('TEXT_OUT_OF_STOCK'));
							break;
						case 'Hide Box':
							return null;
							break;
					}
				}
				
				if ($this->getConfigData('LOGIN_REQUIRED') == 'True'){
					if ($userAccount->isLoggedIn() === false){
						$allowQty = false;
						$button = htmlBase::newElement('button')
							->setHref(itw_app_link(null, 'account', 'login'))
							->setText(sysLanguage::get('TEXT_LOGIN_REQUIRED'));
					}
				}
				
				if ($this->rentalLimitReached() === true){
					$button = htmlBase::newElement('span')
						->addClass('rentalLimitReachedText')
						->html(sysLanguage::get('TEXT_RENTAL_LIMIT_REACHED'));
				}

				$content = htmlBase::newElement('span')
					->css(array(
						'font-size' => '1.5em',
						'font-weight' => 'bold'
					))
					->html($this->displayPrice() . ' / ' . $this->getConfigData('RENTAL_PERIOD') . ' Day(s)');
					
				$return = array(
					'form_action' => itw_app_link(tep_get_all_get_params(array('action'))),
					'purchase_type' => $this->getCode(),
					'allowQty' => $allowQty,
					'header' => $this->getTitle(),
					'content' => $content->draw(),
					'button' => $button
				);
				break;
		}
		return $return;
	}

	public function getOrderedProductBarcode($pInfo){
		return $pInfo['OrdersProductsRentals']['ProductsInventoryBarcodes']['barcode'];
	}

	public function displayOrderedProductBarcode($pInfo){
		return $pInfo['OrdersProductsRentals']['ProductsInventoryBarcodes']['barcode'];
	}

	public function OrderCreatorAllowProductUpdate(OrderCreatorProduct $OrderProduct){
		global $Editor;
		$return = true;
		if ($this->rentalLimitReached($Editor->getCustomerId()) === true){
			$return = false;
			$Editor->addErrorMessage('Rental Limit Reached For This Customer');
		}
		return $return;
	}
}

?>