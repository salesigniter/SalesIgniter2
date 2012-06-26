<?php
$Qreservations = Doctrine_Query::create()
	->from('PayPerRentalReservations')
	->where('end_date <> now()')
	->andWhere('rental_state = ?', 'out');

$TableGrid = htmlBase::newGrid()
	->setMainDataKey('reservation_id')
	->allowMultipleRowSelect(true)
	->useSorting(true)
	->usePagination(true)
	->useSearching(true)
	->setQuery($Qreservations);

$TableGrid->addButtons(array(
	htmlBase::newButton()->addClass('returnReservationsButton')->usePreset('continue')
		->setText(sysLanguage::get('TEXT_BUTTON_RETURN_RENTALS'))->disable()
));

$HeaderColumns = array(
	array(
		'text' => sysLanguage::get('TABLE_HEADING_CUSTOMERS_NAME')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_NAME')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_INV_NUM')
	)
);

if ($centersEnabled){
	if ($centersStockMethod == 'Store'){
		$HeaderColumns[] = array(
			'text' => 'Store'
		);
	}
	else {
		$HeaderColumns[] = array(
			'text' => 'Inventory Center'
		);
	}
}

$HeaderColumns[] = array(
	'text'      => 'Dates',
	'useSort'   => true,
	'sortKey'   => 'end_date',
	'useSearch' => true,
	'searchObj' => GridSearchObj::Between()
		->useFieldObj(htmlBase::newInput()->addClass('makeDatepicker')->attr('size', '10')->setName('end_date'))
		->setDatabaseColumn('end_date')
);

$HeaderColumns[] = array(
	'text' => sysLanguage::get('TABLE_HEADING_DAYS_LATE')
);

$HeaderColumns[] = array(
	'text' => sysLanguage::get('TABLE_HEADING_COMMENTS')
);

if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_MAINTENANCE') == 'False'){
	$HeaderColumns[] = array(
		'text' => sysLanguage::get('TABLE_HEADING_ITEM_DMG')
	);
	$HeaderColumns[] = array(
		'text' => sysLanguage::get('TABLE_HEADING_ITEM_LOST')
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
	foreach($Reservations as $Reservation){
		$BodyColumns = array(
			array('text' => $Reservation->SaleProduct->Sale->customers_firstname . ' ' . $Reservation->SaleProduct->Sale->customers_lastname),
			array('text' => $Reservation->Product->ProductsDescription[Session::get('languages_id')]->products_name),
			array('text' => $Reservation->SaleProduct->SaleInventory[0]->Barcode->barcode)
		);

		if ($centersEnabled === true){
			if ($useCenter == '1'){
				$selectBox = htmlBase::newElement('selectbox')
					->setId('inventory_center')
					->setName('inventory_center[' . $reservationId . ']')
					->attr('defaultValue', $invCenterId);
				foreach($invCenterArray as $invInfo){
					if ($centersStockMethod == 'Store'){
						$selectBox->addOption($invInfo['stores_id'], $invInfo['stores_name']);
					}
					else {
						$selectBox->addOption($invInfo['inventory_center_id'], $invInfo['inventory_center_name']);
					}
				}
				$selectBox->selectOptionByValue($invCenterId);
				$BodyColumns[] = array(
					'text' => $selectBox
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

		$days = $Reservation->end_date->diff(new SesDateTime());
		if ($days->days > 0){
			$datsLate = $days->days . ' Days Until Due';
		}
		elseif ($days->days == 0) {
			$datsLate = 'Due Today';
		}
		else {
			$datsLate = $days->format('%a Days Late');
		}

		$BodyColumns[] = array(
			'text' => $datsLate
		);

		$BodyColumns[] = array(
			'text' => tep_draw_textarea_field('comment[' . $Reservation->id . ']', true, 30, 2)
		);

		if (sysConfig::get('EXTENSION_PAY_PER_RENTALS_USE_MAINTENANCE') == 'False'){
			$BodyColumns[] = array(
				'text' => htmlBase::newCheckbox()->setName('damaged[' . $Reservation->id . ']')
					->setValue($reservationId)
			);
			$BodyColumns[] = array(
				'text' => htmlBase::newCheckbox()->setName('lost[' . $Reservation->id . ']')->setValue($reservationId)
			);
		}

		$BodyColumns[] = array(
			'text' => ''
		);

		$TableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-sale_id'        => $Reservation->SaleProduct->Sale->sale_id,
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
