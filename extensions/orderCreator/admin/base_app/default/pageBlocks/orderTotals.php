<?php
$orderTotalTable = htmlBase::newElement('newGrid')
	->addClass('orderTotalTable');

$orderTotalTable->addButtons(array(
	htmlBase::newElement('button')
		->addClass('addDiscount')
		->usePreset('new')
		->setText('Add Discount'),
	htmlBase::newElement('button')
		->addClass('addShipping')
		->usePreset('new')
		->setText('Add Shipping')
));

$orderTotalTable->addHeaderRow(array(
	'columns' => array(
		array('text' => 'Title'),
		array(
			'css'  => array('width' => '150px'),
			'text' => 'Value'
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
	$TotalModule = $orderTotal->getModule();

	$editable = $orderTotal->isEditable();
	$totalTitle = $TotalModule->getTitle();
	$totalCode = $TotalModule->getCode();
	$totalValue = $TotalModule->getValue();

	if ($editable === true){
		$totalCode = htmlBase::newSelectbox()
			->selectOptionByValue($totalCode);
		foreach($totalTypes as $k => $v){
			$totalCode->addOption($k, $v);
		}

		$totalCode = $totalCode->draw();

		$totalValue = htmlBase::newInput()
			->attr('size', '10')
			->setValue($totalValue)
			->draw();

		$totalTitle = htmlBase::newInput()
			->css('width', '100%')
			->setValue($totalTitle)
			->draw();
	}

	if ($totalCode == 'shipping'){
		$total_weight = $Editor->ProductManager->getTotalWeight();
		OrderShippingModules::setDeliveryAddress($Editor->AddressManager->getAddress('delivery'));

		$totalTitle = '<select style="width:100%;">';
		$Quotes = OrderShippingModules::quote();
		//print_r($Quotes);
		foreach($Quotes as $qInfo){
			$totalTitle .= '<optgroup label="' . $qInfo['module'] . '">';
			foreach($qInfo['methods'] as $mInfo){
				$totalTitle .= '<option value="' . $qInfo['id'] . '_' . $mInfo['id'] . '"' . ($orderTotal->getModule() == $qInfo['id'] && $orderTotal->getMethod() == $mInfo['id'] ? ' selected="selected"' : '') . '>' . $mInfo['title'] . ' ( Recommended Price: ' . $currencies->format($mInfo['cost']) . ' )</option>';
			}
			$totalTitle .= '</optgroup>';
		}

		$totalTitle .= '</select>';
	}

	$orderTotalTable->addBodyRow(array(
		'attr'    => array(
			'data-count'         => $count,
			'data-code'          => $totalCode,
			'data-editable'      => $editable,
			'data-value'         => $totalValue,
			'data-display_order' => $count
		),
		'columns' => array(
			array('attr' => array('data-which' => 'title'), 'align' => 'center', 'text' => $totalTitle),
			array('attr' => array('data-which' => 'value'), 'align' => 'center', 'text' => $totalValue)
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
