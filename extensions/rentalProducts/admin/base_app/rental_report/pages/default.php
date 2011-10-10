<?php
PurchaseTypeModules::loadModule('rental');
$PurchaseType = PurchaseTypeModules::getModule('rental');

$Qorders = Doctrine_Query::create()
	->from('Orders o')
	->leftJoin('o.OrdersAddresses oa')
	->leftJoin('o.OrdersProducts op')
	->leftJoin('op.OrdersProductsRentals opr')
	->leftJoin('opr.ProductsInventoryBarcodes ib');

if (isset($_GET['status'])){
	$Qorders->where('opr.rental_state = ?', (int)$_GET['status']);
}
else {
	$Qorders->where('opr.rental_state != ?', 0);
}

EventManager::notify('RentalReportOrdersQueryBeforeExecute', $Qorders);

$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1))
	->setQuery($Qorders);

$tableGrid->addButtons(array(
		htmlBase::newElement('button')->addClass('returnBarcodeButton')->setText('Return By Barcode')
	));

$StatusSelect = htmlBase::newElement('selectbox')
	->css('vertical-align', 'middle')
	->setName('status');

$StatusSelect->addOption('', sysLanguage::get('TEXT_ALL_STATUSES'));
foreach(getOrderStatuses(null, (int)Session::get('languages_id')) as $sInfo){
	$StatusSelect->addOption(
		$sInfo['orders_status_id'],
		$sInfo['OrdersStatusDescription'][(int)Session::get('languages_id')]['orders_status_name']
	);
}

$showStatusReset = false;
if (isset($_GET['status']) && !empty($_GET['status'])){
	$StatusSelect->selectOptionByValue((int)$_GET['status']);
	$showStatusReset = true;
}

$showReservedReset = false;
$reservedFilterFrom = htmlBase::newElement('input')->attr('size', 9)->addClass('makeDatepicker')
	->css('vertical-align', 'middle')->setName('reserved_from');
if (isset($_GET['reserved_from']) && !empty($_GET['reserved_from'])){
	$reservedFilterFrom->val($_GET['reserved_from']);
	$showReservedReset = true;
}

$reservedFilterTo = htmlBase::newElement('input')->attr('size', 9)->addClass('makeDatepicker')
	->css('vertical-align', 'middle')->setName('reserved_to');
if (isset($_GET['reserved_to']) && !empty($_GET['reserved_to'])){
	$reservedFilterTo->val($_GET['reserved_to']);
	$showReservedReset = true;
}

$showShippedReset = false;
$shippedFilterFrom = htmlBase::newElement('input')->attr('size', 9)->addClass('makeDatepicker')
	->css('vertical-align', 'middle')->setName('shipped_from');
if (isset($_GET['shipped_from']) && !empty($_GET['shipped_from'])){
	$shippedFilterFrom->val($_GET['shipped_from']);
	$showShippedReset = true;
}

$shippedFilterTo = htmlBase::newElement('input')->attr('size', 9)->addClass('makeDatepicker')
	->css('vertical-align', 'middle')->setName('shipped_to');
if (isset($_GET['shipped_to']) && !empty($_GET['shipped_to'])){
	$shippedFilterTo->val($_GET['shipped_to']);
	$showShippedReset = true;
}

$showReturnedReset = false;
$returnedFilterFrom = htmlBase::newElement('input')->attr('size', 9)->addClass('makeDatepicker')
	->css('vertical-align', 'middle')->setName('returned_from');
if (isset($_GET['returned_from']) && !empty($_GET['returned_from'])){
	$returnedFilterFrom->val($_GET['returned_from']);
	$showReturnedReset = true;
}

$returnedFilterTo = htmlBase::newElement('input')->attr('size', 9)->addClass('makeDatepicker')
	->css('vertical-align', 'middle')->setName('returned_to');
if (isset($_GET['returned_to']) && !empty($_GET['returned_to'])){
	$returnedFilterTo->val($_GET['returned_to']);
	$showReturnedReset = true;
}

$LateSelect = htmlBase::newElement('selectbox')
	->css('vertical-align', 'middle')
	->setName('is_late');

$LateSelect->addOption('', sysLanguage::get('TEXT_ALL'));
$LateSelect->addOption('1', sysLanguage::get('TEXT_YES'));
$LateSelect->addOption('0', sysLanguage::get('TEXT_NO'));

$showLateReset = false;
if (isset($_GET['is_late']) && !empty($_GET['is_late'])){
	$LateSelect->selectOptionByValue((int)$_GET['is_late']);
	$showLateReset = true;
}

$goButton = htmlBase::newElement('button')
	->addClass('applyFilterButton')
	->setText(sysLanguage::get('TEXT_APPLY_FILTER'));

$clearButton = htmlBase::newElement('button')
	->setHref(itw_app_link('appExt=rentalProducts', 'rental_report', 'default'))
	->setText(sysLanguage::get('TEXT_CLEAR_FILTER'));

$clearFilter = htmlBase::newElement('span')
	->addClass('ui-icon ui-icon-cancel')
	->css('vertical-align', 'middle')
	->attr('tooltip', sysLanguage::get('TEXT_INFO_CLEAR_FILTER'));

$returnButton = htmlBase::newElement('button')
	->addClass('returnButton')
	->setText(sysLanguage::get('TEXT_BUTTON_RETURN_RENTAL'));

$sendButton = htmlBase::newElement('button')
	->addClass('sendButton')
	->setText(sysLanguage::get('TEXT_BUTTON_SEND_RENTAL'));

$gridHeaders = array();
EventManager::notify('RentalReportsGridAddHeaderColFront', &$gridHeaders);
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMER'));
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS'));
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_BARCODE'));
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_DATE_RESERVED'));
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_DATE_PICKED_UP'));
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_DATE_RETURNED'));
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_LATE'));
$gridHeaders[] = array('text' => sysLanguage::get('TABLE_HEADING_STATUS'));
EventManager::notify('RentalReportsGridAddHeaderColBack', &$gridHeaders);
$gridHeaders[] = array('text' => '');

$tableGrid->addHeaderRow(array(
		'columns' => $gridHeaders
	));

$gridFilterHeaders = array();
EventManager::notify('RentalReportsGridFilterAddHeaderColFront', &$gridFilterHeaders);
$gridFilterHeaders[] = array('text' => '');
$gridFilterHeaders[] = array('text' => '');
$gridFilterHeaders[] = array('text' => '');
$gridFilterHeaders[] = array(
	'text' => $reservedFilterFrom->draw() . ' - ' . $reservedFilterTo->draw() .
		($showReservedReset === true ? ' ' . $clearFilter->draw() : '')
);
$gridFilterHeaders[] = array(
	'text' => $shippedFilterFrom->draw() . ' - ' . $shippedFilterTo->draw() .
		($showShippedReset === true ? ' ' . $clearFilter->draw() : '')
);
$gridFilterHeaders[] = array(
	'text' => $returnedFilterFrom->draw() . ' - ' . $returnedFilterTo->draw() .
		($showReturnedReset === true ? ' ' . $clearFilter->draw() : '')
);
$gridFilterHeaders[] = array(
	'text' => $LateSelect->draw() .
		($showLateReset === true ? ' ' . $clearFilter->draw() : '')
);
$gridFilterHeaders[] = array(
	'text' => $StatusSelect->draw() .
		($showStatusReset === true ? ' ' . $clearFilter->draw() : '')
);
EventManager::notify('RentalReportsGridFilterAddHeaderColBack', &$gridFilterHeaders);
$gridFilterHeaders[] = array(
	'text' => $goButton->draw() . ' ' . $clearButton->draw()
);

$tableGrid->addHeaderRow(array(
		'columns' => $gridFilterHeaders
	));

$Orders = &$tableGrid->getResults();
$NowDate = new DateTime();
if ($Orders){
	foreach($Orders as $oInfo){
		foreach($oInfo['OrdersProducts'] as $opInfo){
			$isReserved = ($opInfo['OrdersProductsRentals']['rental_state'] == $PurchaseType->getConfigData('RENTAL_STATUS_RESERVED'));
			$isOut = ($opInfo['OrdersProductsRentals']['rental_state'] == $PurchaseType->getConfigData('RENTAL_STATUS_OUT'));
			$isReturned = ($opInfo['OrdersProductsRentals']['rental_state'] == $PurchaseType->getConfigData('RENTAL_STATUS_RETURNED'));

			$StartDate = new DateTime($opInfo['OrdersProductsRentals']['start_date']);
			$EndDate = new DateTime($opInfo['OrdersProductsRentals']['end_date']);

			$ShipDate = false;
			if (!empty($opInfo['OrdersProductsRentals']['date_shipped'])){
				$ShipDate = new DateTime($opInfo['OrdersProductsRentals']['date_shipped']);
			}

			$ReturnDate = false;
			if (!empty($opInfo['OrdersProductsRentals']['date_returned'])){
				$ReturnDate = new DateTime($opInfo['OrdersProductsRentals']['date_returned']);
			}

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
				'text' => ($StartDate->getTimestamp() > 0 ? $StartDate->format(sysLanguage::getDateFormat()) : 'N/A'),
				'align' => 'center'
			);
			$gridBodyColumns[] = array(
				'addCls' => 'column-date_shipped',
				'text' => ($ShipDate !== false ? $ShipDate->format(sysLanguage::getDateFormat()) : 'N/A'),
				'align' => 'center'
			);
			$gridBodyColumns[] = array(
				'addCls' => 'column-date_returned',
				'text' => ($ReturnDate !== false ? $ReturnDate->format(sysLanguage::getDateFormat()) : 'N/A'),
				'align' => 'center'
			);
			$gridBodyColumns[] = array(
				'text' => $lateInfo,
				'align' => 'center'
			);
			$gridBodyColumns[] = array(
				'addCls' => 'column-rental_state',
				'text' => tep_translate_order_statuses($opInfo['OrdersProductsRentals']['rental_state']),
				'align' => 'center'
			);

			EventManager::notify('RentalReportsGridAddBodyColBack', &$gridBodyColumns, $oInfo, $opInfo);

			$gridBodyColumns[] = array(
				'text' => ($isReserved === true ? $sendButton->draw() . ' ' : '') .
					($isOut === true ? $returnButton->draw() . ' ' : ''),
				'align' => 'center'
			);

			$tableGrid->addBodyRow(array(
					'rowAttr' => array(
						'data-orders_id' => $oInfo['orders_id'],
						'data-orders_products_id' => $opInfo['orders_products_id'],
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