<?php
	$pID_string = $_POST['products_id'];
	$purchaseTypeClasses = array();	
	$purchaseTypeClass = PurchaseTypeModules::getModule('reservation');
	foreach($pID_string as $pElem){	
		$purchaseTypeClass->loadProduct($pElem);
		$purchaseTypeClasses[] = $purchaseTypeClass;
	}
	$calendar = ReservationUtilities::getCalendar($pID_string, $purchaseTypeClasses, (isset($_POST['rental_qty'])?$_POST['rental_qty']:1), true);

	EventManager::attachActionResponse(array(
		'success' => true,
		'calendar' => $calendar
	), 'json');
?>