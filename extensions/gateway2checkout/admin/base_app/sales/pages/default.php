<?php
$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1));

$gridButtons = array(
	htmlBase::newElement('button')->setText('Details')->addClass('detailsButton')->disable(),
	//htmlBase::newElement('button')->setText('Delete')->addClass('deleteButton')->disable(),
	//htmlBase::newElement('button')->setText('Cancel')->addClass('cancelButton')->disable(),
	//htmlBase::newElement('button')->setText('Invoice')->addClass('invoiceButton')->disable(),
	//htmlBase::newElement('button')->setText('Packing Slip')->addClass('packingSlipButton')->disable()
);

$tableGrid->addButtons($gridButtons);

$gridHeaderColumns = array(
	array('text' => '&nbsp;'),
	array('text' => sysLanguage::get('TABLE_HEADING_SALE_NUMBER')),
	array('text' => sysLanguage::get('TABLE_HEADING_DATE_PLACED')),
	array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMER_NAME')),
	array('text' => sysLanguage::get('TABLE_HEADING_RECURRING')),
	array('text' => sysLanguage::get('TABLE_HEADING_ORDER_TOTAL'))
);

$gridHeaderColumns[] = array('text' => sysLanguage::get('TABLE_HEADING_INFO'));

$tableGrid->addHeaderRow(array(
	'columns' => $gridHeaderColumns
));

$PaymentModule = OrderPaymentModules::getModule('gateway2checkout', true);
$Orders = $PaymentModule->getOrders();
if ($Orders){
	foreach($Orders->sale_summary as $oInfo){
		$gridBodyColumns = array(
			array('text' => '&nbsp;'),
			array('text' => $oInfo->sale_id),
			array('text' => $oInfo->date_placed),
			array('text' => $oInfo->customer_name),
			array('text' => ($oInfo->recurring == 0 ? sysLanguage::get('TEXT_NO') : sysLanguage::get('TEXT_YES'))),
			array('text' => $currencies->format($oInfo->usd_total, false, 'USD', 1))
		);

		$gridBodyColumns[] = array('text' => htmlBase::newElement('icon')->setType('info')->draw());

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-sale_id' => $oInfo->sale_id
			),
			'columns' => $gridBodyColumns
		));

	}
}
/*$orders = &$tableGrid->getResults();
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
			array('text' => $htmlCheckbox->draw(), 'align' => 'center'),
			array('text' => $orderId),
			array('text' => $order['OrdersAddresses']['customer']['entry_name']),
			array('text' => strip_tags($order['order_total']), 'align' => 'right'),
			array('text' => tep_datetime_short($order['date_purchased']), 'align' => 'center'),
			array('text' => $order['OrdersStatus']['OrdersStatusDescription'][Session::get('languages_id')]['orders_status_name'], 'align' => 'center')
		);

		EventManager::notify('OrdersListingAddGridBody', &$order, &$gridBodyColumns);

		$gridBodyColumns[] = array('text' => $arrowIcon->draw(), 'align' => 'right');

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-order_id' => $orderId
			),
			'columns' => $gridBodyColumns
		));

		$tableGrid->addBodyRow(array(
			'addCls' => 'gridInfoRow',
			'columns' => array(
				array(
					'colspan' => sizeof($gridBodyColumns),
					'text' => '<table cellpadding="1" cellspacing="0" border="0" width="75%">' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ORDER_CREATED') . '</b></td>' .
						'<td> ' . tep_date_short($order['date_purchased']) . '</td>' .
						'<td><b>' . sysLanguage::get('TEXT_DATE_ORDER_LAST_MODIFIED') . '</b></td>' .
						'<td>' . tep_date_short($order['last_modified']) . '</td>' .
						'<td></td>' .
						'</tr>' .
						'<tr>' .
						'<td><b>' . sysLanguage::get('TEXT_INFO_PAYMENT_METHOD') . '</b></td>' .
						'<td>'  . $order['payment_module'] . '</td>' .
						'</tr>' .
						'</table>'
				)
			)
		));
	}
}*/
?>
<div class="pageHeading"><?php echo sysLanguage::get('HEADING_TITLE');?></div>
<br />
<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
</div>
