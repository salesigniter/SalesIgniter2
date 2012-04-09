<?php
$tableGrid = htmlBase::newElement('newGrid')
	->useSearching(true)
	->useSorting(true)
	->usePagination(true);

$Type = (isset($_GET['type']) ? $_GET['type'] : 'rental');
if ($Type == 'rental'){
	$Qorders = Doctrine_Query::create()
	->from('RentedProducts rp')
	->leftJoin('rp.Customers c')
	->leftJoin('rp.Products p')
	->leftJoin('p.ProductsDescription pd')
	->leftJoin('rp.ProductsInventoryBarcodes pib');

	$f = false;
	if (isset($_GET['start_date'])){
		$Qorders->andWhere('rp.shipment_date=?', $_GET['start_date']);
	}

	if (isset($_GET['end_date'])){
		$Qorders->andWhere('rp.return_date=?', $_GET['end_date']);
	}

	if(isset($_GET['sortDateSent'])){
		$Qorders->orderBy('rp.shipment_date '.$_GET['sortDateSent']);
		$f = true;
	}
	if(isset($_GET['sortDateReturned'])){
		$Qorders->orderBy('rp.return_date '.$_GET['sortDateReturned']);
		$f = true;
	}

	if(isset($_GET['sortName'])){
		$Qorders->orderBy('CONCAT(c.customers_firstname, c.customers_lastname)  '.$_GET['sortName']);
		$f = true;
	}

	if(isset($_GET['sortProduct'])){
		$Qorders->orderBy('pd.products_name '.$_GET['sortProduct']);
		$f = true;
	}

	if(isset($_GET['sortBarcode'])){
		$Qorders->orderBy('pib.barcode '.$_GET['sortBarcode']);
		$f = true;
	}

	$gridHeaderColumns = array(
		array('text' => '<a href="'.itw_app_link('appExt=payPerRentals&type='.$Type.'&sortName='.(isset($_GET['sortName'])?($_GET['sortName'] == 'ASC'?'DESC':'ASC'):'ASC'),null,null).'">'.sysLanguage::get('TABLE_HEADING_NAME').'</a>'),
		array('text' => '<a href="'.itw_app_link('appExt=payPerRentals&type='.$Type.'&sortProduct='.(isset($_GET['sortProduct'])?($_GET['sortProduct'] == 'ASC'?'DESC':'ASC'):'ASC'),null,null).'">'.sysLanguage::get('TABLE_HEADING_PRODUCT').'</a>'),
		array('text' => '<a href="'.itw_app_link('appExt=payPerRentals&type='.$Type.'&sortBarcode='.(isset($_GET['sortBarcode'])?($_GET['sortBarcode'] == 'ASC'?'DESC':'ASC'):'ASC'),null,null).'">'.sysLanguage::get('TABLE_HEADING_BARCODE').'</a>'),
		array('text' => '<a href="'.itw_app_link('appExt=payPerRentals&type='.$Type.'&sortDateSent='.(isset($_GET['sortDateSent'])?($_GET['sortDateSent'] == 'ASC'?'DESC':'ASC'):'ASC'),null,null).'">'.sysLanguage::get('TABLE_HEADING_DATE_SENT').'</a>'),
		array('text' => '<a href="'.itw_app_link('appExt=payPerRentals&type='.$Type.'&sortDateReturned='.(isset($_GET['sortDateReturned'])?($_GET['sortDateReturned'] == 'ASC'?'DESC':'ASC'):'ASC'),null,null).'">'.sysLanguage::get('TABLE_HEADING_DATE_RETURNED').'</a>'),
	);
}else{
	$Qorders = Doctrine_Query::create()
		->from('OrdersProductsReservation opr')
		->leftJoin('opr.ProductsInventoryBarcodes ib')
		->whereIn('rental_state', array('reserved'));

	$gridHeaderColumns = array(
		array(
			'text' => sysLanguage::get('TABLE_HEADING_NAME')
		),
		array(
			'text' => sysLanguage::get('TABLE_HEADING_PRODUCT')
		),
		array(
			'text' => sysLanguage::get('TABLE_HEADING_BARCODE'),
			'useSort' => true,
			'sortKey' => 'ib.barcode'
		),
		array(
			'text' => sysLanguage::get('HEADING_TITLE_START_DATE'),
			'useSort' => true,
			'sortKey' => 'opr.start_date',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Between()
				->useFieldObj(htmlBase::newElement('input')->addClass('makeDatepicker')->setName('start_date')->attr('size', 10))
				->setDatabaseColumn('opr.start_date')
		),
		array(
			'text' => sysLanguage::get('HEADING_TITLE_END_DATE'),
			'useSort' => true,
			'sortKey' => 'opr.end_date',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Between()
				->useFieldObj(htmlBase::newElement('input')->addClass('makeDatepicker')->setName('end_date')->attr('size', 10))
				->setDatabaseColumn('opr.end_date')
		),
		array(
			'text' => sysLanguage::get('TABLE_HEADING_DATE_SENT'),
			'useSort' => true,
			'sortKey' => 'opr.date_shipped',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Between()
				->useFieldObj(htmlBase::newElement('input')->addClass('makeDatepicker')->setName('date_shipped')->attr('size', 10))
				->setDatabaseColumn('opr.date_shipped')
		),
		array(
			'text' => sysLanguage::get('TABLE_HEADING_DATE_RETURNED'),
			'useSort' => true,
			'sortKey' => 'opr.date_returned',
			'useSearch' => true,
			'searchObj' => GridSearchObj::Between()
				->useFieldObj(htmlBase::newElement('input')->addClass('makeDatepicker')->setName('date_returned')->attr('size', 10))
				->setDatabaseColumn('opr.date_returned')
		),
		array(
			'text' => ''
		)
	);
}
$tableGrid->setQuery($Qorders);

$searchForm = htmlBase::newElement('form')
	->attr('name', 'search')
	->attr('id', 'searchFormOrders')
	->attr('action', itw_app_link('appExt=payPerRentals','sendreturn_reports', 'default'))
	->attr('method', 'get');

$typeSelect = htmlBase::newElement('selectbox')
	->setName('type')
	->setLabel('Type: ')
	->setLabelPosition('before');

/*$startdateField = htmlBase::newElement('input')
	->setName('start_date')
	->setLabel('Event Date: ')
	->setLabelPosition('before')
	->setId('start_date');

if (isset($_GET['start_date']) && !empty($_GET['start_date'])){
	$startdateField->val($_GET['start_date']);
} */
$typeSelect->selectOptionByValue($Type);
$typeSelect->addOption('rental','Rental');
$typeSelect->addOption('reservation','Reservation');

$submitButton = htmlBase::newElement('button')
->setType('submit')
->usePreset('save')
->setText('Search');

$searchForm
->append($typeSelect)
->append($submitButton);

$tableGrid->addHeaderRow(array(
		'columns' => $gridHeaderColumns
));

$orders = &$tableGrid->getResults(false);
$total = 0;
if ($orders){
	foreach($orders as $oInfo){
		if($Type == 'rental'){
			$vId = $oInfo['rented_products_id'];
			//foreach($order['Products'] as $orderp){
				$gridBodyColumns = array(
					array('text' => $oInfo['Customers']['customers_firstname'] .' '.$oInfo['Customers']['customers_lastname']),
					array('text' => $oInfo['Products']['ProductsDescription'][Session::get('languages_id')]['products_name']),
					array('text' => $oInfo['ProductsInventoryBarcodes']['barcode']),
					array('text' => $oInfo['shipment_date']),
					array('text' => $oInfo['return_date'])

				);
				$tableGrid->addBodyRow(array(
						'rowAttr' => array(
							'data-order_id' => $vId
						),
						'columns' => $gridBodyColumns
				));
			//}
		}else{
			$Reservation = $oInfo;
			$OrderProduct = $Reservation->OrdersProducts;
			$Order = $OrderProduct->getOrder();
			$Customer = $Order->Customers;

			$gridBodyColumns = array(
				array('text' => $Customer->customers_firstname .' '.$Customer->customers_lastname),
				array('text' => $OrderProduct->products_name),
				array('text' => $Reservation->ProductsInventoryBarcodes->barcode),
				array('text' => $Reservation->start_date->format(sysLanguage::getDateFormat('short'))),
				array('text' => $Reservation->end_date->format(sysLanguage::getDateFormat('short'))),
				array('text' => $Reservation->date_shipped->format(sysLanguage::getDateFormat('short'))),
				array('text' => $Reservation->date_returned->format(sysLanguage::getDateFormat('short'))),
				array('text' => '')
			);

			$tableGrid->addBodyRow(array(
				'rowAttr' => array(
					'data-order_id' => $Order->orders_id
				),
				'columns' => $gridBodyColumns
			));
		}

	}
}

?>
<div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE');?></div>
<div style="width:100%"><?php
	echo $searchForm->draw();
	?></div>
<br />
<div>
	<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
		<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
	</div>
</div>

