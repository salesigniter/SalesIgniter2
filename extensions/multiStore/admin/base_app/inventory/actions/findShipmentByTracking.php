<?php
$Qbarcodes = Doctrine_Core::getTable('ProductsInventoryBarcodesTransfers')
	->findByTrackingNumber($_GET['value']);
if ($Qbarcodes){
	$barcodes = array();

	foreach($Qbarcodes as $bInfo){
		$barcodes[] = $bInfo->toArray();
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
		'errorMessage' => 'Active Shipment Not Found For Tracking Number: ' . $_GET['value']
	);
}

EventManager::attachActionResponse($response, 'json');
