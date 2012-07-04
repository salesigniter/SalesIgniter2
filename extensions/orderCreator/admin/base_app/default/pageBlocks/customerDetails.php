<?php
$beforeInfoTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
EventManager::notify('OrderCreatorAddToInfoTable', &$beforeInfoTable, $Editor);

/* customer_info --BEGIN-- */
/*EventManager::notify('OrderCreatorAddToInfoTableAfter', $infoTable, $Editor);

$contents = EventManager::notifyWithReturn('OrderInfoAddBlockEdit', (isset($oID) ? $oID : null));
if (!empty($contents)){
	foreach($contents as $content){
		if (empty($content)){
			continue;
		}
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
}*/

$AddressesTable = htmlBase::newTable()
	->setCellPadding(0)
	->setCellSpacing(0);

$AddressesTable->addClass('ui-widget ui-widget-content ui-corner-all addresses');

$AddressList = htmlBase::newList()
	->addClass('addressListing');
$AddressTables = '';

$CustomerInfoListItem = htmlBase::newElement('li')
	->attr('id', 'customerInfo')
	->addClass('ui-state-default')
	->html('Customers Info');

$CustomerAddressListItem = htmlBase::newElement('li')
	->attr('id', 'customerAddress')
	->addClass('ui-state-default')
	->html('Customers Address');

$BillingAddressListItem = htmlBase::newElement('li')
	->attr('id', 'billingAddress')
	->addClass('ui-state-default')
	->html('Billing Address<div class="sameAsContainer"><input type="checkbox" name="billing_same" checked><span>Same As Customer</span></div>');

$DeliveryAddressListItem = htmlBase::newElement('li')
	->attr('id', 'deliveryAddress')
	->addClass('ui-state-default')
	->html('Delivery Address<div class="sameAsContainer"><input type="checkbox" name="delivery_same" checked><span>Same As Customer</span></div>');


$CustomerInfoTable = htmlBase::newTable()
	->setCellPadding(0)
	->setCellSpacing(0)
	->css('width', '100%');

$CustomerInfoTable->addBodyRow(array(
	'columns' => array(
		array('colspan' => 2, 'text' => '<hr>')
	)
));

/*if (sysConfig::get('EXTENSION_ORDER_CREATOR_HAS_MEMBER_NUMBER') == 'True'){
	$infoTable->addBodyRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('ENTRY_MEMBER_NUMBER')),
			array('text' => $Editor->editMemberNumber())
		)
	));
}

if (sysConfig::get('EXTENSION_ORDER_CREATOR_NEEDS_LICENSE_PASSPORT') == 'True'){
	$CustomerInfoTable->addBodyRow(array(
		'columns' => array(
			array('text' => '<b>Drivers License:</b>'),
			array('text' => $Editor->editDriversLicense())
		)
	));
	$CustomerInfoTable->addBodyRow(array(
		'columns' => array(
			array('text' => '<b>Passport: </b>'),
			array('text' => $Editor->editPassPort())
		)
	));
}*/

$CustomerInfoTable->addBodyRow(array(
	'columns' => array(
		array(
			'colspan' => 2,
			'text' => htmlBase::newElement('input')
				->setName('email')
				->attr('placeholder', sysLanguage::get('ENTRY_EMAIL_ADDRESS'))
				->val($Editor->getEmailAddress())
		)
	)
));

if (!isset($_GET['sale_id'])){
	$cssArray = array();
	if (sysConfig::get('EXTENSION_ORDER_CREATOR_HIDE_PASSWORD') == 'True'){
		$cssArray['display'] = 'none';
	}
	$CustomerInfoTable->addBodyRow(array(
		'columns' => array(
			array(
				'colspan' => 2,
				'css'    => $cssArray,
				'text'   => '<input type="password" placeholder="' . sysLanguage::get('ENTRY_PASSWORD') . '" name="account_password"' . ($Editor->getCustomerId() > 0 ? ' disabled="disabled"' : '') . '>'
			)
		)

	));
}

$AddressList->addItemObj($CustomerInfoListItem);
$CustomerCustomFields = $appExtension->getExtension('customersCustomFields');
$Groups = $CustomerCustomFields->getGroups();
if ($Groups){
	$CustomerInfoTable->addBodyRow(array(
		'columns' => array(
			array('colspan' => 2, 'text' => '<hr>')
		)
	));

	$Columns = array();
	foreach($Groups as $Group){
		foreach($Group->Fields as $Field){
			if ($Field->Field->field_data->show_on->order_creator == 1){
				$fInfo = $CustomerCustomFields->getFieldHtml($Field->Field, $Editor);
				$Columns[] = array(
					'valign' => 'top',
					'text' => $fInfo['field']
				);
				if (sizeof($Columns) == 2){
					$CustomerInfoTable->addBodyRow(array(
						'columns' => $Columns
					));
					$Columns = array();
				}
			}
		}
	}
	if (sizeof($Columns) > 0){
		$CustomerInfoTable->addBodyRow(array(
			'columns' => $Columns
		));
	}
}
$AddressTables .= '<div class="customerTabPage customerInfo">' .
	'<div>' .
		'<input style="width:80%;" type="text" placeholder="' . sysLanguage::get('ENTRY_SEARCH_CUSTOMER') . '" name="customer_search" class="customSearchInput">' .
		htmlBase::newElement('button')->addClass('customerSearchReset')->setText(sysLanguage::get('TEXT_BUTTON_RESET'))->draw() .
	'</div>' .
	$CustomerInfoTable->draw() .
'</div>';

$AddressList->addItemObj($CustomerAddressListItem);
$AddressTables .= '<div class="customerTabPage customerAddress">' . $Editor->AddressManager->editAddress('customer') . '</div>';

$AddressList->addItemObj($BillingAddressListItem);
$AddressTables .= '<div class="customerTabPage billingAddress">' . $Editor->AddressManager->editAddress('billing') . '</div>';

$AddressList->addItemObj($DeliveryAddressListItem);
$AddressTables .= '<div class="customerTabPage deliveryAddress">' . $Editor->AddressManager->editAddress('delivery') . '</div>';

if (sysConfig::exists('EXTENSION_PAY_PER_RENTALS_CHOOSE_PICKUP') && sysConfig::get('EXTENSION_PAY_PER_RENTALS_CHOOSE_PICKUP') == 'True'){
	$PickupAddressListItem = htmlBase::newElement('li')
		->attr('id', 'pickupAddress')
		->addClass('ui-state-default')
		->html('Pickup Address<div class="sameAsContainer"><input type="checkbox" name="pickup_same"><span>Same As Customer</span></div>');
	$AddressList->addItemObj($PickupAddressListItem);
	$AddressTables .= '<div class="customerTabPage pickupAddress">' . $Editor->AddressManager->editAddress('pickup') . '</div>';
}

$AddressesTable->addBodyRow(array(
	'columns' => array(
		array('valign' => 'top', 'text' => $AddressList),
		array('valign' => 'top', 'text' => $AddressTables)
	)
));

if (!isset($_GET['sale_id'])){
	if (sysConfig::get('EXTENSION_ORDER_CREATOR_CHOOSE_CUSTOMER_TYPE') == 'True'){
		$hotelGuest = htmlBase::newElement('button')
			->addClass('hotelGuest')
			->setText('Hotel Guest');
		$walkin = htmlBase::newElement('button')
			->addClass('walkin')
			->setText('Walk In');

		echo '<div class="chooseType">' . $hotelGuest->draw() . ' ' . $walkin->draw() . '</div>';
	}
}
echo $beforeInfoTable->draw();

//echo $infoTable->draw();

echo $AddressesTable->draw();

$OrderCustomFields = $appExtension->getExtension('ordersCustomFields');
$CustomFields = $OrderCustomFields->getFields();
if ($CustomFields->count() > 0){
	$CustomFieldsTable = htmlBase::newTable()
		->setCellPadding(0)
		->setCellSpacing(0)
		->addClass('ui-widget ui-widget-content ui-corner-all customFields');

	foreach($CustomFields as $Field){
		$fInfo = $OrderCustomFields->getFieldHtml($Field, $Editor);
		$CustomFieldsTable->addBodyRow(array(
			'columns' => array(
				array('text' => $fInfo['label']),
				array('text' => $fInfo['field'])
			)
		));
	}
	echo $CustomFieldsTable->draw();
}
?>
<br><br>
<?php
echo htmlBase::newElement('button')->addClass('saveAddressButton')
	->setText(sysLanguage::get('TEXT_BUTTON_UPDATE_CUSTOMER'))->draw();
?>

<script>
	$('.addressListing li').click(function () {
		$(this).parent().find('.ui-state-active').removeClass('ui-state-active');
		$(this).addClass('ui-state-active');
		var toShow = $(this).attr('id');

		$('.customerTabPage').hide();
		$('.customerTabPage.' + toShow).show();
	});
	$('.addressListing li').first().click();
</script>