<?php
	$pID_string	= array();
	$purchaseTypeClasses = array();
	$OrderProduct = $Editor->ProductManager->get((int)$_POST['id']);
	$pInfo = $OrderProduct->getInfo();
	$pID_string[] = $pInfo['products_id'];
	$purchaseTypeClasses[] = $OrderProduct->getProductTypeClass()->getPurchaseType();

	$usableBarcodes = array();
	if(isset($_POST['barcode']) && ($_POST['barcode'] != 'undefined')){
		$usableBarcodes[] = $_POST['barcode'];
	}

	$pInfo = $OrderProduct->getInfo();
	$pInfo['usableBarcodes'] = $usableBarcodes;
	$OrderProduct->setInfo($pInfo);


	$calendar = ReservationUtilities::getCalendar(
		$OrderProduct->getProductsId(),
		$purchaseTypeClasses,
		$OrderProduct->getQuantity(),
	true,
	'catalog',
	$usableBarcodes,
	false
);

EventManager::attachActionResponse(array(
		'success' => true,
		'calendar' => $calendar
	), 'json');
?>
