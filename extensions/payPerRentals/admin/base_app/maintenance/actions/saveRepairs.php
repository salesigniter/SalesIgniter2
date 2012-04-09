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

	$Maintenances = Doctrine_Core::getTable('PayPerRentalMaintenanceRepairs');
	if (isset($_GET['mID'])){
		$Maintenance = $Maintenances->find((int) $_GET['mID']);
	}

	$Maintenance->comments = $comments;
	$Maintenance->price = $price;
	$Maintenance->admin_id = $admin;
	$Maintenance->repair_date = $date;
	$Maintenance->save();

$PayPerRentalMaintenanceRepairParts = Doctrine_Core::getTable('PayPerRentalMaintenanceRepairParts');
Doctrine_Query::create()
	->delete('PayPerRentalMaintenanceRepairParts')
//->whereNotIn('price_per_rental_per_products_id', $saveArray)
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


EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $Maintenance->pay_per_rental_maintenance_id, null, 'repairs'), 'redirect');
?>