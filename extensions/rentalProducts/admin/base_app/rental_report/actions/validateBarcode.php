<?php
$oprId = '';
$QbarcodeId = mysql_query('select barcode_id from products_inventory_barcodes where barcode = "' . addslashes(strip_tags($_GET['code'])) . '"');
if (mysql_num_rows($QbarcodeId) > 0){
	$Barcode = mysql_fetch_assoc($QbarcodeId);

	$PurchaseType = PurchaseTypeModules::getModule('rental');

	$Qcheck = mysql_query('select orders_products_rentals_id from orders_products_rentals where barcode_id = "' . $Barcode['barcode_id'] . '" and rental_state = "' . $PurchaseType->getConfigData('RENTAL_STATUS_OUT') . '"');
	if (mysql_num_rows($Qcheck) <= 0){
		$isValid = false;
	}else{
		$check = mysql_fetch_assoc($Qcheck);
		$isValid = true;
		$oprId = $check['orders_products_rentals_id'];
	}
}else{
	$isValid = false;
}

EventManager::attachActionResponse(array(
		'success' => true,
		'isValid' => $isValid,
		'oprId' => $oprId
	), 'json');