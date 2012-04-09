<?php
$Qavail = Doctrine_Query::create()
	->from('RentalAvailability ra')
	->leftJoin('ra.RentalAvailabilityDescription rad')
	->where('rad.language_id=?', Session::get('languages_id'));

$tableGrid = htmlBase::newElement('newGrid')
	->setQuery($Qavail);

$tableGrid->addButtons(array(
	htmlBase::newElement('button')->usePreset('new')->addClass('newButton'),
	htmlBase::newElement('button')->usePreset('edit')->addClass('editButton')->disable(),
	htmlBase::newElement('button')->usePreset('delete')->addClass('deleteButton')->disable()
));

$tableGrid->addHeaderRow(array(
	'columns' => array(
		array('text' => sysLanguage::get('TABLE_HEADING_NAME')),
		array('text' => sysLanguage::get('TABLE_HEADING_RATIO'))
	)
));

$Result = &$tableGrid->getResults();
if ($Result){
	foreach($Result as $aInfo){
		$tableGrid->addBodyRow(array(
			'rowAttr' => array(
				'data-status_id' => $aInfo['rental_availability_id']
			),
			'columns' => array(
				array('text' => $aInfo['RentalAvailabilityDescription'][Session::get('languages_id')]['name']),
				array('text' => $aInfo['ratio'])
			)
		));
	}
}
?>
<div class="pageHeading"><?php
	echo sysLanguage::get('HEADING_TITLE');
	?></div>
<br />
<div>
	<div class="ui-widget ui-widget-content ui-corner-all" style="margin-right:5px;margin-left:5px;">
		<div style="margin:5px;"><?php echo $tableGrid->draw();?></div>
	</div>
</div>
