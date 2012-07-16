<?php
/*
	Multi Stores Extension Version 1
	
	I.T. Web Experts, Rental Store v2
	http://www.itwebexperts.com

	Copyright (c) 2009 I.T. Web Experts

	This script and it's source is not redistributable
*/

$Qstores = Doctrine_Query::create()
	->from('Stores')
	->orderBy('stores_name');

$tableGrid = htmlBase::newGrid()
	->setMainDataKey('store_id')
	->allowMultipleRowSelect(true)
	->setQuery($Qstores);

$tableGrid->addButtons(array(
	htmlBase::newButton()->addClass('newButton')->usePreset('new'),
	htmlBase::newButton()->addClass('editButton')->usePreset('edit')->disable(),
	htmlBase::newButton()->addClass('deleteButton')->usePreset('delete')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_STORES_ID')),
		array('text' => sysLanguage::get('TABLE_HEADING_STORES')),
		array('text' => sysLanguage::get('TABLE_HEADING_STORES_DOMAIN'))
	)
));

$Result = $tableGrid->getResults();
if ($Result){
	foreach($Result as $storeInfo){
		$storeId = $storeInfo['stores_id'];
		$storeName = $storeInfo['stores_name'];
		$storeDomain = $storeInfo['stores_domain'];

		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-store_id' => $storeId
			),
			'columns' => array(
				array('text' => $storeId),
				array('text' => $storeInfo['stores_name']),
				array('text' => '<a href="http://' . $storeDomain . '">' . $storeDomain . '</a>')
			)
		));
	}
}
?>
<div style="margin-right:5px;margin-left:5px;">
	<div style="margin:5px;"><?php
		echo $tableGrid->draw();
		?></div>
</div>
