<?php
$MultiStore = $appExtension->getExtension('multiStore');

$Qbarcodes = Doctrine_Query::create()
	->from('ProductsInventoryBarcodesToStores b2s')
	->leftJoin('b2s.ProductsInventoryBarcodes b')
	->leftJoin('b.ProductsInventoryBarcodesTransfers bt')
	->leftJoin('b2s.Stores s')
	->leftJoin('b.ProductsInventory i')
	->leftJoin('i.Products p')
	->leftJoin('p.ProductsDescription pd')
	->whereIn('b2s.inventory_store_id', Session::get('admin_allowed_stores'))
	->orderBy('b2s.inventory_store_id, b.barcode');

$statusMode = false;
if (isset($_GET['status']) && !empty($_GET['status'])){
	$statusMode = $_GET['status'];
	$Qbarcodes
		->andWhere('bt.is_history = ?', '0')
		->andWhere('bt.status = ?', $statusMode);
	
	if ($statusMode == 'S'){
		$Qbarcodes->andWhere('bt.tracking_number IS NOT NULL');
	}
}

$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 50))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qbarcodes);

$tableGrid->addButtons(array(
		htmlBase::newElement('button')->setText(sysLanguage::get('TEXT_BUTTON_CREATE_SHIPMENT'))->addClass('createShipmentButton'),
		htmlBase::newElement('button')->setText(sysLanguage::get('TEXT_BUTTON_RECEIVE_SHIPMENT'))->addClass('receiveShipmentButton'),
		htmlBase::newElement('button')->setText(sysLanguage::get('TEXT_BUTTON_UPDATE_FROM_CSV'))->addClass('csvButton')
	));

$headerColumns = array();
if ($statusMode !== false){
	$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_DATE') . $transferStatuses[$_GET['status']]);
	if ($statusMode == 'S' || $statusMode == 'R'){
		$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_TRACKING_NUMBER'));
	}elseif ($statusMode == 'E' || $statusMode == 'P'){
		$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_ORIGIN_STORE'));
	}
}
$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_BARCODE'));
$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_PRODUCT_NAME'));

if ($statusMode !== false){
	if ($statusMode == 'S' || $statusMode == 'R'){
		$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_ORIGIN_STORE'));
		$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_DESTINATION_STORE'));
	}elseif ($statusMode == 'E' || $statusMode == 'P'){
		$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_DESTINATION_STORE'));
	}
}
else {
	$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_CURRENT_STORE'));
}

$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_TRANSFER_STATUS'));
$headerColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_INFO'));

$tableGrid->addHeaderRow(array(
		'columns' => $headerColumns
	));

$StatusSelect = htmlBase::newElement('selectbox')
	->css('vertical-align', 'middle')
	->setName('status');

$StatusSelect->addOption('', sysLanguage::get('TEXT_ALL_STATUSES'));
foreach($transferStatuses as $k => $v){
	$StatusSelect->addOption($k, $v);
}

$showStatusReset = false;
if (isset($_GET['status']) && !empty($_GET['status'])){
	$StatusSelect->selectOptionByValue($_GET['status']);
	$showStatusReset = true;
}
/*
$StoreSelect = htmlBase::newElement('selectbox')
	->css('vertical-align', 'middle')
	->setName('store');

$StoreSelect->addOption('', sysLanguage::get('TEXT_ALL_STORES'));
foreach(Session::get('admin_allowed_stores') as $v){
	$StoreInfo = $MultiStore->getStoresArray($v);
	$StoreSelect->addOption($v, $StoreInfo['stores_name']);
}

$showStoreReset = false;
if (isset($_GET['status']) && !empty($_GET['store'])){
	$StoreSelect->selectOptionByValue((int)$_GET['store']);
	$showStoreReset = true;
}
*/
$goButton = htmlBase::newElement('button')
	->addClass('applyFilterButton')
	->setText(sysLanguage::get('TEXT_BUTTON_APPLY_FILTER'));

$clearButton = htmlBase::newElement('button')
	->setHref(itw_app_link('appExt=multiStore', 'inventory', 'default'))
	->setText(sysLanguage::get('TEXT_BUTTON_CLEAR_FILTER'));

$clearFilter = htmlBase::newElement('span')
	->addClass('ui-icon ui-icon-cancel')
	->css('vertical-align', 'middle')
	->attr('tooltip', sysLanguage::get('TEXT_INFO_CLEAR_FILTER'));

$gridFilterHeaders = array();
if ($statusMode !== false){
	$gridFilterHeaders[] = array('text' => '');
	$gridFilterHeaders[] = array('text' => '');
}
$gridFilterHeaders[] = array('text' => '');
$gridFilterHeaders[] = array('text' => '');

if ($statusMode !== false){
	if ($statusMode == 'S' || $statusMode == 'R'){
		$gridFilterHeaders[] = array('text' => '');
		$gridFilterHeaders[] = array('text' => '');
	}elseif ($statusMode == 'E' || $statusMode == 'P'){
		$gridFilterHeaders[] = array('text' => '');
	}
}
else {
	$gridFilterHeaders[] = array('text' => '');
}

$gridFilterHeaders[] = array(
	'text' => $StatusSelect->draw() .
		($showStatusReset === true ? ' ' . $clearFilter->draw() : '')
);
//EventManager::notify('RentalReportsGridFilterAddHeaderColBack', &$gridFilterHeaders);
$gridFilterHeaders[] = array(
	'text' => $goButton->draw() . ' ' . $clearButton->draw()
);

$tableGrid->addHeaderRow(array(
		'columns' => $gridFilterHeaders
	));

$Barcodes = &$tableGrid->getResults();
if ($Barcodes){
	$currentTrackingNum = '';
	$currentFromStore = '';
	foreach($Barcodes as $bInfo){
		$transferStatus = 'N/A';
		$currentTrans = false;

		$BarcodeInfo = $bInfo['ProductsInventoryBarcodes'];

		if (
			isset($BarcodeInfo['ProductsInventoryBarcodesTransfers']) &&
			!empty($BarcodeInfo['ProductsInventoryBarcodesTransfers'])
		){
			$currentTrans = $BarcodeInfo['ProductsInventoryBarcodesTransfers'][0];
			if ($currentTrans['is_history'] == 1){
				$currentTrans = false;
			}else{
				$transferStatus = $transferStatuses[$currentTrans['status']];
			}
		}

		if ($statusMode !== false){
			if ($statusMode == 'S' || $statusMode == 'R'){
				if ($currentTrackingNum != $currentTrans['tracking_number']){
					$currentTrackingNum = $currentTrans['tracking_number'];
					$addTrackingNum = true;
				}
				else {
					$addTrackingNum = false;
				}

				if ($addTrackingNum === true){
					if ($currentTrans !== false){
						$DateAdded = new DateTime($currentTrans['date_added']);
						$DateFormatted = $DateAdded->format(sysLanguage::getDateFormat());

						$OriginInfo = $MultiStore->getStoresArray($currentTrans['origin_id']);
						$DestInfo = $MultiStore->getStoresArray($currentTrans['destination_id']);

						$OriginName = $OriginInfo['stores_name'];
						$DestName = $DestInfo['stores_name'];
					}
					else {
						$DateFormatted = 'Err';
						$OriginName = 'Err';
						$DestName = 'Err';
					}

					$tableGrid->addBodyRow(array(
							'addCls' => 'noHover',
							'columns' => array(
								array('text' => $DateFormatted),
								array('text' => $currentTrackingNum),
								array('text' => ''),
								array('text' => ''),
								array('text' => $OriginName),
								array('text' => $DestName),
								array('text' => $transferStatuses[$currentTrans['status']]),
								array('text' => '')
							)
						));
				}
			}elseif ($statusMode == 'E' || $statusMode == 'P'){
				if ($currentFromStore != $currentTrans['origin_id']){
					$currentFromStore = $currentTrans['origin_id'];
					$addFromStore = true;
				}
				else {
					$addFromStore = false;
				}

				if ($addFromStore === true){
					if ($currentTrans !== false){
						$DateAdded = new DateTime($currentTrans['date_added']);
						$DateFormatted = $DateAdded->format(sysLanguage::getDateFormat());

						$OriginInfo = $MultiStore->getStoresArray($currentTrans['origin_id']);
						$DestInfo = $MultiStore->getStoresArray($currentTrans['destination_id']);

						$OriginName = $OriginInfo['stores_name'];
						$DestName = $DestInfo['stores_name'];
					}
					else {
						$DateFormatted = 'Err';
						$OriginName = 'Err';
						$DestName = 'Err';
					}

					$tableGrid->addBodyRow(array(
							'addCls' => 'noHover',
							'columns' => array(
								array('text' => $DateFormatted),
								array('text' => $OriginName),
								array('text' => ''),
								array('text' => ''),
								array('text' => $DestName),
								array('text' => $transferStatuses[$currentTrans['status']]),
								array('text' => '')
							)
						));
				}
			}
		}

		$bodyCols = array();
		if ($statusMode !== false){
			$bodyCols[] = array('text' => '');
			$bodyCols[] = array('text' => '');
		}
		$bodyCols[] = array('text' => $BarcodeInfo['barcode']);
		$bodyCols[] = array('text' => $BarcodeInfo['ProductsInventory']['Products']['ProductsDescription'][Session::get('languages_id')]['products_name']);

		if ($statusMode !== false){
			if ($statusMode == 'S' || $statusMode == 'R'){
				$bodyCols[] = array('text' => '');
				$bodyCols[] = array('text' => '');
			}elseif ($statusMode == 'E' || $statusMode == 'P'){
				$bodyCols[] = array('text' => '');
			}
			$bodyCols[] = array('text' => '');
			$bodyCols[] = array('text' => '', 'align' => 'center');
		}
		else {
			$bodyCols[] = array('text' => $bInfo['Stores']['stores_name']);
			$bodyCols[] = array('text' => $transferStatus, 'align' => 'center');
			$bodyCols[] = array('text' => htmlBase::newElement('icon')->setType('info')->draw(), 'align' => 'center');
		}

		$tableGrid->addBodyRow(array(
				'rowAttr' => array(
					'data-barcode_id' => $BarcodeInfo['barcode_id']
				),
				'columns' => $bodyCols
			));

		if ($statusMode === false){
			$transferTable = htmlBase::newElement('table')
				->setCellPadding(3)
				->setCellSpacing(0)
				->css('width', '500px');
			if (
				isset($BarcodeInfo['ProductsInventoryBarcodesTransfers']) &&
				!empty($BarcodeInfo['ProductsInventoryBarcodesTransfers'])
			){
				$transferTable->addHeaderRow(array(
						'columns' => array(
							array('text' => 'Date Added', 'align' => 'left'),
							array('text' => 'Origin', 'align' => 'left'),
							array('text' => 'Destination', 'align' => 'left'),
							array('text' => 'Status', 'align' => 'left'),
							array('text' => 'Tracking Number', 'align' => 'left')
						)
					));
				foreach($BarcodeInfo['ProductsInventoryBarcodesTransfers'] as $k => $tInfo){
					$DateAdded = new DateTime($tInfo['date_added']);

					$OriginInfo = $MultiStore->getStoresArray($tInfo['origin_id']);
					$DestInfo = $MultiStore->getStoresArray($tInfo['destination_id']);

					$transferTable->addBodyRow(array(
							'columns' => array(
								array('text' => $DateAdded->format(sysLanguage::getDateFormat())),
								array('text' => $OriginInfo['stores_name']),
								array('text' => $DestInfo['stores_name']),
								array('text' => $transferStatuses[$tInfo['status']]),
								array('text' => $tInfo['tracking_number'])
							)
						));
				}
			}

			$tableGrid->addBodyRow(array(
					'addCls' => 'gridInfoRow',
					'columns' => array(
						array('colspan' => 5, 'text' => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
							'<tr>' .
							'<td><b>' . sysLanguage::get('TEXT_INFO_STORE_STATUS') . '</b></td>' .
							'</tr>' .
							'<tr>' .
							'<td>' . $barcodeStatuses[$BarcodeInfo['status']] . '</td>' .
							'</tr>' .
							'<tr>' .
							'<td><b>' . sysLanguage::get('TEXT_INFO_TRANSFER_HISTORY') . '</b></td>' .
							'</tr>' .
							'<tr>' .
							'<td>' . $transferTable->draw() . '</td>' .
							'</tr>' .
							'</table>')
					)
				));
		}
	}
}
?>
<style>
	.noHover td { font-weight:bold; background: #90aac6; }
</style>
<script>
	function getStoreMenu(name){
		var stores = [];
		stores.push({id: '', text: 'Please Select'});
<?php
foreach($MultiStore->getStoresArray() as $sInfo){
		echo 'stores.push({id: ' . $sInfo['stores_id'] . ', text: \'' . addslashes($sInfo['stores_name']) . '\'});' . "\n";
	}
	?>
		var options = [];
		$.each(stores, function (){
			options.push('<option value="' + this.id + '">' + this.text + '</option>');
		});
		return '<select name="' + name + '">' + options.join('') + '</select>';
	}
</script>
<div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE');?></div>
<br />
<div class="gridContainer">
	<div style="width:100%;float:left;">
		<div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
			<div style="width:99%;margin:5px;"><?php echo $tableGrid->draw();?></div>
		</div>
	</div>
</div>
