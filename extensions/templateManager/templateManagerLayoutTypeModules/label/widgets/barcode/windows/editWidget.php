<?php
$displayType = isset($WidgetSettings->display_type) ? $WidgetSettings->display_type : 'barcode';

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array(
			'colspan' => 2,
			'text'    => '<b>Barcode Widget Properties</b>'
		)
	)
));

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => 'Display Type:'),
		array('text' => htmlBase::newElement('selectbox')
			->selectOptionByValue($displayType)
			->setName('display_type')
			->addOption('barcode', 'Show As Barcode')
			->addOption('text', 'Show As Text')
			->addOption('all_above', 'Show As Barcode With Text Above')
			->addOption('all_below', 'Show As Barcode With Text Below')
			->draw()
		)
	)
));
