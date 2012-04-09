<?php
$OrderedProduct = $Editor->ProductManager->get($_GET['id']);
if ($OrderedProduct !== false){
	$OrderedProduct->onUpdateOrderProduct();

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
}else{
	$response = array(
		'success' => true,
		'hasError' => true,
		'errorMessage' => 'Unable to find order product: ' . $_GET['id']
	);
}

EventManager::attachActionResponse($response, 'json');
