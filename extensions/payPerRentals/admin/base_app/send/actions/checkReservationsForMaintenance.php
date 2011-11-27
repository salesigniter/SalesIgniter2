<?php
/*
 here i check every reservation barcode and see if it was pre hire checked.
 if not it will return an array of barcodes to show a dialog with good - bad for every dialog when bad is checked does the same thing.

*/


	$QMaintenancePeriod = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods ppmp')
	->where('before_send = ?', '1')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

    $prehire = 0; //only one prehire check supported for now
	$lastBarcode = -1;
	foreach($QMaintenancePeriod as $mPeriod){
	//current_maintenance_type must be 0
	//check if before send and current date - number of hours <= send_date then email sent and change type.-- this should be run every hour.
	//i have to make the other way arround ..and check opr first and bhr
	$Qmaint = Doctrine_Query::create()
		->from('OrdersProductsReservation opr')
		->leftJoin('opr.ProductsInventoryBarcodes pib')
		->leftJoin('pib.BarcodeHistoryRented bhr')
		//->where('DATE_SUB(NOW(), INTERVAL '.$mPeriod['hours_before_send'].' DAY) <= DATE_SUB(opr.start_date, INTERVAL opr.shipping_days_before DAY)')
		->whereIn('opr.orders_products_reservations_id', (isset($_POST['sendRes'])?$_POST['sendRes']:array()))
		->andWhere('opr.rental_state = ?','reserved')
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	foreach($Qmaint as $bhr){
		if($bhr['ProductsInventoryBarcodes']['BarcodeHistoryRented']['current_maintenance_type'] != 0) continue;
		$ProductBarcode = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find($bhr['barcode_id']);
		$lastBarcode = $bhr['barcode_id'];
		if($mPeriod['quarantine_until_completed'] == '1'){
			$ProductBarcode->status = 'M';
		}

		$barcodeHistory = Doctrine_Core::getTable('BarcodeHistoryRented')->find($ProductBarcode->barcode_id);
		if(!$barcodeHistory){
			$barcodeHistory = new BarcodeHistoryRented();
			$barcodeHistory->barcode_id = $ProductBarcode->barcode_id;
			$barcodeHistory->save();
		}
		$barcodeHistory->current_maintenance_type = $mPeriod['maintenance_period_id'];
		$barcodeHistory->save();

		$ProductBarcode->save();

	}
		$prehire = $mPeriod['maintenance_period_id'];
}


	$popupContent = '';
	ob_start();
	$_GET['type'] = $prehire;
	$_GET['dialog'] = true;
	$_GET['lastBarcode'] = $lastBarcode;
	if (file_exists(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/admin/base_app/maintenance/language_defines/global.xml')){
		sysLanguage::loadDefinitions(sysConfig::getDirFsCatalog() . 'extensions/payPerRentals/admin/base_app/maintenance/language_defines/global.xml');
	}
	require(sysConfig::getDirFsCatalog(). 'extensions/payPerRentals/admin/base_app/maintenance/pages/default.php');

	$popupContent = ob_get_contents();
	ob_end_clean();




	EventManager::attachActionResponse(array(
			'success' => true,
			'popupContent' => $popupContent
		), 'json');

?>