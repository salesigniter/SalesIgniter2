<?php
	$Qmaint = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsInventory pi')
	->leftJoin('pi.ProductsInventoryBarcodes pib')
	->leftJoin('pib.BarcodeHistoryRented bhr')
	->leftJoin('p.Manufacturers m');

	$multiStore = $appExtension->getExtension('multiStore');
   if ($multiStore !== false && $multiStore->isEnabled() === true){
	   $Qmaint->leftJoin('pib.ProductsInventoryBarcodesToStores pibs')
	   ->andWhereIn('pibs.inventory_store_id', Session::get('admin_showing_stores'));
   }

	if(isset($_GET['start_date']) && !empty($_GET['start_date'])){
		$Qmaint->where('bhr.last_maintenance_date >= ?',$_GET['start_date']);
	}

	if(isset($_GET['end_date']) && !empty($_GET['end_date'])){
		$Qmaint->andWhere('bhr.last_maintenance_date <= ?',$_GET['end_date']);
	}

	if(isset($_GET['status']) && $_GET['status'] != 'All'){
		$Qmaint->andWhere('pib.status = ?',$_GET['status']);
	}

	if(isset($_GET['maintenance_type']) && $_GET['maintenance_type'] != '0'){
		$Qmaint->andWhere('bhr.last_maintenance_type = ?', $_GET['maintenance_type']);
	}


$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);

$gridHeaderColumns = array(
	array('text' => sysLanguage::get('TABLE_HEADING_BARCODE')),
	array('text' => sysLanguage::get('TABLE_HEADING_BARCODE_TYPE')),
	array('text' => sysLanguage::get('TABLE_HEADING_MANUFACTURER')),
	array('text' => sysLanguage::get('TABLE_HEADING_MODEL')),
	array('text' => sysLanguage::get('TABLE_HEADING_LAST_MAINTENANCE_DATE')),
	array('text' => sysLanguage::get('TABLE_HEADING_LAST_MAINTENANCE_TYPE')),
	array('text' => sysLanguage::get('TABLE_HEADING_STATUS')),
	array('text' => sysLanguage::get('TABLE_HEADING_END_DATE_LAST_RENT'))

);

$limitField = htmlBase::newElement('selectbox')
	->setName('limit')
	->setLabel('Items per page: ')
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
	->attr('action', itw_app_link('appExt=payPerRentals','items_reports', 'default'))
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

$statusHtml = htmlBase::newElement('selectbox')
->setName('status');

if(isset($_GET['status'])){
	$statusHtml->selectOptionByValue($_GET['status']);
}

$statusHtml->addOption('All','All');
$statusHtml->addOption('A','Available');
$statusHtml->addOption('M','Maintenance');


$maintenanceTypeHtml = htmlBase::newElement('selectbox')
->setName('maintenance_type');

if(isset($_GET['maintenance_type'])){
	$maintenanceTypeHtml->selectOptionByValue($_GET['maintenance_type']);
}

$QPayPerRentalMaintenancePeriods = Doctrine_Query::create()
->from('PayPerRentalMaintenancePeriods')
->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

$maintenanceTypeHtml->addOption('0', 'Any');

foreach($QPayPerRentalMaintenancePeriods as $mInfo){
	$maintenanceTypeHtml->addOption($mInfo['maintenance_period_id'], $mInfo['maintenance_period_name']);
}


$submitButton = htmlBase::newElement('button')
	->setType('submit')
	->usePreset('save')
	->setText('Search');

$searchForm
	->append($limitField)
	->append($startdateField)
	->append($enddateField)
	->append($statusHtml)
	->append($maintenanceTypeHtml)
	->append($submitButton);

$tableGrid->addHeaderRow(array(
		'columns' => $gridHeaderColumns
	));

$products = &$tableGrid->getResults();
if ($products){
	foreach($products as $product){
		foreach($product['ProductsInventory'] as $inv){
			foreach($inv['ProductsInventoryBarcodes'] as $pib){
				$mId = $pib['barcode_id'];
				$model = $product['products_model'];
				$manufacturer = $product['Manufacturers']['manufacturers_name'];
				$last_maintenance = $pib['BarcodeHistoryRented'][0]['last_maintenance_date'];
				$QPayPerRentalMaintenancePeriods = Doctrine_Core::getTable('PayPerRentalMaintenancePeriods')->find($pib['BarcodeHistoryRented'][0]['last_maintenance_type']);
				if($QPayPerRentalMaintenancePeriods){
					$last_maintenance_type = $QPayPerRentalMaintenancePeriods->maintenance_period_name;
				}else{
					$last_maintenance_type = 'None';
				}

				$Qreservations = Doctrine_Query::create()
					->from('OrdersProductsReservation opr')
					->leftJoin('opr.ProductsInventoryBarcodes ib')
					->where('ib.barcode_id = ?', $mId)
					->andWhereIn('opr.rental_state', array('out','reserved'))
					->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

				$status = 'Available';
				$date_return = 'Not Applicable';
				if($pib['status'] == 'M'){
					$status = 'Maintenance';
				}else{
					if(isset($Qreservations[0])){
						if($Qreservations[0]['rental_state'] == 'reserved'){
							$status = 'Reserved';
						}else{
							$status = 'On Hire';
						}
						$date_return = $Qreservations[0]['end_date'];
					}
				}

				$gridBodyColumns = array(
					array('text' => $pib['barcode']),
					array('text' => is_null($pib['barcode_type'])?'None':$pib['barcode_type']),
					array('text' => $manufacturer),
					array('text' => $model),
					array('text' => (isset($last_maintenance) && $last_maintenance != '0000-00-00 00:00:00')?strftime(sysLanguage::getDateFormat('long'), strtotime($last_maintenance)):'None'),
					array('text' => (isset($last_maintenance_type))?$last_maintenance_type:''),
					array('text' => $status),
					array('text' => ($date_return != 'Not Applicable'?strftime(sysLanguage::getDateFormat('long'), strtotime($date_return)):$date_return))
				);
				$tableGrid->addBodyRow(array(
						'rowAttr' => array(
							'data-order_id' => $mId
						),
						'columns' => $gridBodyColumns
					));
			}
		}
	}
}

?>
<div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE');?></div>
<div style="width:100%"><?php
	echo $searchForm->draw().'<br/>';
	echo 'Current Date: '. strftime(sysLanguage::getDateFormat('long'), strtotime(date('Y-m-d'))).'<br/>';
	?></div>
<br />
<div style="width:100%;float:left;">
	<div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
		<div style="width:99%;margin:5px;"><?php echo $tableGrid->draw();?></div>
		<br style="clear:both;"/> <br/>
		<?php echo htmlBase::newElement('button')
		->setText(sysLanguage::get('TEXT_BUTTON_GENERATE_CSV'))
		->setHref(itw_app_link('action=csvExport&appExt=payPerRentals'.(isset($_GET['start_date'])?'&start_date='.$_GET['start_date']:'').(isset($_GET['end_date'])?'&end_date='.$_GET['end_date']:'').(isset($_GET['status'])?'&status='.$_GET['status']:'').(isset($_GET['maintenance_type'])?'&maintenance_type='.$_GET['maintenance_type']:''), 'items_reports', 'default'))
		->draw();
		?>
	</div>
</div>

