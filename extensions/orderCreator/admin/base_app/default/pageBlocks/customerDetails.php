<?php
$beforeInfoTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
EventManager::notify('OrderCreatorAddToInfoTable', &$beforeInfoTable, $Editor);
/* customer_info --BEGIN-- */
$addressesTable = $Editor->editAddresses();

$infoTable = htmlBase::newElement('table')->setCellPadding(3)->setCellSpacing(0);
$inputType = htmlBase::newElement('input')
	->setName('isType')
	->addClass('isType')
	->setType('hidden')
	->val((isset($_GET['isType']) ? $_GET['isType'] : 'walkin'));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>Room Number:</b>'
		),
		array(
			'addCls' => 'main',
			'text'   => $Editor->editRoomNumber()
		)
	)
));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => ''
		),
		array(
			'addCls' => 'main',
			'text'   => $inputType->draw()
		)
	)
));

$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_TELEPHONE_NUMBER') . '</b>'
		),
		array(
			'addCls' => 'main',
			'text'   => $Editor->editTelephone()
		)
	)
));

if (sysConfig::get('EXTENSION_ORDER_CREATOR_HAS_MEMBER_NUMBER') == 'True'){
	$infoTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => '<b>' . sysLanguage::get('ENTRY_MEMBER_NUMBER') . '</b>'
			),
			array(
				'addCls' => 'main',
				'text'   => $Editor->editMemberNumber()
			)
		)
	));
}

if (sysConfig::get('EXTENSION_ORDER_CREATOR_NEEDS_LICENSE_PASSPORT') == 'True'){
	$infoTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => '<b>Drivers License:</b>'
			),
			array(
				'addCls' => 'main',
				'text'   => $Editor->editDriversLicense()
			)
		)
	));
	$infoTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'text'   => '<b>Passport: </b>'
			),
			array(
				'addCls' => 'main',
				'text'   => $Editor->editPassPort()
			)
		)
	));
}
$infoTable->addBodyRow(array(
	'columns' => array(
		array(
			'addCls' => 'main',
			'text'   => '<b>' . sysLanguage::get('ENTRY_EMAIL_ADDRESS') . '</b>'
		),
		array(
			'addCls' => 'main',
			'text'   => $Editor->editEmailAddress()
		)
	)
));

if (!isset($_GET['oID'])){
	$infoTable->addBodyRow(array(
		'columns' => array(
			array(
				'addCls' => 'main',
				'css'	=> array(
					'display' => (sysConfig::get('EXTENSION_ORDER_CREATOR_HIDE_PASSWORD') == 'True') ? 'none' : 'block'
				),
				'text'   => '<b>' . sysLanguage::get('ENTRY_PASSWORD') . '</b>'
			),
			array(
				'addCls' => 'main',
				'css'	=> array(
					'display' => (sysConfig::get('EXTENSION_ORDER_CREATOR_HIDE_PASSWORD') == 'True') ? 'none' : 'block'
				),
				'text'   => '<input type="password" name="account_password"' . ($Editor->getCustomerId() > 0 ? ' disabled="disabled"' : '') . '>'
			)
		)

	));
}

EventManager::notify('OrderCreatorAddToInfoTableAfter', $infoTable, $Editor);

$contents = EventManager::notifyWithReturn('OrderInfoAddBlockEdit', (isset($oID) ? $oID : null));
if (!empty($contents)){
	foreach($contents as $content){
		$infoTable->addBodyRow(array(
			'columns' => array(
				array(
					'addCls'  => 'main',
					'colspan' => '2',
					'text'	=> $content
				),
			)
		));
	}
}

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

	echo '<b>' . sysLanguage::get('ENTRY_SEARCH_CUSTOMER') . '</b><br>' .
		'<input type="text" name="customer_search" style="width:70%;">' .
		htmlBase::newElement('button')->addClass('customerSearchReset')
			->setText(sysLanguage::get('TEXT_BUTTON_RESET'))->draw() . '<br>';
}
echo $beforeInfoTable->draw();
echo $addressesTable;
if (sysConfig::get('EXTENSION_ORDER_CREATOR_SHOW_COPY_ADDRESS_BUTTON') == 'True'){
	echo '<div style="text-align:left">' .
		htmlBase::newElement('div')
			->html($Editor->AddressManager->getCopyToButtons())
			->draw();
	'</div><br>';
}

echo $infoTable->draw() .
	htmlBase::newElement('button')->addClass('saveAddressButton')
		->setText(sysLanguage::get('TEXT_BUTTON_UPDATE_CUSTOMER'))->draw();
