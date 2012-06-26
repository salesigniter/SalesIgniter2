<?php
$lastBarcode = -1;
$damaged = (isset($_POST['damaged']) ? $_POST['damaged'] : array());
$lost = (isset($_POST['lost']) ? $_POST['lost'] : array());
$comment = (isset($_POST['comment']) ? $_POST['comment'] : array());

$Reservations = Doctrine_Core::getTable('PayPerRentalReservations');
$toReturn = explode(',', $_POST['reservation_id']);
foreach($toReturn as $reservationId){

	$status = 'A';
	if (isset($damaged[$bookingID])) {
		$status = 'B';
	}
	if (isset($lost[$bookingID])) {
		$status = 'L';
	}

	$Reservation = $Reservations->find($reservationId);
	if ($Reservation && $Reservation->count() > 0){
		$lastBarcode = $Reservation->SaleProduct->SaleInventory[0]->barcode_id;

		ReservationUtilities::returnReservation(
			$Reservation,
			$status,
			(isset($comment[$reservationId]) ? $comment[$reservationId] : ''),
			(isset($lost[$reservationId]) ? '1' : '0'),
			(isset($damaged[$reservationId]) ? '1' : '0')
		);
	}
}

EventManager::attachActionResponse(array(
	'success'     => true,
	'lastBarcode' => $lastBarcode
), 'json');
