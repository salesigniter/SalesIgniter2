<?php
if (class_exists('PurchaseType_reservation') === false){
	require(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/purchaseTypeModules/reservation/module.php');
}

class OrderCreatorPurchaseTypeReservation extends PurchaseType_reservation {

	public function OrderCreatorAllowAddToContents(OrderCreatorProduct $OrderProduct){
		global $messageStack;
		$allow = false;
		if (isset($_POST['reservation_begin']) && isset($_POST['reservation_end'])){
			$allow = true;
		}else{
			if (!isset($_POST['reservation_begin'])){
				$messageStack->add('OrderCreator', 'No Start Date Entered', 'error');
			}

			if (!isset($_POST['reservation_end'])){
				$messageStack->add('OrderCreator', 'No End Date Entered', 'error');
			}
		}
		/**
		 * @TODO Add in reservation availability check
		 */

		return $allow;
	}

	public function OrderCreatorOnAddToContents(OrderCreatorProduct $OrderProduct){
		$ProductInfo = $OrderProduct->getInfo();

		$ProductInfo['reservationInfo'] = array(
			'start_date' => $_POST['reservation_begin'],
			'start_date_time' => $_POST['reservation_begin_time'],
			'end_date' => $_POST['reservation_end'],
			'end_date_time' => $_POST['reservation_end_time'],
			'quantity' => $OrderProduct->getQuantity(),
			'weight' => $OrderProduct->getWeight(),
			'days_before' => (isset($_POST['days_before']) ? $_POST['days_before'] : 0),
			'days_after' => (isset($_POST['days_after']) ? $_POST['days_after'] : 0),
			'shipping' => false
		);

		$ProductInfo['reservationInfo']['start_date'] = $ProductInfo['reservationInfo']['start_date']->modify('-' . $ProductInfo['reservationInfo']['days_before'] . ' Day');
		$ProductInfo['reservationInfo']['end_date'] = $ProductInfo['reservationInfo']['end_date']->modify('+' . $ProductInfo['reservationInfo']['days_after'] . ' Day');

		if (isset($_POST['shipping_method'])){
			$Module = OrderShippingModules::getModule($_POST['shipping_method']);
			if (is_object($Module)){
				$quote = $Module->quote($shippingMethod, $ProductInfo['reservationInfo']['weight']);

				$ProductInfo['reservationInfo']['shipping'] = array(
					'title' => (isset($quote['methods'][0]['title']) ? $quote['methods'][0]['title'] : ''),
					'cost' => (isset($quote['methods'][0]['cost']) ? $quote['methods'][0]['cost'] : ''),
					'id' => (isset($quote['methods'][0]['id']) ? $quote['methods'][0]['id'] : ''),
					'module' => $Module->getCode()
				);
			}
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
			$ProductInfo['reservationInfo']['event_date'] = $_POST['event_date'];
			$ProductInfo['reservationInfo']['event_name'] = $_POST['event_name'];
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				if (isset($_POST['event_gate'])){
					$ProductInfo['reservationInfo']['event_gate'] = $_POST['event_gate'];
				}
			}
		}
		if (isset($_POST['semester_name'])){
			$ProductInfo['reservationInfo']['semester_name'] = $_POST['semester_name'];
		}
		else {
			$ProductInfo['reservationInfo']['semester_name'] = '';
		}

		$newPrice = 0;

		$pricing = $this->figureProductPricing($ProductInfo['reservationInfo']);
		if (!empty($pricing)){
			$newPrice =+ $pricing['price'];
			$ProductInfo['reservationInfo']['deposit_amount'] = $this->getDepositAmount();
		}

		if (isset($_POST['hasInsurance']) && $_POST['hasInsurance'] == '1'){
			$payPerRentals = Doctrine_Query::create()
				->select('insurance')
				->from('ProductsPayPerRental')
				->where('products_id = ?', $ProductInfo['products_id'])
				->fetchOne();

			$ProductInfo['reservationInfo']['insurance'] = $payPerRentals->insurance;
			$newPrice =+ $payPerRentals->insurance;
		}

		if (isset($_POST['id']['reservation']) && !empty($_POST['id']['reservation'])){
			$attrValue = attributesUtil::getAttributeString($_POST['id']['reservation']);
			if (!empty($attrValue)){
				$ProductInfo['aID_string'] = $attrValue;
			}
		}

		$OrderProduct->updateInfo($ProductInfo);
		$OrderProduct->setPrice($newPrice);

		if ($this->getAvailableBarcode($OrderProduct, array()) == -1){
			$OrderProduct->needsConfirmation(true);
		}
	}

	public function onUpdateOrderProduct(OrderCreatorProduct &$OrderedProduct){
	}

	public function OrderCreatorProductManagerUpdateFromPost(OrderCreatorProduct &$OrderedProduct){

	}

	public function addToOrdersProductCollection(OrderCreatorProduct $OrderProduct, OrdersProducts &$OrderedProduct) {
		global $Editor;
		$allInfo = $OrderProduct->getInfo();
		if (!isset($allInfo['reservationInfo'])){
			$ResInfo = $allInfo['OrdersProductsReservation'][0];
			$Quantity = $allInfo['products_quantity'];
			$ShippingInfo = array(
				'id'                    => $ResInfo['shipping_method'],
				'shipping_method_title' => $ResInfo['shipping_method_title'],
				'shipping_method'       => $ResInfo['shipping_method'],
				'shipping_days_before'  => $ResInfo['shipping_days_before'],
				'shipping_days_after'   => $ResInfo['shipping_days_after'],
				'shipping_cost'         => $ResInfo['shipping_cost']
			);
		}else{
			$ResInfo = $allInfo['reservationInfo'];
			$ShippingInfo = $ResInfo['shipping'];
			$Quantity = $OrderProduct->getQuantity();
		}

		$Insurance = (isset($ResInfo['insurance']) ? $ResInfo['insurance'] : 0);
		$InventoryCls =& $this->getInventoryClass();
		$TrackMethod = $InventoryCls->getTrackMethod();
		if (isset($allInfo['aID_string']) && !empty($allInfo['aID_string'])){
			$InventoryCls->trackMethod->aID_string = $allInfo['aID_string'];
		}
		$EventName = '';
		$EventDate = '0000-00-00 00:00:00';
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
			$EventName = $ResInfo['event_name'];
			$EventDate = $ResInfo['event_date'];
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$EventGate = $ResInfo['event_gate'];
			}
		}

		$Reservations =& $OrderedProduct->OrdersProductsReservation;
		$Reservations->delete();

		$excludedBarcode = array();
		$excludedQuantity = array();
		for($count = 1; $count <= $Quantity; $count++){
			$Reservation = new OrdersProductsReservation();
			$Reservation->start_date = $ResInfo['start_date'];
			$Reservation->end_date = $ResInfo['end_date'];
			$Reservation->insurance = $Insurance;
			$Reservation->event_name = $EventName;
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$Reservation->event_gate = $EventGate;
			}
			$Reservation->event_date = $EventDate;
			$Reservation->track_method = $TrackMethod;
			$Reservation->rental_state = 'reserved';
			if (isset($_POST['estimateOrder'])){
				$Reservation->is_estimate = 1;
			}
			else {
				$Reservation->is_estimate = 0;
			}
			if (isset($ShippingInfo['id']) && !empty($ShippingInfo['id'])){
				$Reservation->shipping_method_title = $ShippingInfo['title'];
				$Reservation->shipping_method = $ShippingInfo['id'];
				$Reservation->shipping_days_before = $ShippingInfo['days_before'];
				$Reservation->shipping_days_after = $ShippingInfo['days_after'];
				$Reservation->shipping_cost = $ShippingInfo['cost'];
			}
			if (!isset($_POST['estimateOrder'])){
				$hBarcode = '';
				if ($OrderProduct->hasBarcodes()){
					$hBarcode = $OrderProduct->getBarcodes();
					$hBarcode = $hBarcode[0]['barcode_id'];
					$QBarcodeExists = Doctrine_Query::create()
						->from('ProductsInventoryBarcodes')
						->where('barcode_id = ?', $hBarcode)
						->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
					$hBarcode = (isset($QBarcodeExists[0]['barcode_id']) ? $QBarcodeExists[0]['barcode_id'] : '');
				}
				if (!empty($hBarcode)){
					$Reservation->barcode_id = $hBarcode;
					$excludedBarcode[] = $Reservation->barcode_id;
					$Reservation->ProductsInventoryBarcodes->status = 'R';
				}
				else {
					if ($TrackMethod == 'barcode'){
						$barId = $this->getAvailableBarcode($OrderProduct, $InventoryCls->getInventoryItems(), $excludedBarcode, $allInfo['usableBarcodes']);
						if($barId != -1){
							$Reservation->barcode_id = $barId;
						}
						else {
							$Editor->addErrorMessage('Reservation already taken for the date. Please reselect');
						}
						$excludedBarcode[] = $Reservation->barcode_id;
						$Reservation->ProductsInventoryBarcodes->status = 'R';
					}
					elseif ($TrackMethod == 'quantity') {
						$qtyId = $this->getAvailableQuantity($OrderProduct, $InventoryCls->getInventoryItems(), $excludedQuantity);
						if($qtyId != -1){
							$Reservation->quantity_id = $qtyId;
						}
						else {
							$Editor->addErrorMessage('Reservation already taken for the date. Please reselect');
						}
						$excludedQuantity[] = $Reservation->quantity_id;
						$Reservation->ProductsInventoryQuantity->available -= 1;
						$Reservation->ProductsInventoryQuantity->reserved += 1;
					}
				}
			}
			EventManager::notify('ReservationOnInsertOrderedProduct', &$Reservation, &$OrderProduct);

			$Reservations->add($Reservation);
		}
	}

	public function OrderCreatorAfterProductName(OrderCreatorProduct $OrderedProduct, $allowEdit = true) {
		global $currencies;
		$return = '';
		$resInfo = null;
		if ($OrderedProduct->hasInfo('OrdersProductsReservation')){
			$resData = $OrderedProduct->getInfo('OrdersProductsReservation');
			$resInfo = $this->formatOrdersReservationArray($resData);
		}
		elseif ($OrderedProduct->hasInfo('reservationInfo')){
			$resInfo = $OrderedProduct->getInfo('reservationInfo');
		}
		$id = $OrderedProduct->getId();
		$changeButton = htmlBase::newElement('button')
			->setText('Select Dates')
			->addClass('reservationDates');

		$return .= '<br /><small><b><i><u>' . sysLanguage::get('TEXT_INFO_RESERVATION_INFO') . '</u></i></b>&nbsp;' . '</small>';
		/*This part will have to be changed for events*/

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'False'){
			if (is_null($resInfo) === false){
				$start = $resInfo['start_date'];
				$end = $resInfo['end_date'];
			}else{
				$start = new SesDateTime();
				$end = new SesDateTime();
			}
			$return .= '<br /><small><i>' .
				'- Start Date: <span class="res_start_date">'.$start->format(sysLanguage::getDateTimeFormat()).'</span><br/>' .
				'- End Date: <span class="res_end_date">'.$end->format(sysLanguage::getDateTimeFormat()).'</span>'.
				($allowEdit === true ? $changeButton->draw() .
					'<input type="hidden" class="ui-widget-content resDateHidden" name="product[' . $id . '][reservation][dates]" value="' . $start->format(sysLanguage::getDateTimeFormat()) . ',' . $end->format(sysLanguage::getDateTimeFormat()) . '">'
					: '') .
				'</i></small><div class="selectDialog"></div>';

		}else{
			$eventb = htmlBase::newElement('selectbox')
				->setName('product[' . $id . '][reservation][events]')
				->addClass('eventf');
			//->attr('id', 'eventz');
			$eventb->addOption('0', 'Select an Event');

			$Events = PurchaseType_reservation_utilities::getEvents();
			if ($Events){
				foreach($Events as $qev){
					$eventb->addOption($qev['events_id'], $qev['events_name']);
					if (isset($resInfo['event_name']) && $resInfo['event_name'] == $qev['events_name']){
						$eventb->selectOptionByValue($qev['events_id']);
					}
				}
			}

			$gateb = htmlBase::newElement('selectbox')
				->setName('gate')
				->addClass('gatef');
			$gateb->addOption('0', 'Autoselect Gate');

			$Gates = PurchaseType_reservation_utilities::getGates();
			if ($Gates){
				foreach($Gates as $iGate){
					$gateb->addOption($iGate['gates_id'], $iGate['gate_name']);
				}
			}

			if (isset($resInfo['event_name']) && !empty($resInfo['event_name'])){
				$QeventSelected = Doctrine_Query::create()
					->from('PayPerRentalEvents')
					->where('events_name = ?', $resInfo['event_name'])
					->fetchOne();

				if ($QeventSelected){
					$eventb->selectOptionByValue($QeventSelected->events_id);
				}
			}

			if (isset($resInfo['event_gate']) && !empty($resInfo['event_gate'])){
				$GateSelected = PurchaseType_reservation_utilities::getGates($resInfo['event_gate']);
				if ($GateSelected){
					$gateb->selectOptionByValue($GateSelected->gates_id);
				}
			}

			$return .= '<br /><small><i> - Events ' . $eventb->draw() . '</i></small>'; //use gates too in OC
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$return .= '<br /><small><i> - Gates ' . $gateb->draw() . '</i></small>'; //use gates too in OC
			}
		}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_UPS_RESERVATION') == 'False'){
			$Module = OrderShippingModules::getModule('zonereservation');
		}
		else {
			$Module = OrderShippingModules::getModule('upsreservation');
		}

		if ($this->shippingIsNone() === false && $this->shippingIsStore() === false){
			$shipInput = '';
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
				$selectBox = htmlBase::newElement('selectbox')
					->addClass('ui-widget-content reservationShipping')
					->setName('product[' . $id . '][reservation][shipping]');

				if (isset($Module) && is_object($Module)){
					$quotes = $Module->quote();
					foreach($quotes['methods'] as $method){
						$selectBox->addOption(
							$method['id'],
							$method['title'] . ' ( ' . $currencies->format($method['cost']) . ' )',
							false,
							array(
								'days_before' => $method['days_before'],
								'days_after' => $method['days_after']
							)
						);
					}
				}
			}
			else {
				$selectBox = htmlBase::newElement('input')
					->setType('hidden')
					->addClass('ui-widget-content reservationShipping')
					->setName('product[' . $id . '][reservation][shipping]');
			}
			if (is_null($resInfo) === false && isset($resInfo['shipping']) && $resInfo['shipping'] !== false && isset($resInfo['shipping']['title']) && !empty($resInfo['shipping']['title']) && isset($resInfo['shipping']['cost']) && !empty($resInfo['shipping']['cost'])){
				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
					$selectBox->selectOptionByValue($resInfo['shipping']['id']);
				}
				else {
					$selectBox->setValue($resInfo['shipping']['id']);
				}
				$shipInput = '<span class="reservationShippingText">' . $resInfo['shipping']['title'] . '</span>';
				$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_SHIPPING_METHOD') . ' ' . $selectBox->draw() . $shipInput . '</i></small>';
			}
		}
		//if (is_null($resInfo) === false && isset($resInfo['deposit_amount']) && $resInfo['deposit_amount'] > 0){
		if ($this->getDepositAmount() > 0){
			$return .= '<br /><small><i> - ' . sysLanguage::get('TEXT_INFO_DEPOSIT_AMOUNT') . ' ' . $currencies->format($this->getDepositAmount()) . '</i></small>';
		}
		//}

		EventManager::notify('ParseReservationInfoEdit', $return, $resInfo);
		return $return;
	}

	public function hasEnoughInventory(OrderProduct &$Product, $Qty = null){
		if (parent::hasEnoughInventory($Product, $Qty) === false){
			$Product->setConfirmationMessage('This Product Does Not have Enough Inventory For The Selected Dates.');
			return false;
		}
		return true;
	}
}