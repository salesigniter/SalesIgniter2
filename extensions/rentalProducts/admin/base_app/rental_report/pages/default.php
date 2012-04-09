<?php
PurchaseTypeModules::loadModule('rental');
$PurchaseType = PurchaseTypeModules::getModule('rental');

$Qorders = Doctrine_Query::create()
	->from('Orders o')
	->leftJoin('o.OrdersAddresses oa')
	->leftJoin('o.OrdersProducts op')
	->leftJoin('op.OrdersProductsRentals opr')
	->leftJoin('opr.ProductsInventoryBarcodes ib');

if (!isset($_GET['search_status'])){
	$Qorders->where('opr.rental_state != ?', 0);
}

EventManager::notify('RentalReportOrdersQueryBeforeExecute', $Qorders);

$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->useSearching(true)
	->useSorting(true)
	->setQuery($Qorders);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('print')->addClass('printLabelsButton')->setText('Print Labels'),
	htmlBase::newElement('button')->addClass('returnBarcodeButton')->setText('Return By Barcode')
));

$StatusSelect = htmlBase::newElement('selectbox')
	->css('vertical-align', 'middle')
	->setName('search_status');

$StatusSelect->addOption('', sysLanguage::get('TEXT_ALL_STATUSES'));
foreach(getOrderStatuses(null, (int)Session::get('languages_id')) as $sInfo){
	$StatusSelect->addOption(
		$sInfo['orders_status_id'],
		$sInfo['OrdersStatusDescription'][(int)Session::get('languages_id')]['orders_status_name']
	);
}

$returnButton = htmlBase::newElement('button')
	->addClass('returnButton')
	->setText(sysLanguage::get('TEXT_BUTTON_RETURN_RENTAL'))
	->draw();

$sendButton = htmlBase::newElement('button')
	->addClass('sendButton')
	->setText(sysLanguage::get('TEXT_BUTTON_SEND_RENTAL'))
	->draw();

$gridHeaders = array();
$gridHeaders[] = array('text' => htmlBase::newElement('checkbox')->addClass('selectAll')->draw());
EventManager::notify('RentalReportsGridAddHeaderColFront', &$gridHeaders);
$gridHeaders[] = array(
	'text'      => sysLanguage::get('TABLE_HEADING_CUSTOMER'),
	'allowSort' => true,
	'sortKey'   => 'oa.entry_name',
	'useSearch' => true,
	'searchObj' => GridSearchObj::Like()
		->setFieldName('search_customer_name')
		->setDatabaseColumn('oa.entry_name')
);
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS'));
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_BARCODE'));
$gridHeaders[] = array(
	'text'      => sysLanguage::get('TABLE_HEADING_DATE_RESERVED'),
	'allowSort' => true,
	'sortKey'   => 'opr.start_date',
	'useSearch' => true,
	'searchObj' => GridSearchObj::Between()
		->useFieldObj(htmlBase::newElement('input')
			->attr('size', 10)
			->addClass('makeDatepicker')
			->setName('search_start_date')
		)
		->setDatabaseColumn('opr.start_date')
);
$gridHeaders[] = array(
	'text'      => sysLanguage::get('TABLE_HEADING_DATE_PICKED_UP'),
	'allowSort' => true,
	'sortKey'   => 'opr.date_shipped',
	'useSearch' => true,
	'searchObj' => GridSearchObj::Between()
		->useFieldObj(htmlBase::newElement('input')
			->attr('size', 10)
			->addClass('makeDatepicker')
			->setName('search_date_shipped')
		)
		->setDatabaseColumn('opr.date_shipped')
);
$gridHeaders[] = array(
	'text'      => sysLanguage::get('TABLE_HEADING_DATE_RETURNED'),
	'allowSort' => true,
	'sortKey'   => 'opr.date_returned',
	'useSearch' => true,
	'searchObj' => GridSearchObj::Between()
		->useFieldObj(htmlBase::newElement('input')
			->attr('size', 10)
			->addClass('makeDatepicker')
			->setName('search_date_returned')
		)
		->setDatabaseColumn('opr.date_returned')
);
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_LATE'));
$gridHeaders[] = array(
	'text'      => sysLanguage::get('TABLE_HEADING_STATUS'),
	'allowSort' => true,
	'sortKey'   => 'opr.rental_state',
	'useSearch' => true,
	'searchObj' => GridSearchObj::Equal()
		->useFieldObj($StatusSelect)
		->setDatabaseColumn('opr.rental_state')
);
EventManager::notify('RentalReportsGridAddHeaderColBack', &$gridHeaders);
$gridHeaders[] = array('text' => '');

$tableGrid->addHeaderRow(array(
	'columns' => $gridHeaders
));

$Orders = &$tableGrid->getResults();
$NowDate = new DateTime();
if ($Orders){
	foreach($Orders as $oInfo){
		foreach($oInfo['OrdersProducts'] as $opInfo){
			$rentalId = $opInfo['OrdersProductsRentals']['orders_products_rentals_id'];
			$isReserved = ($opInfo['OrdersProductsRentals']['rental_state'] == $PurchaseType->getConfigData('RENTAL_STATUS_RESERVED'));
			$isOut = ($opInfo['OrdersProductsRentals']['rental_state'] == $PurchaseType->getConfigData('RENTAL_STATUS_OUT'));
			$isReturned = ($opInfo['OrdersProductsRentals']['rental_state'] == $PurchaseType->getConfigData('RENTAL_STATUS_RETURNED'));

			$StartDate = $opInfo['OrdersProductsRentals']['start_date'];
			$EndDate = $opInfo['OrdersProductsRentals']['end_date'];
			$ShipDate = $opInfo['OrdersProductsRentals']['date_shipped'];
			$ReturnDate = $opInfo['OrdersProductsRentals']['date_returned'];

			$lateInfo = 'N/A';
			if ($isOut === true){
				if ($EndDate < $NowDate){
					$LateDays = $EndDate->diff($NowDate);
					$lateInfo = sysLanguage::get('TEXT_YES') . '<br>' . $LateDays->format('%R%d Day(s) %H Hour(s)');
				}
				else {
					$lateInfo = sysLanguage::get('TEXT_NO');
				}
			}
			elseif ($isReturned === true) {
				$lateInfo = sysLanguage::get('TEXT_NO');
			}

			$gridBodyColumns = array();

			$gridBodyColumns[] = array(
				'text' => htmlBase::newElement('checkbox')->setName('rental[]')->val($rentalId)->draw()
			);
			EventManager::notify('RentalReportsGridAddBodyColFront', &$gridBodyColumns, $oInfo, $opInfo);

			$gridBodyColumns[] = array(
				'text' => $oInfo['OrdersAddresses']['customer']['entry_name']
			);
			$gridBodyColumns[] = array(
				'text' => $opInfo['products_name']
			);
			$gridBodyColumns[] = array(
				'text' => $opInfo['OrdersProductsRentals']['ProductsInventoryBarcodes']['barcode']
			);
			$gridBodyColumns[] = array(
				'text'  => $StartDate->format(sysLanguage::getDateFormat()),
				'align' => 'center'
			);
			$gridBodyColumns[] = array(
				'addCls' => 'column-date_shipped',
				'text'   => $ShipDate->format(sysLanguage::getDateFormat()),
				'align'  => 'center'
			);
			$gridBodyColumns[] = array(
				'addCls' => 'column-date_returned',
				'text'   => $ReturnDate->format(sysLanguage::getDateFormat()),
				'align'  => 'center'
			);
			$gridBodyColumns[] = array(
				'text'  => $lateInfo,
				'align' => 'center'
			);
			$gridBodyColumns[] = array(
				'addCls' => 'column-rental_state',
				'text'   => tep_translate_order_statuses($opInfo['OrdersProductsRentals']['rental_state']),
				'align'  => 'center'
			);

			EventManager::notify('RentalReportsGridAddBodyColBack', &$gridBodyColumns, $oInfo, $opInfo);

			$gridBodyColumns[] = array(
				'text'  => ($isReserved === true ? $sendButton . ' ' : '') .
					($isOut === true ? $returnButton . ' ' : ''),
				'align' => 'center'
			);

			$tableGrid->addBodyRow(array(
				'rowAttr' => array(
					'data-orders_id'                  => $oInfo['orders_id'],
					'data-orders_products_id'         => $opInfo['orders_products_id'],
					'data-orders_products_rentals_id' => $opInfo['OrdersProductsRentals']['orders_products_rentals_id']
				),
				'columns' => $gridBodyColumns
			));
		}
	}
}
?>
<div class="pageHeading"><?php
	echo sysLanguage::get('HEADING_TITLE_RENTAL_REPORTS');
	?></div>
<br />
<div style="width:100%;float:left;">
	<div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
		<div style="width:99%;margin:5px;">
			<?php echo $tableGrid->draw();?>
		</div>
	</div>
</div>