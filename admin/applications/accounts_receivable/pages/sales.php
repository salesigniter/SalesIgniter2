<?php
$SalesModule = AccountsReceivableModules::getModule($_GET['type']);
$QSales = $SalesModule->getSalesQuery();

$SalesGrid = htmlBase::newGrid()
	->setMainDataKey('sale_id')
	->useSorting(true)
	->useSearching(true)
	->usePagination(true)
	->allowMultipleRowSelect(true)
	->setQuery($QSales);

if ($SalesModule->canExport() === true){
	DataManagementModules::loadModule('accountsReceivable');
	$ExportModule = DataManagementModules::getModule('accountsReceivable');
	if ($ExportModule){
		$SalesGrid->useCsvExport(true)
			->setCsvFields($ExportModule->getSupportedColumns());
	}
}

$gridButtons = array();

if ($SalesModule->canShowDetails() === true){
	$gridButtons[] = htmlBase::newElement('button')
		->usePreset('details')
		->addClass('detailsButton')
		->disable();
}

$gridButtons[] = htmlBase::newElement('button')
	->usePreset('delete')
	->addClass('deleteButton')
	->disable();

if ($SalesModule->canCancel() === true){
	$gridButtons[] = htmlBase::newElement('button')
		->usePreset('cancel')
		->addClass('cancelButton')
		->disable();
}

if ($SalesModule->canPrint() === true){
	$SalesModule->getPrintButtons(&$gridButtons);
}

if (sysConfig::get('SHOW_PACKING_SLIP_BUTTONS') == 'true'){
	$gridButtons[] = htmlBase::newElement('button')->setText('Packing Slip')->addClass('packingSlipButton')->disable();
}

EventManager::notify('SalesGridButtonsBeforeAdd', &$gridButtons);

$SalesGrid->addButtons($gridButtons);

$StatusDrop = htmlBase::newElement('selectbox')
	->setName('search_status');
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
	array(
		'text'      => 'ID',
		'useSort'   => true,
		'sortKey'   => 'sale_id',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj(htmlBase::newElement('input')->attr('size', 4)->setName('search_sale_id'))
			->setDatabaseColumn('sale_id')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_REVISION'),
		'useSearch' => true,
		'searchObj' =>  GridSearchObj::Equal()
			->useFieldObj(htmlBase::newElement('input')->attr('size', 4)->setName('search_sale_revision'))
			->setDatabaseColumn('sale_revision')
	),
	array(
		'text'      => 'Type',
		'useSort'   => true,
		'sortKey'   => 'sale_module',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj(htmlBase::newElement('input')->attr('size', 4)->setName('search_sale_module'))
			->setDatabaseColumn('sale_module')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_CUSTOMERS'),
		'useSort'   => true,
		'sortKey'   => 'customers_name',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Like()
			->useFieldObj(htmlBase::newElement('input')->setName('search_customer_name'))
			->setDatabaseColumn('customers_name')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_ORDER_TOTAL'),
		'useSort'   => true,
		'sortKey'   => 'sale_total'
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_DATE_PURCHASED'),
		'useSort'   => true,
		'sortKey'   => 'date_added',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Between()
			->useFieldObj(htmlBase::newElement('input')->attr('size', 10)->addClass('makeDatepicker')
			->setName('search_date_added'))
			->setDatabaseColumn('date_added')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_STATUS'),
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj($StatusDrop)
			->setDatabaseColumn('sale_status_id')
	)
);

//EventManager::notify('OrdersListingAddGridHeader', &$gridHeaderColumns);

$SalesGrid->addHeaderRow(array(
	'columns' => $gridHeaderColumns
));

$Sales = $SalesGrid->getResults();
foreach($Sales as $sInfo){
	$Sale = new Order();

	$SaleModule = AccountsReceivableModules::getModule($sInfo['sale_module']);
	$SaleModule->load($Sale, true, $sInfo['sale_id'], $sInfo['sale_revision']);

	$gridBodyColumns = array(
		array('text' => $Sale->getId()),
		array('align' => 'center', 'text' => $Sale->getRevision()),
		array('text' => $Sale->getSaleModule()->getTitle()),
		array('text' => $Sale->getCustomersName()),
		array(
			'text'  => $currencies->format($Sale->getTotal()),
			'align' => 'right'
		),
		array(
			'text'  => $Sale->getDateAdded()->format(sysLanguage::getDateFormat('long')),
			'align' => 'center'
		),
		array(
			'text'  => $Sale->getStatusName(),
			'align' => 'center'
		)
	);

	//EventManager::notify('OrdersListingAddGridBody', &$order, &$gridBodyColumns);

	$SalesGrid->addBodyRow(array(
		'rowAttr' => array(
			'data-sale_id' => $Sale->getId(),
			'data-sale_module' => $Sale->getSaleModule()->getCode(),
			'data-revision' => $Sale->getRevision()
		),
		'columns' => $gridBodyColumns
	));
}
?>
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $SalesGrid->draw();?></div>
</div>
