<?php
if (
	isset($_POST['products_type']) &&
	(!is_array($_POST['products_type']) && $_POST['products_type'] == 'rental') ||
	(is_array($_POST['products_type']) && in_array('rental', $_POST['products_type']))
){
	$PurchaseType = PurchaseTypeModules::getModule('rental');

	$Product->ProductsRentalSettings->rental_period = $PurchaseType->getConfigData('RENTAL_PERIOD');
}else{
	try {
		$Product->ProductsRentalSettings->remove();
	} catch (Exception $e){
		
	}
}
$Product->save();
