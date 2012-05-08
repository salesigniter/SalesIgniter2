<?php
	$Qmaint = Doctrine_Query::create()
	->from('Products p')
	->leftJoin('p.ProductsInventory pi')
	->leftJoin('pi.ProductsInventoryBarcodes pib')
	->leftJoin('pib.BarcodeHistoryRented bhr')
	->where('pib.status = ?','Q');


$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setQuery($Qmaint);

$gridHeaderColumns = array(
	array('text' => sysLanguage::get('TABLE_HEADING_BARCODE')),
	array('text' => sysLanguage::get('TABLE_HEADING_MODEL')),
	array('text' => sysLanguage::get('TABLE_HEADING_LAST_MAINTENANCE'))

);

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
						array('text' => $model),
						array('text' => $last_maintenance[3]->format(sysLanguage::getDateFormat('long')))

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
<div style="width:100%"><?php
	echo 'Current Date: '. date(sysLanguage::getDateFormat('long')) .'<br/>';
	?></div>
<br />
	<div>
		<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
			<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
		</div>
	</div>

