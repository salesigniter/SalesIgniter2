<?php
/*
	PPR Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

	$comments = $_POST['comments'];
	$price = $_POST['price'];
	$admin = Session::get('login_id');
	$date = date('Y-m-d H:i:s');

	/*$Maintenances = Doctrine_Core::getTable('PayPerRentalMaintenanceRepairs');
	$Maintenance = $Maintenances->findOneByBarcodeId((int) $_GET['mID']);
	if(!$Maintenance){*/
	$Maintenance = new PayPerRentalMaintenanceRepairs;
	//}
	$Maintenance->comments = $comments;
	$Maintenance->price = $price;
	$Maintenance->barcode_id = $_GET['mID'];
	$Maintenance->admin_id = $admin;
	$Maintenance->repair_date = $date;


	$BarcodeHistoryRented = Doctrine_Core::getTable('BarcodeHistoryRented')->find((int) $_GET['mID']);
	$BarcodeHistoryRented->current_maintenance_cond = '3';
	$BarcodeHistoryRented->save();
	$Maintenance->maintenance_date = $BarcodeHistoryRented->last_maintenance_date;
	$Maintenance->maintenance_period_id = $BarcodeHistoryRented->last_maintenance_type;
	$Maintenance->save();

$PayPerRentalMaintenanceRepairParts = Doctrine_Core::getTable('PayPerRentalMaintenanceRepairParts');
Doctrine_Query::create()
	->delete('PayPerRentalMaintenanceRepairParts')
	->andWhere('pay_per_rental_maintenance_repairs_id =?', $Maintenance->pay_per_rental_maintenance_repairs_id)
	->execute();

if(isset($_POST['parts'])){
	foreach($_POST['parts'] as $prodevid => $iprodev){
		$PayPerRentalMaintenanceRepairPart = $PayPerRentalMaintenanceRepairParts->create();
		$PayPerRentalMaintenanceRepairPart->part_name = $iprodev['part_name'];
		$PayPerRentalMaintenanceRepairPart->part_price = $iprodev['part_price'];
		$PayPerRentalMaintenanceRepairPart->pay_per_rental_maintenance_repairs_id = $Maintenance->pay_per_rental_maintenance_repairs_id;
		$PayPerRentalMaintenanceRepairPart->save();
	}
}


EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $_GET['mID'], null, 'repairs'), 'redirect');
?>