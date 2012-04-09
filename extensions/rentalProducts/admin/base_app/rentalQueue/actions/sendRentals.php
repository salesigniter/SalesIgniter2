<?php
$queueItems = (isset($_POST['queueItem']) ? $_POST['queueItem'] : '');
$cID = $_GET['cID'];
$Customer = Doctrine_Core::getTable('Customers')->find((int) $cID);
$Membership = $Customer->CustomersMembership;
$Plan = $Membership->Membership;

$error = false;
if (!is_array($queueItems) || sizeof($queueItems) <= 0){
	$error = true;
	$messageStack->addSession('pageStack', sysLanguage::get('TEXT_NO_MOVIE_SELECTED'), 'error');
}else{
	$totalInQueue = $RentalQueue->countContents();
	$totalRented = $RentalQueue->count_rented();

	$allowedRentals = $Plan->no_of_titles - $totalRented;
	if (sizeof($queueItems) > $allowedRentals){
		$error = true;
		$customers_name = $Customer->customers_firstname . ' ' . $Customer->customers_lastname;
		$messageStack->addSession('pageStack', sysLanguage::get('TEXT_INFO_TOO_MANY_MOVIES') . ' ' . $customers_name, 'warning');
	}
}

if ($error === false){
	foreach($queueItems as $queueID){
		$barcodeId = $_POST['barcode'][$queueID];

		$Product = $RentalQueue->getProduct($queueID);

		$RentalQueue->incrementTopRentals($Product->getData('product_id'));

		$shipmentDate = date('Y-m-d');
		$arrivalDate = date('Y-m-d', mktime(0,0,0,date('m'),date('d')+sysConfig::get('RENTAL_QUEUE_DAYS_INTERVAL'),date('Y')));

		$NewRenedQueue = new RentedQueue();
		$NewRenedQueue->customers_id = $cID;
		$NewRenedQueue->products_id = $Product->getData('product_id');
		$NewRenedQueue->products_barcode = $barcodeId;
		$NewRenedQueue->shipment_date = $shipmentDate;
		$NewRenedQueue->arrival_date = $arrivalDate;
		$NewRenedQueue->save();

		$rentedProductId = $NewRenedQueue->customers_queue_id;

		/*
		 * @TODO: Does this even need to happen?
		 */
		$NewRentedProduct = new RentedProducts();
		$NewRentedProduct->customers_id = $cID;
		$NewRentedProduct->products_id = $Product->getData('product_id');
		$NewRentedProduct->rented_products_id = $rentedProductId;
		$NewRentedProduct->products_barcode = $barcodeId;
		$NewRentedProduct->shipment_date = $shipmentDate;
		$NewRentedProduct->arrival_date = $arrivalDate;
		$NewRentedProduct->save();

		EventManager::notify('RentalQueueProductSent', &$NewRentedProduct, $QproductsQueue);

		$RentalQueue->remove($Product->getId());

		Doctrine_Query::create()
			->update('ProductsInventoryBarcodes')
			->set('status', '?', 'O')
			->where('barcode_id = ?', $barcodeId)
			->execute();

		$emailEvent = new emailEvent('rental_sent', $Customer->language_id);
		$emailEvent->setVars(array(
			'firstname' => $Customer->customers_firstname,
			'lastname' => $Customer->customers_lastname,
			'full_name' => $Customer->customers_firstname . ' ' . $Customer->customers_lastname,
			'rentedProduct' => $Product->getName($Customer->language_id),
			'requestDate' => $Product->getData('date_added'),
			'shipmentDate' => tep_date_short($shipmentDate),
			'arrivalDate' => tep_date_short($arrivalDate)
		));

		$emailEvent->sendEmail(array(
			'name'  => $Customer->customers_firstname . ' ' . $Customer->customers_lastname,
			'email' => $Customer->customers_email_address
		));
	}
	$RentalQueue->fixPriorities();
	$messageStack->addSession('pageStack', sysLanguage::get('TEXT_INFO_MOVIES_RENTED'), 'success');
}
EventManager::attachActionResponse(itw_app_link(tep_get_all_get_params(array('action'))), 'redirect');
?>