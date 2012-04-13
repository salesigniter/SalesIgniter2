<?php
class PurchaseType_reservation_getters extends PurchaseType_reservation_setters
{

	public function getPayPerRentalId() { return $this->pprInfo['pay_per_rental_id']; }

	public function getPriceDaily() { return $this->pprInfo['price_daily']; }

	public function getPriceWeekly() { return $this->pprInfo['price_weekly']; }

	public function getPriceMonthly() { return $this->pprInfo['price_monthly']; }

	public function getPriceSixMonth() { return $this->pprInfo['price_six_month']; }

	public function getPriceYear() { return $this->pprInfo['price_year']; }

	public function getPriceThreeYear() { return $this->pprInfo['price_three_year']; }

	public function getQuantity() { return $this->pprInfo['quantity']; }

	public function getComboProducts() { return $this->pprInfo['combo_products']; }

	public function getComboPrice() { return $this->pprInfo['combo_price']; }

	public function getMaxDays() { return $this->pprInfo['max_days']; }

	public function getMaxMonths() { return $this->pprInfo['max_months']; }

	public function getShipping() { return $this->pprInfo['shipping']; }

	public function getShippingArray() { return explode(',', $this->pprInfo['shipping']); }

	public function getOverbooking() { return $this->pprInfo['overbooking']; }

	public function getDepositAmount() { return $this->pprInfo['deposit_amount']; }

	public function getInsurance() { return $this->pprInfo['insurance']; }

	public function getMinRentalDays() { return $this->pprInfo['min_rental_days']; }

	public function getMinPeriod() { return $this->pprInfo['min_period']; }

	public function getMaxPeriod() { return $this->pprInfo['max_period']; }

	public function getMinType() { return $this->pprInfo['min_type']; }

	public function getMaxType() { return $this->pprInfo['max_type']; }

	public function getShipModuleCode() { return $this->shipModuleCode; }

	public function getMaintenance(){ return $this->pprInfo['maintenance']; }

	public function getEnabledShippingMethods() {
		return $this->enabledShipping;
	}

	public function getMaxShippingDays($starting) {
		return ReservationUtilities::getMaxShippingDays(
			$this->getData('products_id'),
			$starting,
			$this->overBookingAllowed()
		);
	}

	public function getPriceSemester($semName) {
		$QPeriodsNames = Doctrine_Query::create()
			->from('PayPerRentalPeriods')
			->where('period_name=?', $semName)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		if (count($QPeriodsNames) > 0){
			$QPricePeriod = Doctrine_Query::create()
				->from('ProductsPayPerPeriods')
				->where('period_id=?', $QPeriodsNames[0]['period_id'])
				->andWhere('products_id=?', $this->getProductId())
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			return $QPricePeriod[0]['price'];
		}
		else {
			return 0;
		}
	}

	public function getReservePrice($type) {
		if (isset($this->pprInfo)){
			return $this->pprInfo['price_' . $type];
		}
		return;
	}

	public function getAvailableBarcode($cartProduct, $excluded, $usableBarcodes = array()) {
		$invItems = $this->getInventoryItems();
		$pInfo = $cartProduct->getInfo();
		if ($cartProduct instanceof OrderCreatorProduct){
			if (isset($pInfo['reservationInfo'])){
				$resInfo = $pInfo['reservationInfo'];
			}else{
				$resInfo = $pInfo['OrdersProductsReservation'];
				if (isset($resInfo['shipping_days_before'])){
					$shippingDaysBefore = (int)$resInfo['shipping_days_before'];
				}
				else {
					$shippingDaysBefore = 0;
				}

				if (isset($resInfo['shipping_days_after'])){
					$shippingDaysAfter = (int)$resInfo['shipping_days_after'];
				}
				else {
					$shippingDaysAfter = 0;
				}
			}
		}else{
			$resInfo = $pInfo['reservationInfo'];
		}

		if (isset($resInfo['shipping']['days_before'])){
			$shippingDaysBefore = (int)$resInfo['shipping']['days_before'];
		}
		elseif (!isset($shippingDaysBefore)) {
			$shippingDaysBefore = 0;
		}

		if (isset($resInfo['shipping']['days_after'])){
			$shippingDaysAfter = (int)$resInfo['shipping']['days_after'];
		}
		elseif (!isset($shippingDaysAfter)) {
			$shippingDaysAfter = 0;
		}

		$startDate = $resInfo['start_date']->modify('-' . $shippingDaysBefore . ' Day');
		$endDate = $resInfo['end_date']->modify('+' . $shippingDaysAfter . ' Day');

		$barcodeID = -1;
		foreach($invItems as $barcodeInfo){
			if (count($usableBarcodes) == 0 || in_array($barcodeInfo['id'], $usableBarcodes)){
				if (in_array($barcodeInfo['id'], $excluded)){
					continue;
				}

				$bookingInfo = array(
					'item_type'   => 'barcode',
					'item_id'     => $barcodeInfo['id'],
					'start_date'  => $startDate,
					'end_date'    => $endDate,
					'cartProduct' => $cartProduct
				);
				if (Session::exists('isppr_inventory_pickup')){
					$pickupCheck = Session::get('isppr_inventory_pickup');
					if (!empty($pickupCheck)){
						$bookingInfo['inventory_center_pickup'] = $pickupCheck;
					}
				}
				$bookingInfo['quantity'] = 1;
				//if allow overbooking is enabled what barcode should be chosen.. I think any is good.

				$bookingCount = ReservationUtilities::CheckBooking($bookingInfo);
				if ($bookingCount <= 0 || sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_STOCK') == 'True'){
					$barcodeID = $barcodeInfo['id'];
					break;
				}
			}
		}
		return $barcodeID;
	}

	/*
	 * Get Available Quantity Function
	 */

	public function getAvailableQuantity($cartProduct, $excluded) {
		$invItems = $this->getInventoryItems();
		if ($cartProduct->hasInfo('quantity_id') === false){
			$resInfo = $cartProduct->getInfo('reservationInfo');
			if (isset($resInfo['shipping']['days_before'])){
				$shippingDaysBefore = (int)$resInfo['shipping']['days_before'];
			}
			else {
				$shippingDaysBefore = 0;
			}

			if (isset($resInfo['shipping']['days_after'])){
				$shippingDaysAfter = (int)$resInfo['shipping']['days_after'];
			}
			else {
				$shippingDaysAfter = 0;
			}

			$startDate = $resInfo['start_date'];
			$startDate->modify('-' . $shippingDaysBefore . ' Day');

			$endDate = $resInfo['end_date'];
			$endDate->modify('+' . $shippingDaysAfter . ' Day');
			$qtyID = -1;
			foreach($invItems as $qInfo){
				if (in_array($qInfo, $excluded)){
					continue;
				}
				$bookingCount = ReservationUtilities::CheckBooking(array(
					'item_type'   => 'quantity',
					'item_id'     => $qInfo['id'],
					'start_date'  => $startDate,
					'end_date'    => $endDate,
					'cartProduct' => $cartProduct
				));
				if ($bookingCount <= 0 || sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_STOCK') == 'True'){
					$qtyID = $qInfo['id'];
					break;
				}
				else {
					if ($qInfo['available'] > $bookingCount){
						$qtyID = $qInfo['id'];
						break;
					}
				}
			}
		}
		else {
			$qtyID = $cartProduct->getInfo('quantity_id');
		}
		return $qtyID;
	}

	public function getProductsBarcodes() {
		return $this->getInventoryItems();
	}

	public function getBookedDaysArray($starting, $qty, &$reservationsArr, &$bookedDates, $usableBarcodes = array()) {
		$reservationsArr = ReservationUtilities::getMyReservations(
			$this->getProductId(),
			$starting,
			$this->overBookingAllowed(),
			$usableBarcodes
		);
		//$bookedDates = array();
		foreach($reservationsArr as $iReservation){
			if (isset($iReservation['start']) && isset($iReservation['end'])){
				$startTime = strtotime($iReservation['start']);
				$endTime = strtotime($iReservation['end']);
				while($startTime <= $endTime){
					$dateFormated = date('Y-n-j', $startTime);
					if ($this->getTrackMethod() == 'barcode'){
						$bookedDates[$dateFormated]['barcode'][] = $iReservation['barcode'];
						//check if all the barcodes are already or make a new function to make checks by qty... (this function can return also the free barcode?)
					}
					else {
						if (isset($bookedDates[$dateFormated]['qty'])){
							$bookedDates[$dateFormated]['qty'] = $bookedDates[$dateFormated]['qty'] + 1;
						}
						else {
							$bookedDates[$dateFormated]['qty'] = 1;
						}
						//check if there is still qty available.
					}

					$startTime += 60 * 60 * 24;
				}
			}
		}
		$bookingsArr = array();
		$prodBarcodes = array();

		foreach($this->getProductsBarcodes() as $iBarcode){
			if (count($usableBarcodes) == 0 || in_array($iBarcode['id'], $usableBarcodes)){
				$prodBarcodes[] = $iBarcode['id'];
			}
		}
		//print_r($prodBarcodes);
		//echo '------------'.$qty;
		//print_r($bookedDates);

		if (count($prodBarcodes) < $qty){
			return false;
		}
		else {
			foreach($bookedDates as $dateFormated => $iBook){
				if ($this->getTrackMethod() == 'barcode'){
					$myqty = 0;
					foreach($iBook['barcode'] as $barcode){
						if (in_array($barcode, $prodBarcodes)){
							$myqty++;
						}
					}
					if (count($prodBarcodes) - $myqty < $qty){
						$bookingsArr[] = $dateFormated;
					}
				}
				else {
					if ($prodBarcodes['available'] - $iBook['qty'] < $qty){
						$bookingsArr[] = $dateFormated;
					}
				}
			}
		}
		return $bookingsArr;
	}

	public function getBookedTimeDaysArray($starting, $qty, $minTime, &$reservationsArr, &$bookedDates) {
		/*$reservationsArr = ReservationUtilities::getMyReservations(
			$this->getProductId(),
			$starting,
			$this->overBookingAllowed()
		);*/
		$bookedTimes = array();
		//print_r($bookedDates);
		//print_r($reservationsArr);

		foreach($reservationsArr as $iReservation){
			if (isset($iReservation['start_time']) && isset($iReservation['end_time'])){
				$startTime = strtotime($iReservation['start_date'] . ' ' . $iReservation['start_time']);
				$endTime = strtotime($iReservation['start_date'] . ' ' . $iReservation['end_time']);
				while($startTime <= $endTime){
					$dateFormated = date('Y-n-j H:i', $startTime);
					if ($this->getTrackMethod() == 'barcode'){
						$bookedTimes[$dateFormated]['barcode'][] = $iReservation['barcode'];
						if (isset($bookedDates[$iReservation['start_date']]['barcode'])){
							foreach($bookedDates[$iReservation['start_date']]['barcode'] as $iBarc){
								$bookedTimes[$dateFormated]['barcode'][] = $iBarc;
							}
						}
						//check if all the barcodes are already or make a new function to make checks by qty... (this function can return also the free barcode?)
					}
					else {
						if (isset($bookedTimes[$dateFormated]['qty'])){
							$bookedTimes[$dateFormated]['qty'] = $bookedTimes[$dateFormated]['qty'] + 1;
						}
						else {
							$bookedTimes[$dateFormated]['qty'] = 1;
						}
						if (isset($bookedDates[$iReservation['start_date']]['qty'])){
							$bookedTimes[$dateFormated]['qty'] = $bookedTimes[$dateFormated]['qty'] + count($bookedDates[$iReservation['start_date']]['qty']);
						}
						//check if there is still qty available.
					}

					$startTime += $minTime * 60;
				}
			}
		}
		$bookingsArr = array();
		$prodBarcodes = array();
		foreach($this->getProductsBarcodes() as $iBarcode){
			$prodBarcodes[] = $iBarcode['id'];
		}

		foreach($bookedTimes as $dateFormated => $iBook){
			if ($this->getTrackMethod() == 'barcode'){
				$myqty = 0;
				foreach($iBook['barcode'] as $barcode){
					if (in_array($barcode, $prodBarcodes)){
						$myqty++;
					}
				}
				if (count($prodBarcodes) - $myqty < $qty){
					$bookingsArr[] = $dateFormated;
				}
			}
			else {
				if ($prodBarcodes['available'] - $iBook['qty'] < $qty){
					$bookingsArr[] = $dateFormated;
				}
			}
		}

		return $bookingsArr;
	}

	public function getReservations($start, $end) {
		$booked = ReservationUtilities::getReservations(
			$this->getProductId(),
			$start,
			$end,
			$this->overBookingAllowed()
		);

		return $booked;
	}
}
