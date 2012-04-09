<?php
$Statuses = Doctrine_Core::getTable('RentalAvailability');
if (isset($_GET['sID'])){
	$Status = $Statuses->find((int) $_GET['sID']);
	$boxHeading = sysLanguage::get('WINDOW_HEADING_EDIT_STATUS');
	$boxIntro = sysLanguage::get('WINDOW_EDIT_STATUS_INTRO');
}else{
	$Status = $Statuses->getRecord();
	$boxHeading = sysLanguage::get('WINDOW_HEADING_NEW_STATUS');
	$boxIntro = sysLanguage::get('WINDOW_NEW_STATUS_INTRO');
}

$infoBox = htmlBase::newElement('infobox');
$infoBox->setHeader('<b>' . $boxHeading . '</b>');
$infoBox->setButtonBarLocation('top');

$saveButton = htmlBase::newElement('button')->addClass('saveButton')->usePreset('save');
$cancelButton = htmlBase::newElement('button')->addClass('cancelButton')->usePreset('cancel');

$infoBox->addButton($saveButton)->addButton($cancelButton);

$infoBox->addContentRow($boxIntro);

$nameInputs = '';
foreach(sysLanguage::getLanguages() as $lInfo){
	$htmlInput = htmlBase::newElement('input')
		->setName('name[' . $lInfo['id'] . ']');
	if (!empty($Status->RentalAvailabilityDescription[$lInfo['id']]->name)){
		$htmlInput->val($Status->RentalAvailabilityDescription[$lInfo['id']]->name);
	}

	$nameInputs .= '<br>' . $lInfo['showName']('&nbsp;') . ': ' . $htmlInput->draw();
}
$infoBox->addContentRow(sysLanguage::get('TEXT_ENTRY_AVAILABILITY_NAME') . $nameInputs);

$infoBox->addContentRow(htmlBase::newElement('input')->setName('ratio')->setLabel(sysLanguage::get('TEXT_ENTRY_AVAILABILITY_RATIO'))->setLabelSeparator('<br>')->setLabelPosition('before')->val($Status->ratio)->draw());

EventManager::notify('RentalAvailabilityEditWindowBeforeDraw', $infoBox, $Package);

EventManager::attachActionResponse($infoBox->draw(), 'html');
?>