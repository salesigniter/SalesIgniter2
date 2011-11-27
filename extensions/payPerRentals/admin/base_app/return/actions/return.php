<?php
	$msgError = array();
	$msgSuccess = array();
	$lastBarcode = -1;
	$rentals = (isset($_POST['rental']) ? $_POST['rental'] : '');
	$damaged = (isset($_POST['damaged']) ? $_POST['damaged'] : array());
	$lost = (isset($_POST['lost']) ? $_POST['lost'] : array());
	$comment = (isset($_POST['comment']) ? $_POST['comment'] : array());
	$error = false;
	if (!is_array($rentals) || sizeof($rentals) <= 0){
		$error = true;
		$messageStack->addSession('pageStack', sysLanguage::get('TEXT_NO_BARCODE_ENTERED'), 'error');
	}

	if ($error === false){
		foreach($rentals as $bookingID => $info){
			$status = 'A';
			if (isset($damaged[$bookingID])) $status = 'B';
			if (isset($lost[$bookingID])) $status = 'L';

			$QbookingRes = Doctrine_Query::create()
			->from('OrdersProductsReservation')
			->where('orders_products_reservations_id = ?', $bookingID)
			->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			$lastBarcode = $QbookingRes[0]['barcode_id'];
			ReservationUtilities::returnReservation(
				$bookingID,
				$status,
				(isset($comment[$bookingID]) ? $comment[$bookingID] : ''),
				(isset($lost[$bookingID]) ? '1' : '0'),
				(isset($damaged[$bookingID]) ? '1' : '0')
			);
		}
		$messageStack->addSession('pageStack', sysLanguage::get('TEXT_SUCCESS_MOVIES_RETURNED'), 'success');
	}

EventManager::attachActionResponse(array(
		'success' => true,
		'lastBarcode' => $lastBarcode
	), 'json');
?>