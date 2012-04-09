<?php
$Rental = Doctrine_Core::getTable('OrdersProductsRentals')
	->find((int) $_GET['orders_products_rentals_id']);
if ($Rental && $Rental->count() > 0){
	$status = true;
	$statusMsg = sysLanguage::get('TEXT_SUCCESS_RENTAL_SENT');
	
	$PurchaseType = PurchaseTypeModules::getModule('rental');
	
	$Rental->rental_state = $PurchaseType->getConfigData('RENTAL_STATUS_OUT');
	$Rental->date_shipped = date(DATE_TIMESTAMP);
	if ($Rental->barcode_id > 0){
		$Rental->ProductsInventoryBarcodes->status = 'O';
	}else{
		$Rental->ProductsInventoryQuantity->reserved -= 1;
		$Rental->ProductsInventoryQuantity->qty_out += 1;
	}
	$Rental->save();

	EventManager::notify('PurchaseTypeRentalOnSend', $Rental, &$statusMsg);
}else{
	$status = false;
	$statusMsg = sysLanguage::get('TEXT_ERROR_RENTAL_NOT_FOUND');
}

EventManager::attachActionResponse(array(
	'status'       => $status,
	'statusMsg'    => $statusMsg,
	'rental_state' => tep_translate_order_statuses($Rental->rental_state),
	'date_shipped' => date(sysLanguage::getDateFormat(), strtotime($Rental->date_shipped))
), 'json');
