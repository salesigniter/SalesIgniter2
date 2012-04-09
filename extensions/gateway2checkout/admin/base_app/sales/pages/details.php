<?php
$PaymentModule = OrderPaymentModules::getModule('gateway2checkout', true);

$CompanyInfo = $PaymentModule->getCompanyInfo();
$currencyCode = $CompanyInfo->currency_code;
$currencyName = $CompanyInfo->currency_name;
$vendorId = $CompanyInfo->vendor_id;
$vendorName = $CompanyInfo->vendor_name;

$OrderDetails = $PaymentModule->getOrderDetails(array(
	'sale_id' => $_GET['saleId']
));
/*
echo '<pre>';
print_r($CompanyInfo);
print_r($OrderDetails);
echo '</pre>';
*/
$Invoices = array();
$SaleTotal = 0;
$SaleRefundTotals = array(
	'vendor'                  => 0,
	strtolower($currencyCode) => 0,
	'customer'                => 0
);
foreach($OrderDetails->invoices as $k => $Invoice){
	$SaleTotal += $Invoice->vendor_total;
	$InvoiceItemsTable = htmlBase::newElement('table')
		->setCellPadding(3)
		->setCellSpacing(0)
		->attr('border', 0)
		->addClass('invoiceItemTable');

	$InvoiceItemsTable->addHeaderRow(array(
		'addCls'  => 'invoiceItemTableHeader',
		'columns' => array(
			array(
				'align' => 'left',
				'text'  => 'ID'
			),
			array(
				'align' => 'left',
				'text'  => 'Description'
			),
			array(
				'align' => 'center',
				'text'  => 'Refund'
			),
			array(
				'align' => 'right',
				'text'  => 'Your Amount<br>(' . $currencyName . ')'
			),
			array(
				'align' => 'right',
				'text'  => $currencyCode . ' Amount'
			),
			array(
				'align' => 'right',
				'text'  => 'Customer Amount<br>(' . $currencyName . ')'
			)
		)
	));

	$InvoiceItemsTable->addFooterRow(array(
		'addCls'  => 'invoiceItemTableFooter',
		'columns' => array(
			array(
				'colspan' => 2,
				'align'   => 'right',
				'text'	=> '<b>Total at Checkout</b>'
			),
			array('text' => '&nbsp;'),
			array(
				'align' => 'right',
				'text'  => $currencies->format($Invoice->vendor_total, false, $currencyCode, 1)
			),
			array(
				'align' => 'right',
				'text'  => $currencies->format($Invoice->{strtolower($currencyCode) . '_total'}, false, $currencyCode, 1)
			),
			array(
				'align' => 'right',
				'text'  => $currencies->format($Invoice->customer_total, false, $currencyCode, 1)
			)
		)
	));

	$InvoiceRefundTotals = array(
		'vendor'                  => 0,
		strtolower($currencyCode) => 0,
		'customer'                => 0
	);
	foreach($Invoice->lineitems as $k => $Item){
		$rowInfo = array();
		$rowInfo['addCls'] = 'invoiceItemTableBody';
		if ($Item->status == 'refund'){
			$rowInfo['addCls'] .= ' refund';
			$productId = '';
			$productName = $Item->product_name;
			$refundButton = SesDateTime::createFromFormat('Y-m-d', $Item->billing->date_start)
				->format(sysLanguage::getDateFormat('long'));

			$SaleRefundTotals['vendor'] += $Item->vendor_amount;
			$SaleRefundTotals[strtolower($currencyCode)] += $Item->{strtolower($currencyCode) . '_amount'};
			$SaleRefundTotals['customer'] += $Item->customer_amount;

			$InvoiceRefundTotals['vendor'] += $Item->vendor_amount;
			$InvoiceRefundTotals[strtolower($currencyCode)] += $Item->{strtolower($currencyCode) . '_amount'};
			$InvoiceRefundTotals['customer'] += $Item->customer_amount;
		}
		else {
			$productId = $Item->vendor_product_id;
			$productName = $Item->product_name . '<br><br>' . $Item->product_description;
			if (isset($Invoice->lineitems[$k + 1]) && $Invoice->lineitems[$k + 1]->status == 'refund'){
				$refundButton = '';
			}
			else {
				$refundButton = htmlBase::newElement('button')
					->setText('Refund Item')
					->addClass('refundLineItemButton')
					->draw();
			}
		}
		$rowInfo['columns'] = array(
			array(
				'align' => 'left',
				'text'  => $productId
			),
			array(
				'align' => 'left',
				'text'  => $productName
			),
			array(
				'align' => 'center',
				'text'  => $refundButton
			),
			array(
				'align' => 'right',
				'text'  => $currencies->format($Item->vendor_amount, false, $currencyCode, 1)
			),
			array(
				'align' => 'right',
				'text'  => $currencies->format($Item->{strtolower($currencyCode) . '_amount'}, false, $currencyCode, 1)
			),
			array(
				'align' => 'right',
				'text'  => $currencies->format($Item->customer_amount, false, $currencyCode, 1)
			)
		);

		$InvoiceItemsTable->addBodyRow($rowInfo);
	}

	if ($InvoiceRefundTotals['vendor'] > 0){
		$InvoiceItemsTable->addFooterRow(array(
			'addCls'  => 'invoiceItemTableFooter refund',
			'columns' => array(
				array(
					'colspan' => 2,
					'align'   => 'right',
					'text'	=> '<b>Total Refunds</b>'
				),
				array('text' => '&nbsp;'),
				array(
					'align' => 'right',
					'text'  => $currencies->format($InvoiceRefundTotals['vendor'], false, $currencyCode, 1)
				),
				array(
					'align' => 'right',
					'text'  => $currencies->format($InvoiceRefundTotals[strtolower($currencyCode)], false, $currencyCode, 1)
				),
				array(
					'align' => 'right',
					'text'  => $currencies->format($InvoiceRefundTotals['customer'], false, $currencyCode, 1)
				)
			)
		));

		$InvoiceItemsTable->addFooterRow(array(
			'addCls'  => 'invoiceItemTableFooter',
			'columns' => array(
				array(
					'colspan' => 2,
					'align'   => 'right',
					'text'	=> '<b>Net Amount</b>'
				),
				array('text' => '&nbsp;'),
				array(
					'align' => 'right',
					'text'  => $currencies->format(($Invoice->vendor_total - $InvoiceRefundTotals['vendor']), false, $currencyCode, 1)
				),
				array(
					'align' => 'right',
					'text'  => $currencies->format(($Invoice->{strtolower($currencyCode) . '_total'} - $InvoiceRefundTotals[strtolower($currencyCode)]), false, $currencyCode, 1)
				),
				array(
					'align' => 'right',
					'text'  => $currencies->format(($Invoice->customer_total - $InvoiceRefundTotals['customer']), false, $currencyCode, 1)
				)
			)
		));
	}

	$InvoiceDataTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->attr('border', 0)
		->css('width', '100%');

	$InvoiceDataTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => 'Invoice'
			),
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => 'Order Date'
			),
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => 'HTTP Referrer'
			)
		)
	));

	$datePlaced = SesDateTime::createFromFormat(DATE_TIMESTAMP, $Invoice->date_placed)
		->format(sysLanguage::getDateFormat('long'));

	$InvoiceDataTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => $Invoice->invoice_id
			),
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => $datePlaced
			),
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => $Invoice->referrer
			)
		)
	));

	$InvoiceDataTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => 'For'
			),
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => 'Paid Date'
			),
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => ''
			)
		)
	));

	$datePaid = SesDateTime::createFromFormat(DATE_TIMESTAMP, $Invoice->date_vendor_paid)
		->format(sysLanguage::getDateFormat('long'));

	$InvoiceDataTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => $vendorName
			),
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => $datePaid
			),
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => ''
			)
		)
	));

	$InvoiceDataTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => 'Vendor'
			),
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => 'Status'
			),
			array(
				'addCls' => 'invoiceOverviewLabel',
				'text'   => ''
			)
		)
	));

	$InvoiceDataTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => $vendorId
			),
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => $Invoice->status
			),
			array(
				'addCls' => 'invoiceOverviewText',
				'text'   => ''
			)
		)
	));

	$InvoiceTable = htmlBase::newElement('table')
		->setCellPadding(2)
		->setCellSpacing(0)
		->attr('border', 0)
		->addClass('invoiceTable');

	$InvoiceTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'ui-widget-content invoiceOverviewBlock',
				'text'   => $InvoiceDataTable
			),
			array(
				'addCls' => 'ui-widget-content invoiceOverviewBlock',
				'align'  => 'right',
				'text'   => ($InvoiceRefundTotals['vendor'] < $Invoice->vendor_total ? htmlBase::newElement('button')
					->attr('data-sale_id', $OrderDetails->sale_id)
					->attr('data-invoice_id', $Invoice->invoice_id)
					->setText('Issue Partial Refund')
					->addClass('invoicePartialRefundButton')
					->draw() : '')
			)
		)
	));

	$InvoiceTable->addBodyRow(array(
		'columns' => array(
			array(
				'css'     => array('padding' => '1em'),
				'colspan' => 2,
				'text'    => $InvoiceItemsTable
			),
		)
	));

	$Invoices[$k] = htmlBase::newElement('div')
		->addClass('ui-widget ui-widget-content invoiceContainer')
		->append($InvoiceTable);
}
$disableRefunds = ($SaleTotal - $SaleRefundTotals['vendor'] <= 0);
?>
<div class="pageHeading"><?php
	echo 'Sale #' . $OrderDetails->sale_id;
	?></div>
<br><br>
<div>
	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="ui-widget ui-widget-content orderDetailTable">
		<thead>
		<tr class="ui-widget-header orderDetailHeading">
			<th class="ui-widget-header-text orderDetailHeadingText"></th>
			<th class="ui-widget-header-text orderDetailHeadingText">Customer Address</th>
			<th class="ui-widget-header-text orderDetailHeadingText">Shipping Address</th>
			<th class="ui-widget-header-text orderDetailHeadingText">Ip Address</th>
		</tr>
		</thead>
		<tbody>
		<tr>
			<td class="ui-widget-content orderDetailTableBlock first">
				<table cellpadding="2" cellspacing="0" border="0">
					<tbody>
					<tr>
						<td class="orderDetailLabel">Customer Id:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->customer_id;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Total (<?php echo $currencyCode;?>):</td>
						<td class="orderDetailText"><?php echo $currencies->format($SaleTotal, false, $currencyCode, 1);?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel" valign="top">Refund:</td>
						<td class="orderDetailText"><?php
							$refundButton = htmlBase::newElement('button')
								->attr('data-sale_id', $OrderDetails->sale_id)
								->attr('data-remaining_total', ($SaleTotal - $SaleRefundTotals['vendor']))
								->setText('Refund Sale')
								->addClass('refundSaleButton');

							if ($SaleRefundTotals['vendor'] > 0){
								echo '<span class="refund">' . $currencies->format($SaleRefundTotals['vendor'], false, $currencyCode, 1) . '</span><br>';
								$refundButton->setText('Refund Balance Of Sale');
							}

							if ($disableRefunds === false){
								echo $refundButton->draw();
							}
							?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Method:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->pay_method->method;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Cardholder Name:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->cardholder_name;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Card Mask:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->pay_method->first_six_digits . '...' . $OrderDetails->customer->pay_method->last_two_digits;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">AVS:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->pay_method->avs;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">CVV:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->pay_method->cvv;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Language:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->lang;?></td>
					</tr>
					</tbody>
				</table>
			</td>
			<td class="ui-widget-content orderDetailTableBlock">
				<table cellpadding="2" cellspacing="0" border="0">
					<tbody>
					<tr>
						<td class="orderDetailLabel">Address Id:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->address_id;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Name:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->first_name . ' ' . $OrderDetails->customer->middle_initial . ' ' . $OrderDetails->customer->last_name;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel" valign="top">Address:</td>
						<td class="orderDetailText"><?php echo $PaymentModule->formatAddress($OrderDetails->customer);?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Email:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->email_address;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Phone Number/Ext:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->phone . ' Ext. ' . $OrderDetails->customer->phone_ext;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Prefix:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->customer->prefix;?></td>
					</tr>
					</tbody>
				</table>
			</td>
			<td class="ui-widget-content orderDetailTableBlock">
				<table cellpadding="2" cellspacing="0" border="0">
					<tbody>
					<tr>
						<td class="orderDetailLabel">Name:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->invoices[0]->shipping->shipping_name;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel" valign="top">Address:</td>
						<td class="orderDetailText"><?php echo $PaymentModule->formatAddress($OrderDetails->invoices[0]->shipping);?></td>
					</tr>
					</tbody>
				</table>
			</td>
			<td class="ui-widget-content orderDetailTableBlock last">
				<table cellpadding="2" cellspacing="0" border="0">
					<tbody>
					<tr>
						<td class="orderDetailLabel">IP Address:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->detail_ip->address;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Location:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->detail_ip->city . ' ' . $OrderDetails->detail_ip->region . ', ' . $OrderDetails->detail_ip->zip;?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Country:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->detail_ip->country . ' (' . $OrderDetails->detail_ip->country_code . ')';?></td>
					</tr>
					<tr>
						<td class="orderDetailLabel">Area Code:</td>
						<td class="orderDetailText"><?php echo $OrderDetails->detail_ip->area_code;?></td>
					</tr>
					</tbody>
				</table>
			</td>
		</tr>
		</tbody>
	</table>
</div>
<br>
<?php
foreach($Invoices as $Invoice){
	echo '<br>' . $Invoice->draw();
}
?>
<br><br>
<div class="ui-widget ui-widget-content commentsContainer">
	<table cellpadding="3" cellspacing="0" border="0" width="100%" class="commentsTable">
		<thead>
		<tr>
			<th colspan="5">Sale Comments</th>
		</tr>
		<tr>
			<th>Date</th>
			<th>Who</th>
			<th>Ip</th>
			<th>Comment</th>
			<th><?php
				echo htmlBase::newElement('button')
					->attr('data-sale_id', $OrderDetails->sale_id)
					->addClass('addCommentButton')
					->usePreset('comment')
					->setText('Add Comment')
					->setTooltip('Add Comment')
					->draw();
				?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		foreach($OrderDetails->comments as $comment){
			?>
		<tr>
			<td><?php
				echo SesDateTime::createFromFormat(DATE_TIMESTAMP, $comment->timestamp)
					->format(sysLanguage::getDateTimeFormat());
				?></td>
			<td><?php echo $comment->username;?></td>
			<td><?php echo $comment->changed_by_ip;?></td>
			<td colspan="2"><?php echo $comment->comment;?></td>
		</tr>
			<?php
		}
		?>
		</tbody>
	</table>
</div>