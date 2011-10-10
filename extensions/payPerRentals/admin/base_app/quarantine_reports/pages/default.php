<?php
	$Qmaint = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsInventory pi')
	->leftJoin('pi.ProductsInventoryBarcodes pib')
	->leftJoin('pib.BarcodeHistoryRented bhr')
	->leftJoin('p.Manufacturers m')
	->where('pib.status = ?','Q');


$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qmaint);

$gridHeaderColumns = array(
	array('text' => sysLanguage::get('TABLE_HEADING_BARCODE')),
	array('text' => sysLanguage::get('TABLE_HEADING_MANUFACTURER')),
	array('text' => sysLanguage::get('TABLE_HEADING_MODEL')),
	array('text' => sysLanguage::get('TABLE_HEADING_LAST_MAINTENANCE'))

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
	->attr('action', itw_app_link('appExt=payPerRentals','maintenance_reports', 'default'))
	->attr('method', 'get');


$submitButton = htmlBase::newElement('button')
	->setType('submit')
	->usePreset('save')
	->setText('Search');

$searchForm
->append($limitField)
->append($submitButton);

$tableGrid->addHeaderRow(array(
		'columns' => $gridHeaderColumns
	));

function DateSort($a,$b,$d="-") {
	if ($a == $b) {
		return 0;
	} else {
		$a = strtotime($a);
		$b = strtotime($b);
		if($a<$b) {
			return -1;
		} else {
			return 1;
		}
	}
}

$products = &$tableGrid->getResults();
if ($products){
	foreach($products as $product){
			foreach($product['ProductsInventory'] as $inv){
				foreach($inv['ProductsInventoryBarcodes'] as $pib){
					$mId = $pib['barcode_id'];
					$model = $product['products_model'];
					$manufacturer = $product['Manufacturers']['manufacturers_name'];
					$last_maintenance = array();
					$last_maintenance[] = $pib['BarcodeHistoryRented'][0]['last_maintenance_date'];
					$last_maintenance[] = $pib['BarcodeHistoryRented'][0]['last_biweekly_date'];
					$last_maintenance[] = $pib['BarcodeHistoryRented'][0]['last_monthly_date'];
					$last_maintenance[] = $pib['BarcodeHistoryRented'][0]['last_quarantine_date'];
					usort($last_maintenance, 'DateSort');
					$gridBodyColumns = array(
						array('text' => $pib['barcode']),
						array('text' => $manufacturer),
						array('text' => $model),
						array('text' => strftime(sysLanguage::getDateFormat('long'), strtotime($last_maintenance[3])))

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
		</div>
	</div>

