<?php
//verify current maintenance type of mid if is not before send isBefore false
//find all available barcodes excluding the current mid

$BarcodeHistoryRented = Doctrine_Core::getTable('BarcodeHistoryRented')->find((int) $_GET['mID']);
$curMaintenance = $BarcodeHistoryRented->current_maintenance_type;

$QPeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->find($curMaintenance);

$isBefore = false;
$htmlSelect = '';
if($QPeriods->before_send == '1'){
	$Qmaint = Doctrine_Query::create()
		->from('OrdersProductsReservation opr')
		->leftJoin('opr.ProductsInventoryBarcodes pib')
		->leftJoin('pib.BarcodeHistoryRented bhr')
		//->where('DATE_SUB(NOW(), INTERVAL '.$QPeriods->hours_before_send.' DAY) <= DATE_SUB(opr.start_date, INTERVAL opr.shipping_days_before DAY)')
		->andWhere('opr.rental_state = ?','reserved')
		->andWhere('opr.barcode_id = ?', $_GET['mID'])
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	$QProduct = Doctrine_Query::create()
	->from('ProductsInventory pi')
	->leftJoin('pi.ProductsInventoryBarcodes pib')
	->where('pib.barcode_id = ?', $_GET['mID'])
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$pID = $QProduct[0]['products_id'];

	 $PurchaseType = PurchaseTypeModules::getModule('reservation');

	 $PurchaseType->loadProduct($pID);
	 $invItems = $PurchaseType->getInventoryItems();
	 $dropdownValues = array();
	 $excludedVal = array($_GET['mID']);
	 $opr = $Qmaint[0];

	while(true){
		$barcode_id = getAvailableBarcode($opr, $invItems, $excludedVal);
		if($barcode_id == -1) break;
		$dropdownValues[] = $barcode_id;
		$excludedVal[] = $barcode_id;
	}

	$htmlSelect = '<input type="hidden" name="opr" value="'.$opr['orders_products_reservations_id'].'" /><select id="availBarcodes" name="availBarcodes"><option value="0">Please select</option>';

	foreach($dropdownValues as $dVal){
		 $ProductBarcode = Doctrine_Core::getTable('ProductsInventoryBarcodes')->find($dVal);
		 $htmlSelect .= '<option value="'.$dVal.'">'.$ProductBarcode->barcode.'</option>';
	}

	$htmlSelect .= '</select>';
	$isBefore = true;
}

 function getAvailableBarcode($opr, $invItems, $excluded){

			$shippingDaysBefore = $opr['shipping_days_before'];
			$shippingDaysAfter  = $opr['shipping_days_after'];


			$startArr = date_parse($opr['start_date']);
			$startDate = mktime($startArr['hour'],$startArr['minute'],$startArr['second'],$startArr['month'],$startArr['day']-$shippingDaysBefore,$startArr['year']);

			$endArr = date_parse($opr['end_date']);
			$endDate = mktime($endArr['hour'],$endArr['minute'],$endArr['second'],$endArr['month'],$endArr['day']+$shippingDaysAfter,$endArr['year']);
			$barcodeID = -1;
			foreach($invItems as $barcodeInfo){


					if (in_array($barcodeInfo['id'], $excluded)){
						continue;
					}

					$bookingInfo = array(
						'item_type'               => 'barcode',
						'item_id'                 => $barcodeInfo['id'],
						'start_date'              => $startDate,
						'end_date'                => $endDate
					);

					$bookingInfo['quantity'] = 1;

					$bookingCount = ReservationUtilities::CheckBooking($bookingInfo);
					if ($bookingCount <= 0 || sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_STOCK') == 'True'){
						$barcodeID = $barcodeInfo['id'];
						break;
					}
			}
		return $barcodeID;
	}

EventManager::attachActionResponse(array(
		'success' => true,
		'isBefore' => $isBefore,
		'dropDown' => $htmlSelect
	), 'json');

?>