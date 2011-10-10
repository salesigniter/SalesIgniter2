<?php
	$Qmaint = Doctrine_Query::create()
	->from('PayPerRentalMaintenance pm')
	->leftJoin('pm.Admin a')
	->leftJoin('pm.PayPerRentalMaintenanceRepairs pmr')
	->leftJoin('pm.ProductsInventoryBarcodes pib')
	->leftJoin('pmr.PayPerRentalMaintenanceRepairParts pmrp');

	if(isset($_GET['stat_date'])){
		$Qmaint = $Qmaint->where('maintenance_date >= ?',$_GET['start_date']);
	}

$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);

$gridHeaderColumns = array(
	array('text' => sysLanguage::get('TABLE_HEADING_BARCODE')),
	array('text' => sysLanguage::get('TABLE_HEADING_TYPE')),
	array('text' => sysLanguage::get('TABLE_HEADING_ADMIN')),
	array('text' => sysLanguage::get('TABLE_HEADING_DATE')),
	array('text' => sysLanguage::get('TABLE_HEADING_PRICE')),
	array('text' => sysLanguage::get('TABLE_HEADING_PARTS_USED')),
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

$submitButton = htmlBase::newElement('button')
	->setType('submit')
	->usePreset('save')
	->setText('Search');

$searchForm
->append($limitField)
->append($startdateField)
->append($submitButton);

$tableGrid->addHeaderRow(array(
		'columns' => $gridHeaderColumns
	));


$maintenances = &$tableGrid->getResults();
if ($maintenances){
	foreach($maintenances as $maintenance){
				$mId = $maintenance['pay_per_rental_maintenance_id'];
		        $eventType = '8-points check';
				$price = 0;
				$priceParts = 0;
				$parts = '';
				foreach($maintenance['PayPerRentalMaintenanceRepairs'] as $repair){
					$price += $repair['price'];
					foreach($repair['PayPerRentalMaintenanceRepairParts'] as $part){
						$priceParts += $part['part_price'];
						$parts .= $part['part_name'].'; ';
					}
				}
		        switch($maintenance['type']){
			       case '1': $eventType = '8-points check';
			            break;
			       case '2': $eventType = 'bi-weekly';
			                  break;
			       case '3': $eventType = '6-months';
			                  break;
		        }
				$gridBodyColumns = array(
					array('text' => $maintenance['ProductsInventoryBarcodes']['barcode']),
					array('text' => $eventType),
					array('text' => $maintenance['Admin']['admin_firstname'].' '.$maintenance['Admin']['admin_lastname']),
					array('text' => strftime(sysLanguage::getDateFormat('long'), strtotime($maintenance['maintenance_date']))),
					array('text' => $currencies->format($price)),
					array('text' => $parts),
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
		</div>
	</div>

