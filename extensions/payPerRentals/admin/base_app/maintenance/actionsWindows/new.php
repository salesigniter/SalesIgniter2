<?php

$QMaintenancePeriods = Doctrine_Query::create()
	->from('PayPerRentalMaintenancePeriods')
	->where('maintenance_period_id = ?', $_GET['type'])
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

if ($QMaintenancePeriods[0]['is_repair'] == '1') {
	$maintenanceRel = Doctrine_Core::getTable('PayPerRentalMaintenanceRepairs')->find($_GET['mID']);

	$infoBox = htmlBase::newElement('infobox');
	$infoBox->setButtonBarLocation('top');
	if (isset($_GET['mID'])) {
		$infoBox->setHeader('<b>Edit Repair</b>');
	}


$commentsMaint = htmlBase::newElement('div');
$commentsMaint->html('');

$infoMaint = htmlBase::newElement('textarea')
	->attr('rows', '5')
	->attr('cols','20')
	->addClass('makeFCK')
	->attr('name','comments');
$infoMaint->html($maintenanceRel->comments);

$priceHtml = htmlBase::newElement('input')
	->setLabel('Labour Price')
	->setLabelPosition('before')
	->setName('price')
	->setValue($maintenanceRel->price);


$Qcheck = Doctrine_Query::create()
	->select('MAX(pay_per_rental_maintenance_repairs_parts_id) as nextId')
	->from('PayPerRentalMaintenanceRepairParts')
	->execute(array(), Doctrine_Core::HYDRATE_ARRAY);

$TableParts = htmlBase::newElement('table')
	->setCellPadding(3)
	->setCellSpacing(0)
	->addClass('ui-widget ui-widget-content PartsTable')
	->css(array(
		'width' => '100%'
	))
	->attr('data-next_id', $Qcheck[0]['nextId'] + 1)
	->attr('language_id', Session::get('languages_id'));

$TableParts->addHeaderRow(array(
		'addCls' => 'ui-state-hover PartsTableHeader',
		'columns' => array(
			array('text' => '<div style="float:left;width:100px;">' .sysLanguage::get('TABLE_HEADING_PRODUCT_PART_NAME').'</div>'.
				'<div style="float:left;width:100px;">'.sysLanguage::get('TABLE_HEADING_PART_PRICE').'</div>'.
				'<div style="float:left;width:40px;">'.htmlBase::newElement('icon')->setType('insert')->addClass('insertIconHidden')->draw().
				'</div><br style="clear:both"/>'
			)
		)
	));

$deleteIcon = htmlBase::newElement('icon')->setType('delete')->addClass('deleteIconHidden')->draw();
$hiddenList = htmlBase::newElement('list')
	->addClass('hiddenList');

$TableParts->addBodyRow(array(
		'columns' => array(
			array('align' => 'center', 'text' => $hiddenList->draw(),'addCls' => 'parts')
		)
	));

$saveButton = htmlBase::newElement('button')
	->addClass('saveButton')
	->usePreset('save');
$cancelButton = htmlBase::newElement('button')
	->addClass('cancelButton')
	->usePreset('cancel');

//$infoBox->addContentRow('Comments of maintenance: '.$maintenanceMain->comments);
$infoBox->addContentRow($priceHtml->draw());
$infoBox->addContentRow($TableParts->draw());
$infoBox->addContentRow($infoMaint->draw());

$infoBox->addButton($saveButton)->addButton($cancelButton);

}else{
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
}
	EventManager::attachActionResponse($infoBox->draw(), 'html');
?>