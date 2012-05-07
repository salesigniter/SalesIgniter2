<?php
$AddressFormat = Doctrine_Core::getTable('AddressFormat');
if (isset($_GET['format_id']) && empty($_POST)){
	$AddressFormat = $AddressFormat->find((int)$_GET['format_id']);
}
else {
	$AddressFormat = $AddressFormat->getRecord();
}

$AddressBook = Doctrine_Core::getTable('AddressBook');
$addressBookColumns = $AddressBook->getColumns();
$columns = '';
$myColumn = 'country';
$columns .= '$' . $myColumn . '<br/>';

$myColumn = 'abbrstate';
$columns .= '$' . $myColumn . '<br/>';

foreach($addressBookColumns as $column => $value){
	if (strpos($column, '_id') === false){
		$myColumn = str_replace('entry_', '', $column);
		$columns .= '$' . $myColumn . '<br/>';
	}
}
if (isset($_GET['format_id'])){
	$address_format = $AddressFormat->address_format;
	$address_summary = $AddressFormat->address_summary;
}
else {
	$address_format = '';
	$address_summary = '';
}

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . ($Group->address_format_id > 0 ? sysLanguage::get('TEXT_INFO_HEADING_EDIT') : sysLanguage::get('TEXT_INFO_HEADING_NEW')) . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$InputTable = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0);

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TEXT_ADDRESS_FORMAT_COLUMNS')),
		array('text' => $columns)
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TEXT_ADDRESS_FORMAT_NAME')),
		array(
			'text' => htmlBase::newElement('textarea')->setName('address_summary')->attr(array(
				'wrap' => 'hard',
				'rows' => 5,
				'cols' => 20
			))->html($address_summary)
		)
	)
));

$InputTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TEXT_ADDRESS_FORMAT')),
		array(
			'text' => htmlBase::newElement('textarea')->setName('address_format')->attr(array(
				'wrap' => 'hard',
				'rows' => 3,
				'cols' => 40
			))->html($address_format)
		)
	)
));

$infoBox->addContentRow($InputTable->draw());

EventManager::notify('AdminAddressFormatNewEditWindowBeforeDraw', $infoBox, $AddressFormat);

EventManager::attachActionResponse($infoBox->draw(), 'html');
