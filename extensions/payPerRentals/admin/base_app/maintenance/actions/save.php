<?php
/*
	PPR Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/
	$comments = $_POST['comments'];
	$type = $_GET['type'];
	$cond = (($_POST['cond'] == 'g')?1:2);
	$admin = Session::get('login_id');
	$date = date('Y-m-d H:i:s');

	$Maintenances = Doctrine_Core::getTable('PayPerRentalMaintenance');
    $BarcodeHistoryRented = Doctrine_Core::getTable('BarcodeHistoryRented')->find($Maintenances->find((int) $_GET['mID'])->barcode_id);
	if($type == 1){
		if (isset($_GET['mID'])){
			$Maintenance = $Maintenances->find((int) $_GET['mID']);
		}
		$BarcodeHistoryRented->last_maintenance_date = $date;
	}elseif($type == 2 || $type == 3){
		$Maintenance = new PayPerRentalMaintenance;
		$Maintenance->barcode_id = $Maintenances->find((int) $_GET['mID'])->barcode_id;
		if($type == 2){
			$BarcodeHistoryRented->last_biweekly_date = $date;
		}else{
			$BarcodeHistoryRented->last_monthly_date = $date;
		}
	}else{
		//make number_rents = 0
		$BarcodeHistoryRented->last_quarantine_date = $date;
		$BarcodeHistoryRented->number_rents = 0;
		//add last_quarantine_date to table, lastbieweekly date, and last maintenance date
		$Maintenance = new PayPerRentalMaintenance;
		$Maintenance->barcode_id = $Maintenances->find((int) $_GET['mID'])->barcode_id;
	}
	$BarcodeHistoryRented->save();

	$Maintenance->comments = $comments;
	$Maintenance->admin_id = $admin;
	$Maintenance->type = $type;
	$Maintenance->maintenance_date = $date;
    $Maintenance->cond = $cond;
	$Maintenance->save();

	if($cond == 2){
		$Repairs = new PayPerRentalMaintenanceRepairs;
		$Repairs->pay_per_rental_maintenance_id = $Maintenance->pay_per_rental_maintenance_id;
		$Repairs->save();
		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find($Maintenance->barcode_id);
		$ProductsInventoryBarcodes->status = 'M';
		$ProductsInventoryBarcodes->save();
	}else{
		//make barcode available
		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find($Maintenance->barcode_id);
		$ProductsInventoryBarcodes->status = 'A';
		$ProductsInventoryBarcodes->save();
	}
	switch($type){
		case 1:	$link = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $Maintenance->pay_per_rental_maintenance_id, null, 'default');
			break;
		case 2:	$link = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $Maintenance->pay_per_rental_maintenance_id, null, 'biweek');
		           break;
		case 3:	$link = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $Maintenance->pay_per_rental_maintenance_id, null, 'monthly');
		           break;
		case 4:	$link = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $Maintenance->pay_per_rental_maintenance_id, null, 'quarantine');
		           break;
	}

    EventManager::attachActionResponse($link, 'redirect');
?>