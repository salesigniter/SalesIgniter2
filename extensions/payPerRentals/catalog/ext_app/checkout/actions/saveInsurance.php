<?php
	foreach ($ShoppingCart->getProducts() as $cartProduct){
		if ($cartProduct->hasInfo('ReservationInfo')){
			$pInfo = $cartProduct->getInfo();
			$pID = $cartProduct->getIdString();
			if ($pInfo['ReservationInfo']['start_date'] == $_GET['start_date'] && $pInfo['ReservationInfo']['end_date'] == $_GET['end_date'] && $pID == $_GET['pID']){

				if( isset($pInfo['ReservationInfo']['insurance']) && $pInfo['ReservationInfo']['insurance'] > 0){
					$pInfo['ReservationInfo']['insurance'] = 0;
				}else{
					$payPerRentals = Doctrine_Query::create()
							         ->select('insurance')
									->from('ProductsPayPerRental')
									->where('products_id = ?', $pID)
									->fetchOne();
					$pInfo['ReservationInfo']['insurance'] = $payPerRentals->insurance;//getInsurance from db
				}
				$ShoppingCart->updateProduct($pID, $pInfo);
			}
		}
	}
	
	EventManager::attachActionResponse(itw_app_link(null, 'checkout', 'default', sysConfig::get('REQUEST_TYPE')), 'redirect');
?>