<?php
 require('includes/application_top.php');

$QMaintenancePeriod = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods ppmp')
	->where('before_send = ?', '1')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
 	$mAdmins = array();
foreach($QMaintenancePeriod as $mPeriod){
	//current_maintenance_type must be 0
	//check if before send and current date - number of hours <= send_date then email sent and change type.-- this should be run every hour.
	$Qmaint = Doctrine_Query::create()
		->from('OrdersProductsReservation opr')
		->leftJoin('opr.ProductsInventoryBarcodes pib')
		->leftJoin('pib.BarcodeHistoryRented bhr')
		->where('DATE_SUB(NOW(), INTERVAL '.$mPeriod['hours_before_send'].' DAY) <= DATE_SUB(opr.start_date, INTERVAL opr.shipping_days_before DAY)')
		->whereIn('opr.orders_products_reservations_id', (isset($_POST['sendRes'])?$_POST['sendRes']:array()))
		->andWhere('opr.rental_state = ?','reserved')
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	foreach($Qmaint as $bhr){
		if($bhr['ProductsInventoryBarcodes']['BarcodeHistoryRented']['current_maintenance_type'] != 0) continue;

		$ProductBarcode = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find($bhr['barcode_id']);

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

		$multiStore = $appExtension->getExtension('multiStore');
		if ($multiStore !== false && $multiStore->isEnabled() === true){
			$storeBarcode = Doctrine_Core::getTable('ProductsInventoryBarcodesToStores')->findOneByBarcodeId($bhr['barcode_id']);
			$storeId = $storeBarcode->stores_id;
			$adminStores = Doctrine_Core::getTable('MaintenancePeriodsToStores')->findOneByMaintenancePeriodIdAndStoresId($mPeriod['maintenance_period_id'], $storeId);
			$mAdmins = array_merge($mAdmins, explode(',',$adminStores->assign_to));
		}

	}


	if(count($mAdmins) > 0){
		//$mAdmins = explode(',',$mPeriod['assign_to']);
		foreach($mAdmins as $admin_id){
			$Admin = Doctrine_Core::getTable('Admin')->find($admin_id);
			$emailEvent = new emailEvent('maintenance_item', Session::get('languages_id'));
			$emailEvent->setVar('admin_name', $Admin->admin_firstname . ' ' .$Admin->admin_lastname);
			$emailEvent->setVar('url', itw_app_link('appExt=payPerRentals&type='.$mPeriod['maintenance_period_id'],'maintenance','default'));
			$emailEvent->sendEmail(array(
					'email' => $Admin->admin_email_address,
					'name'  => $Admin->admin_firstname
				));
		}
	}
}


require('includes/application_bottom.php');
?>