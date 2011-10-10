<?php
$Qbarcode = Doctrine_Core::getTable('ProductsInventoryBarcodesTransfers')
	->findOneByBarcodeAndStatus($_GET['value'], 'S');
if ($Qbarcode){
	if ($Qbarcode->tracking_number != ''){
		$barcodes = array();
		
		$Qbarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodesTransfers')
			->findByTrackingNumber($Qbarcode->tracking_number);
		foreach($Qbarcodes as $bInfo){
			$barcodes[] = $bInfo->toArray();
		}
	}else{
		$barcodes = $Qbarcode->toArray();
	}
	$response = array(
		'success' => true,
		'hasError' => false,
		'barcodes' => $barcodes
	);
}else{
	$response = array(
		'success' => true,
		'hasError' => true,
		'errorMessage' => 'Active Shipment Not Found For Barcode: ' . $_GET['value']
	);
}

EventManager::attachActionResponse($response, 'json');
