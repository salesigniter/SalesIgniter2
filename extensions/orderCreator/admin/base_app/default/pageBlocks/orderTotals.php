<?php
$orderTotalTable = htmlBase::newElement('newGrid')
	->addClass('orderTotalTable');

$orderTotalTable->addButtons(array(
	htmlBase::newElement('button')->addClass('addOrderTotalButton')->usePreset('new'),
	htmlBase::newElement('button')->addClass('deleteOrderTotalButton')->usePreset('delete')->disable(),
	htmlBase::newElement('button')->addClass('moveOrderTotalButton')->attr('data-direction', 'up')
		->usePreset('moveup')->setText('Move Up')->disable(),
	htmlBase::newElement('button')->addClass('moveOrderTotalButton')->attr('data-direction', 'down')
		->usePreset('movedown')->setText('Move Down')->disable()
));

$orderTotalTable->addHeaderRow(array(
	'columns' => array(
		array('text' => 'Title'),
		array(
			'css'  => array('width' => '150px'),
			'text' => 'Value'
		),
		array(
			'css'  => array('width' => '225px'),
			'text' => 'Type'
		)
	)
));
$count = 0;
$totalTypes = array();
foreach(OrderTotalModules::getModules() as $Module){
	$totalTypes[$Module->getCode()] = $Module->getTitle();
}
$totalTypes['custom'] = 'Custom';

foreach($Editor->TotalManager->getAll() as $orderTotal){
	$editable = $orderTotal->isEditable();
	$totalType = $orderTotal->getModuleType();

	$hiddenField = '';
	$typeMenu = '<div class="orderTotalType">' . $totalType . '<input type="hidden" name="order_total[' . $count . '][type]" value="' . $totalType . '"></div>';
	$totalValue = '<div class="orderTotalValue" style="width:82px;text-align:left;"><span>' . $orderTotal->getValue() . '</span><input type="hidden" name="order_total[' . $count . '][value]" value="' . $orderTotal->getValue() . '"></div>';
	if ($editable === true){
		$typeMenu = htmlBase::newElement('selectbox')
			->addClass('orderTotalType')
			->setName('order_total[' . $count . '][type]');
		foreach($totalTypes as $k => $v){
			$typeMenu->addOption($k, $v);
		}

		$typeMenu->selectOptionByValue($orderTotal->getModule());
		$typeMenu = $typeMenu->draw();

		$hiddenField = '';
		if ($orderTotal->hasOrderTotalId()){
			$hiddenField .= '<input type="hidden" name="order_total[' . $count . '][id]" value="' . $orderTotal->getOrderTotalId() . '">';
		}

		$totalValue = '<input class="ui-widget-content orderTotalValue" type="text" size="10" name="order_total[' . $count . '][value]" value="' . $orderTotal->getValue() . '">';
	}

	if ($totalType == 'shipping'){
		$total_weight = $Editor->ProductManager->getTotalWeight();
		OrderShippingModules::setDeliveryAddress($Editor->AddressManager->getAddress('delivery'));

		$titleField = '<select name="order_total[' . $count . '][title]" style="width:98%;">';
		$Quotes = OrderShippingModules::quote();
		//print_r($Quotes);
		foreach($Quotes as $qInfo){
			$titleField .= '<optgroup label="' . $qInfo['module'] . '">';
			foreach($qInfo['methods'] as $mInfo){
				$titleField .= '<option value="' . $qInfo['id'] . '_' . $mInfo['id'] . '"' . ($orderTotal->getModule() == $qInfo['id'] && $orderTotal->getMethod() == $mInfo['id'] ? ' selected="selected"' : '') . '>' . $mInfo['title'] . ' ( Recommended Price: ' . $currencies->format($mInfo['cost']) . ' )</option>';
			}
			$titleField .= '</optgroup>';
		}

		$titleField .= '</select>';
	}
	else {
		if ($editable === true){
			$titleField = '<input class="ui-widget-content" type="text" style="width:98%;" name="order_total[' . $count . '][title]" value="' . $orderTotal->getTitle() . '">';
		}
		else {
			$titleField = '<div style="width:98%;text-align:left;">' . $orderTotal->getTitle() . '<input type="hidden" name="order_total[' . $count . '][title]" value="' . $orderTotal->getTitle() . '"></div>';
		}
	}

	$orderTotalTable->addBodyRow(array(
		'attr'    => array(
			'data-count' => $count,
			'data-code'  => $totalType
		),
		'columns' => array(
			array(
				'align' => 'center',
				'text'  => $hiddenField . $titleField
			),
			array(
				'align' => 'center',
				'text'  => $totalValue . '<input type="hidden" name="order_total[' . $count . '][sort_order]" class="totalSortOrder" value="' . $count . '"></span>'
			),
			array(
				'align' => 'right',
				'text'  => $typeMenu
			)
		)
	));
	$count++;
}
$orderTotalTable->attr('data-next_id', $count);
echo $orderTotalTable->draw();

if ($Editor->hasDebt() === true){
	echo '<div style="text-align:right"><b>' . sysLanguage::get('TEXT_UNPAID_BALANCE') . '</b> <span style="font-weight:bold;color:red;">' . $Editor->getBalance('debt') . '</span></div>';
}

if ($Editor->hasPendingPayments() === true){
	echo '<div style="text-align:right"><b>' . sysLanguage::get('TEXT_PENDING_PAYMENTS') . '</b> <span style="font-weight:bold;color:blue;">' . $Editor->getBalance('pending') . '</span></div>';
}

if ($Editor->hasCredit() === true){
	echo '<div style="text-align:right"><b>' . sysLanguage::get('TEXT_OVERPAID_BALANCE') . '</b> <span style="font-weight:bold;color:green;">' . $Editor->getBalance('credit') . '</span></div>';
}
