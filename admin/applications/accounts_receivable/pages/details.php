<?php
$saleType = (isset($_GET['sale_type']) ? $_GET['sale_type'] : null);
if ($saleType === null){
	$QSaleType = Doctrine_Query::create()
		->select('sale_module')
		->from('AccountsReceivableSales')
		->where('sale_id = ?', $_GET['sale_id'])
		->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

	$saleType = $QSaleType[0]['sale_module'];
}
$Sale = AccountsReceivable::getSale($saleType, $_GET['sale_id']);
$SaleModule = $Sale->getSaleModule();

$tabsObj = htmlBase::newElement('tabs')
	->setId('tabs')
	->addTabHeader('tab_customer_info', array('text' => 'Customer Info'))
	->addTabHeader('tab_products', array('text' => 'Products'));

if ($SaleModule->acceptsPayments() === true){
	$tabsObj->addTabHeader('tab_payment_info', array('text' => 'Payment Info'));
}

$tabsObj->addTabHeader('tab_history', array('text' => 'History'))
	->addTabHeader('tab_comments', array('text' => 'Comments/Tracking'));
/* Tab: tab_customer_info --BEGIN-- */
$addressesTable = $Sale->listAddresses();

$infoTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0);
$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_TELEPHONE_NUMBER') . '</b>'
		),
		array(
			'addCls' => 'main',
			'text'   => $Sale->getTelephone()
		)
	)
));
if (sysConfig::get('SHOW_IP_ADDRESS_ORDERS_DETAILS') == 'true'){
	$infoTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => '<b>' . sysLanguage::get('ENTRY_IPADDRESS') . '</b>'
			),
			array(
				'addCls' => 'main',
				'text'   => $Sale->getIPAddress()
			)
		)
	));
}
$oEmail = $Sale->getEmailAddress();
if (strpos($oEmail, '@') === false){
	$oEmail = 'N/A';
}
$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_EMAIL_ADDRESS') . '</b>'
		),
		array(
			'addCls' => 'main',
			'text'   => '<a href="mailto:' . $oEmail . '"><u>' . $oEmail . '</u></a>'
		)
	)
));

$contents = EventManager::notifyWithReturn('OrderInfoAddBlock', $oID);
if (!empty($contents)){
	foreach($contents as $content){
		$infoTable->addBodyRow(array(
			'columns' => array(
				array(
					'addCls'  => 'main',
					'colspan' => '2',
					'text'    => $content
				),
			)
		));
	}
}

$tabsObj->addTabPage('tab_customer_info', array('text' => $addressesTable . '<br />' . $infoTable->draw()));

$contents = EventManager::notifyWithReturn('OrderShowExtraShippingInfo', $Sale, $tabsObj);
foreach($contents as $content){
	echo $content;
}
$contents = EventManager::notifyWithReturn('OrderShowExtraPaymentInfo', $Sale, $tabsObj);
foreach($contents as $content){
	echo $content;
}
/* Tab: tab_customer_info --END-- */

/* Tab: tab_products --BEGIN-- */
$productsGrid = htmlBase::newGrid();

$productsGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_QTY')),
		array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_NAME')),
		array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_BARCODE')),
		array('text' => sysLanguage::get('TABLE_HEADING_PRODUCTS_MODEL')),
		array('text' => sysLanguage::get('TABLE_HEADING_TAX')),
		array('text' => sysLanguage::get('TABLE_HEADING_PRICE_EXCLUDING_TAX')),
		array('text' => sysLanguage::get('TABLE_HEADING_PRICE_INCLUDING_TAX')),
		array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_EXCLUDING_TAX')),
		array('text' => sysLanguage::get('TABLE_HEADING_TOTAL_INCLUDING_TAX'))
	)
));

foreach($Sale->ProductManager->getContents() as $orderedProduct){
	$finalPrice = $orderedProduct->getPrice();
	$finalPriceWithTax = $orderedProduct->getPrice(true);
	$taxRate = $orderedProduct->getTaxRate();
	$productQty = $orderedProduct->getQuantity();

	$productsGrid->addBodyRow(array(
		'columns' => array(
			array(
				'align' => 'right',
				'text'  => $orderedProduct->getQuantity() . '&nbsp;x'
			),
			array('text' => $orderedProduct->getNameHtml(true)),
			array('text' => $orderedProduct->displayBarcodes()),
			array('text' => $orderedProduct->getModel()),
			array('align' => 'right', 'text'  => $taxRate . '%'),
			array(
				'align' => 'right',
				'text'  => '<b>' . $currencies->format($finalPrice, true, $Sale->getCurrency(), $Sale->getCurrencyValue()) . '</b>'
			),
			array(
				'align' => 'right',
				'text'  => '<b>' . $currencies->format($finalPriceWithTax, true, $Sale->getCurrency(), $Sale->getCurrencyValue()) . '</b>'
			),
			array(
				'align' => 'right',
				'text'  => '<b>' . $currencies->format($finalPrice * $productQty, true, $Sale->getCurrency(), $Sale->getCurrencyValue()) . '</b>'
			),
			array(
				'align' => 'right',
				'text'  => '<b>' . $currencies->format($finalPriceWithTax * $productQty, true, $Sale->getCurrency(), $Sale->getCurrencyValue()) . '</b>'
			)
		)
	));
}

$orderTotalTable = $Sale->listTotals();

$productsGrid->insertAfterGrid($orderTotalTable->draw());

$tabsObj->addTabPage('tab_products', array('text' => $productsGrid));
/* Tab: tab_products --END-- */

/* Tab: tab_payment_info --BEGIN-- */
if ($SaleModule->acceptsPayments() === true){
	$paymentHistoryTable = $Sale->listPaymentHistory();

	$tabsObj->addTabPage('tab_payment_info', array('text' => $paymentHistoryTable));
}
/* Tab: tab_payment_info --END-- */

/* Tab: tab_history --BEGIN-- */
$historyTable = htmlBase::newGrid();

$historyTable->addHeaderRow(array(
	'columns' => array(
		array('text' => 'Revision Number'),
		array('text' => sysLanguage::get('TABLE_HEADING_DATE_ADDED'))
	)
));

if ($SaleModule->hasRevisions()){
	foreach($SaleModule->getRevisions() as $Revision){
		$historyTable->addBodyRow(array(
			'columns' => array(
				array('text' => $Revision['id']),
				array('text' => $Revision['date_added']->format(sysLanguage::getDateFormat('short')))
			)
		));
	}
}
else {
	$historyTable->addBodyRow(array(
		'columns' => array(
			array(
				'align'   => 'center',
				'colspan' => 2,
				'text'    => 'No Revision History'
			)
		)
	));
}
$tabsObj->addTabPage('tab_history', array('text' => '<h1 style="font-size:1.5em;">Current Revision: ' . $SaleModule->getCurrentRevision() . '</h1><br>' . $historyTable->draw()));
/* Tab: tab_history --END-- */

/* Tab: tab_comments --BEGIN-- */
$tracking = array(
	array(
		'heading' => sysLanguage::get('TABLE_HEADING_USPS_TRACKING'),
		'link'    => 'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=',
		'data'    => array('usps_track_num', 'usps_track_num2')
	),
	array(
		'heading' => sysLanguage::get('TABLE_HEADING_UPS_TRACKING'),
		'link'    => 'http://wwwapps.ups.com/etracking/tracking.cgi?InquiryNumber2=&InquiryNumber3=&InquiryNumber4=&InquiryNumber5=&TypeOfInquiryNumber=T&UPS_HTML_Version=3.0&IATA=us&Lang=en&submit=Track+Package&InquiryNumber1=',
		'data'    => array('ups_track_num', 'ups_track_num2')
	),
	array(
		'heading' => sysLanguage::get('TABLE_HEADING_FEDEX_TRACKING'),
		'link'    => 'http://www.fedex.com/Tracking?action=track&language=english&cntry_code=us&tracknumbers=',
		'data'    => array('fedex_track_num', 'fedex_track_num2')
	),
	array(
		'heading' => sysLanguage::get('TABLE_HEADING_DHL_TRACKING'),
		'link'    => 'http://track.dhl-usa.com/atrknav.asp?action=track&language=english&cntry_code=us&ShipmentNumber=',
		'data'    => array('dhl_track_num', 'dhl_track_num2')
	)
);

$trackingTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0);
$orderInfo = $Sale->getOrderInfo();

foreach($tracking as $tracker){
	$bodyCols = array(
		array('text' => '<b>' . $tracker['heading'] . ':</b> ')
	);
	foreach($tracker['data'] as $fieldName){
		$trackNum = $orderInfo[$fieldName];
		$bodyCols[] = array(
			'text' => tep_draw_input_field($fieldName, $trackNum, 'size="40" maxlength="40"')
		);
		$bodyCols[] = array(
			'text' => htmlBase::newElement('button')
				->setHref($tracker['link'] . $trackNum, false, '_blank')
				->setText('Track')
		);
	}
	$trackingTable->addBodyRow(array(
		'columns' => $bodyCols
	));
}

$tabContent = '<div class="main"><b>' . sysLanguage::get('TABLE_HEADING_COMMENTS') . '</b></div>' .
	'<form name="status" action="' . itw_app_link(tep_get_all_get_params(array('action')) . 'action=updateOrder') . '" method="post">' .
	tep_draw_textarea_field('comments', 'hard', '60', '5') .
	'<br />';
EventManager::notify('OrderDetailsTabPaneInsideComments', &$orderInfo, &$tabContent);
$tabContent .= $trackingTable->draw() .
	'<br />' .
	'<table border="0" cellspacing="0" cellpadding="2">' .
	'<tr>' .
	'<td><table border="0" cellspacing="0" cellpadding="2">' .
	'<tr>' .
	'<td class="main"><b>' . sysLanguage::get('ENTRY_STATUS') . '</b> ' . tep_draw_pull_down_menu('status', $orders_statuses, $Sale->getCurrentStatus(true)) . '</td>' .
	'</tr>' .
	'<tr>' .
	'<td class="main"><b>' . sysLanguage::get('ENTRY_NOTIFY_CUSTOMER') . '</b> ' . tep_draw_checkbox_field('notify', '', (sysConfig::get('CUSTOMER_CHANGE_SEND_NOTIFICATION_EMAIL_DEFAULT') == 'true' ? true : false)) . '</td>' .
	'<td class="main"><b>' . sysLanguage::get('ENTRY_NOTIFY_COMMENTS') . '</b> ' . tep_draw_checkbox_field('notify_comments', '', true) . '</td>' .
	'</tr>' .
	'</table></td>' .
	'<td valign="top">' . htmlBase::newElement('button')
	->usePreset('save')
	->setText('Update')
	->setType('submit')
	->draw() . '</td>' .
	'</tr>' .
	'</table>' .
	'</form>';
$tabsObj->addTabPage('tab_comments', array('text' => $tabContent));
/* Tab: tab_comments --END-- */

EventManager::notify('OrderDetailsTabPaneBeforeDraw', $Sale, &$tabsObj);

echo $tabsObj->draw();
