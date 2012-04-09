<?php
	$starting_date = DateTime::createFromFormat('m/d/Y', $_POST['start_date']);
	$ending_date = DateTime::createFromFormat('m/d/Y', $_POST['end_date']);
 	//$isSemester = (isset($_POST['isSemester'])?true:false);
  	$semName = (isset($_POST['semester_name'])?$_POST['semester_name']:'');
	$success = false;
	$price = 0;
	$message = '';
    foreach($_POST['reservation_products_id'] as $pElem){
		$purchaseTypeClass = PurchaseTypeModules::getModule('reservation');
		$purchaseTypeClass->loadProduct($pElem);
		OrderShippingModules::calculateWeight();
	    $rInfo = '';

	    $onlyShow = true;
	    if(sysconfig::get('EXTENSION_PAY_PER_RENTALS_SHORT_PRICE') == 'True'){
		    $onlyShow = false;
	    }
		$pricing = $purchaseTypeClass->getReservationPrice($starting_date, $ending_date, $rInfo, $semName, isset($_POST['hasInsurance'])?true:false, $onlyShow);
	    if (is_array($pricing) && is_numeric($pricing['price'])){
		    $price += $pricing['price'];
		    $message .= $pricing['message'].'<br/>';
		    $success = true;
	    }
	}

	EventManager::attachActionResponse(array(
		'success' => $success,
		'price'   => $currencies->format($price),
		'message' => $message
	), 'json');
?>