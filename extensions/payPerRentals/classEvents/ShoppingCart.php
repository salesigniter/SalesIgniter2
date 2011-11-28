<?php
	class ShoppingCart_payPerRentals {
		
		public function __construct(){
		}
		
		public function init(){
			
			EventManager::attachEvents(array(
				'CountContents',
				'AddToCartAfterAction',
				'AddToCartBeforeAction',
				'AddToCartAllow'
			), 'ShoppingCart', $this);
		}

		public function AddToCartAllow($cartData, $Product){
			return true;
		}

		public function AddToCartBeforeAction(ShoppingCartProduct &$cartProduct){
			if($_POST['purchase_type'] == 'reservation'){
				$pInfo = $cartProduct->getInfo();
				if(isset($_POST['rental_qty'])){
					$pInfo['reservationInfo']['quantity'] = $_POST['rental_qty'];
				}

				if (isset($pInfo['rental_shipping']) && $_POST['rental_shipping'] !== false) {
					list($module, $method) = explode('_', $_POST['rental_shipping']);
					$pInfo['reservationInfo']['shipping']['module'] = $module;
					$pInfo['reservationInfo']['shipping']['id'] = $method;
				}
				if (isset($_POST['start_date'])){
					$pInfo['reservationInfo']['start_date'] = $_POST['start_date'];
				}

				if (isset($_POST['event_date'])) {
					$pInfo['reservationInfo']['event_date'] = $_POST['event_date'];
				}
				if (isset($_POST['event_name'])) {
					$pInfo['reservationInfo']['event_name'] = $_POST['event_name'];
				}

				if (isset($_POST['event_gate'])) {
					$pInfo['reservationInfo']['event_gate'] = $_POST['event_gate'];
				}

				if (isset($_POST['semester_name'])) {
					$pInfo['reservationInfo']['semester_name'] = $_POST['semester_name'];
				}

				if (isset($_POST['end_date'])) {
					$pInfo['reservationInfo']['end_date'] = $_POST['end_date'];
				}

				if (isset($_POST['rental_qty'])) {
					$pInfo['reservationInfo']['quantity'] = $_POST['rental_qty'];
				}

				$shippingInfo = array(
					'zonereservation',
					'zonereservation'
				);
				if (isset($_POST['rental_shipping']) && $_POST['rental_shipping'] !== false){
					$shippingInfo = explode('_', $_POST['rental_shipping']);
				}

				if(isset($_POST['start_date']) && isset($_POST['end_date']) && isset($_POST['days_before']) && isset($_POST['days_after'])){
					$reservationInfo = array(
						'shipping_module' => $shippingInfo[0],
						'shipping_method' => $shippingInfo[1],
						'start_date'      => $_POST['start_date'],
						'end_date'        => $_POST['end_date'],
						'days_before'     => $_POST['days_before'],
						'days_after'     => $_POST['days_after'],
						'quantity'        => $_POST['rental_qty']
					);
				}else{
					$reservationInfo = array(
						'shipping_module' => $pInfo['reservationInfo']['shipping']['module'],
						'shipping_method' => $pInfo['reservationInfo']['shipping']['id'],
						'start_date'      => $pInfo['reservationInfo']['start_date'],
						'end_date'        => $pInfo['reservationInfo']['end_date'],
						'days_before'     => $pInfo['reservationInfo']['days_before'],
						'days_after'     => $pInfo['reservationInfo']['days_after'],
						'quantity'        => $pInfo['reservationInfo']['quantity']
					);
				}


				if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
					if(isset($_POST['event_date']) && isset($_POST['event_name'])){
						$reservationInfo['event_date'] = $_POST['event_date'];
						$reservationInfo['event_name'] = $_POST['event_name'];
						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
							$reservationInfo['event_gate'] = $_POST['event_gate'];
						}
					}else{
						$reservationInfo['event_date'] = $pInfo['reservationInfo']['event_date'];
						$reservationInfo['event_name'] = $pInfo['reservationInfo']['event_name'];
						if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
							$reservationInfo['event_gate'] = $pInfo['reservationInfo']['event_gate'];
						}
					}
				}
				if(isset($_POST['semester_name'])){
					$reservationInfo['semester_name'] = $_POST['semester_name'];
				}else{
					$reservationInfo['semester_name'] = $pInfo['reservationInfo']['semester_name'];
				}

				$purchaseType = PurchaseTypeModules::getModule('reservation');
				$purchaseType->loadProduct($cartProduct->getIdString());

				$purchaseType->processAddToOrderOrCart($reservationInfo, $pInfo);

				EventManager::notify('ReservationProcessAddToCart', &$pInfo['reservationInfo']);
				EventManager::notify('PurchaseTypeAddToCart', $purchaseType->getCode(), &$pInfo, $purchaseType->pprInfo);

				$cartProduct->updateInfo($pInfo);
			}
		}
		
		public function CountContents(&$totalItems){
			global $order;
			if (is_object($order)){
				$reservationProducts = 0;
				if ($order->hasReservation() === true){
					$products = $order->products;
					for ($i=0, $n=sizeof($products); $i<$n; $i++) {
						if ($products[$i]['purchase_type'] == 'reservation'){
							$reservationProducts++;
						}
					}
				}

				if ($totalItems > 1){
					$totalItems -= $reservationProducts;
				}
			}
		}
		public function AddToCartAfterAction(ShoppingCartProduct &$cartProduct){
			global $messageStack, $ShoppingCart;
			if ($cartProduct->hasInfo('reservationInfo') === false){
				return;
			}
			
			$pID = $_GET['products_id'];
			$isRemoved = false;
			$shoppingProducts = $ShoppingCart->getProducts();

			for ($i=0;$i<count($shoppingProducts)-1;$i++){
				if(is_object($shoppingProducts[$i])){
					$shoppingInfo = $shoppingProducts[$i]->getInfo();
					if($shoppingInfo['products_id'] == $pID){
						$cartInfo = $shoppingProducts[$i]->getInfo();
						break;
					}
				}
			}
			//
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_DIFFERENT_SHIPPING_METHODS') == 'False'){
				//echo print_r($shoppingProducts->getContents());

				if (!empty($cartInfo['reservationInfo'])){
					for ($i=0;$i<count($shoppingProducts)-1;$i++){

						$shoppingInfo = $shoppingProducts[$i]->getInfo();
						if (!empty($shoppingInfo['reservationInfo']) && $shoppingInfo['products_id'] != $pID){
							if ($cartInfo['reservationInfo']['shipping']['id'] != $shoppingInfo['reservationInfo']['shipping']['id']){
								$isRemoved = true;
							}
						}
					}
				}
				if ($isRemoved){
					$ShoppingCart->remove($pID);
					$messageStack->addSession('pageStack','You cannot add products with different level of service on the same order','error');
				}
			}
			if ($isRemoved === false){
				if (!empty($cartInfo['reservationInfo'])){
					$purchaseTypeClass = PurchaseTypeModules::getModule('reservation');
					$purchaseTypeClass->loadProduct($pID);
					$shippingArray = $purchaseTypeClass->getEnabledShippingMethods();

					if (is_array($shippingArray) && !in_array($cartInfo['reservationInfo']['shipping']['id'], $shippingArray) && !$purchaseTypeClass->shippingIsNone() && !$purchaseTypeClass->shippingIsStore()){
						$ShoppingCart->remove($pID, 'reservation');
						$messageStack->addSession('pageStack','You are not allowed to use this level of service with this product. Please choose another level of service','error');
					}
				}
			}
		}
	}
?>