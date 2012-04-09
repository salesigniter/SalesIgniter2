<?php
$purchaseTypeClasses = array();
$OrderProduct = $Editor->ProductManager->get((int)$_POST['id']);
$ProductType = $OrderProduct->getProductTypeClass();
if ($ProductType->getCode() == 'package'){
	$ProductIds = array();
	foreach($OrderProduct->getInfo('PackagedProducts') as $PackageOrderProduct){
		if ($PackageOrderProduct->hasInfo('purchase_type') && $PackageOrderProduct->getInfo('purchase_type') == 'reservation'){
			$PackageProductType = $PackageOrderProduct->getProductTypeClass();
			$PackageProductPurchaseType = $PackageProductType->getPurchaseType($PackageData->purchase_type);
			if ($PackageProductPurchaseType){
				$purchaseTypeClasses[] = $PackageProductPurchaseType;
			}
		}
	}
}else{
	$purchaseTypeClasses[] = $ProductType->getPurchaseType();
}

$usableBarcodes = array();
if (isset($_POST['barcode']) && ($_POST['barcode'] != 'undefined')){
	$usableBarcodes[] = $_POST['barcode'];
}

$OrderProduct->updateInfo(array(
	'usableBarcodes' => $usableBarcodes
));

$calendar = ReservationUtilities::getCalendar(array(
	'purchaseTypeClasses' => $purchaseTypeClasses,
	'quantity' => $OrderProduct->getQuantity(),
	'callType' => 'admin',
	'usableBarcodes' => $usableBarcodes,
	'hasButton' => false,
	'calanderMonths' => 3
));

EventManager::attachActionResponse(array(
	'success'  => true,
	'calendar' => $calendar,
	'extraHidden' => '<input type="hidden" name="id" value="' . (int)$_POST['id'] . '">'
), 'json');
?>
