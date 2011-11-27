<?php

	$maintenancePeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods');
	if (isset($_GET['mID'])){
		$maintenancePeriod = $maintenancePeriods->find((int)$_GET['mID']);
	}else{
		$maintenancePeriod = new PayPerRentalMaintenancePeriods();
	}


$maintenancePeriod->maintenance_period_name = $_POST['maintenance_period_name'];
$maintenancePeriod->maintenance_period_description = $_POST['maintenance_period_description'];
$maintenancePeriod->maintenance_period_start_date = $_POST['maintenance_period_start_date'];
$maintenancePeriod->before_send = isset($_POST['before_send'])?1:0;
$maintenancePeriod->hours_before_send = $_POST['hours_before_send'];
$maintenancePeriod->after_return = isset($_POST['after_return'])?1:0;
$maintenancePeriod->is_repair = isset($_POST['is_repair'])?1:0;
$maintenancePeriod->hours_after_return = $_POST['hours_after_return'];
$maintenancePeriod->quarantine_until_completed = isset($_POST['quarantine_until_completed'])?1:0;
$maintenancePeriod->email_admin = isset($_POST['email_admin'])?1:0;
$maintenancePeriod->show_number_days = $_POST['show_number_days'];
$maintenancePeriod->show_number_rentals = $_POST['show_number_rentals'];
$maintenancePeriod->quarantine_number_days = $_POST['quarantine_number_days'];
$maintenancePeriod->quarantine_number_rentals = $_POST['quarantine_number_rentals'];

$maintenancePeriod->save();

	EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $maintenancePeriod->maintenance_period_id, null, 'default'), 'redirect');
?>