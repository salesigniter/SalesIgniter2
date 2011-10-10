<?php


	$tableGrid = htmlBase::newElement('table')
 	->setCellPadding(2)
 	->setCellSpacing(0);

	$tableGrid->addHeaderRow(array(
		'columns' => array(
			array('text' => sysLanguage::get('TABLE_HEADING_MEMBERSHIP')),
			array('text' => 'Not Enabled For Product')
		)
	));


	$tableGrid->addBodyRow(array(
		'columns' => array(
			array('text' => ''),
			array('text' => '', 'align' => 'center')
		)
	));

echo $tableGrid->draw();

?>
