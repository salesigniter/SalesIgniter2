<?php

if(isset($_POST['admins'])){

	$MaintenancePeriodsToStores = new MaintenancePeriodsToStores();
	foreach($_POST['admins'] as $store => $admin){
		$MaintenancePeriodsToStores = Doctrine_Core::getTable('MaintenancePeriodsToStores');
		//if (isset($_GET['mID'])){
		$maintenancePeriodToStore = $MaintenancePeriodsToStores->findOneByMaintenancePeriodIdAndStoresId($maintenancePeriod->maintenance_period_id, $store);
		//}else{
		if(!$maintenancePeriodToStore){
			$maintenancePeriodToStore = new MaintenancePeriodsToStores();
		}


		$maintenancePeriodToStore->maintenance_period_id = $maintenancePeriod->maintenance_period_id;
		$maintenancePeriodToStore->stores_id = $store;
		$maintenancePeriodToStore->assign_to = implode(',',$admin);
		$maintenancePeriodToStore->save();
	}

}



?>