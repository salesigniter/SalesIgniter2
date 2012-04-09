<?php
foreach($_POST['items'] as $oprID){
	$Rental = Doctrine_Core::getTable('OrdersProductsRentals')
		->find((int) $oprID);
	if ($Rental && $Rental->count() > 0){
		$PurchaseType = PurchaseTypeModules::getModule('rental');

		$Rental->rental_state = $PurchaseType->getConfigData('RENTAL_STATUS_RETURNED');
		$Rental->date_returned = date(DATE_TIMESTAMP);

		if ($Rental->barcode_id > 0){
			$Rental->ProductsInventoryBarcodes->status = 'A';
		}else{
			$Rental->ProductsInventoryQuantity->available += 1;
			$Rental->ProductsInventoryQuantity->qty_out -= 1;
		}
		$Rental->save();

		EventManager::notify('PurchaseTypeRentalOnReturn', $Rental, $PurchaseType, &$statusMsg);
	}
}

EventManager::attachActionResponse(array(
		'success' => true
	), 'json');
