<?php
$OrderProduct = $Editor->ProductManager->get((int)$_POST['id']);

$startDate = SesDateTime::createFromFormat('m/d/Y', $_POST['start_date']);
$endDate = SesDateTime::createFromFormat('m/d/Y', $_POST['end_date']);
$semesterName = (isset($_POST['semester_name']) ? $_POST['semester_name'] : '');
$success = false;
$price = 0;
$Qty = (isset($_POST['rental_qty']) ? (int)$_POST['rental_qty'] : $OrderProduct->getQuantity());
$onlyShow = true;
if (sysconfig::get('EXTENSION_PAY_PER_RENTALS_SHORT_PRICE') == 'True'){
	$onlyShow = false;
}

if ($OrderProduct->getProductTypeClass()->getCode() == 'package'){
	$OrderProduct->getProductTypeClass()->loadReservationPricing($OrderProduct->getInfo('PackagedProducts'));
}

foreach($_POST['reservation_products_id'] as $pElem){
	$Product = new Product($pElem);
	$ProductType = $Product->getProductTypeClass();
	$purchaseTypeClass = $ProductType->getPurchaseType('reservation');
	global $total_weight;
	$total_weight = $Qty * $Product->getWeight();
	OrderShippingModules::calculateWeight();
	$rInfo = '';
	if ($Editor->hasData('store_id')){
		$rInfo = array(
			'store_id' => $Editor->getData('store_id')
		);
	}
	$pricing = $purchaseTypeClass->getReservationPrice(
		$startDate,
		$endDate,
		$rInfo,
		$semesterName,
		isset($_POST['hasInsurance']) ? true : false,
		$onlyShow
	);

	if (is_array($pricing) && is_numeric($pricing['price'])){
		$price += $pricing['price'];
		$message .= strip_tags($pricing['message']);
		$success = true;
	}
}

EventManager::attachActionResponse(array(
	'success' => $success,
	'price'   => $currencies->format($price),
	'totalPrice'   => $currencies->format($price),
	'message' => $message
), 'json');
?>