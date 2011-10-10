<?php
if (isset($_POST['items']) && !empty($_POST['items'])){
	$error = false;
	$badCodes = array();
	foreach($_POST['items'] as $barcode){
		$Qcheck = mysql_query('select count(*) as total from products_inventory_barcodes where barcode = "' . $barcode . '"');
		$check = mysql_fetch_assoc($Qcheck);
		if ($check['total'] <= 0){
			$error = true;
			$badCodes[] = $barcode;
		}
	}

	if ($error === false){
		if (empty($_POST['tracking_number'])){
			$error = true;
		}

		if ($error === false){
			$toStoreId = $_POST['to_store_id'];
			$fromStoreId = $_POST['from_store_id'];
			$trackingNum = $_POST['tracking_number'];
			foreach($_POST['items'] as $barcode){
				mysql_query('update products_inventory_barcodes_transfers set is_history = 1 where barcode = "' . $barcode . '" and is_history = 0');
				mysql_query('insert into products_inventory_barcodes_transfers ' .
						'(barcode, status, origin_id, destination_id, date_added, tracking_number)' .
						' values ' .
						'("' . $barcode . '", "S", "' . $fromStoreId . '", "' . $toStoreId . '", now(), "' . $trackingNum . '")'
				);
			}
		}else{
			$response = array(
				'success' => true,
				'hasErrors' => true,
				'errorMessage' => 'UPS Tracking Number Is Required'
			);
		}
	}else{
		$response = array(
			'success' => true,
			'hasErrors' => true,
			'errorMessage' => 'Unknown Barcode(s) Supplied (' . implode(', ', $badCodes) . ')'
		);
	}
}else{
	$response = array(
		'success' => true,
		'hasErrors' => true,
		'errorMessage' => 'No Barcodes Supplied For Shipment'
	);
}

EventManager::attachActionResponse($response, 'json');
