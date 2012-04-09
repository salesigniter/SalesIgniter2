<?php
$tableGrid = htmlBase::newElement('newGrid')
	->usePagination(true)
	->setPageLimit((isset($_GET['limit']) ? (int)$_GET['limit'] : 25))
	->setCurrentPage((isset($_GET['page']) ? (int)$_GET['page'] : 1));

$gridButtons = array(
	htmlBase::newElement('button')->setText('Details')->addClass('detailsButton')->disable(),
	htmlBase::newElement('button')->setText('Delete')->addClass('deleteButton')->disable(),
	htmlBase::newElement('button')->setText('Cancel')->addClass('cancelButton')->disable(),
	htmlBase::newElement('button')->setText('Invoice')->addClass('invoiceButton')->disable(),
	htmlBase::newElement('button')->setText('Packing Slip')->addClass('packingSlipButton')->disable()
);

$tableGrid->addButtons($gridButtons);

$gridHeaderColumns = array(
	array('text' => '&nbsp;'),
	array('text' => 'ID'),
	array('text' => sysLanguage::get('TABLE_HEADING_CUSTOMERS')),
	array('text' => sysLanguage::get('TABLE_HEADING_ORDER_TOTAL')),
	array('text' => sysLanguage::get('TABLE_HEADING_DATE_PURCHASED')),
	array('text' => sysLanguage::get('TABLE_HEADING_STATUS'))
);

$gridHeaderColumns[] = array('text' => 'info');

$tableGrid->addHeaderRow(array(
	'columns' => $gridHeaderColumns
));

$PaymentModule = OrderPaymentModules::getModule('gateway2checkout', true);
$Pending = $PaymentModule->getPendingPayment();
if ($Pending){
	$PendingTable = htmlBase::newElement('table')
		->addClass('pendingPayment')
		->setCellPadding(2)
		->setCellSpacing(0);

	$PendingTable->addHeaderRow(array(
		'columns' => array(
			array('colspan' => 5, 'text' => 'Pending Payment Information', 'addCls' => 'ui-widget-header ui-state-hover')
		)
	));

	$CurrencyCode = $Pending->currency;

	$PendingTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Amount'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Fee'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Payment Id'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Payment Method'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Release Level')
		)
	));

	$PendingTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->amount, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->payment_fee, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $Pending->payment_id),
			array('addCls' => 'pendingPaymentData', 'text' => $Pending->payment_method),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->release_level, false, $CurrencyCode))
		)
	));

	$PendingTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Reserve Held'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Fees'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Adjustments'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Balance forward'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Chargeback Fees')
		)
	));

	$PendingTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->reserve_held, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_fees, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_adjustments, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_balance_forward, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_chargeback_fees, false, $CurrencyCode))
		)
	));

	$PendingTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Commissions'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Outgoing Commissions'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Refunds'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Reserve Released'),
			array('addCls' => 'pendingPaymentHeading', 'text' => 'Total Sales')
		)
	));

	$PendingTable->addBodyRow(array(
		'columns' => array(
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_commissions, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_outgoing_commissions, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_refunds, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_reserve_released, false, $CurrencyCode)),
			array('addCls' => 'pendingPaymentData', 'text' => $currencies->format($Pending->total_sales, false, $CurrencyCode))
		)
	));
}
$Past = $PaymentModule->getPastPayments();

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
<div style="width:100%;float:left;">
	<div class="ui-widget ui-widget-content ui-corner-all" style="width:99%;margin-right:5px;margin-left:5px;">
		<?php if (isset($PendingTable)){?>
		<div style="width:99%;margin:5px;"><?php echo $PendingTable->draw();?></div>
		<?php }?>
		<div style="width:99%;margin:5px;"><?php echo $tableGrid->draw();?></div>
	</div>
</div>
