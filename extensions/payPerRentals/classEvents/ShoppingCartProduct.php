<?php
	class ShoppingCartProduct_payPerRentals {

		public function __construct(){
		}

		public function init(){
			EventManager::attachEvents(array(
				'ProductNameAppend'
			), 'ShoppingCartProduct', $this);
		}
		
		public function ProductNameAppend(&$cartProduct){
			//echo 'dfff'.print_r($cartProduct->getInfo('reservationInfo'));
			//itwExit();
			if ($cartProduct->hasInfo('reservationInfo')){
				$resData = $cartProduct->getInfo('reservationInfo');
				if ($resData && !empty($resData['start_date'])){
					$purchaseTypeClass = PurchaseTypeModules::getModule('reservation');
					$purchaseTypeClass->loadProduct($cartProduct->getIdString());
					return $purchaseTypeClass->parse_reservation_info($cartProduct->getIdString(), $resData);
				}
			}
		}
	}
?>