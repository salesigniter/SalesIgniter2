<?php
$OrderProduct = $Editor->ProductManager->get((int)$_POST['id']);
//$OrderProduct->setPurchaseType($_GET['purchase_type']);
if ($OrderProduct->getProductTypeClass()->getCode() == 'package'){
	$PackageProducts = $OrderProduct->getInfo('PackagedProducts');
	foreach($PackageProducts as $PackagedProduct){
		$PackageData = $PackagedProduct->getInfo('PackageData');
		if ($PackageData->purchase_type == 'reservation'){
				$PurchaseType = $PackagedProduct->getProductTypeClass()->getPurchaseType();
				if (isset($PackageData->price) && is_object($PackageData->price)){
					PurchaseType_reservation_utilities::getRentalPricing($PurchaseType->getPayPerRentalId());
					$CachedPrice =& PurchaseType_reservation_utilities::$RentalPricingCache[$PurchaseType->getPayPerRentalId()];
					foreach($CachedPrice as $k => $pInfo){
						$Price = (array)$PackageData->price;
						if (isset($Price[$pInfo['pay_per_rental_id']])){
							$Type = (array)$Price[$pInfo['pay_per_rental_id']];
							if (isset($Type[$pInfo['pay_per_rental_types_id']])){
								$NumOf = (array)$Type[$pInfo['pay_per_rental_types_id']];
								if (isset($NumOf[$pInfo['number_of']])){
									$CachedPrice[$k]['price'] = $NumOf[$pInfo['number_of']];
								}
							}
						}
					}
				}
		}
	}
}

$ProductType = $OrderProduct->getProductTypeClass();
if (method_exists($ProductType, 'getPurchaseType')){
	$PurchaseType = $ProductType->getPurchaseType();
}
else {
	$PurchaseType = null;
}

$starting_date = SesDateTime::createFromFormat('m/d/Y', $_POST['start_date']);
$ending_date = SesDateTime::createFromFormat('m/d/Y', $_POST['end_date']);

if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'False'){
	if ((isset($_POST['start_date']) && $_POST['start_date'] != 'undefined') && (isset($_POST['end_date']) && $_POST['end_date'] != 'undefined')){
		if ($OrderProduct->hasInfo('PackagedProducts')){
			foreach($OrderProduct->getInfo('PackagedProducts') as $PackagedProduct){
				if ($PackagedProduct->getInfo('purchase_type') == 'reservation'){
					if ($PackagedProduct->hasInfo('reservationInfo')){
						$resInfo = $PackagedProduct->getInfo('reservationInfo');
					}else{
						$resInfo = array();
					}

					$resInfo['start_date'] = $starting_date;
					$resInfo['end_date'] = $ending_date;

					$PackagedProduct->updateInfo(array(
						'reservationInfo' => $resInfo
					));
				}
			}
		}else{
			if ($OrderProduct->hasInfo('reservationInfo')){
				$resInfo = $OrderProduct->getInfo('reservationInfo');
			}else{
				$resInfo = array();
			}

			$resInfo['start_date'] = $starting_date;
			$resInfo['end_date'] = $ending_date;

			$OrderProduct->updateInfo(array(
				'reservationInfo' => $resInfo
			));
		}
	}
}
else {
	if (isset($_POST['event']) && $_POST['event'] != 'undefined'){
		$event_duration = 1;
		$Qevent = Doctrine_Query::create()
			->from('PayPerRentalEvents')
			->where('events_id = ?', $_POST['event'])
			->fetchOne();
		if ($Qevent){
			$start_date = strtotime($Qevent->events_date);
			if (!isset($_POST['days_before'])){
				$_POST['days_before'] = 0;
			}
			if (!isset($_POST['days_after'])){
				$_POST['days_after'] = 0;
			}
			$starting_date = $starting_date->modify('-' . $_POST['days_before'] . ' Day');
			$ending_date = $ending_date->modify('+' . ($_POST['days_after'] + $event_duration) . ' Day');

			if (isset($_POST['qty']) && $_POST['qty'] > 0){
				$rQty = $_POST['qty'];
			}
			else {
				$rQty = 1;
			}

			//this part under a for i merge array
			$reservArr = array();
			$barcodesBooked = array();
			$bookings = $PurchaseType->getBookedDaysArrayNew($starting_date, $rQty, &$reservArr, &$barcodesBooked, $Editor->ProductManager->getContents());

			$startingTime = $starting_date->getTimestamp(); //here can be multiple dates...right now for event are two
			$endingTime = $ending_date->getTimestamp();
			$dateIsReserved = false;
			while($startingTime <= $endingTime){
				$dateFormatted = date('Y-n-j', $startingTime);
				if (in_array($dateFormatted, $bookings)){
					$dateIsReserved = true;
					break;
				}
				$startingTime += 60 * 60 * 24;
			}

			if (!$dateIsReserved){
				if ($OrderProduct->hasInfo('reservationInfo')){
					$resInfo = $OrderProduct->getInfo('reservationInfo');
				}else{
					$resInfo = array();
				}

				$resInfo['start_date'] = SesDateTime::createFromFormat(DATE_TIMESTAMP, $Qevent->events_date);
				$resInfo['end_date'] = SesDateTime::createFromFormat(DATE_TIMESTAMP, $Qevent->events_date)
					->modify('+' . $event_duration . ' Day');

				$OrderProduct->updateInfo(array(
					'reservationInfo' => $resInfo
				));
			}
		}
		else {
		}
	}
}

function setShippingInfo(&$ProductData) {
	if (isset($_POST['shipping']) && $_POST['shipping'] != 'undefined'){
		$shippingInfo = explode('_', $_POST['shipping']);
		$ProductData['reservationInfo']['shipping_module'] = $shippingInfo[0];
		$ProductData['reservationInfo']['shipping_method'] = $shippingInfo[1];
		$ProductData['reservationInfo']['days_before'] = (isset($_POST['days_before']) ? $_POST['days_before'] : 0);
		$ProductData['reservationInfo']['days_after'] = (isset($_POST['days_after']) ? $_POST['days_after'] : 0);
	}
	else {
		$ProductData['reservationInfo']['rental_shipping'] = false;
	}
}

function setQuantityData(&$ProductData) {
	if (isset($_POST['qty']) && $_POST['qty'] != 'undefined'){
		$ProductData['reservationInfo']['quantity'] = $_POST['qty'];
	}else{
		$ProductData['reservationInfo']['quantity'] = 1;
	}
}

function setEventData(&$ProductData) {
	global $Qevent, $starting_date;
	if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True' && $Qevent){
		$ProductData['reservationInfo']['event_name'] = $Qevent->events_name;
		$ProductData['reservationInfo']['event_date'] = $starting_date;
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True' && isset($_POST['gate'])){
			$Qgate = Doctrine_Query::create()
				->from('PayPerRentalGates')
				->where('gates_id = ?', $_POST['gate'])
				->fetchOne();
			if ($Qgate){
				$ProductData['reservationInfo']['event_gate'] = $Qgate->gate_name;
			}
		}
	}
}

/*todo move into attributes*/
function setAttributesData(&$ProductData) {
	if (isset($_POST['id']['reservation']) && !empty($_POST['id']['reservation'])){
		$attrValue = attributesUtil::getAttributeString($_POST['id']['reservation']);
		if (!empty($attrValue)){
			$ProductData['aID_string'] = $attrValue;
		}
	}
}

function setInsuranceData(&$ProductData) {
	if (isset($_POST['hasInsurance']) && $_POST['hasInsurance'] == '1'){
		$payPerRentals = Doctrine_Query::create()
			->select('insurance')
			->from('ProductsPayPerRental')
			->where('products_id = ?', $ProductData['products_id'])
			->fetchOne();

		$ProductData['reservationInfo']['insurance'] = $payPerRentals->insurance;
		$ProductData['price'] += $payPerRentals->insurance;
	}
}

$ProductInfo = $OrderProduct->getInfo();
if (
	(isset($ProductInfo['reservationInfo']['start_date']) && isset($ProductInfo['reservationInfo']['end_date'])) ||
	$ProductType->getCode() == 'package'
){

	if ($ProductType->getCode() == 'package'){
		$ProductInfo['price'] = 0;
		if ($OrderProduct->hasInfo('PackagedProducts')){
			foreach($OrderProduct->getInfo('PackagedProducts') as $PackagedProduct){
				$PackageProductInfo = $PackagedProduct->getInfo();
				$PackagedProductPurchaseType = $PackagedProduct->getProductTypeClass()->getPurchaseType();
				if ($PackagedProduct->getInfo('purchase_type') == 'reservation'){
					setShippingInfo(&$PackageProductInfo);
					setQuantityData(&$PackageProductInfo);

					$PackagedProductPurchaseType->processAddToOrderOrCart(&$PackageProductInfo['reservationInfo'], &$PackageProductInfo);

					setEventData(&$PackageProductInfo);
					setAttributesData(&$PackageProductInfo);

					$ProductInfo['price'] += ($PackageProductInfo['price'] * $PackageData->quantity);
					$PackageProductInfo['reservationInfo']['quantity'] = $PackageData->quantity;
				}else{
					$PackageProductInfo['quantity'] = $PackageData->quantity;
					if (isset($PackageData->price)){
						$PackageProductInfo['price'] += ($PackageData->price * $PackageData->quantity);
					}
					else {
						$PackageProductInfo['price'] += ($PackagedProductPurchaseType->getPrice() * $PackageData->quantity);
					}
				}
			}
		}
	}
	else {
		setShippingInfo(&$ProductInfo);
		setQuantityData(&$ProductInfo);

		$PurchaseType->processAddToOrderOrCart(&$ProductInfo['reservationInfo'], &$ProductInfo);

		setEventData(&$ProductInfo);
		setAttributesData(&$ProductInfo);
	}
	$OrderProduct->setInfo($ProductInfo);

	EventManager::attachActionResponse(array(
		'success'  => true,
		'price'	=> (isset($ProductInfo['price']) ? $ProductInfo['price'] : 0)
	), 'json');
}
else {
	EventManager::attachActionResponse(array(
		'success'  => false,
		'price'	=> (isset($ProductInfo['price']) ? $ProductInfo['price'] : 0)
	), 'json');
}
