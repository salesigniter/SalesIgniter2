<?php
class PurchaseType_reservation_checkers extends PurchaseType_reservation_getters {




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


}
