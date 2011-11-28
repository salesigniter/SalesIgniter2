<?php
 require('includes/application_top.php');

$QMaintenancePeriod = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods')
	->where('before_send = ?', '0')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
    $mAdmins = array();
foreach($QMaintenancePeriod as $mPeriod){

	$Qmaint = Doctrine_Query::create()
		->from('BarcodeHistoryRented bhr');

	if($mPeriod['show_number_rentals'] > 0){
		$Qmaint->where('bhr.number_rents >= ?', $mPeriod['show_number_rentals']);
	}
	if($mPeriod['show_number_days'] > 0 && $mPeriod['maintenance_period_start_date'] != '0000-00-00 00:00:00'){
		$Qmaint->orWhere('bhr.last_maintenance_date is null AND DATE_SUB(NOW(), INTERVAL '.$mPeriod['show_number_days'].' DAY)<='.$mPeriod['maintenance_period_start_date']);
	}
	if($mPeriod['show_number_days'] > 0){
		$Qmaint->orWhere('DATE_SUB(NOW(), INTERVAL '.$mPeriod['show_number_days'].' DAY) <= bhr.last_maintenance_date');
	}

	$Qmaint = $Qmaint->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	foreach($Qmaint as $bhr){
		if($bhr['current_maintenance_type'] != 0) continue;
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

	$Qmaint2 = Doctrine_Query::create()
		->from('BarcodeHistoryRented bhr');

	if($mPeriod['quarantine_number_rentals'] > 0){
		$Qmaint2->where('bhr.number_rents >= ?', $mPeriod['quarantine_number_rentals']);
	}
	if($mPeriod['quarantine_number_days'] > 0 && $mPeriod['maintenance_period_start_date'] != '0000-00-00 00:00:00'){
		$Qmaint2->orWhere('bhr.last_maintenance_date is null AND DATE_SUB(NOW(), INTERVAL '.$mPeriod['quarantine_number_days'].' DAY)<='.$mPeriod['maintenance_period_start_date']);
	}
	if($mPeriod['quarantine_number_days'] > 0){
		$Qmaint2->orWhere('DATE_SUB(NOW(), INTERVAL '.$mPeriod['quarantine_number_days'].' DAY) <= bhr.last_maintenance_date');
	}

	$Qmaint2 = $Qmaint2->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	foreach($Qmaint2 as $bhr){
		$ProductBarcode = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find($bhr['barcode_id']);
		$ProductBarcode->status = 'M';

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