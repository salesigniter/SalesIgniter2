<?php
$BoxMode = (isset($WidgetSettings->box_mode) ? $WidgetSettings->box_mode : 'full');

$BoxModeSelect = htmlBase::newElement('selectbox')
	->setName('box_mode')
	->selectOptionByValue($BoxMode)
	->addOption('full', 'Full Box Mode - Shows Everything')
	->addOption('mini', 'Mini Box Mode - Shows Only Count Of Products');

$WidgetSettingsTable->addBodyRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TEXT_SHOPPING_CART_BOX_MODE')),
		array('text' => $BoxModeSelect->draw())
	)
));
?>