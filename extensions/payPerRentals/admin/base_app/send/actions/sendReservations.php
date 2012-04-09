<?php
$ShippingCarrierInfo = array(
	array(
		'pattern' => '/\b(1Z ?[0-9A-Z]{3} ?[0-9A-Z]{3} ?[0-9A-Z]{2} ?[0-9A-Z]{4} ?[0-9A-Z]{3} ?[0-9A-Z]|[\dT]\d\d\d ?\d\d\d\d ?\d\d\d)\b/i',
		'url'     => '<a href="http://wwwapps.ups.com/etracking/tracking.cgi?InquiryNumber2=&InquiryNumber3=&InquiryNumber4=&InquiryNumber5=&TypeOfInquiryNumber=T&UPS_HTML_Version=3.0&IATA=us&Lang=en&submit=Track+Package&InquiryNumber1=%s">Track Order</a>',
		'name'    => 'UPS'
	),
	array(
		'pattern' => '/\b((96\d\d\d\d\d ?\d\d\d\d|96\d\d) ?\d\d\d\d ?d\d\d\d( ?\d\d\d)?)\b/i',
		'url'     => '<a href="http://www.fedex.com/Tracking?action=track&language=english&cntry_code=us&tracknumbers=%s">Track Order</a>',
		'name'    => 'FEDEX'
	),
	array(
		'pattern' => '/\b(91\d\d ?\d\d\d\d ?\d\d\d\d ?\d\d\d\d ?\d\d\d\d ?\d\d|91\d\d ?\d\d\d\d ?\d\d\d\d ?\d\d\d\d ?\d\d\d\d)\b/i',
		'url'     => '<a href="http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=%s">Track Order</a>',
		'name'    => 'USPS'
	)
);

$Qreservations = Doctrine_Query::create()
	->from('OrdersProductsReservation')
	->whereIn('orders_products_reservations_id', (isset($_POST['sendRes']) ? $_POST['sendRes'] : array()))
	->andWhere('parent_id IS NULL')
	->execute();
if ($Qreservations->count() > 0){
	foreach($Qreservations as $Reservation){
		$OrderProducts = $Reservation->OrdersProducts;
		$Order = $OrderProducts->getOrder();

		$ReservationId = $Reservation->orders_products_reservations_id;

					if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_MAINTENANCE') == 'True'){
						//check if it has prehire maintenance. if yes then check current_maintenance_type to be 0 if not then cannot be sended
						$QMaintenancePeriod = Doctrine_Query::create()
							->from('PayPerRentalMaintenancePeriods ppmp')
							->where('before_send = ?', '1')
							->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
						if(count($QMaintenancePeriod) > 0){
							$BarcodeHistoryRented = Doctrine_Core::getTable('BarcodeHistoryRented')->findOneByBarcodeId($oprInfo->barcode_id);

							if(!$BarcodeHistoryRented || $BarcodeHistoryRented->current_maintenance_type != 0){
								continue;
							}
						}
					}

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_PROCESS_SEND') == 'True'){
			if (isset($_POST['amount_payed'][$ReservationId])){
				$paidDiff = $_POST['amount_payed'][$ReservationId];
				$paidDiff -= $OrderProducts->final_price;
				if ($paidDiff < 0){
					continue;
				}
			}
		}
					$Arr[] = $ReservationId;

		if (isset($_POST['shipping_number'][$ReservationId]) && !empty($_POST['shipping_number'][$ReservationId])){
			$ShippingNumber = $_POST['shipping_number'][$ReservationId];
			$noMatch = true;
			foreach($ShippingCarrierInfo as $sInfo){
				if (preg_match($sInfo['pattern'], $ShippingNumber)){
					$noMatch = false;
					$shippingURL = sprintf($sInfo['url'], $ShippingNumber);
					$oprInfo->tracking_number = $shippingNumber;
					$oprInfo->tracking_type = $sInfo['name'];
				}
			}

			if ($noMatch === true){
				$shippingURL = '<a href="http://track.dhl-usa.com/atrknav.asp?action=track&language=english&cntry_code=us&ShipmentNumber=' . $ShippingNumber . '">Track Order</a>'; //DHL-starts with JD
				$oprInfo->tracking_number = $shippingNumber;
				$oprInfo->tracking_type = 'DHL';
			}
		}

		$Reservation->rental_state = 'out';
		$Reservation->date_shipped = date('Y-m-d');

		if (isset($_POST['barcode_replacement'][$ReservationId]) && !empty($_POST['barcode_replacement'][$ReservationId])){
			$QBarcode = Doctrine_Query::create()
				->from('ProductsInventoryBarcodes')
				->andWhere('barcode = ?', $_POST['barcode_replacement'][$ReservationId])
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
			if ($QBarcode){
				$Reservation->barcode_id = $QBarcode[0]['barcode_id'];
			}
		}

		if ($Reservation->track_method == 'barcode'){
			$Reservation->ProductsInventoryBarcodes->status = 'O';
		}
		elseif ($Reservation->track_method == 'quantity') {
			$Reservation->ProductsInventoryQuantity->reserved -= 1;
			$Reservation->ProductsInventoryQuantity->qty_out += 1;
		}
		$Reservation->save();

		$Customer = $Order->Customers;
		if ($Customer){
			$OrderAddress = $Order->OrdersAddresses['customer'];

			$emailEvent = new emailEvent('reservation_sent', $Customer->language_id);

			$emailEvent->setVars(array(
				'full_name'       => $OrderAddress->entry_name,
				'rented_product'  => $OrderProducts->products_name,
				'due_date'        => $Reservation->end_date->format(sysLanguage::getDateFormat('long')),
				'shipping_number' => (isset($shippingURL) ? $shippingURL : ''),
				'email_address'   => $Order->customers_email_address
			));
						if(sysConfig::get('EXTENSION_PAY_PER_RENTALS_SEND_EMAIL_SEND') == 'True'){
			$emailEvent->sendEmail(array(
				'email' => $Order->customers_email_address,
				'name'  => $OrderAddress->entry_name
			));
			}
		}
	}
}

EventManager::attachActionResponse(array(
	'success' => true,
		'Arr' => $Arr
), 'json');
?>