<?php
$isRemove = false;
foreach ($ShoppingCart->getProducts() as $cartProduct){
		if ($cartProduct->hasInfo('ReservationInfo')){
			$pInfo = $cartProduct->getInfo();
			$pID = $cartProduct->getIdString();
			if (isset($_POST['insure_all_products']) || (isset($_POST['insure_product']) && array_search($pID, $_POST['insure_product']) !== false)){
				$payPerRentals = Doctrine_Query::create()
						         ->select('insurance')
				   				 ->from('ProductsPayPerRental')
								 ->where('products_id = ?', $pID)
								 ->fetchOne();

				if (!isset($pInfo['ReservationInfo']['insurance']) || (isset($pInfo['ReservationInfo']['insurance']) && $pInfo['ReservationInfo']['insurance'] == 0)){
					$pInfo['ReservationInfo']['insurance'] = $payPerRentals->insurance;//getInsurance from db
					$isRemove = true;
				}else{
					$pInfo['ReservationInfo']['insurance'] = 0;
					$isRemove = false;
				}
				$ShoppingCart->updateProduct($pID, $pInfo);
			}
		}
	}

    ob_start();
	require(sysConfig::getDirFsCatalog() . 'applications/checkout/pages/cart.php');
	$pageHtml = ob_get_contents();
	ob_end_clean();

	EventManager::attachActionResponse(array(
		'success' => true,
		'pageHtml' => $pageHtml,
		'isRemove' => $isRemove
	), 'json');

?>