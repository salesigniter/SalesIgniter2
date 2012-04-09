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
    $removed = -1;
$QMaintenancePeriods = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods')
	->where('maintenance_period_id = ?', $_GET['type'])
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

if ($QMaintenancePeriods[0]['is_repair'] == '1') {
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
	}else{

    $BarcodeHistoryRented = Doctrine_Core::getTable('BarcodeHistoryRented')->find((int) $_GET['mID']);
	$curMaintenance = $BarcodeHistoryRented->current_maintenance_type;

	if($type > 0){
        $BarcodeHistoryRented->last_maintenance_date = $date;
		$BarcodeHistoryRented->last_maintenance_type = $type;
		$BarcodeHistoryRented->current_maintenance_type = 0;
	}
    $BarcodeHistoryRented->current_maintenance_cond = $cond;      //2 - to be repaired //3 - repaired // 1 - good
    $removed = '';
	if($cond == 2){
		//if current_maintenance_type is before_send it will have to assign a new barcode id to ordersproductsreservation of the current barcode id
		if(isset($_POST['opr']) && isset($_POST['availBarcodes'])){
			$OrdersProductsReservation = Doctrine_Core::getTable('OrdersProductsReservation')->find($_POST['opr']);
			$OrdersProductsReservation->barcode_id = $_POST['availBarcodes'];
			$OrdersProductsReservation->save();
			$BarcodeHistoryRentedNew = Doctrine_Core::getTable('BarcodeHistoryRented')->find($_POST['availBarcodes']);
			if(!$BarcodeHistoryRentedNew){
				$BarcodeHistoryRentedNew = new BarcodeHistoryRented();
				$BarcodeHistoryRentedNew->barcode_id = $_POST['availBarcodes'];
			}
			$BarcodeHistoryRentedNew->current_maintenance_type = 0;
			$BarcodeHistoryRentedNew->last_maintenance_date = $date;
			$BarcodeHistoryRentedNew->last_maintenance_type = $type;
			$BarcodeHistoryRentedNew->current_maintenance_cond = 1;
			$BarcodeHistoryRentedNew->save();
		}
		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find((int) $_GET['mID']);
		$ProductsInventoryBarcodes->status = 'M';
		$ProductsInventoryBarcodes->save();
		$BarcodeHistoryRented->current_maintenance_comments = $comments;

		$QMaintenancePeriodList = Doctrine_Query::create()
			->from('PayPerRentalMaintenancePeriods')
			->where('is_repair = ?', '1')
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
		$mAdmins = array();
		foreach($QMaintenancePeriodList as $mPeriod){

			$multiStore = $appExtension->getExtension('multiStore');
			if ($multiStore !== false && $multiStore->isEnabled() === true){
				$storeBarcode = Doctrine_Core::getTable('ProductsInventoryBarcodesToStores')->findOneByBarcodeId((int) $_GET['mID']);
				$storeId = $storeBarcode->inventory_store_id;
				$adminStores = Doctrine_Core::getTable('MaintenancePeriodsToStores')->findOneByMaintenancePeriodIdAndStoresId($mPeriod['maintenance_period_id'], $storeId);
				$mAdmins = array_merge($mAdmins, explode(',',$adminStores->assign_to));
			}

			if(count($mAdmins) > 0){
				//$mAdmins = explode(',',$mPeriod['assign_to']);
				foreach($mAdmins as $admin_id){
					$Admin = Doctrine_Core::getTable('Admin')->find($admin_id);
					$emailEvent = new emailEvent('maintenance_item', Session::get('languages_id'));
					$emailEvent->setVar('admin_name', $Admin->admin_firstname . ' ' .$Admin->admin_lastname);
					$emailEvent->setVar('url', itw_app_link('appExt=payPerRentals&type='.$mPeriod['maintenance_period_id'],'maintenance','repairs'));
					$emailEvent->sendEmail(array(
							'email' => $Admin->admin_email_address,
							'name'  => $Admin->admin_firstname
						));
				}
			}
		}
		$removed = $_GET['mID'];

	}else{
		$ProductsInventoryBarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find((int) $_GET['mID']);
		$ProductsInventoryBarcodes->status = 'A';
		$ProductsInventoryBarcodes->save();
	}
	$BarcodeHistoryRented->save();
	$removed = $_GET['mID'];
	}

	//$link = itw_app_link(tep_get_all_get_params(array('action', 'mID')) . 'mID=' . $_GET['mID'], null, 'default');
	EventManager::attachActionResponse(array(
		'success' => true,
		'removed' => $removed
	), 'json');
    //EventManager::attachActionResponse($link, 'redirect');
?>