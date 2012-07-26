<?php
$QaddressFormat = Doctrine_Query::create()
	->from('AddressFormat')
	->orderBy('address_format_id asc');

$tableGrid = htmlBase::newElement('newGrid')
	->setMainDataKey('format_id')
	->allowMultipleRowSelect(true)
	->usePagination(true)
	->setQuery($QaddressFormat);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->addClass('newButton')->usePreset('new'),
	htmlBase::newElement('button')->addClass('editButton')->usePreset('edit')->disable(),
	htmlBase::newElement('button')->addClass('deleteButton')->usePreset('delete')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_ADDRESS_FORMAT_NAME'))
	)
));

$addressFormat = &$tableGrid->getResults();
if ($addressFormat){
	foreach($addressFormat as $address){
		$tableGrid->addBodyRow(array(
			'rowAttr'  => array(
				'data-format_id' => $address['address_format_id']
			),
			'columns'  => array(
				array('text' => strip_tags($address['address_summary']))
			)
		));
	}
}

echo $tableGrid->draw();
