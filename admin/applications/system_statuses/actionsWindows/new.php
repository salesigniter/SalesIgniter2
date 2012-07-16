<?php
$SystemStatuses = Doctrine_Core::getTable('SystemStatuses');
if (isset($_GET['status_id'])){
	$Status = $SystemStatuses->find((int)$_GET['status_id']);
	$boxHeading = sysLanguage::get('TEXT_INFO_HEADING_EDIT_STATUS');
	$boxIntro = sysLanguage::get('TEXT_INFO_EDIT_INTRO');
}
else {
	$Status = $SystemStatuses->getRecord();
	$boxHeading = sysLanguage::get('TEXT_INFO_HEADING_NEW_STATUS');
	$boxIntro = sysLanguage::get('TEXT_INFO_INSERT_INTRO');
}

$Fieldset = htmlBase::newFieldsetFormBlock();
$Fieldset->setLegend($boxHeading);

$statusInputs = array();
foreach(sysLanguage::getLanguages() as $lInfo){
	$statusInputs[] = array(
		htmlBase::newInput()
		->setName('status_name[' . $lInfo['id'] . ']')
		->setLabel($lInfo['showName']('&nbsp;'))
		->setLabelPosition('below')
		->setValue($Status->Description[$lInfo['id']]->status_name)
	);
}

$Fieldset->addBlock('status', sysLanguage::get('TEXT_INFO_STATUS_NAME'), $statusInputs);

$typesGroup = htmlBase::newCheckboxGroup()
	->setName('status_types')
	->addInput(htmlBase::newCheckbox()->setValue('sales')->setLabel('Sales & Payments')->setLabelPosition('right'))
	->addInput(htmlBase::newCheckbox()->setValue('inventory')->setLabel('Products Inventory')->setLabelPosition('right'));
$Fieldset->addBlock('uses', 'Used In:', array(
	array($typesGroup)
));

$Infobox = htmlBase::newActionWindow()
	->addButton(htmlBase::newElement('button')->addClass('saveButton')->usePreset('save'))
	->addButton(htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel'))
	->setContent($Fieldset);

EventManager::attachActionResponse($Infobox->draw(), 'html');
