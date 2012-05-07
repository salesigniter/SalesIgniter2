<?php
require(sysConfig::getDirFsCatalog() . 'includes/modules/dataManagementModules/modules.php');
DataManagementModules::loadModule('orders');
$ExportModule = DataManagementModules::getModule('orders');

$Qorders = Doctrine_Query::create()
	->select('o.orders_id, a.entry_name, o.date_purchased, o.customers_id, o.last_modified, o.currency, o.currency_value, s.orders_status_id, sd.orders_status_name, ot.text as order_total, o.payment_module')
	->from('Orders o')
	->leftJoin('o.OrdersTotal ot')
	->leftJoin('o.OrdersAddresses a')
	->leftJoin('o.OrdersStatus s')
	->leftJoin('s.OrdersStatusDescription sd')
	->where('sd.language_id = ?', Session::get('languages_id'))
	->andWhere('o.orders_status != ?', sysConfig::get('ORDERS_STATUS_ESTIMATE_ID'))
	->andWhereIn('ot.module_type', array('total', 'ot_total'))
	->andWhere('a.address_type = ?', 'customer')
	->orderBy('o.date_purchased desc');

EventManager::notify('AdminOrdersListingBeforeExecute', &$Qorders);

if (isset($_GET['cID'])){
	$Qorders->andWhere('o.customers_id = ?', (int)$_GET['cID']);
}
elseif (isset($_GET['status']) && is_numeric($_GET['status']) && $_GET['status'] > 0) {
	$Qorders->andWhere('s.orders_status_id = ?', (int)$_GET['status']);
}

$tableGrid = htmlBase::newElement('newGrid')
	->useSorting(true)
	->useSearching(true)
	->usePagination(true)
	->useCsvExport(true)
	->setCsvFields($ExportModule->getSupportedColumns())
	->allowMultipleRowSelect(true)
	->setMainDataKey('order_id')
	->setQuery($Qorders);

$gridButtons = array(
	htmlBase::newElement('button')->usePreset('details')->addClass('detailsButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable(),
	htmlBase::newElement('button')->usePreset('cancel')->addClass('cancelButton')->disable(),
	htmlBase::newElement('button')->usePreset('invoice')->addClass('invoiceButton')->disable()
);
if (sysConfig::get('SHOW_PACKING_SLIP_BUTTONS') == 'true'){
	$gridButtons[] = htmlBase::newElement('button')->setText('Packing Slip')->addClass('packingSlipButton')->disable();
}

EventManager::notify('OrdersGridButtonsBeforeAdd', &$gridButtons);

$tableGrid->addButtons($gridButtons);

$SelectAll = htmlBase::newElement('checkbox')
	->setName('select_all')
	->setId('selectAllOrders');

$StatusDrop = htmlBase::newElement('selectbox')
	->setName('search_order_status');
$QStatus = Doctrine_Query::create()
	->from('OrdersStatusDescription')
	->where('language_id = ?', Session::get('languages_id'))
	->orderBy('orders_status_name')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);
$StatusDrop->addOption('', sysLanguage::get('TEXT_PLEASE_SELECT'));
foreach($QStatus as $sInfo){
	$StatusDrop->addOption($sInfo['orders_status_id'], $sInfo['orders_status_name']);
}

$gridHeaderColumns = array(
	array('text' => $SelectAll->draw()),
	array(
		'text'      => 'ID',
		'useSort'   => true,
		'sortKey'   => 'o.orders_id',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj(htmlBase::newElement('input')->attr('size', 4)->setName('search_order_id'))
			->setDatabaseColumn('o.orders_id')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_CUSTOMERS'),
		'useSort'   => true,
		'sortKey'   => 'a.entry_name',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Like()
			->useFieldObj(htmlBase::newElement('input')->setName('search_customer_name'))
			->setDatabaseColumn('a.entry_name')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_ORDER_TOTAL'),
		'useSort'   => true,
		'sortKey'   => 'ot.value'
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_DATE_PURCHASED'),
		'useSort'   => true,
		'sortKey'   => 'o.date_purchased',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Between()
			->useFieldObj(htmlBase::newElement('input')->attr('size', 10)->addClass('makeDatepicker')
			->setName('search_date_purchased'))
			->setDatabaseColumn('o.date_purchased')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_STATUS'),
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj($StatusDrop)
			->setDatabaseColumn('o.orders_status')
	)
);

EventManager::notify('OrdersListingAddGridHeader', &$gridHeaderColumns);

$gridHeaderColumns[] = array('text' => 'info');

$tableGrid->addHeaderRow(array(
	'columns' => $gridHeaderColumns
));

$orders = &$tableGrid->getResults();
$noOrders = false;
if ($orders){
	foreach($orders as $order){
		$orderId = $order['orders_id'];

		$arrowIcon = htmlBase::newElement('icon')->setType('info');

		$htmlCheckbox = htmlBase::newElement('checkbox')
			->setName('selectedOrder[]')
			->addClass('selectedOrder')
			->setValue($orderId);

		$gridBodyColumns = array(
			array(
				'text'  => $htmlCheckbox->draw(),
				'align' => 'center'
			),
			array('text' => $orderId),
			array('text' => $order['OrdersAddresses']['customer']['entry_name']),
			array(
				'text'  => strip_tags($order['order_total']),
				'align' => 'right'
			),
			array(
				'text'  => $order['date_purchased']->format(sysLanguage::getDateFormat('long')),
				'align' => 'center'
			),
			array(
				'text'  => $order['OrdersStatus']['OrdersStatusDescription'][Session::get('languages_id')]['orders_status_name'],
				'align' => 'center'
			)
		);

		EventManager::notify('OrdersListingAddGridBody', &$order, &$gridBodyColumns);

		$gridBodyColumns[] = array(
			'text'  => $arrowIcon->draw(),
			'align' => 'right'
		);

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-order_id' => $orderId
			),
			'columns' => $gridBodyColumns
		));

		$tableGrid->addBodyRow(array(
			'addCls'  => 'gridInfoRow',
			'columns' => array(
				array(
					'colspan' => sizeof($gridBodyColumns),
					'text'    => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ORDER_CREATED') . '</b></td>' .
						'<td> ' . $order['date_purchased']->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ORDER_LAST_MODIFIED') . '</b></td>' .
						'<td>' . $order['last_modified']->format(sysLanguage::getDateFormat('long')) . '</td>' .
						'<td></td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_PAYMENT_METHOD') . '</b></td>' .
						'<td>' . $order['payment_module'] . '</td>' .
						'</tr>' .
						'</table>'
				)
			)
		));
	}
}
switch($action){
	case 'delete':
		$infoBox->setHeader('<b>' . sysLanguage::get('TEXT_INFO_HEADING_DELETE_ORDER') . '</b>');
		$infoBox->setForm(array(
			'name'   => 'orders',
			'action' => itw_app_link(tep_get_all_get_params(array('action', 'oID')) . 'action=deleteConfirm&oID=' . $oInfo->orders_id)
		));

		$deleteButtonReservationRestock = htmlBase::newElement('button')
			->setType('submit')
			->usePreset('delete')
			->setText('Delete');

		$checkBoxDeleteReservation = htmlBase::newElement('checkbox')
			->setName('deleteReservationRestock')
			->setLabel('Delete reservations')
			->setChecked(true)
			->setValue('1');
		$checkBoxDeleteRestock = htmlBase::newElement('checkbox')
			->setName('deleteRestockNoReservation')
			->setLabel('Restock quantity based inventory')
			->setChecked(true)
			->setValue('1');

		$cancelButton = htmlBase::newElement('button')->setType('submit')->usePreset('cancel')
			->setHref(itw_app_link(tep_get_all_get_params(array('action', 'oID')) . 'oID=' . $oInfo->orders_id));

		$infoBox->addButton($deleteButtonReservationRestock)
			->addButton($cancelButton);

		$oID = $_GET['oID'];
		$QOrdersQuery = Doctrine_Query::create()
			->from('Orders o')
			->leftJoin('o.OrdersAddresses oa')
			->leftJoin('o.OrdersProducts op')
			->leftJoin('op.OrdersProductsReservation opr')
			->leftJoin('opr.ProductsInventoryBarcodes ib')
			->leftJoin('ib.ProductsInventory ibi')
			->leftJoin('opr.ProductsInventoryQuantity iq')
			->leftJoin('iq.ProductsInventory iqi')
			->where('o.orders_id = ?', $oID)
			->andWhere('oa.address_type = ?', 'customer')
			->andWhere('parent_id IS NULL');

		$Qorders = $QOrdersQuery->execute();
		$isreservation = false;
		$isquantity = false;
		foreach($Qorders as $oInfo){
			foreach($oInfo->OrdersProducts as $opInfo){

				foreach($opInfo->OrdersProductsReservation as $oprInfo){
					$isreservation = true;
				}

				if ($opInfo['purchase_type'] == 'new ' || $opInfo['purchase_type'] == 'used'){
					$isquantity = true;
				}
			}
		}

		if ($isreservation){
			$infoBox->addContentRow($checkBoxDeleteReservation->draw());
		}

		if ($isquantity){
			$infoBox->addContentRow($checkBoxDeleteRestock->draw());
		}

		$infoBox->addContentRow(sysLanguage::get('TEXT_INFO_DELETE_INTRO'));
		$infoBox->addContentRow('<b>' . $oInfo->OrdersAddresses['customer']['entry_name'] . '</b>');
		//$infoBox->addContentRow(tep_draw_checkbox_field('restock') . ' ' . sysLanguage::get('TEXT_INFO_RESTOCK_PRODUCT_QUANTITY'));
		break;
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
</div>
<?php EventManager::notify('AdminOrdersAfterTableDraw');?>
