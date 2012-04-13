<?php
class PurchaseType_reservation_checkers extends PurchaseType_reservation_getters {
	public function shippingIsStore() {
		return ($this->getShipping() == 'store');
	}

	public function shippingIsNone() {
		return ($this->getShipping() == 'false');
	}

	private function isIn($var) {
		if (in_array($var, Session::get('noInvDates'))){
			return false;
		}
		return true;
	}

	public function hasMaxDays() {
		if (isset($this->pprInfo)){
			return $this->pprInfo['max_days'] > 0;
		}
		return false;
	}

	public function hasMaxMonths() {
		if (isset($this->pprInfo)){
			return $this->pprInfo['max_months'] > 0;
		}
		return false;
	}

	public function checkAvailableBarcodes($Product){
		$barcodes = array();
		for($i=0; $i<$Product->getQuantity(); $i++){
			$barcodeId = $this->getAvailableBarcode($Product, $barcodes);
			if ($barcodeId > -1){
				$barcodes[] = $barcodeId;
			}
		}
		return (sizeof($barcodes) > $Product->getQuantity());
	}

	public function overBookingAllowed() {
		return ($this->getOverbooking() == '1');
	}

	public function dateIsBooked($date, $bookedDays, $invItems, $qty = 1) {
		if ($invItems === false){
			return true;
		}
		$totalAvail = 0;
		foreach($invItems as $item){
			if ($this->getTrackMethod() == 'barcode'){
				if (!isset($bookedDays['barcode'][$date]) || !in_array($item['id'], $bookedDays['barcode'][$date])){
					$totalAvail++;
				}
			}
			elseif ($this->getTrackMethod() == 'quantity') {
				$realAvail = ($item['available'] + $item['reserved']) /* - $Qcheck[0]['total']*/
				;
				if (!isset($bookedDays['quantity'][$date]) || !isset($bookedDays['quantity'][$date][$item['id']])){
					$totalAvail += $realAvail;
				}
				elseif ($realAvail > $qty) {
					$totalAvail += $realAvail;
				}
			}

			if ($totalAvail >= $qty){
				break;
			}
		}
		if ($totalAvail >= $qty){
			return false;
		}
		else {
			if ($this->overBookingAllowed() === true){
				return false;
			}
			else {
				return true;
			}
		}
	}
}
