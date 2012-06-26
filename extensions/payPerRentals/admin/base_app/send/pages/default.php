<?php
$Qreservations = Doctrine_Query::create()
	->from('PayPerRentalReservations')
	->where('start_date <> now()')
	->andWhere('rental_state = ?', 'reserved');

$TableGrid = htmlBase::newGrid()
	->setMainDataKey('reservation_id')
	->allowMultipleRowSelect(true)
	->useSorting(true)
	->usePagination(true)
	->useSearching(true)
	->setQuery($Qreservations);

$TableGrid->addButtons(array(
	htmlBase::newButton()->addClass('sendReservationsButton')->usePreset('continue')->setText(sysLanguage::get('TEXT_BUTTON_SEND')),
	htmlBase::newButton()->addClass('payReservationsButton')->usePreset('continue')->setText(sysLanguage::get('TEXT_BUTTON_PAY_RES')),
	htmlBase::newButton()->addClass('updateReservationsButton')->usePreset('continue')->setText(sysLanguage::get('TEXT_BUTTON_STATUS_RES'))
));

$HeaderColumns = array(
	array(
		'text' => sysLanguage::get('TABLE_HEADING_CUSTOMERS_NAME')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_NAME')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_BARCODE')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_BARCODE_REPLACE')
	)
);

if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
	$HeaderColumns[] = array(
		'text' => sysLanguage::get('TABLE_HEADING_EVENT')
	);

	if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
		$HeaderColumns[] = array(
			'text' => sysLanguage::get('TABLE_HEADING_GATE')
		);
	}
}

$HeaderColumns[] = array(
	'text'      => 'Dates',
	'useSort'   => true,
	'sortKey'   => 'start_date',
	'useSearch' => true,
	'searchObj' => GridSearchObj::Between()
		->useFieldObj(htmlBase::newInput()->addClass('makeDatepicker')->attr('size', '10')->setName('start_date'))
		->setDatabaseColumn('start_date')
);
$HeaderColumns[] = array(
	'text' => 'Location'
);

if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_TRACKING_NUMBER_COLUMN') == 'True'){
	$HeaderColumns[] = array(
		'text' => 'Tracking Number'
	);
}

if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_RESERVATION_STATUS_COLUMN') == 'True'){
	$FilterStatusSelect = htmlBase::newSelectbox()
		->setName('search_status');
	$FilterStatusSelect->addOption('', 'All Statuses');

	$StatusSelect = htmlBase::newSelectbox()
		->setName('reservation_status');

	$QrentalStatus = Doctrine_Query::create()
		->from('RentalStatus')
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
	foreach($QrentalStatus as $iStatus){
		$FilterStatusSelect->addOption($iStatus['rental_status_id'], $iStatus['rental_status_text']);
		$StatusSelect->addOption($iStatus['rental_status_id'], $iStatus['rental_status_text']);
	}

	$HeaderColumns[] = array(
		'text'      => 'Reservation Status',
		'useSort'   => true,
		'sortKey'   => 'rental_status_id',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj($FilterStatusSelect)
			->setDatabaseColumn('rental_status_id')
	);
}

if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_PROCESS_SEND') == 'True'){
	$HeaderColumns[] = array(
		'text' => 'Pay Reservation'
	);
}
$HeaderColumns[] = array(
	'text' => ''
);

$TableGrid->addHeaderRow(array(
	'columns' => $HeaderColumns
));

$Reservations = $TableGrid->getResults(false);
if ($Reservations->count() > 0){
	$barcodeReplacement = htmlBase::newElement('input')
		->setName('barcode_replacement')
		->addClass('barcodeReplacement');

	foreach($Reservations as $Reservation){
		$TableGrid->addBodyRow(array(
			'addCls'  => 'noHover noSelect',
			'columns' => array(
				array('colspan' => 12, 'text' => 'Sale Id: ' . $Reservation->SaleProduct->Sale->sale_id)
			)
		));

		$BodyColumns = array(
			array('text' => $Reservation->SaleProduct->Sale->customers_firstname . ' ' . $Reservation->SaleProduct->Sale->customers_lastname),
			array('text' => $Reservation->Product->ProductsDescription[Session::get('languages_id')]->products_name),
			array('text' => $Reservation->SaleProduct->SaleInventory[0]->Barcode->barcode),
			array('text' => $barcodeReplacement->draw())
		);

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_EVENTS') == 'True'){
			$BodyColumns[] = array(
				'text' => $Reservation->event_name
			);
			if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_GATES') == 'True'){
				$BodyColumns[] = array(
					'text' => $Reservation->event_gate
				);
			}
		}

		$shipOn = $Reservation->start_date->modify('-' . (int)$Reservation->shipping_days_before . ' Day');
		$dueBack = $Reservation->end_date->modify('+' . (int)$Reservation->shipping_days_after . ' Day');

		$BodyColumns[] = array(
			'text' => '<table cellpadding="2" cellspacing="0" border="0">' .
				'<tr>' .
				'<td class="dataTableContent">Ship On: </td>' .
				'<td class="dataTableContent">' . $shipOn->format(sysLanguage::getDateFormat('short')) . '</td>' .
				'</tr>' .
				'<tr>' .
				'<td class="dataTableContent">Res Start: </td>' .
				'<td class="dataTableContent">' . $Reservation->start_date->format(sysLanguage::getDateFormat('short')) . '</td>' .
				'</tr>' .
				'<tr>' .
				'<td class="dataTableContent">Res End: </td>' .
				'<td class="dataTableContent">' . $Reservation->end_date->format(sysLanguage::getDateFormat('short')) . '</td>' .
				'</tr>' .
				'<tr>' .
				'<td class="dataTableContent">Due Back: </td>' .
				'<td class="dataTableContent">' . $dueBack->format(sysLanguage::getDateFormat('short')) . '</td>' .
				'</tr>' .
				'</table>'
		);

		$inventoryCenterName = "Default Store";
		$BodyColumns[] = array(
			'text' => $inventoryCenterName
		);

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_TRACKING_NUMBER_COLUMN') == 'True'){
			$trackNumber = '';
			$shippingTrackingNumber = htmlBase::newElement('input')
				->setName('shipping_number')
				->setValue($trackNumber);

			$BodyColumns[] = array(
				'text' => $shippingTrackingNumber->draw()
			);
		}
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_SHOW_RESERVATION_STATUS_COLUMN') == 'True'){
			$BodyColumns[] = array(
				'text' => $StatusSelect->selectOptionByValue($Reservation->rental_status_id)->draw()
			);
		}
		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_PROCESS_SEND') == 'True'){
			$BodyColumns[] = array(
				'text' => $payAmount->draw()
			);
		}
		$BodyColumns[] = array(
			'text' => ''
		);

		$TableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-sale_id' => $Reservation->SaleProduct->Sale->sale_id,
				'data-reservation_id' => $Reservation->id
			),
			'columns' => $BodyColumns
		));
	}
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin:5px;">
	<div style="margin:5px;"><?php echo $TableGrid->draw();?></div>
</div>
