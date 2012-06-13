<?php
ProductTypeModules::loadModules();
$ProductTypes = ProductTypeModules::getModules(true);
$EditTabs = htmlBase::newElement('tabs')
	->setId('productTypeTabs');
$addTabs = false;
foreach($ProductTypes as $ProductType){
	if (method_exists($ProductType, 'getTemplateBoxSettings')){
		$EditTabs->addTabHeader('productType_' . $ProductType->getCode(), array('text' => $ProductType->getTitle()))
			->addTabPage('productType_' . $ProductType->getCode(), array('text' => $ProductType->getTemplateBoxSettings($WidgetSettings)));
		$addTabs = true;
	}
}

if ($addTabs === true){
	$javascript = '<script>$(\'#productTypeTabs\').tabs();</script>';
	$WidgetSettingsTable->addBodyRow(array(
		'columns' => array(
			array('colspan' => 2, 'text' => $EditTabs->draw() . $javascript)
		)
	));
}
