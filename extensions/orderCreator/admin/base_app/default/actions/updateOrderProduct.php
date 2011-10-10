<?php
$OrderedProduct = $Editor->ProductManager->get($_GET['id']);
$mainPurchaseType = $_GET['purchase_type'];
$mainInfo = $OrderedProduct->getInfo();
$OrderedProduct->updateProductInfo();
if($mainPurchaseType == 'reservation'){
	if(isset($mainInfo['reservationInfo'])){
		$pInfo = $OrderedProduct->getInfo();
		$pInfo['reservationInfo'] = $mainInfo['reservationInfo'];
		$OrderedProduct->setInfo($pInfo);
	}
}

if ($Editor->hasErrors() === false){
	$response = array(
		'success' => true,
		'hasError' => false,
		'price' => (float)$OrderedProduct->getFinalPrice(false, false),
		'name' => $OrderedProduct->getNameEdit(),
		'barcodes' => $OrderedProduct->getBarcodeEdit()
	);
}
else {
	$response = array(
		'success' => true,
		'hasError' => true,
		'errorMessage' => $Editor->getErrors()
	);
}
EventManager::attachActionResponse($response, 'json');
