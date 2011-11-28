<?php
$infoBox = htmlBase::newElement('infobox');
$infoBox->setButtonBarLocation('top');
if (isset($_GET['mID'])) {
	$infoBox->setHeader('<b>Edit Maintenance</b>');
}

$QMaintenancePeriods = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods')
	->where('maintenance_period_id = ?', $_GET['type'])
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

$infoMaint = htmlBase::newElement('textarea')
	->attr('rows', '5')
	->attr('cols','20')
	->attr('id', 'commentID')
	->addClass('makeFCK')
	->attr('name','comments');

$condMaint = htmlBase::newElement('radio')
	->addGroup(array(
		'name'      => 'cond',
		'checked'   => 'g',
		'data'      => array(
			array('label' => 'Good', 'addCls' => 'isG', 'labelPosition' => 'before', 'value' => 'g'),
			array('label' => 'Broken', 'addCls' => 'isB', 'labelPosition' => 'before', 'value' => 'b')
		)
	));

$divMid = htmlBase::newElement('span')
	->attr('id','mid')
	->attr('mid', $_GET['mID']);

$saveButton = htmlBase::newElement('button')
	->addClass('saveButton')
	->usePreset('save');
$cancelButton = htmlBase::newElement('button')
	->addClass('cancelButton')
	->usePreset('cancel');


$infoBox->addContentRow($QMaintenancePeriods[0]['maintenance_period_description']);
$infoBox->addContentRow($infoMaint->draw());
$infoBox->addContentRow($condMaint->draw());
$infoBox->addContentRow($divMid->draw());
$infoBox->addButton($saveButton)->addButton($cancelButton);
	
	EventManager::attachActionResponse($infoBox->draw(), 'html');
?>