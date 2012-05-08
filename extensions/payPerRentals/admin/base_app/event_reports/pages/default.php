<?php
$Qreservations = Doctrine_Query::create()
	->from('OrdersProductsReservation')
	->whereIn('rental_state', array('out', 'reserved'))
	->andWhere('parent_id IS NULL');
/*
	$Qorders = Doctrine_Query::create()
	->from('Orders o')
	->leftJoin('o.Customers c')
	->leftJoin('o.OrdersTotal ot')
	->leftJoin('o.OrdersProducts op')
	->leftJoin('op.Products p')
	->leftJoin('p.ProductsDescription pd')
	->leftJoin('op.OrdersProductsReservation opr')
	->leftJoin('o.OrdersStatus s')
	->andWhere('o.orders_status != ?', sysConfig::get('ORDERS_STATUS_ESTIMATE_ID'))
	->andWhere('pd.language_id = ?', Session::get('languages_id'))
	->andWhereIn('ot.module_type', array('total', 'ot_total'))
	->andWhereIn('opr.rental_state', array('out', 'reserved'));

$f = false;
if (isset($_GET['start_date'])){
	$Qorders->andWhere('opr.start_date=?', $_GET['start_date']);
}

if (isset($_GET['event_name']) && !empty($_GET['event_name'])){
	$Qorders->andWhere('opr.event_name = ?', $_GET['event_name']);
}

if(isset($_GET['sortEvent'])){
	$Qorders->orderBy('opr.event_name '.$_GET['sortEvent']);
	$f = true;
}

if(isset($_GET['sortDate'])){
	$Qorders->orderBy('o.date_purchased '.$_GET['sortDate']);
	$f = true;
}

if(isset($_GET['sortDateReserved'])){
	$Qorders->orderBy('opr.start_date '.$_GET['sortDateReserved']);
	$f = true;
}


if(isset($_GET['sortGate'])){
	$Qorders->orderBy('opr.event_gate '.$_GET['sortGate']);
	$f = true;
}

if(isset($_GET['sortLastname'])|| !is_array($_GET)){
	$Qorders->orderBy('c.customers_lastname '.$_GET['sortLastname']);
	$f = true;
}

if(isset($_GET['sortFirstname'])){
	$Qorders->orderBy('c.customers_firstname '.$_GET['sortFirstname']);
	$f = true;
}

if(isset($_GET['sortProduct'])){
	$Qorders->orderBy('pd.products_name '.$_GET['sortProduct']);
	$f = true;
}

if(isset($_GET['sortPrice'])){
	$Qorders->orderBy('ot.value '.$_GET['sortPrice']);
	$f = true;
}

if(isset($_GET['sortQty'])){
	$Qorders->orderBy('op.products_quantity '.$_GET['sortQty']);
	$f = true;
}

if(isset($_GET['sortInsurance'])){
	$Qorders->orderBy('opr.insurance '.$_GET['sortInsurance']);
	$f = true;
}

if($f == false){
	$Qorders->orderBy('opr.start_date '.$_GET['sortDateReserved']);
	$Qorders->orderBy('c.customers_lastname '.$_GET['sortLastname']);
}
*/
$Qevents = Doctrine_Query::create()
	->from('PayPerRentalEvents')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

$eventSelect = htmlBase::newElement('selectbox')
	->setName('event_name');

$eventSelect->addOption('','all');
foreach($Qevents as $iEvent){
	$eventSelect->addOption($iEvent['events_name'], $iEvent['events_name']);
}

$tableGrid = htmlBase::newElement('newGrid')
	->useSorting(true)
	->useSearching(true)
	->usePagination(true)
	->setQuery($Qreservations);

$gridHeaderColumns = array(
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_EVENT'),
		'useSort'   => true,
		'sortKey'   => 'opr.event_name',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Equal()
			->useFieldObj($eventSelect)
			->setDatabaseColumn('opr.event_name')
	),
	array(
		'text'      => sysLanguage::get('TABLE_HEADING_DATE_RESERVED'),
		'useSort'   => true,
		'sortKey'   => 'opr.start_date',
		'useSearch' => true,
		'searchObj' => GridSearchObj::Between()
			->useFieldObj(htmlBase::newElement('input')->attr('size', 10)->addClass('makeDatepicker')->setName('search_start_date'))
			->setDatabaseColumn('opr.start_date')
	),
	array(
		'text'    => sysLanguage::get('TABLE_HEADING_GATE'),
		'useSort' => true,
		'sortKey' => 'opr.event_gate'
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_LASTNAME')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_FIRSTNAME')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_PRODUCT_NAME')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_QUANTITY')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_INSURANCE')
	),
	array(
		'text' => sysLanguage::get('TABLE_HEADING_PRICE')
	)
);

$tableGrid->addHeaderRow(array(
		'columns' => $gridHeaderColumns
	));

$rentedProd = array();

$Reservations = &$tableGrid->getResults(false);
$total = 0;
if ($Reservations){
	foreach($Reservations as $Reservation){
		$OrderProduct = $Reservation->OrdersProducts;
		$Order = $OrderProduct->getOrder();
		$Customer = $Order->Customers;

		$OrderId = $Order->orders_id;
		$EventName = $Reservation->event_name;
		$ProductModel = $OrderProduct->Products->products_model;
		$OrderedQuantity = $OrderProduct->products_quantity;
		$OrderedFinalPrice = $OrderProduct->final_price;
		$OrderedCalculatedCost = $OrderedFinalPrice * $OrderedQuantity;

		$gridBodyColumns = array(
			array('text' => $EventName),
			//array('text' => $Order->date_purchased->format(sysLanguage::getDateFormat('short'))),
			array('text' => $Reservation->start_date->format(sysLanguage::getDateFormat('short'))),
			array('text' => $Reservation->event_gate),
			array('text' => $Customer->customers_lastname),
			array('text' => $Customer->customers_firstname),
			array('text' => $OrderProduct->products_name),
			array('text' => $OrderedQuantity),
			array('text' => $Reservation->insurance),
			array('text' => $currencies->format($OrderedCalculatedCost))

		);
		$total += $OrderedCalculatedCost;
		if (!isset($rentedProd[$ProductModel])){
			$rentedProd[$ProductModel][$EventName] = 0;
		}
		$rentedProd[$ProductModel][$EventName] += $OrderedQuantity;
		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-order_id' => $OrderId
			),
			'columns' => $gridBodyColumns
		));

		$orderId = $order['orders_id'];
	}
}

if (isset($_GET['event_name']) && !empty($_GET['event_name'])){
	$Qevents = Doctrine_Query::create()
	->from('PayPerRentalEvents')
	->where('events_name=?', $_GET['event_name'])
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);



	$avail = '';
	foreach($rentedProd as $model => $qty){
		$QProductEvents = Doctrine_Query::create()
		->from('ProductQtyToEvents')
		->where('events_id = ?', $Qevents[0]['events_id'])
		->andWhere('products_model = ?', $model)
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

		if($QProductEvents){
			$avail .= '<b>Availability for model: '. $model .' is '. ($QProductEvents[0]['qty']-$qty[$Qevents[0]['events_name']]) .' items</b><br/>';
		}
	}
}else{
	$Qevents = Doctrine_Query::create()
		->from('PayPerRentalEvents')
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);



	$avail = '';
	foreach($rentedProd as $model => $qty){
		foreach($Qevents as $iEvent){
			$QProductEvents = Doctrine_Query::create()
				->from('ProductQtyToEvents')
				->where('events_id = ?', $iEvent['events_id'])
				->andWhere('products_model = ?', $model)
				->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

			if($QProductEvents){
				$avail .= '<b>Availability for model: '. $model .' for event: "'.$iEvent['events_name'].'" is '. ($QProductEvents[0]['qty']-$qty[$iEvent['events_name']]) .' items</b><br/>';
			}
		}
	}
}

$gridBodyColumns = array(
	array('text' => ''),
	array('text' => ''),
	array('text' => ''),
	array('text' => ''),
	array('text' => ''),
	array('text' => ''),
	array('text' => ''),
	array('text' => ''),
	array('text' =>'<b>Total for the day:</b>'. $currencies->format($total))

);
$tableGrid->addBodyRow(array(
		'columns' => $gridBodyColumns
));

?>
<div>
	<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
		<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
		<div style="margin-left:30px;margin-top:10px;"><?php echo $avail;?></div>
	</div>
</div>

