<?php
	$pID_string = $_POST['reservation_products_id'];
	$purchaseTypeClasses = array();	
	$purchaseTypeClass = PurchaseTypeModules::getModule('reservation');
	foreach($pID_string as $pElem){	
		$purchaseTypeClass->loadProduct($pElem);
		$purchaseTypeClasses[] = $purchaseTypeClass;
	}
	$calendar = ReservationUtilities::getCalendar(array(
		'purchaseTypeClasses' => $purchaseTypeClasses,
		'quantity' => (isset($_POST['rental_qty'])?$_POST['rental_qty']:1),
		'calanderMonths' => 5
	));

	EventManager::attachActionResponse(array(
		'success' => true,
		'calendar' => $calendar
	), 'json');
?>