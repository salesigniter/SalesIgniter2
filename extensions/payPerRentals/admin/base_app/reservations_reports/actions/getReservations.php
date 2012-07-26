<?php
$Response = array(
	'success' => true,
	'events'  => array()
);

$Products = explode(',', $_POST['product_id']);
$Reservations = Doctrine_Query::create()
	->from('PayPerRentalReservations')
	->whereIn('products_id', $Products)
	->execute();
if ($Reservations->count() > 0){
	foreach($Reservations as $Reservation){
		$Serials = array();

		$SaleProduct = $Reservation->SaleProduct;
		$SaleInventory = $SaleProduct->SaleInventory;
		if ($SaleInventory->Serial && $SaleInventory->Serial->count() > 0){
			foreach($SaleInventory as $Inv){
				$Serials[] = $Inv->Serial->serial_number;
			}
		}
		$Response['events'][] = array(
			'title' => $Reservation->start_date->format(sysLanguage::getDateFormat('short')) . ' - ' . $Reservation->end_date->format(sysLanguage::getDateFormat('short')),
			'start' => $Reservation->start_date->format(sysLanguage::getDateFormat('short')),
			'end'   => $Reservation->end_date->format(sysLanguage::getDateFormat('short')),
			'serials' => $Serials
		);
	}
}

EventManager::attachActionResponse($Response, 'json');
