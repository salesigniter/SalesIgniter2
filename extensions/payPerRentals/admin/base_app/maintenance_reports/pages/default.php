<?php
	$Qmaint = Doctrine_Query::create()
	->from('BarcodeHistoryRented bhr')
	->leftJoin('bhr.ProductsInventoryBarcodes pib')
	->leftJoin('pib.PayPerRentalMaintenanceRepairs pmr')
	->leftJoin('pmr.Admin a')
	->leftJoin('pmr.PayPerRentalMaintenanceRepairParts pmrp');

	if(isset($_GET['start_date']) && !empty($_GET['start_date'])){
		$Qmaint->where('bhr.last_maintenance_date >= ?',$_GET['start_date']);
	}

	if(isset($_GET['end_date']) && !empty($_GET['end_date'])){
		$Qmaint->andWhere('bhr.last_maintenance_date <= ?',$_GET['end_date']);
	}

	$multiStore = $appExtension->getExtension('multiStore');
	if ($multiStore !== false && $multiStore->isEnabled() === true){
		$Qmaint->leftJoin('pib.ProductsInventoryBarcodesToStores pibs')
			->andWhereIn('pibs.inventory_store_id', Session::get('admin_showing_stores'));
	}


$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);

$gridHeaderColumns = array(
	array('text' => sysLanguage::get('TABLE_HEADING_BARCODE')),
	array('text' => sysLanguage::get('TABLE_HEADING_BARCODE_TYPE')),
	array('text' => sysLanguage::get('TABLE_HEADING_TYPE')),
	array('text' => sysLanguage::get('TABLE_HEADING_ADMIN')),
	array('text' => sysLanguage::get('TABLE_HEADING_DATE')),
	array('text' => sysLanguage::get('TABLE_HEADING_NEXT_DATE')),
	array('text' => sysLanguage::get('TABLE_HEADING_PRICE')),
	array('text' => sysLanguage::get('TABLE_HEADING_PARTS_USED')),
	array('text' => sysLanguage::get('TABLE_HEADING_REPAIR_DESC')),
	array('text' => sysLanguage::get('TABLE_HEADING_PARTS_PRICE'))

);

$limitField = htmlBase::newElement('selectbox')
	->setName('limit')
	->setLabel('Maintenance Events per Page: ')
	->setLabelPosition('before');

$limitField->addOption('25','25');
$limitField->addOption('100','100');
$limitField->addOption('250','250');

if (isset($_GET['limit']) && !empty($_GET['limit'])){
	$limitField->selectOptionByValue($_GET['limit']);
}

$searchForm = htmlBase::newElement('form')
	->attr('name', 'search')
	->attr('id', 'searchForm')
	->attr('action', itw_app_link('appExt=payPerRentals','maintenance_reports', 'default'))
	->attr('method', 'get');

$startdateField = htmlBase::newElement('input')
	->setName('start_date')
	->setLabel('Start Date: ')
	->setLabelPosition('before')
	->setId('start_date');

if (isset($_GET['start_date']) && !empty($_GET['start_date'])){
	$startdateField->val($_GET['start_date']);
}

$enddateField = htmlBase::newElement('input')
	->setName('end_date')
	->setLabel('End Date: ')
	->setLabelPosition('before')
	->setId('end_date');

if (isset($_GET['end_date']) && !empty($_GET['end_date'])){
	$enddateField->val($_GET['end_date']);
}

$submitButton = htmlBase::newElement('button')
	->setType('submit')
	->usePreset('save')
	->setText('Search');

$searchForm
->append($limitField)
->append($startdateField)
->append($enddateField)
->append($submitButton);

$tableGrid->addHeaderRow(array(
		'columns' => $gridHeaderColumns
	));


$maintenances = &$tableGrid->getResults();
if ($maintenances){
	foreach($maintenances as $maintenance){
				$mId = $maintenance['barcode_id'];
		        $eventType = '8-points check';
				$price = 0;
				$priceParts = 0;
				$admin = '';
				$parts = '';
				$repairDesc = '';
		        $mNext = 0;
				$QMaintenancePeriodDays = Doctrine_Query::create()
					->from('PayPerRentalMaintenancePeriods')
					->where('before_send = ?', '0')
					->andWhere('after_return = ?', '0')
					->andWhere('is_repair = ?', '0')
					->andWhere('show_number_days > 0')
					->orderBy('show_number_days')
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
				if(isset($QMaintenancePeriodDays[0])){
					$mNext = $QMaintenancePeriodDays[0]['show_number_days'];
				}

				foreach($maintenance['ProductsInventoryBarcodes']['PayPerRentalMaintenanceRepairs'] as $repair){
					$price += $repair['price'];
					$repairDesc .= $repair['comments'];
					foreach($repair['PayPerRentalMaintenanceRepairParts'] as $part){
						$priceParts += $part['part_price'];
						$parts .= $part['part_name'].'; ';
					}
					$admin .= $repair['Admin']['admin_firstname'].' '.$repair['Admin']['admin_lastname'].'<br/>';
				}

		        $QPPRMaintenancePeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->find($maintenance['last_maintenance_type']);
				if($QPPRMaintenancePeriods){
					$eventType = $QPPRMaintenancePeriods->maintenance_period_name;
				}

				$gridBodyColumns = array(
					array('text' => $maintenance['ProductsInventoryBarcodes']['barcode']),
					array('text' => is_null($maintenance['ProductsInventoryBarcodes']['barcode_type'])?'None':$maintenance['ProductsInventoryBarcodes']['barcode_type']),
					array('text' => $eventType),
					array('text' => $admin),
					array('text' => strftime(sysLanguage::getDateFormat('long'), strtotime($maintenance['last_maintenance_date']))),
					array('text' => strftime(sysLanguage::getDateFormat('long'), strtotime('+'.$mNext.' DAY',strtotime($maintenance['last_maintenance_date'])))),
					array('text' => $currencies->format($price)),
					array('text' => $parts),
					array('text' => $repairDesc),
					array('text' => $currencies->format($priceParts))

				);
					$tableGrid->addBodyRow(array(
						'rowAttr' => array(
							'data-order_id' => $mId
						),
						'columns' => $gridBodyColumns
					));
	}
}

?>
<div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE');?></div>
<div style="width:100%"><?php
	echo $searchForm->draw();
	?></div>
<br />
	<div style="width:100%;float:left;">
		<div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
			<div style="width:99%;margin:5px;"><?php echo $tableGrid->draw();?></div>
			<br style="clear:both;"/> <br/>
			<?php echo htmlBase::newElement('button')
			->setText(sysLanguage::get('TEXT_BUTTON_GENERATE_CSV'))
			->setHref(itw_app_link('action=csvExport&appExt=payPerRentals'.(isset($_GET['start_date'])?'&start_date='.$_GET['start_date']:'').(isset($_GET['end_date'])?'&end_date='.$_GET['end_date']:''), 'maintenance_reports', 'default'))
			->draw();
			?>

		</div>
	</div>

